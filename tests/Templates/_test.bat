cls
for %%f in (*Test.php) do call phpunit "%%f"

call ../config.bat

del tmp\* /q

for %%f in (test.*.php) do %php% -q "%%f" > "output\%%f.html"

IF NOT "%diff%"=="" ( start "" %diff% output ref )
