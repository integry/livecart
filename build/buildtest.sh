#!/bin/bash

function assertEqual
{
	if [ "$1" != "$2" ]
	then
		echo "ERROR: equals assertion failed ('$1' and '$2') at line $3"
		exit
	fi
}

set -x
set -e

DIR=/tmp/module/test
export PKG_ROOT=/tmp/packages

rm -rf $DIR
mkdir -p $DIR
cd $DIR

rm -rf /var/db/repo/test

# FIRST VERSION

echo -e "[Module]\n\
name = Test Module\n\
vendor = UAB Integry Systems\n\
url = http://livecart.com/\n\
version = 1.0.0\n\
free = true\n\
pkg = test" > Module.ini

echo "<?php /* Integry Systems */ echo 'test module, first stable version'; ?>" > index.php

mkdir --parents application/view
echo -e "<html>\n\
	<body>this is the base file</body>\n\
</html>" > application/view/test.tpl

echo "blabla" > delete.pending

/l/build/buildnew.sh $DIR current

# SECOND VERSION

rm -f delete.pending

echo -e "[Module]\n\
name = Test Module\n\
vendor = UAB Integry Systems\n\
url = http://livecart.com/\n\
version = 1.0.1\n\
free = true\n\
pkg = test" > Module.ini

echo "<?php /* Integry Systems */ echo 'test module, second version'; ?>" > index.php

echo -e "<html>\n\
	<body>this is the base file</body>\n\
	<script>we forgot to add some jQuery the first time around</script>
</html>" > application/view/test.tpl

echo "some dot file" > .htaccess

/l/build/buildnew.sh $DIR current

# UPDATE FOR FIRST VERSION (STABLE BRANCH)

rm -f delete.pending

echo -e "[Module]\n\
name = Test Module\n\
vendor = UAB Integry Systems\n\
url = http://livecart.com/\n\
version = 1.0.0.1\n\
line = stable\n\
parent = 1.0.0\n\
parentline = current\n\
free = true\n\
pkg = test" > Module.ini

echo "<?php /* Integry Systems */ echo 'test module, first version, but updated'; ?>" > index.php

echo -e "<html>\n\
	<body>this is the base file</body>\n\
	<!-- stable version, no jQuery goodness allowed ;(( !-->
</html>" > application/view/test.tpl

/l/build/buildnew.sh $DIR stable

# THIRD VERSION

rm -f delete.pending

echo -e "[Module]\n\
name = Test Module\n\
vendor = UAB Integry Systems\n\
url = http://livecart.com/\n\
version = 1.0.2\n\
free = true\n\
line = current\n\
pkg = test" > Module.ini

echo "<?php /* Integry Systems */ echo 'test module, third version'; ?>" > index.php

echo -e "<html>\n\
	<body>this is the base file</body>\n\
	<script>we forgot to add some jQuery the first time around</script>
</html>" > application/view/test.tpl

echo "some dot file" > .htaccess

/l/build/buildnew.sh $DIR current

############# TESTS #############

rm -rf /tmp/buildtest && mkdir /tmp/buildtest
cd /tmp/buildtest
tar xfz /tmp/packages/updates/test/test-stable-update-1.0.0-to-1.0.0.1.tar.gz
assertEqual "`cat Module.ini | grep version`" "version = 1.0.0.1" $LINENO

# should be 4 changed and 1 deleted files
assertEqual "`cat update/1.0.0.1/changed | wc -l`" "4" $LINENO
assertEqual "`cat update/1.0.0.1/deleted | wc -l`" "1" $LINENO

# dot files should still be present
assertEqual "`ls .htaccess | wc -l`" "1" $LINENO

# no changed templates in 1.0.2
assertEqual "`ls $PKG_ROOT/templates/test/1.0.2 | wc -l`" "0" $LINENO

# TEST DOWNGRADE
tar xfz /tmp/packages/updates/test/test-stable-downgrade-1.0.0.1-to-1.0.0.tar.gz
assertEqual "`cat Module.ini | grep version`" "version = 1.0.0" $LINENO

echo "------------------ DONE ------------------------"