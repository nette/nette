call ../config.bat

"%wget%" "%testUrl%/Web.HttpRequest/test.httpRequest.php" -O output\test.httpRequest.php.wget.html

for %%f in (test.*.php) do "%php%" -q "%%f" > "output\%%f.html"

IF NOT "%diff%"=="" ( start "" %diff% output ref )
