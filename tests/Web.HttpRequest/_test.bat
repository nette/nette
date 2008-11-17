call ../config.bat

"%wget%" "%testUri%/Web.HttpRequest/test.ht%%74pRequest.php?xparam=val&pa%%72am=val2#frag" --header "Cookie: hello=world" -O output\test.httpRequest.php.wget.html

"%wget%" "%testUri%/Web.HttpRequest/test.ht%%74pRequest.php?x param=val.&pa%%72am=val2.#frag" -O output\test.httpRequest.php.wget.filter.html

for %%f in (test.*.php) do %php% -q "%%f" > "output\%%f.html"

IF NOT "%diff%"=="" ( start "" %diff% output ref )
