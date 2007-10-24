#!/bin/bash
cat $1/build/copyright.js $2 > $2.copy
rm $2
mv $2.copy $2