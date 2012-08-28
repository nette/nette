#!/bin/sh

# saves the path to this script's directory
dir=` dirname $0 `

# absolutizes the path if necessary
if echo $dir | grep -v ^/ > /dev/null; then
	dir=` pwd `/$dir
fi

# runs RunTests.php with script's arguments
php "$dir/Test/RunTests.php" $*

# returns what script returned
