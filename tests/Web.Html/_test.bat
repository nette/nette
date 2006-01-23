for %%f in (test.*.php) do C:\PHP\versions\php-5.2.5-Win32\php.exe -q "%%f" > "output\%%f.html"

start "" diff output ref
