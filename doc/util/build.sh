#!/bin/sh

rm -rf /home/www/cart/build
mkdir /home/www/cart/build

/usr/local/bin/svn export svn://192.168.1.6/livecart/trunk /home/www/cart/build --force --username saulius --password test

chmod -R 777 /home/www/cart/build/cache
chown -R www /home/www/cart/build/cache
chgrp -R www /home/www/cart/build/cache
