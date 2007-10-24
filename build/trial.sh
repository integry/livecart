#!/bin/bash

set -e

# get the LiveCart version number
VERSION=`head /home/livecart/public_html/update/.version`

# copy LiveCart build file
rm -rf /tmp/build.tar.gz
cp /var/db/livecart/livecart-$VERSION.tar.gz /tmp/build.tar.gz

# unpack
rm -rf /tmp/build /tmp/encoded
cd /tmp/build
tar -xzf /tmp/build.tar.gz

# copy ioncube files
cp -r /home/livecart/ioncube /tmp/build

# copy readme file
cp -rf /home/mercurial/repo/livecart/trialReadme.txt /tmp/build/readme.txt

# encode
/home/turnk/public_html/ion/ioncube_encoder5 --replace-target --expire-in 15d --add-comment "LiveCart Trial Version" --action-if-no-loader="echo(\'No ionCube loader is installed. The loader is necessary to run the encoded trial version files. <a href=ioncube/ioncube-loader-helper.php>See further instructions for installing the loader</a>.<br/><br/>The loader will not be necessary for the paid version as it is shipped with full source code.\');exit (199);" /tmp/build -o /tmp/encoded

# create archive packages
cd /tmp/encoded

TAR=/tmp/trial.tar
rm -rf $TAR.gz
tar cf $TAR .
gzip -9 $TAR

ZIP=/tmp/trial.zip
rm -rf $ZIP
zip -rq $ZIP .

# copy to public directory
cp -rf /tmp/trial.tar.gz /home/livecart/public_html/trial/livecart-trial.tar.gz
cp -rf /tmp/trial.zip /home/livecart/public_html/trial/livecart-trial.zip

# remove all files
rm -rf /tmp/build /tmp/encoded /tmp/trial*