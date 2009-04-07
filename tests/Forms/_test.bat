call ../config.bat

for %%f in (test.*.php) do %php% -q -d magic_quotes_gpc=on "%%f" > "output\%%f.html"

for %%f in (../../examples/forms/*.php) do %php% -q -d magic_quotes_gpc=on "../../examples/forms/%%f" > "output\%%f.html"

for %%f in (../../examples/forms/*.php) do %php% -q -d magic_quotes_gpc=on -d auto_prepend_file=submit.php "../../examples/forms/%%f" > "output\submitted.%%f.html"

IF NOT "%diff%"=="" ( start "" %diff% output ref )
