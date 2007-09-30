#!/build/sh
$MAIN=/var/db/repo/livecart
$BUILD=/var/db/repo/build
$TMP=/tmp/build

# get last log message
cd $MAIN
LOG=`hg log -l 1 --template "{desc}"`

# copy to a temporary directory and remove .hg directories
rm -rf $TMP
cp -rf $MAIN $TMP
find $TMP -name '.hg' | xargs rm -rf

# copy all files to build repo
mv $BUILD/.hg $TMP/.hg
rm -rf $BUILD
mv $TMP $BUILD
cd $BUILD

# get version
$VERSION=`head .version`

# commit changes
hg addremove
hg commit -m "$VERSION:"$'\n'"$LOG"
hg tag $VERSION