#ifdef HAVE_CONFIG_H
#    include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "ext/standard/php_array.h"
#include "Zend/zend_exceptions.h"

#include "php_jq.h"

#include "jq.h"

ZEND_DECLARE_MODULE_GLOBALS(jq)

#define PHP_JQ_NS "Jq"

zend_class_entry *php_jq_ce;
static zend_object_handlers php_jq_handlers;

typedef struct {
    jq_state *jq;
    jv json;
    int loaded;
    zend_object std;
} php_jq_t;

#define PHP_JQ_METHOD(name) \
    ZEND_METHOD(Jq, name)
#define PHP_JQ_ME(name, arg_info, flags) \
    ZEND_ME(Jq, name, arg_info, flags)
#define PHP_JQ_MALIAS(alias, name, arg_info, flags) \
    ZEND_MALIAS(Jq, alias, name, arg_info, flags)
#define PHP_JQ_CONST_LONG(name, value) \
    zend_declare_class_constant_long( \
        php_jq_ce, ZEND_STRS(#name)-1, value)
#define PHP_JQ_EXCEPTION(_code, ...) \
    zend_throw_exception_ex(NULL, _code, __VA_ARGS__)


ZEND_INI_BEGIN()
    STD_ZEND_INI_ENTRY("jq.display_errors", "1",
                       ZEND_INI_ALL, OnUpdateBool, display_errors,
                       zend_jq_globals, jq_globals)
ZEND_INI_END()

ZEND_BEGIN_ARG_INFO_EX(arginfo_jq___construct, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_jq_load, 0, 0, 1)
    ZEND_ARG_INFO(0, string)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_jq_loadfile, 0, 0, 1)
    ZEND_ARG_INFO(0, filename)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_jq_filter, 0, 0, 1)
    ZEND_ARG_INFO(0, string)
    ZEND_ARG_INFO(0, flags)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_jq_parse, 0, 0, 2)
    ZEND_ARG_INFO(0, string)
    ZEND_ARG_INFO(0, filter)
    ZEND_ARG_INFO(0, flags)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_jq_parsefile, 0, 0, 2)
    ZEND_ARG_INFO(0, filename)
    ZEND_ARG_INFO(0, filter)
    ZEND_ARG_INFO(0, flags)
ZEND_END_ARG_INFO()

#define PHP_JQ_OBJ(self, obj) \
    self = (php_jq_t *)((char *)Z_OBJ_P(obj) - XtOffsetOf(php_jq_t, std))

enum {
    JQ_OPT_RAW = 1
};

static void
php_jq_err_cb(void *data, jv err)
{
    if (jv_is_valid(err) && PHP_JQ_G(display_errors)) {
        jv dump = jv_dump_string(jv_copy(err), 0);
        if (jv_is_valid(dump)) {
            PHP_JQ_ERR(E_WARNING, "%s", jv_string_value(dump));
        }
        jv_free(dump);
    }
}

static jq_state *
php_jq_init(void)
{
    jq_state *jq = jq_init();

    if (jq) {
        jq_set_error_cb(jq, php_jq_err_cb, NULL);
    }

    return jq;
}

PHP_JQ_METHOD(__construct)
{
    zval *options = NULL;
    php_jq_t *intern;

    if (zend_parse_parameters_none() == FAILURE) {
        RETURN_FALSE;
    }

    PHP_JQ_OBJ(intern, getThis());

    intern->jq = php_jq_init();

    if (!intern->jq) {
        PHP_JQ_EXCEPTION(0, "jq object has not been correctly initialized "
                         "by its constructor");
        RETURN_FALSE;
    }
}

PHP_JQ_METHOD(load)
{
    char *str;
    size_t str_len;
    php_jq_t *intern;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "s",
                              &str, &str_len) == FAILURE) {
        RETURN_FALSE;
    }

    if (str_len == 0) {
        RETURN_FALSE;
    }

    PHP_JQ_OBJ(intern, getThis());

    if (intern->loaded) {
        jv_free(intern->json);
    }
    intern->loaded = 0;

    intern->json = jv_parse_sized(str, str_len);
    if (!jv_is_valid(intern->json)) {
        jv_free(intern->json);
        if (PHP_JQ_G(display_errors)) {
            PHP_JQ_ERR(E_WARNING, "load json parse error");
        }
        RETURN_FALSE;
    }

    intern->loaded = 1;

    RETURN_TRUE;
}

PHP_JQ_METHOD(loadFile)
{
    char *filename;
    size_t filename_len;
    zend_string *contents = NULL;
    int len;
    long maxlen = PHP_STREAM_COPY_ALL;
    php_stream *stream;
    php_jq_t *intern;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "s",
                              &filename, &filename_len) == FAILURE) {
        RETURN_FALSE;
    }

    if (filename_len == 0) {
        RETURN_FALSE;
    }

    PHP_JQ_OBJ(intern, getThis());

    if (intern->loaded) {
        jv_free(intern->json);
    }
    intern->loaded = 0;

    /* read file */
    stream = php_stream_open_wrapper_ex(filename, "rb",
                                        REPORT_ERRORS, NULL, NULL);
    if (!stream) {
        RETURN_FALSE;
    }

    contents = php_stream_copy_to_mem(stream, maxlen, 0);
    if (contents) {
        if (ZSTR_LEN(contents) > 0) {
            intern->json = jv_parse_sized(ZSTR_VAL(contents),
                                          ZSTR_LEN(contents));
            if (jv_is_valid(intern->json)) {
                intern->loaded = 1;
                RETVAL_TRUE;
            } else {
                if (PHP_JQ_G(display_errors)) {
                    PHP_JQ_ERR(E_WARNING, "load json parse error");
                }
                jv_free(intern->json);
                RETVAL_FALSE;
            }
        } else {
            RETVAL_FALSE;
        }
        zend_string_release(contents);
    } else {
        RETVAL_FALSE;
    }

    php_stream_close(stream);
}

static void php_jv_dump(zval **return_value, jv x);
static void
php_jv_dump(zval **return_value, jv x)
{
    switch (jv_get_kind(x)) {
        default:
        case JV_KIND_INVALID:
            if (PHP_JQ_G(display_errors)) {
                PHP_JQ_ERR(E_WARNING, "json parse error");
            }
            break;
        case JV_KIND_NULL:
            ZVAL_NULL(*return_value);
            break;
        case JV_KIND_FALSE:
            ZVAL_BOOL(*return_value, 0);
            break;
        case JV_KIND_TRUE:
            ZVAL_BOOL(*return_value, 1);
            break;
        case JV_KIND_NUMBER: {
            double d = jv_number_value(x);
            if (d != d || d > LONG_MAX || d < LONG_MIN) {
                ZVAL_DOUBLE(*return_value, jv_number_value(x));
            } else if (d == (long)d) {
                ZVAL_LONG(*return_value, (long)d);
            } else {
                ZVAL_DOUBLE(*return_value, jv_number_value(x));
            }
            break;
        }
        case JV_KIND_STRING: {
            int len = jv_string_length_bytes(jv_copy(x));
            if (len <= 0) {
                ZVAL_EMPTY_STRING(*return_value);
            } else {
                ZVAL_STRINGL(*return_value, jv_string_value(x), len);
            }
            break;
        }
        case JV_KIND_ARRAY: {
            int i, len = jv_array_length(jv_copy(x));
            array_init(*return_value);
            if (len == 0) {
                break;
            }

            for (i = 0; i < len; i++) {
                jv value = jv_array_get(jv_copy(x), i);
                if (jv_is_valid(value)) {
                    zval zv, *p = &zv;
                    php_jv_dump(&p, value);
                    zend_hash_next_index_insert_new(Z_ARRVAL_P(*return_value),
                                                    &zv);
                } else {
                    jv_free(value);
                }
            }
            break;
        }
        case JV_KIND_OBJECT: {
            int i = 0, first = 1;
            array_init(*return_value);
            if (jv_object_length(jv_copy(x)) == 0) {
                break;
            }

            while (1) {
                jv key, value;
                zval zv, *p = &zv;
                zend_string *jv_key;

                if (first) {
                    i = jv_object_iter(x);
                } else {
                    i = jv_object_iter_next(x, i);
                }
                if (!jv_object_iter_valid(x, i)) {
                    break;
                }

                key = jv_object_iter_key(x, i);
                value = jv_object_iter_value(x, i);

                php_jv_dump(&p, value);

                jv_key = zend_string_init(jv_string_value(key),
                                          jv_string_length_bytes(jv_copy(key)),
                                          0);
                zend_symtable_update(Z_ARRVAL_P(*return_value), jv_key, &zv);
                zend_string_release(jv_key);

                first = 0;
                jv_free(key);
            }
        }
    }

    jv_free(x);
}

static void
php_jq_filter(zval **return_value, jq_state *jq, jv json, int flags)
{
    jv result;

    jq_start(jq, jv_copy(json), 0);

    if (jv_is_valid(result = jq_next(jq))) {
        int multiple = 0;
        while (1) {
            zval zv, *p = &zv;
            if (flags == JQ_OPT_RAW) {
                if (jv_get_kind(result) == JV_KIND_STRING) {
                    ZVAL_STRING(&zv, jv_string_value(result));
                } else {
                    jv dump = jv_dump_string(result, 0);
                    if (jv_is_valid(dump)) {
                        ZVAL_STRING(&zv, jv_string_value(dump));
                    }
                    jv_free(dump);
                }
            } else {
                php_jv_dump(&p, result);
            }

            if (!jv_is_valid(result = jq_next(jq))) {
                if (multiple) {
                    zend_hash_next_index_insert_new(Z_ARRVAL_P(*return_value),
                                                    &zv);
                } else {
                    ZVAL_ZVAL(*return_value, &zv, 1, 1);
                }
                break;
            }

            if (!multiple) {
                multiple = 1;
                array_init(*return_value);
            }

            zend_hash_next_index_insert_new(Z_ARRVAL_P(*return_value), &zv);
        }
    } else {
        jv_free(result);
        if (PHP_JQ_G(display_errors)) {
            PHP_JQ_ERR(E_WARNING, "filter parse error");
        }
        ZVAL_BOOL(*return_value, 0);
    }
}

PHP_JQ_METHOD(filter)
{
    char *str;
    size_t str_len;
    zend_long flags = 0;
    jv result;
    php_jq_t *intern;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "s|l",
                              &str, &str_len, &flags) == FAILURE) {
        RETURN_FALSE;
    }

    if (str_len == 0) {
        RETURN_FALSE;
    }

    PHP_JQ_OBJ(intern, getThis());

    if (!intern->loaded) {
        if (PHP_JQ_G(display_errors)) {
            PHP_JQ_ERR(E_WARNING, "undefined load json");
        }
        RETURN_FALSE;
    }

    str[str_len] = 0;

    if (!jq_compile(intern->jq, str)) {
        if (PHP_JQ_G(display_errors)) {
            PHP_JQ_ERR(E_WARNING, "filter compile error");
        }
        RETURN_FALSE;
    }

    php_jq_filter(&return_value, intern->jq, intern->json, flags);
}

static void
php_jq_exec(zval **return_value,
            char *str, int str_len, char *filter, int filter_len,
            long flags)
{
    jq_state *jq = php_jq_init();
    jv json, result;

    if (!jq) {
        PHP_JQ_EXCEPTION(0, "jq object has not been correctly initialized "
                         "by its constructor");
        ZVAL_BOOL(*return_value, 0);
        return;
    }

    json = jv_parse_sized(str, str_len);
    if (!jv_is_valid(json)) {
        jv_free(json);
        jq_teardown(&jq);
        if (PHP_JQ_G(display_errors)) {
            PHP_JQ_ERR(E_WARNING, "load json parse error");
        }
        ZVAL_BOOL(*return_value, 0);
        return;
    }

    filter[filter_len] = 0;

    if (!jq_compile(jq, filter)) {
        jv_free(json);
        jq_teardown(&jq);
        if (PHP_JQ_G(display_errors)) {
            PHP_JQ_ERR(E_WARNING, "filter compile error");
        }
        ZVAL_BOOL(*return_value, 0);
        return;
    }

    php_jq_filter(return_value, jq, json, flags);

    jv_free(json);
    jq_teardown(&jq);
}

PHP_JQ_METHOD(parse)
{
    char *str, *filter;
    size_t str_len, filter_len;
    zend_long flags = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ss|l",
                              &str, &str_len, &filter, &filter_len,
                              &flags) == FAILURE) {
        RETURN_FALSE;
    }

    if (str_len == 0 || filter_len == 0) {
        RETURN_FALSE;
    }

    php_jq_exec(&return_value, str, str_len,
                filter, filter_len, flags);
}

PHP_JQ_METHOD(parseFile)
{
    char *filename, *filter;
    size_t filename_len, filter_len;
    zend_long flags = 0;
    zend_string *contents = NULL;
    int len;
    long maxlen = PHP_STREAM_COPY_ALL;
    php_stream *stream;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "ss|l",
                              &filename, &filename_len, &filter, &filter_len,
                              &flags) == FAILURE) {
        RETURN_FALSE;
    }

    if (filename_len == 0 || filter_len == 0) {
        RETURN_FALSE;
    }

    /* read file */
    stream = php_stream_open_wrapper_ex(filename, "rb",
                                        REPORT_ERRORS, NULL, NULL);
    if (!stream) {
        RETURN_FALSE;
    }

    contents = php_stream_copy_to_mem(stream, maxlen, 0);
    if (contents) {
        if (ZSTR_LEN(contents) > 0) {
            php_jq_exec(&return_value, ZSTR_VAL(contents), ZSTR_LEN(contents),
                        filter, filter_len, flags);
        } else {
            RETVAL_FALSE;
        }
        zend_string_release(contents);
    } else {
        RETVAL_FALSE;
    }

    php_stream_close(stream);
}

static zend_function_entry php_jq_methods[] = {
    PHP_JQ_ME(__construct, arginfo_jq___construct,
              ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
    PHP_JQ_ME(load, arginfo_jq_load, ZEND_ACC_PUBLIC)
    PHP_JQ_MALIAS(loadString, load, arginfo_jq_load, ZEND_ACC_PUBLIC)
    PHP_JQ_ME(loadFile, arginfo_jq_loadfile, ZEND_ACC_PUBLIC)
    PHP_JQ_ME(filter, arginfo_jq_filter, ZEND_ACC_PUBLIC)
    PHP_JQ_ME(parse, arginfo_jq_parse, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_JQ_MALIAS(parseString, parse, arginfo_jq_parse,
                  ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_JQ_ME(parseFile, arginfo_jq_parsefile,
              ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    ZEND_FE_END
};

static void
php_jq_free_storage(zend_object *std)
{
    php_jq_t *intern;
    intern = (php_jq_t *)((char *)std - XtOffsetOf(php_jq_t, std));
    if (!intern) {
        return;
    }

    if (intern->loaded) {
        jv_free(intern->json);
    }

    if (intern->jq) {
        jq_teardown(&intern->jq);
    }

    zend_object_std_dtor(std);
}

static zend_object *
php_jq_new_ex(zend_class_entry *ce, php_jq_t **ptr)
{
    php_jq_t *intern;

    intern = ecalloc(1, sizeof(php_jq_t)+zend_object_properties_size(ce));
    if (ptr) {
        *ptr = intern;
    }

    zend_object_std_init(&intern->std, ce);
    object_properties_init(&intern->std, ce);
    rebuild_object_properties(&intern->std);

    intern->std.handlers = &php_jq_handlers;

    intern->jq = NULL;
    intern->loaded = 0;

    return &intern->std;
}

static zend_object *
php_jq_new(zend_class_entry *ce)
{
    return php_jq_new_ex(ce, NULL);
}

static zend_class_entry *zend_jq_exception_ce;

#define PHP_JQ_REGISTER_EXCEPTION_CLASS(name, class_name) \
    { \
        zend_class_entry ce; \
        INIT_NS_CLASS_ENTRY(ce, PHP_JQ_NS, #class_name, NULL); \
        zend_jq_##name##_ce = zend_register_internal_class_ex(&ce, zend_ce_error); \
    }

static void
jq_init_globals(zend_jq_globals *jq_globals)
{
    jq_globals->display_errors = 1;
}

ZEND_MINIT_FUNCTION(jq)
{
    zend_class_entry ce;

    /* class register */
    INIT_CLASS_ENTRY(ce, "Jq", php_jq_methods);

    ce.create_object = php_jq_new;

    php_jq_ce = zend_register_internal_class(&ce);
    if (php_jq_ce == NULL) {
        return FAILURE;
    }

    memcpy(&php_jq_handlers, zend_get_std_object_handlers(),
           sizeof(zend_object_handlers));

    php_jq_handlers.offset = XtOffsetOf(php_jq_t, std);
    php_jq_handlers.dtor_obj = zend_objects_destroy_object;
    php_jq_handlers.free_obj = php_jq_free_storage;
    php_jq_handlers.clone_obj = NULL;

    PHP_JQ_REGISTER_EXCEPTION_CLASS(exception, Exception);

    /* class constant */
    PHP_JQ_CONST_LONG(RAW, JQ_OPT_RAW);

    /* ini */
    ZEND_INIT_MODULE_GLOBALS(jq, jq_init_globals, NULL);
    REGISTER_INI_ENTRIES();

    return SUCCESS;
}

ZEND_MSHUTDOWN_FUNCTION(jq)
{
#if defined(ZTS) && defined(COMPILE_DL_JQ)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif

    UNREGISTER_INI_ENTRIES();
    return SUCCESS;
}

ZEND_MINFO_FUNCTION(jq)
{
    php_info_print_table_start();
    php_info_print_table_row(2, "jq support", "enabled");
    php_info_print_table_row(2, "Extension Version", PHP_JQ_EXT_VERSION);
    php_info_print_table_end();

    DISPLAY_INI_ENTRIES();
}

zend_module_entry jq_module_entry = {
    STANDARD_MODULE_HEADER,
    "jq",
    NULL,
    ZEND_MINIT(jq),
    ZEND_MSHUTDOWN(jq),
    NULL,
    NULL,
    ZEND_MINFO(jq),
    PHP_JQ_EXT_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#if COMPILE_DL_JQ
ZEND_GET_MODULE(jq)
#endif
