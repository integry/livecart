#!/bin/bash

sed --posix -e 's/    /\t/g' $1 > temp.tmp
rm -f $1
mv temp.tmp $1
