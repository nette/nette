wget.exe http://localhost/nette/_trunk/tests/Web.HttpRequest/test.httpRequest.php -O output\test.httpRequest.php.wget.html

for %%f in (test.*.php) do C:\PHP\versions\php-5.2.5-Win32\php.exe -q "%%f" > "output\%%f.html"

start "" diff output ref
