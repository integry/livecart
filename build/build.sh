#!/bin/bash

set -e

MAIN=/home/mercurial/repo/livecart
BUILD=/home/mercurial/repo/build
TMP=/tmp/build
PACKAGE=/var/db/livecart

MAIN=/var/www/livecart
#BUILD=/var/www/build

# get last log message
cd $MAIN
LOG=`hg log -l 1 --template "{desc}"`

# copy to a temporary directory and remove .hg directories
rm -rf $TMP
cp -rf $MAIN $TMP
find $TMP -name '.hg' | xargs rm -rf

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

# copy version file to update server (update.livecart.com)
# cp .version /home/livecart/public_html/update/.version

# remove non-distributed files
rm -rf build cache doc update
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
rm -rf .hg*
rm -rf .snap

rm -rf storage
mkdir cache storage
mkdir storage/configuration
mkdir public/cache public/upload

echo "<?php
	// your custom initialization code goes here
?>" > storage/configuration/CustomInitialize.php

# create package files
TAR=$PACKAGE/livecart-$VERSION.tar
rm -rf $TAR.gz
tar cf $TAR .
gzip -9 $TAR

ZIP=$PACKAGE/livecart-$VERSION.zip
rm -rf $ZIP
zip -rq $ZIP .

# copy changed files for update
rm -rf /tmp/update
mkdir /tmp/update
cat $MAIN/update/$VERSION/changed | xargs cp --parents -f --target-directory=/tmp/update

# prepare update package
mkdir /tmp/update/update
cp -r $MAIN/update/$VERSION /tmp/update/update/$VERSION

cd $MAIN/update
cp readme.txt /tmp/update/update/$VERSION

# create update package files
cd /tmp/update
FROMVERSION=`head /tmp/update/update/$VERSION/from.version`
TAR=$PACKAGE/livecart-update-$FROMVERSION-to-$VERSION.tar
rm -rf $TAR.gz
tar cf $TAR .
gzip -9 $TAR

ZIP=$PACKAGE/livecart-update-$FROMVERSION-to-$VERSION.zip
rm -rf $ZIP
zip -rq $ZIP .

rm -rf /tmp/update

echo 'Build process completed successfuly'