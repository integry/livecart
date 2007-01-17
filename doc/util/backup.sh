#!/usr/local/bin/bash

cd /home/backup
echo Backup Started `date` >> log

#Mysql databse backup
/usr/local/bin/mysqldump --all-databases > mysql_`date +%Y%m%d`
/usr/bin/gzip mysql_`date +%Y%m%d`

#SVN repository backup
/usr/local/bin/svnadmin dump /var/db/svn/repository/ > svn_`date +%Y%m%d`
/usr/bin/gzip svn_`date +%Y%m%d`

#
# Upload files to remote ftp backup server
#
ftp -i -in <<EOF
open integry.net
user devbackup@integry.net devbkp321
put mysql_`date +%Y%m%d`.gz
put svn_`date +%Y%m%d`.gz
EOF

echo Backup Completed `date` >> log