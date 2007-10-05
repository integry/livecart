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

# get version
VERSION=`head .version`

# copy version file to update server (update.livecart.com)
cp .version /home/livecart/public_html/update/.version

# commit changes
hg addremove
hg commit -m "$VERSION:"$'\n'"$LOG"
hg tag $VERSION

# prepare build
rm -rf $TMP
cp -rf $BUILD $TMP
cd $TMP

rm -rf .hg*
rm -rf .snap
rm -rf build.sh
rm -rf doc

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