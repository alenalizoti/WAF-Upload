<?php

namespace App\Waf\Rules;

use Illuminate\Http\UploadedFile;

/**
 * Proverava da li uploadovani fajl prelazi dozvoljenu velicinu.
 *
 * Maksimalna velicina je podesiva kroz config/waf.php kako bi WAF ostao
 * prilagodljiv razlicitim okruzenjima.
 */
class SizeRule implements UploadRule
{
    public function check(UploadedFile $file): ?string
    {
        $maxSize = config('waf.max_size', 5 * 1024 * 1024);

        if ($file->getSize() > $maxSize) {
            return 'Fajl prelazi dozvoljenu velicinu od '.$maxSize.' bajtova.';
        }

        return null;
    }
}
