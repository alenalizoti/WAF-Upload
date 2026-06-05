<?php

namespace App\Waf\Rules;

use Illuminate\Http\UploadedFile;

/**
 * Proverava ekstenzije fajla kroz allowlist i denylist pristup.
 *
 * Provera obuhvata sve delove originalnog imena, pa blokira i duple
 * ekstenzije poput shell.php.jpg ili shell.jpg.php.
 */
class ExtensionRule implements UploadRule
{
    public function check(UploadedFile $file): ?string
    {
        $filename = $file->getClientOriginalName();
        $parts = array_map('strtolower', explode('.', $filename));

        if (count($parts) < 2) {
            return 'Fajl nema ekstenziju.';
        }

        $extensions = array_slice($parts, 1);
        $lastExtension = end($extensions);
        $allowedExtensions = config('waf.allowed_extensions', []);
        $blockedExtensions = config('waf.blocked_extensions', []);

        foreach ($extensions as $extension) {
            if (in_array($extension, $blockedExtensions, true)) {
                return 'Ekstenzija nije dozvoljena: '.$extension;
            }
        }

        if (! in_array($lastExtension, $allowedExtensions, true)) {
            return 'Ekstenzija nije na listi dozvoljenih: '.$lastExtension;
        }

        return null;
    }
}
