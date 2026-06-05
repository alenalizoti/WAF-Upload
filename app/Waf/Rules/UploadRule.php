<?php

namespace App\Waf\Rules;

use Illuminate\Http\UploadedFile;

/**
 * Zajednicki ugovor za WAF pravila koja proveravaju uploadovan fajl.
 *
 * Pravilo vraca null kada fajl prolazi proveru, ili tekstualni razlog
 * blokade kada treba zaustaviti zahtev pre cuvanja fajla.
 */
interface UploadRule
{
    public function check(UploadedFile $file): ?string;
}
