# Copyright (C) 2008 Alexander Logvinov <ports@logvinov.com> 
#   
# This file is free software; as a special exception the author gives 
# unlimited permission to copy and/or distribute it, with or without  
# modifications, as long as this notice is preserved. 
#  
# This program is distributed in the hope that it will be useful, but 
# WITHOUT ANY WARRANTY, to the extent permitted by law; without even the 
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 

AC_INIT(src)
AM_CONFIG_HEADER([config.h])
AM_INIT_AUTOMAKE([libfetion], [0.98.4], gradetwo@gmail.com)

AC_PROG_CC
AM_DISABLE_STATIC
AC_LIBTOOL_DLOPEN
AC_PROG_LIBTOOL

PKG_CHECK_MODULES([PURPLE],[purple >= 2.0.0],,AC_MSG_ERROR([
*** libpurple 2.0+ is required to build.]))
AC_SUBST(PURPLE_CFLAGS)
AC_SUBST(PURPLE_LIBS)
PURPLE_LIBDIR=`pkg-config --variable=libdir purple`
PURPLE_DATADIR=`pkg-config --variable=datadir purple`
AC_SUBST(PURPLE_LIBDIR)
AC_SUBST(PURPLE_DATADIR)

AC_OUTPUT([Makefile src/Makefile])
