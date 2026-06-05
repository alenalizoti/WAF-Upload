<?php

namespace App\Waf\Rules;

use Illuminate\Http\UploadedFile;

/**
 * Proverava prijavljeni i stvarni MIME tip uploadovanog fajla.
 *
 * Prijavljeni MIME dolazi od klijenta, a stvarni MIME se odredjuje iz
 * sadrzaja fajla. Oba moraju biti u listi dozvoljenih tipova.
 */
class MimeTypeRule implements UploadRule
{
    public function check(UploadedFile $file): ?string
    {
        $allowedMime = config('waf.allowed_mime', []);
        $clientMime = $file->getClientMimeType();
        $realMime = $file->getMimeType();

        if (! in_array($realMime, $allowedMime, true)) {
            return 'Stvarni MIME tip nije dozvoljen: '.$realMime;
        }

        if ($clientMime !== null && ! in_array($clientMime, $allowedMime, true)) {
            return 'Prijavljeni MIME tip nije dozvoljen: '.$clientMime;
        }

        return null;
    }
}
