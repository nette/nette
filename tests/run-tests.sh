#!/bin/sh

# saves the path to this script's directory
dir=` dirname $0 `

# absolutizes the path if necessary
if echo $dir | grep -v ^/ > /dev/null; then
	dir=` pwd `/$dir
fi

while getopts "p:" opt;
do
    case $opt in
        p) php_exec=$OPTARG ;;
    esac
done


# runs RunTests.php with script's arguments
if [ ! -x "$php_exec" ]; then
    php "$dir/Test/RunTests.php" -p `which php` $*
else
    php "$dir/Test/RunTests.php" $*
fi

# returns what script returned
