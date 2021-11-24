#ifdef HAVE_CONFIG_H
#    include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "ext/standard/php_array.h"
#include "Zend/zend_exceptions.h"
#include "Zend/zend_interfaces.h"

#include "php_jq.h"

#include "jq.h"

ZEND_DECLARE_MODULE_GLOBALS(jq)

/* For compatibility with older PHP versions */
#ifndef ZEND_PARSE_PARAMETERS_NONE
#define ZEND_PARSE_PARAMETERS_NONE()  \
    ZEND_PARSE_PARAMETERS_START(0, 0) \
    ZEND_PARSE_PARAMETERS_END()
#endif
#if PHP_VERSION_ID < 70300
static void *zend_object_alloc(size_t obj_size, zend_class_entry *class_type)
{
    void *obj = emalloc(obj_size + zend_object_properties_size(class_type));
    memset(obj, 0, obj_size - sizeof(zval));
    return obj;
}
#endif

#define PHP_JQ_NS "Jq"

static zend_class_entry *zend_jq_exception_ce;

zend_class_entry *php_jq_ce;
static zend_object_handlers php_jq_handlers;

typedef struct {
    zend_object std;
} php_jq_t;

#define PHP_JQ_METHOD(name) \
    ZEND_METHOD(Jq, name)
#define PHP_JQ_ME(name, arg_info, flags) \
    ZEND_ME(Jq, name, arg_info, flags)
#define PHP_JQ_CONST_LONG(name, value) \
    zend_declare_class_constant_long( \
        php_jq_ce, ZEND_STRS(#name)-1, value)


ZEND_INI_BEGIN()
    STD_ZEND_INI_ENTRY("jq.display_errors", "1",
                       ZEND_INI_ALL, OnUpdateBool, display_errors,
                       zend_jq_globals, jq_globals)
ZEND_INI_END()

ZEND_BEGIN_ARG_INFO_EX(arginfo_jq___construct, 0, 0, 0)
ZEND_END_ARG_INFO()

enum {
    JQ_OPT_RAW = 1
};

static jq_state *php_jq_init(void);

PHP_JQ_METHOD(__construct)
{
    ZEND_PARSE_PARAMETERS_NONE();
}

static void php_jv_dump(zval **return_value, jv x)
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

static void
php_jq_exec(zval **return_value,
            char *str, int str_len, char *filter, int filter_len,
            long flags)
{
    jq_state *jq = php_jq_init();
    jv json, result;

    if (!jq) {
        zend_throw_error(zend_jq_exception_ce,
                         "jq object has not been correctly initialized "
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

static zend_function_entry php_jq_methods[] = {
    PHP_JQ_ME(__construct, arginfo_jq___construct,
              ZEND_ACC_PRIVATE | ZEND_ACC_CTOR)
    ZEND_FE_END
};

static void
php_jq_free_storage(zend_object *std)
{
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

    return &intern->std;
}

static zend_object *
php_jq_new(zend_class_entry *ce)
{
    return php_jq_new_ex(ce, NULL);
}

static zend_class_entry *zend_jq_input_ce;
static zend_object_handlers zend_jq_input_handlers;
typedef struct {
    zend_object std;
} zend_jq_input;

static zend_class_entry *zend_jq_executor_ce;
static zend_object_handlers zend_jq_executor_handlers;
typedef struct {
    jq_state *state;
    jv json;
    int loaded;
    zend_object std;
} zend_jq_executor;

static zend_class_entry *zend_jq_run_ce;
static zend_object_handlers zend_jq_run_handlers;
typedef struct {
    zend_object std;
} zend_jq_run;

static void php_jq_err_cb(void *data, jv err)
{
    if (jv_is_valid(err) && PHP_JQ_G(display_errors)) {
        jv dump = jv_dump_string(jv_copy(err), 0);
        if (jv_is_valid(dump)) {
            PHP_JQ_ERR(E_WARNING, "%s", jv_string_value(dump));
        }
        jv_free(dump);
    }
}

static jq_state *php_jq_init(void)
{
    jq_state *jq = jq_init();

    if (jq) {
        jq_set_error_cb(jq, php_jq_err_cb, NULL);
    }

    return jq;
}

static int php_jq_load_file(jv *var, const char *file)
{
    long maxlen = PHP_STREAM_COPY_ALL;
    php_stream *stream;
    zend_string *contents;

    stream = php_stream_open_wrapper_ex(file, "rb", REPORT_ERRORS, NULL, NULL);
    if (!stream) {
        return FAILURE;
    }

    contents = php_stream_copy_to_mem(stream, maxlen, 0);
    if (!contents) {
        php_stream_close(stream);
        return FAILURE;
    }
    if (ZSTR_LEN(contents) == 0) {
        *var = jv_string_empty(0);
    } else {
        *var = jv_parse_sized(ZSTR_VAL(contents), ZSTR_LEN(contents));
    }

    zend_string_release(contents);
    php_stream_close(stream);

    return SUCCESS;
}

#ifndef ZEND_NS_MN
#define ZEND_NS_MN(ns, name) zim_##ns_##name
#endif
#ifndef ZEND_NS_ME
#define ZEND_NS_ME(ns, classname, name, arg_info, flags) ZEND_FENTRY(name, ZEND_NS_MN(ns, classname##_##name), arg_info, flags)
#endif
#ifndef ZEND_NS_METHOD
#define ZEND_NS_METHOD(ns, classname, name) ZEND_NAMED_FUNCTION(ZEND_NS_MN(ns, classname##_##name))
#endif

#define PHP_JQ_HANDLER(type, obj) (type *)((char *)obj - XtOffsetOf(type, std))
#define PHP_JQ_HANDLER_ZVAL(type, zval) PHP_JQ_HANDLER(type, Z_OBJ_P(zval))

/* Input */
ZEND_BEGIN_ARG_INFO(arginfo_jq_input_construct, 0)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Input, __construct)
{
    ZEND_PARSE_PARAMETERS_NONE();
}

ZEND_BEGIN_ARG_INFO(arginfo_jq_input_fromstring, 1)
    ZEND_ARG_INFO(0, text)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Input, fromString)
{
    char *text;
    size_t text_len;
    zend_jq_executor *retval;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(text, text_len)
    ZEND_PARSE_PARAMETERS_END();

    object_init_ex(return_value, zend_jq_executor_ce);
    retval = PHP_JQ_HANDLER_ZVAL(zend_jq_executor, return_value);

    retval->state = php_jq_init();

    retval->json = jv_parse_sized(text, text_len);
    if (!jv_is_valid(retval->json)) {
        jv_free(retval->json);
        zend_throw_error(zend_jq_exception_ce, "failed to load json.");
        RETURN_FALSE;
    }

    retval->loaded = 1;
}

ZEND_BEGIN_ARG_INFO(arginfo_jq_input_fromfile, 1)
    ZEND_ARG_INFO(0, file)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Input, fromFile)
{
    char *file;
    size_t file_len;
    zend_jq_executor *retval;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STRING(file, file_len)
    ZEND_PARSE_PARAMETERS_END();

    object_init_ex(return_value, zend_jq_executor_ce);
    retval = PHP_JQ_HANDLER_ZVAL(zend_jq_executor, return_value);

    retval->state = php_jq_init();

    if (php_jq_load_file(&retval->json, file) != SUCCESS) {
        zend_throw_error(zend_jq_exception_ce, "failed to open file.");
        RETURN_FALSE;
    }

    if (!jv_is_valid(retval->json)) {
        jv_free(retval->json);
        zend_throw_error(zend_jq_exception_ce, "failed to load json.");
        RETURN_FALSE;
    }

    retval->loaded = 1;
}

static zend_object *zend_jq_input_new(zend_class_entry *class_type)
{
    zend_jq_input *intern;

    intern = zend_object_alloc(sizeof(zend_jq_input), class_type);

    zend_object_std_init(&intern->std, class_type);
    object_properties_init(&intern->std, class_type);

    intern->std.handlers = &zend_jq_input_handlers;

    return &intern->std;
}

static void zend_jq_input_free_storage(zend_object *object)
{
    zend_jq_input *intern;

    intern = PHP_JQ_HANDLER(zend_jq_input, object);
    if (intern) {
        ;
    }

    zend_object_std_dtor(object);
}

static const zend_function_entry zend_jq_input_methods[] = {
    ZEND_NS_ME(##PHP_JQ_NS, Input, __construct,
               arginfo_jq_input_construct, ZEND_ACC_PRIVATE)
    ZEND_NS_ME(##PHP_JQ_NS, Input, fromFile,
               arginfo_jq_input_fromfile, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
    ZEND_NS_ME(##PHP_JQ_NS, Input, fromString,
               arginfo_jq_input_fromstring, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
    ZEND_FE_END
};

/* Executor */
ZEND_BEGIN_ARG_INFO(arginfo_jq_executor_construct, 0)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Executor, __construct)
{
    ZEND_PARSE_PARAMETERS_NONE();
}

ZEND_BEGIN_ARG_INFO(arginfo_jq_executor_filter, 2)
    ZEND_ARG_INFO(0, filter)
    ZEND_ARG_INFO(0, flags)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Executor, filter)
{
    char *filter;
    size_t filter_len;
    zend_long flags = 0;
    zend_jq_executor *intern;

    ZEND_PARSE_PARAMETERS_START(1, 2)
        Z_PARAM_STRING(filter, filter_len)
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(flags)
    ZEND_PARSE_PARAMETERS_END();

    intern = PHP_JQ_HANDLER_ZVAL(zend_jq_executor, getThis());

    filter[filter_len] = 0;

    if (!jq_compile(intern->state, filter)) {
        zend_throw_error(zend_jq_exception_ce,
                         "failed to compile filter string.");
        RETURN_FALSE;
    }

    php_jq_filter(&return_value, intern->state, intern->json, flags);
}

static zend_object *zend_jq_executor_new(zend_class_entry *class_type)
{
    zend_jq_executor *intern;

    intern = zend_object_alloc(sizeof(zend_jq_executor), class_type);

    zend_object_std_init(&intern->std, class_type);
    object_properties_init(&intern->std, class_type);

    intern->std.handlers = &zend_jq_executor_handlers;

    intern->state = NULL;
    intern->loaded = 0;

    return &intern->std;
}

static void zend_jq_executor_free_storage(zend_object *object)
{
    zend_jq_executor *intern;

    intern = PHP_JQ_HANDLER(zend_jq_executor, object);
    if (intern) {
        if (intern->loaded) {
            jv_free(intern->json);
        }
        if (intern->state) {
            jq_teardown(&intern->state);
            intern->state = NULL;
        }
    }

    zend_object_std_dtor(object);
}

static const zend_function_entry zend_jq_executor_methods[] = {
    ZEND_NS_ME(##PHP_JQ_NS, Executor, __construct,
               arginfo_jq_executor_construct, ZEND_ACC_PRIVATE)
    ZEND_NS_ME(##PHP_JQ_NS, Executor, filter,
               arginfo_jq_executor_filter, ZEND_ACC_PUBLIC)
    ZEND_FE_END
};

/* Run */
ZEND_BEGIN_ARG_INFO(arginfo_jq_run_construct, 0)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Run, __construct)
{
    ZEND_PARSE_PARAMETERS_NONE();
}

ZEND_BEGIN_ARG_INFO(arginfo_jq_run_fromstring, 2)
    ZEND_ARG_INFO(0, text)
    ZEND_ARG_INFO(0, filter)
    ZEND_ARG_INFO(0, flags)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Run, fromString)
{
    char *filter, *text;
    jq_state *state;
    jv json;
    size_t filter_len, text_len;
    zend_long flags = 0;

    ZEND_PARSE_PARAMETERS_START(2, 3)
        Z_PARAM_STRING(text, text_len)
        Z_PARAM_STRING(filter, filter_len)
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(flags)
    ZEND_PARSE_PARAMETERS_END();

    json = jv_parse_sized(text, text_len);
    if (!jv_is_valid(json)) {
        jv_free(json);
        zend_throw_error(zend_jq_exception_ce, "failed to load json.");
        RETURN_FALSE;
    }

    filter[filter_len] = 0;

    state = php_jq_init();

    if (!jq_compile(state, filter)) {
        jv_free(json);
        jq_teardown(&state);
        zend_throw_error(zend_jq_exception_ce,
                         "failed to compile filter string.");
        RETURN_FALSE;
    }

    php_jq_filter(&return_value, state, json, flags);

    jv_free(json);
    jq_teardown(&state);
}

ZEND_BEGIN_ARG_INFO(arginfo_jq_run_fromfile, 2)
    ZEND_ARG_INFO(0, file)
    ZEND_ARG_INFO(0, filter)
    ZEND_ARG_INFO(0, flags)
ZEND_END_ARG_INFO()
ZEND_NS_METHOD(##PHP_JQ_NS, Run, fromFile)
{
    char *file, *filter;
    jq_state *state;
    jv json;
    size_t file_len, filter_len;
    zend_long flags = 0;

    ZEND_PARSE_PARAMETERS_START(2, 3)
        Z_PARAM_STRING(file, file_len)
        Z_PARAM_STRING(filter, filter_len)
        Z_PARAM_OPTIONAL
        Z_PARAM_LONG(flags)
    ZEND_PARSE_PARAMETERS_END();

    if (php_jq_load_file(&json, file) != SUCCESS) {
        zend_throw_error(zend_jq_exception_ce, "failed to open file.");
        RETURN_FALSE;
    }
    if (!jv_is_valid(json)) {
        jv_free(json);
        zend_throw_error(zend_jq_exception_ce, "failed to load json.");
        RETURN_FALSE;
    }

    state = php_jq_init();

    filter[filter_len] = 0;

    if (!jq_compile(state, filter)) {
        jv_free(json);
        jq_teardown(&state);
        zend_throw_error(zend_jq_exception_ce,
                         "failed to compile filter string.");
        RETURN_FALSE;
    }

    php_jq_filter(&return_value, state, json, flags);

    jv_free(json);
    jq_teardown(&state);
}

static zend_object *zend_jq_run_new(zend_class_entry *class_type)
{
    zend_jq_run *intern;

    intern = zend_object_alloc(sizeof(zend_jq_run), class_type);

    zend_object_std_init(&intern->std, class_type);
    object_properties_init(&intern->std, class_type);

    intern->std.handlers = &zend_jq_run_handlers;

    return &intern->std;
}

static void zend_jq_run_free_storage(zend_object *object)
{
    zend_object_std_dtor(object);
}

static const zend_function_entry zend_jq_run_methods[] = {
    ZEND_NS_ME(##PHP_JQ_NS, Run, __construct,
               arginfo_jq_run_construct, ZEND_ACC_PRIVATE)
    ZEND_NS_ME(##PHP_JQ_NS, Run, fromFile,
               arginfo_jq_run_fromfile, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
    ZEND_NS_ME(##PHP_JQ_NS, Run, fromString,
               arginfo_jq_run_fromstring, ZEND_ACC_PUBLIC|ZEND_ACC_STATIC)
    ZEND_FE_END
};

#define PHP_JQ_REGISTER_EXCEPTION_CLASS(name, class_name) \
    { \
        zend_class_entry ce; \
        INIT_NS_CLASS_ENTRY(ce, PHP_JQ_NS, #class_name, NULL); \
        zend_jq_##name##_ce = zend_register_internal_class_ex(&ce, zend_ce_error); \
    }
#define PHP_JQ_REGISTER_CLASS(name, class_name, flags) \
    { \
        zend_class_entry ce; \
        INIT_NS_CLASS_ENTRY(ce, PHP_JQ_NS, #class_name, zend_jq_##name##_methods); \
        zend_jq_##name##_ce = zend_register_internal_class_ex(&ce, NULL); \
        zend_jq_##name##_ce->ce_flags |= flags; \
        zend_jq_##name##_ce->create_object = zend_jq_##name##_new; \
        zend_jq_##name##_ce->serialize = zend_class_serialize_deny; \
        zend_jq_##name##_ce->unserialize = zend_class_unserialize_deny; \
        memcpy(&zend_jq_##name##_handlers, &std_object_handlers, sizeof(zend_object_handlers)); \
        zend_jq_##name##_handlers.offset = XtOffsetOf(zend_jq_##name, std); \
        zend_jq_##name##_handlers.free_obj = zend_jq_##name##_free_storage; \
        zend_jq_##name##_handlers.clone_obj = NULL; \
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
    PHP_JQ_REGISTER_CLASS(input, Input, ZEND_ACC_FINAL);
    PHP_JQ_REGISTER_CLASS(executor, Executor, ZEND_ACC_FINAL);
    PHP_JQ_REGISTER_CLASS(run, Run, ZEND_ACC_FINAL);

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
