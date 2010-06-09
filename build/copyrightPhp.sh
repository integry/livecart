#!/bin/bash
cat $1/build/copyright.phps $2 > $2.copy
rm -f $2
mv $2.copy $2

# remove redundant PHP open/close tags
cat $2 | grep -v '?><?php' > $2.copy
rm -f $2
mv $2.copy $2
