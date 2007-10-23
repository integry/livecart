#!/bin/bash

set -e

# we're in /update/version/
cd files
cp -rf . ../../../

cd ..
php ./update.php