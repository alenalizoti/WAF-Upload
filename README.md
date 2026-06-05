Napravljena je mala ranjiva aplikacija za upload fajlova, a ispred jedne rute zakacen je WAF (Web Application Firewall) middleware koji hvata i blokira zlonamerne fajlove. Na istom kodu se vidi razlika izmedju rute koja je probijena i rute koja je zasticena.

Postoje dve rute koje cuvaju fajl na isti nacin, jedina razlika je WAF:

/vulnerable/upload — bez ikakve zastite, sve sto stigne se snima. Sluzi za demonstraciju napada.
/secure/upload — isti kod za cuvanje, ali ispred njega stoji WAF middleware koji proverava fajl pre kontrolera. Los fajl se odbija sa HTTP 403 (ili 413 ako je prevelik).

Demonstacija napada

U attacks/ folderu se nalazi PowerShell skripta koja ispali deset razlicitih napada zaredom i ispise rezultat za svaki: PHP web shell, alternativne ekstenzije (.phtml), duple ekstenzije (shell.jpg.php), MIME spoofing, magic bytes spoofing (validan PNG potpis + ugradjen PHP), polyglot GIF, SVG sa XSS-om, path traversal u imenu i preveliki fajl (DoS).

# u jednom terminalu se podigne server:
php artisan serve --port=8585

# u drugom se pokrenu napadi:
./attacks/run_attacks.ps1                 # gadja ranjivu rutu - sve prolazi
./attacks/run_attacks.ps1 -Route secure   # gadja zasticenu rutu - WAF sve blokira


Potreban je PHP 8.3+, Composer i Git.

# kloniranje repozitorijuma
git clone <url-repozitorijuma>
cd <folder-projekta>

# instalacija zavisnosti
composer install

# kreiranje .env i aplikacionog kljuca
cp .env.example .env
php artisan key:generate
php artisan serve