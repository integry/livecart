#!/bin/sh

rm -rf /home/www/cart/build
mkdir /home/www/cart/build

/usr/local/bin/svn export svn://192.168.1.6/livecart/trunk /home/www/livecart/build --force --username rinalds --password test

chmod -R 777 /home/www/livecart/build/cache
chmod -R 777 /home/www/livecart/build/storage

chown -R www /home/www/livecart/build/cache
chgrp -R www /home/www/livecart/build/cache