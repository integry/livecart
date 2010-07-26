#!/bin/bash

set -e
set -x

function makeProfessional
{
	mv license-free.txt /tmp
	createArchive $1
	mv /tmp/license-free.txt .
}

function makeCommunity
{
	rm license.txt
	mv license-free.txt license.txt
	applyPatch community
	createArchive $1-community
	applyPatch community -R
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
	dos2unix $MAIN/build/patch/$1.diff
	dos2unix application/controller/backend/SettingsController.php
	dos2unix application/view/layout/frontend.tpl

	patch -p0 $2 < $MAIN/build/patch/$1.diff
	find . -name '*.orig' | xargs rm
}

function build
{
	set -e

	BUILD=$1
	PACKAGE=$2
	BRANCH=$3
	MAKEFUNC=$4

	TMP=/tmp/build
	MAIN=/var/www/livecart

	# get last log message
	cd $MAIN
	git checkout $BRANCH

	# copy to a temporary directory and remove .hg directories
	rm -rf $TMP
	cp -rf $MAIN $TMP
	find $TMP -name '.git' | xargs rm -rf

	# copy all files to build repo
	mv $BUILD/.hg $TMP/.hg

	if [ -e $BUILD/.hgtags ]
	then
		mv $BUILD/.hgtags $TMP/.hgtags
	fi

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
	rm -rf public/module
	rm -rf import* output* plugin

	cd module
	ls | grep -v ads | grep -v captcha | xargs rm -rf

	# @todo: headset-no module still not deleted (he*ads*et)
	rm -rf customization*

	cd ..

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
	mkdir cache storage public/cache public/upload public/module

	# create package files
	$MAKEFUNC $PACKAGE/livecart-$VERSION

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
	cp readme.txt /tmp/update/update
	cp update.php /tmp/update/update

	cd $MAIN
	cp -r --parents license.txt application/controller/backend/SettingsController.php /tmp/update

	# create update package files
	cd /tmp/update
	rm -rf module
	FROMVERSION=`head /tmp/update/update/$VERSION/from.version`
	$MAKEFUNC $PACKAGE/livecart-update-$FROMVERSION-to-$VERSION

	rm -rf /tmp/update
}

DIR=`pwd`

git stash save build

build /home/mercurial/repo/build /var/db/livecart stable makeProfessional
#build /home/mercurial/repo/build-community /var/db/livecart/community community makeCommunity

cd $DIR

git reset --hard
git checkout master
git stash pop build

echo 'Build process completed successfuly'
