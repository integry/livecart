#!/bin/bash
cat ./build/copyright.php $1 > $1.copy
rm $1
mv $1.copy $1