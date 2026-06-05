<?php

namespace App\Waf\Rules;

use Illuminate\Http\UploadedFile;

/**
 * Proverava pocetne bajtove fajla za tipove koji imaju stabilan potpis.
 *
 * Pravilo sprecava lazno predstavljanje binarnih fajlova, a tekstualne
 * fajlove prepusta skeneru sadrzaja jer nemaju pouzdane magic bytes.
 */
class MagicBytesRule implements UploadRule
{
    public function check(UploadedFile $file): ?string
    {
        $mime = $file->getMimeType();
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            return 'Nije moguce procitati fajl za magic bytes proveru.';
        }

        $header = fread($handle, 16);
        fclose($handle);

        if ($header === false) {
            return 'Nije moguce procitati pocetak fajla.';
        }

        return match ($mime) {
            'image/jpeg' => str_starts_with($header, "\xFF\xD8\xFF")
                ? null
                : 'JPEG fajl nema validan potpis.',
            'image/png' => str_starts_with($header, "\x89PNG\x0D\x0A\x1A\x0A")
                ? null
                : 'PNG fajl nema validan potpis.',
            'image/gif' => (str_starts_with($header, 'GIF87a') || str_starts_with($header, 'GIF89a'))
                ? null
                : 'GIF fajl nema validan potpis.',
            'application/pdf' => str_starts_with($header, '%PDF-')
                ? null
                : 'PDF fajl nema validan potpis.',
            'text/plain' => null,
            default => 'Tip fajla nema dozvoljen magic bytes potpis: '.$mime,
        };
    }
}
