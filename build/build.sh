#!/bin/bash

set -e

function makePackages
{
	createArchive $1

	applyPatch branded
	createArchive $1-branded

	applyPatch encoded
	createArchive $1-encoded

	applyPatch encoded -R
	applyPatch branded -R
}

function createArchive
{
	TAR=$1.tar
	rm -rf $TAR.gz
	tar cf $TAR .
	gzip -9 $TAR

	ZIP=$1.zip
	rm -rf $ZIP
	zip -rq $ZIP .
}

function applyPatch
{
	patch -p0 $2 < $MAIN/build/patch/$1.diff
	find . -name '*.orig' | xargs rm
}

MAIN=/home/mercurial/repo/livecart
BUILD=/home/mercurial/repo/build
TMP=/tmp/build
PACKAGE=/var/db/livecart

MAIN=/var/www/livecart
#MAIN=/var/www/release
#BUILD=/var/www/build

# check if there are no uncommitted files left
#if [ `hg fstatus | grep -v '\[' | grep ' ' | wc -l` != 0 ]; then
#	echo "Uncommitted files left"
#	exit
#fi

# get last log message
cd $MAIN
#LOG=`hg log -l 1 --template "{desc}"`

# copy to a temporary directory and remove .hg directories
rm -rf $TMP
cp -rf $MAIN $TMP
find $TMP -name '.git' | xargs rm -rf

# copy all files to build repo
mv $BUILD/.hg $TMP/.hg
mv $BUILD/.hgtags $TMP/.hgtags
rm -rf $BUILD
mv $TMP $BUILD
cd $BUILD

# prepend copyright messages to source files
find -name '*.php' | xargs grep -l Integry | xargs --max-args=1 $MAIN/build/copyrightPhp.sh $MAIN
find -name '*.js' | xargs grep -l Integry | xargs --max-args=1 $MAIN/build/copyrightJs.sh $MAIN

# get version
VERSION=`head .version`

# remove non-distributed files
rm -rf build cache doc update .git* .snap test push status
rm -rf public/cache public/upload
rm -rf storage/configuration/*.php
rm -rf library/payment/test/simpletest
rm -rf library/payment/test/unittest

# commit changes
hg add

# get changed and removed files
hg status | grep "^[AM]" | cut -c 3- | grep -v "^\.hg" | grep -v "^\.snap" > $MAIN/update/$VERSION/changed
hg status | grep "^[!]" | cut -c 3- > $MAIN/update/$VERSION/deleted

hg addremove
hg commit -m "$VERSION:"$'\n'"$LOG"
hg tag $VERSION

# prepare build
rm -rf $TMP
cp -rf $BUILD $TMP
cd $TMP

# remove Mercurial files
rm -rf .hg* .snap storage
mkdir cache storage public/cache public/upload

# create package files
makePackages $PACKAGE/livecart-$VERSION

set +e

# copy changed files for update
rm -rf /tmp/update
mkdir /tmp/update
cat $MAIN/update/$VERSION/changed | xargs cp --parents -f --target-directory=/tmp/update

# prepare update package
mkdir /tmp/update/update
cp -r $MAIN/update/$VERSION /tmp/update/update/$VERSION

cd $MAIN/update
cp readme.txt /tmp/update/update/$VERSION
cp readme.txt /tmp/update/

cd $MAIN
cp -r --parents license.txt application/controller/backend/SettingsController.php /tmp/update

# create update package files
cd /tmp/update
FROMVERSION=`head /tmp/update/update/$VERSION/from.version`
makePackages $PACKAGE/livecart-update-$FROMVERSION-to-$VERSION

rm -rf /tmp/update

echo 'Build process completed successfuly'