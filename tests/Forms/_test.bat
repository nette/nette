call ../config.bat

for %%f in (test.*.php) do "%php%" -q -d magic_quotes_gpc=on "%%f" > "output\%%f.html"

IF NOT "%diff%"=="" ( start "" %diff% output ref )
