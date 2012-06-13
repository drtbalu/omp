#!/bin/bash

#
# buildpkg.sh
#
# Copyright (c) 2003-2012 John Willinsky
# Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
#
# Script to create an OMP package for distribution.
#
# Usage: buildpkg.sh <version> [<tag>] [<patch_dir>]
#

GITREP=git://github.com/pkp/omp.git

if [ -z "$1" ]; then
	echo "Usage: $0 <version> [<tag>-<branch>] [<patch_dir>]";
	exit 1;
fi

VERSION=$1
TAG=$2
PATCHDIR=${3-}
PREFIX=omp
BUILD=$PREFIX-$VERSION
TMPDIR=`mktemp -d $PREFIX.XXXXXX` || exit 1

EXCLUDE="dbscripts/xml/data/locale/en_US/sample.xml		\
dbscripts/xml/data/locale/te_ST					\
dbscripts/xml/data/sample.xml					\
docs/dev							\
docs/doxygen							\
lib/adodb/CHANGED_FILES						\
lib/adodb/diff							\
lib/smarty/CHANGED_FILES					\
lib/smarty/diff							\
locale/te_ST							\
cache/*.php							\
tools/buildpkg.sh						\
tools/genLocaleReport.sh					\
tools/genTestLocale.php						\
tools/test							\
lib/pkp/tests							\
.git								\
lib/pkp/.git"

cd $TMPDIR

echo -n "Cloning $GITREP and checking out tag $TAG ... "
git clone -q -n $GITREP $BUILD || exit 1
cd $BUILD
git checkout -q $TAG || exit 1
echo "Done"

echo -n "Checking out corresponding submodule ... "
git submodule -q update --init >/dev/null || exit 1
echo "Done"

echo -n "Preparing package ... "
cp config.TEMPLATE.inc.php config.inc.php
find . \( -name .gitignore -o -name .gitmodules -o -name .keepme \) -exec rm '{}' \;
rm -rf $EXCLUDE
echo "Done"

cd ..

echo -n "Creating archive $BUILD.tar.gz ... "
tar -zcf ../$BUILD.tar.gz $BUILD
echo "Done"

if [ ! -z "$PATCHDIR" ]; then
	echo "Creating patches in $BUILD.patch ..."
	[ -e "../${BUILD}.patch" ] || mkdir "../$BUILD.patch"
	for FILE in $PATCHDIR/*; do
		OLDBUILD=$(basename $FILE)
		OLDVERSION=${OLDBUILD/$PREFIX-/}
		OLDVERSION=${OLDVERSION/.tar.gz/}
		echo -n "Creating patch against ${OLDVERSION} ... "
		tar -zxf $FILE
		diff -urN $PREFIX-$OLDVERSION $BUILD | gzip -c > ../${BUILD}.patch/$PREFIX-${OLDVERSION}_to_${VERSION}.patch.gz
		echo "Done"
	done
	echo "Done"
fi

cd ..

echo -n "Building doxygen documentation... "
doxygen docs/dev/omp.doxygen > /dev/null && cd docs/doxygen && tar czf ../../${BUILD}-doxygen.tar.gz html latex && cd ../..

echo "Done"

rm -r $TMPDIR
