#!/bin/sh

# saves the path to this script's directory
dir=`dirname $0`

# absolutizes the path if necessary
if echo "$dir" | grep -v ^/ > /dev/null; then
	dir="`pwd`/$dir"
fi

PhpIni=
while getopts ":c:" opt; do
	case $opt in
	c)	PhpIni="$OPTARG"
		;;

	:)	echo "Missing argument for -$OPTARG option"
		exit 2
		;;
	esac
done

# runs RunTests.php with script's arguments, add default php.ini if not specified
if [ -n "$PhpIni" ]; then
	php -c "$PhpIni" "$dir/Test/RunTests.php" "$@"
else
	php -c "$dir/php.ini-unix" "$dir/Test/RunTests.php" -c "$dir/php.ini-unix" "$@"
fi

# returns what script returned
