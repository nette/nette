call ../config.bat

"%wget%" "%testUri%/Web.HttpRequest/test.ht%%74pRequest.php?xparam=val&pa%%72am=val2#frag" -O output\test.httpRequest.php.wget.html

for %%f in (test.*.php) do "%php%" -q "%%f" > "output\%%f.html"

IF NOT "%diff%"=="" ( start "" %diff% output ref )
