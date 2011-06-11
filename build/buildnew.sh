#!/bin/bash

# /l/build/buildnew.sh /tmp/module/whatever current

set -x
set -e

REPO_ROOT=/var/db/repo
PKG_DEF_ROOT=/var/db/livecart

if [ ! $PKG_ROOT ]
then
	PKG_ROOT=$PKG_DEF_ROOT
fi

SCRIPTDIR="`dirname $0`"
LINE=$2
ROOT=$1

cd $1

function getPackageDetails
{
	# read Module.ini
	eval `sed -e 's/[[:space:]]*\=[[:space:]]*/=/g' \
		-e 's/;.*$//' \
		-e 's/[[:space:]]*$//' \
		-e 's/^[[:space:]]*//' \
		-e "s/^\(.*\)=\([^\"']*\)$/\1=\"\2\"/" \
	   < Module.ini \
		| sed -n -e "/^\[Module\]/,/^\s*\[/{/^[^;].*\=.*/p;}"`

	# get package name
	if [ "$pkg" == "" ]
	then
		if [[ `pwd` =~ module\/(.*)$ ]]
		then
			pkg="${BASH_REMATCH[1]}"
		fi
	fi

	if [[ `pwd` =~ module\/(.*)$ ]]
	then
		ISMODULE=1
	fi

	LINE=$line
	if [ ! $line ]
	then
		LINE=current
	fi
}

# init/switch to repository
function initBuildRepository
{
	REPO=$REPO_ROOT/$pkg
	if [ ! -d $REPO ]
	then
		mkdir $REPO
		cd $REPO
		git init

		# make a dummy commit
		echo "$pkg" > ./.package
		git add ./.package
		git commit -m "Initial"

		git branch current
	fi

	cd $REPO
	git checkout current

	if [ $parent ]
	then
		git branch $LINE $parent
	elif [ `git branch | grep $LINE | wc -l` == "0" ]
	then
		git branch $LINE
	fi

	git checkout -q $LINE
}

# get changed and removed files
function listChangedFiles
{
	amkdir $UPDATEDIR

	git status --porcelain | grep -v ".package" | grep -v "^\.git" | grep -v "^\.snap" | egrep "^[ AM]{3}" | cut -c 4- > $UPDATEDIR/changed
	git status --porcelain | grep -v ".package" | egrep "^[ D]{3}" | cut -c 3- > $UPDATEDIR/deleted
}

# make copies of changed tpl files for auto-merge service
function saveChangedTemplateFiles
{
	TPL_DIR=$PKG_ROOT/templates/$pkg/$VERSION
	amkdir $TPL_DIR
	grep ".tpl" $UPDATEDIR/changed > $TPL_DIR/.list || true
	cat $TPL_DIR/.list | xargs --no-run-if-empty cp --parents -f --target-directory=$TPL_DIR
}

# @param extension
# @param sign script base name
function setFileCopyright
{
	find -name '*.$1' | xargs --no-run-if-empty grep -l Integry | xargs --no-run-if-empty --max-args=1 $SCRIPTDIR/$2.sh $SCRIPTDIR
}

# prepend copyright messages to source files
function markCopyright
{
	setFileCopyright php copyrightPhp
	setFileCopyright js copyrightJs

	cd $SCRIPTDIR/..
	if [ $ISMODULE ]
	then
		if [ -f /tmp/update/license.txt ]
		then
			cp license-module.txt /tmp/update
		fi
	else
		cp license.txt /tmp/update
	fi
}

function removeNonDistributedFiles
{
	# remove non-distributed files
	rm -rf build cache doc update .git* .snap test push status
	rm -rf public/cache public/upload
	rm -rf storage/configuration/*.php
	rm -rf library/payment/test/simpletest
	rm -rf library/payment/test/unittest
	rm -rf public/module
	rm -rf import* output* plugin

	if [ -d module ]
	then
		cd module
		ls | grep -v ads | grep -v captcha | xargs rm -rf
	fi

	# @todo: headset-no module still not deleted (he*ads*et)
	rm -rf customization*
}

function prepareBuildDirectory
{
	# prepare build
	rm -rf $TMP
	cp -rf $BUILD $TMP
	cd $TMP

	# remove Mercurial files
	rm -rf .git* .snap storage

	if [ ! $ISMODULE ]
	then
		mkdir cache storage public/cache public/upload public/module
	fi
}

# @param File base name
# @param Relative package directory
function makeProfessional
{
	#mv license-free.txt /tmp
	createArchive $1 $2
	#mv /tmp/license-free.txt .
}

function createArchive
{
	amkdir $PKG_ROOT

	TAR=$PKG_ROOT/$1.tar
	rm -rf $TAR.gz
	tar cf $TAR .
	gzip -9 $TAR

	ZIP=$PKG_ROOT/$1.zip
	rm -rf $ZIP
	zip -rq $ZIP .

	if [ $2 ]
	then
		PKG_DIR=$PKG_ROOT/$2
		amkdir $PKG_DIR
		mv $TAR.gz $ZIP $PKG_DIR
	fi
}

function amkdir
{
	if [ ! -d $1 ]
	then
		mkdir -p $1
	fi
}

function getPreviousVersion
{
	if [ $parent ]
	then
		FROMVERSION=$parent
	else
		FROMVERSION=`git log --pretty="format:%s" -n 1`
		if [ "Initial" == "$FROMVERSION" ]
		then
			FROMVERSION=""
		fi
	fi
}

function buildUpdatePackages
{
	cd $REPO

	ISDOWNGRADE=$1
	if [ $ISDOWNGRADE ]
	then
		EXPORTRANGE="HEAD~1..HEAD"
		ARCHIVE=$pkg-$LINE-downgrade-$VERSION-to-$FROMVERSION
		ARCHIVEFROMVERSION=$VERSION
		git checkout HEAD~1
	else
		EXPORTRANGE="HEAD..HEAD~1"
		ARCHIVE=$pkg-$LINE-update-$FROMVERSION-to-$VERSION
		ARCHIVEFROMVERSION=$FROMVERSION
	fi

	# copy changed files for update
	rm -rf /tmp/update && mkdir /tmp/update
	git exportfiles $EXPORTRANGE /tmp/update
	git checkout HEAD

	cd /tmp/update
	markCopyright

	# prepare update package
	UPDATEDIR=/tmp/update/update/$VERSION
	mkdir /tmp/update/update
	cp -r $MAIN/update/$VERSION $UPDATEDIR

	cd $SCRIPTDIR/../update
	cp readme.txt $UPDATEDIR
	cp readme.txt /tmp/update/update
	cp update.php /tmp/update/update

	cd ..
	if [ ! $ISMODULE ]
	then
		cp --parents application/controller/backend/SettingsController.php /tmp/update
	fi

	# update/downgrade SQL files
	if [ $ISDOWNGRADE ]
	then
		rm -f $UPDATEDIR/update.sql
	else
		rm -f $UPDATEDIR/downgrade.sql
	fi

	# create update package files
	cd /tmp/update
	rm -rf module
	echo $ARCHIVEFROMVERSION > /tmp/update/update/$VERSION/from.version
	$MAKEFUNC $ARCHIVE updates/$pkg

	rm -rf /tmp/update
}

function createPackageDescriptionFile
{
	DESCRDIR=$PKG_ROOT/versions/$pkg
	amkdir $DESCRDIR

	DESCRFILE=$DESCRDIR/$PACKAGE
	cp $MAIN/Module.ini $DESCRFILE
}

function build
{
	#set -e

	MAIN=$1
	BUILD=$2
	PACKAGE=$3
	VERSION=$version
	TMP=/tmp/build
	UPDATEDIR=$MAIN/update/$VERSION
	MAKEFUNC=makeProfessional

	# copy to a temporary directory and remove .hg directories
	rm -rf $TMP
	cp -rf $MAIN $TMP
	find $TMP -name '.git' | xargs rm -rf

	#removeDeletedFiles

	cd $TMP
	removeNonDistributedFiles
	markCopyright

	# copy all files to build repo
	mv $BUILD/.git /tmp
	rm -rf $BUILD
	cp -rf $TMP $BUILD
	mv /tmp/.git $BUILD
	rm -f $BUILD/.package

	cd $BUILD

	# commit changes
	git add .

	getPreviousVersion
	listChangedFiles
	saveChangedTemplateFiles
	git add .

	git commit -m "$VERSION"
	git tag $VERSION

	prepareBuildDirectory

	# create package files
	$MAKEFUNC $PACKAGE releases/$pkg

	if [ $FROMVERSION ]
	then
		buildUpdatePackages
		buildUpdatePackages "downgrade"
	fi

	createPackageDescriptionFile
}

getPackageDetails
PACKAGE="$pkg-$LINE-$version"

initBuildRepository

build $ROOT $REPO $PACKAGE