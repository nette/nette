#!/bin/sh

# saves the path to this script's directory
dir=` dirname $0 `

# absolutizes the path if necessary
if echo $dir | grep -v ^/ > /dev/null; then
	dir=` pwd `/$dir
fi
runner="$dir/Test/RunTests.php";

case $1 in
	"-h"|"--help")
		php $runner --help
		exit 0
	;;
esac

while getopts "p:c:log:d:l:s:j:" opt;
do
    case $opt in
        p) php_exec=$OPTARG ;;
    esac
done


# runs RunTests.php with script's arguments
if [ ! -x "$php_exec" ]; then
    php $runner -p `which php` $*
else
    php $runner $*
fi

# returns what script returned
