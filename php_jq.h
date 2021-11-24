#ifndef PHP_JQ_H
#define PHP_JQ_H

#define PHP_JQ_EXT_VERSION "0.2.0"

extern zend_module_entry jq_module_entry;
#define phpext_jq_ptr &jq_module_entry

#ifdef PHP_WIN32
#    define PHP_JQ_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#    define PHP_JQ_API __attribute__ ((visibility("default")))
#else
#    define PHP_JQ_API
#endif

#ifdef ZTS
#    include "TSRM.h"
#endif

ZEND_BEGIN_MODULE_GLOBALS(jq)
    zend_bool display_errors;
ZEND_END_MODULE_GLOBALS(jq)

#ifdef ZTS
#    define PHP_JQ_G(v) TSRMG(jq_globals_id, zend_jq_globals *, v)
#else
#    define PHP_JQ_G(v) (jq_globals.v)
#endif

#define PHP_JQ_ERR(e, ...) php_error_docref(NULL, e, __VA_ARGS__)

#ifndef ZED_FE_END
#define ZEND_FE_END { NULL, NULL, NULL, 0, 0 }
#endif

#ifndef ZVAL_COPY_VALUE
#define ZVAL_COPY_VALUE(z, v)      \
    do {                           \
        (z)->value = (v)->value;   \
        Z_TYPE_P(z) = Z_TYPE_P(v); \
    } while (0)
#endif

#ifndef INIT_PZVAL_COPY
#define INIT_PZVAL_COPY(z, v)   \
    do {                        \
        ZVAL_COPY_VALUE(z, v);  \
        Z_SET_REFCOUNT_P(z, 1); \
        Z_UNSET_ISREF_P(z);     \
    } while (0)
#endif

#endif  /* PHP_JQ_H */
