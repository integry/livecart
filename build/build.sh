#!/bin/bash
MAIN=/home/mercurial/repo/livecart
BUILD=/home/mercurial/repo/build
TMP=/tmp/build
PACKAGE=/var/db/livecart

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
find -name '*.php' | xargs grep -l Integry | ./build/copyrightPhp.sh
find -name '*.js' | xargs grep -l Integry | ./build/copyrightJs.sh

# get version
VERSION=`head .version`

# copy version file to update server (update.livecart.com)
cp .version /home/livecart/public_html/update/.version

# remove non-distributed files
rm -rf build
rm -rf doc

# commit changes
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

mkdir cache
mkdir storage
mkdir public/cache

# create package files
TAR=$PACKAGE/$VERSION.tar
rm -rf $TAR.gz
tar cf $TAR *
gzip -9 $TAR

ZIP=$PACKAGE/$VERSION.zip
rm -rf $ZIP
zip -rq $ZIP *