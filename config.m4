dnl config.m4 for extension jq

dnl Check PHP version:
AC_MSG_CHECKING(PHP version)
if test ! -z "$phpincludedir"; then
    PHP_VERSION=`grep 'PHP_VERSION ' $phpincludedir/main/php_version.h | sed -e 's/.*"\([[0-9\.]]*\)".*/\1/g' 2>/dev/null`
elif test ! -z "$PHP_CONFIG"; then
    PHP_VERSION=`$PHP_CONFIG --version 2>/dev/null`
fi

if test x"$PHP_VERSION" = "x"; then
    AC_MSG_WARN([none])
else
    PHP_MAJOR_VERSION=`echo $PHP_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\1/g' 2>/dev/null`
    PHP_MINOR_VERSION=`echo $PHP_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\2/g' 2>/dev/null`
    PHP_RELEASE_VERSION=`echo $PHP_VERSION | sed -e 's/\([[0-9]]*\)\.\([[0-9]]*\)\.\([[0-9]]*\).*/\3/g' 2>/dev/null`
    AC_MSG_RESULT([$PHP_VERSION])
fi

if test $PHP_MAJOR_VERSION -lt 5; then
    AC_MSG_ERROR([need at least PHP 5.3 or newer])
fi

if test $PHP_MAJOR_VERSION -eq 5 -a $PHP_MINOR_VERSION -lt 3; then
    AC_MSG_ERROR([need at least PHP 5.3 or newer])
fi

dnl jq Extension
PHP_ARG_WITH([jq],
    [for jq support],
    [AS_HELP_STRING([--with-jq], [Include jq support])])

dnl coverage
PHP_ARG_ENABLE([jq-coverage],
    [whether to enable jq coverage support],
    [AS_HELP_STRING([--enable-jq-coverage], [Enable coverage support])],
    [no],
    [no])

if test "$PHP_JQ" != "no"; then

    dnl check with-path
    SEARCH_PATH="/usr/local /usr"
    SEARCH_FOR="/include/jq.h"
    if test -r $PHP_JQ/$SEARCH_FOR; then
      JQ_DIR=$PHP_JQ
    else
      AC_MSG_CHECKING([for jq files in default path])
      for i in $SEARCH_PATH ; do
        if test -r $i/$SEARCH_FOR; then
          JQ_DIR=$i
          AC_MSG_RESULT(found in $i)
        fi
      done
    fi

    if test -z "$JQ_DIR"; then
      AC_MSG_RESULT([not found])
      AC_MSG_ERROR([Please reinstall the jq development files])
    fi

    dnl add include path
    PHP_ADD_INCLUDE($JQ_DIR/include)

    dnl check for lib and symbol presence
    LIBNAME=jq
    LIBSYMBOL=jq_init

    PHP_CHECK_LIBRARY($LIBNAME, $LIBSYMBOL,
    [
      PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $JQ_DIR/$PHP_LIBDIR, JQ_SHARED_LIBADD)
      AC_DEFINE(HAVE_JQ_FEATURE, 1, [ ])
    ],[
      AC_MSG_ERROR([FEATURE not supported by your jq library.])
    ],[
      -L$JQ_DIR/$PHP_LIBDIR
    ])

    PHP_SUBST(JQ_SHARED_LIBADD)

    AC_DEFINE(HAVE_JQ, 1, [ Have jq support ])

    PHP_NEW_EXTENSION(jq, jq.c, $ext_shared)

    if test "$PHP_JQ_COVERAGE" != "no"; then
        EXTRA_CFLAGS="--coverage"
        PHP_SUBST(EXTRA_CFLAGS)
    fi
fi
