#!/bin/bash

cd `dirname $0`

SUBDOMAIN=extra

export REPO_ROOT=/var/db/$SUBDOMAIN.livecart.com/repo
export PKG_ROOT=/var/db/$SUBDOMAIN.livecart.com/packages

./buildnew.sh $1