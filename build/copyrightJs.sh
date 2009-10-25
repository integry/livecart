#!/bin/bash
if [ "$2" != "" ]; then
	cat $1/build/copyright.js $2 > $2.copy
	rm $2
	mv $2.copy $2
fi