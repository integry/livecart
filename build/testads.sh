#!/bin/bash
set -x

cd /l/module/ads
git reset --hard && rm -rf update && git checkout master

rm -rf /var/db/repo/ads

cd /l/module/ads
git checkout 1.0
/l/build/buildnew.sh /l/module/ads/

git reset --hard && rm -rf update && git checkout master

cd /l/module/ads/
git checkout 1.0.1
/l/build/buildnew.sh /l/module/ads/

cd /l/module/ads/
git reset --hard && rm -rf update && git checkout master