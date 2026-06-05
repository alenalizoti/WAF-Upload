<?php

namespace App\Waf\Rules;

use Illuminate\Http\UploadedFile;

/**
 * Proverava originalno ime fajla na opasne putanje i null bajt.
 *
 * Blokira path traversal, apsolutne putanje i skrivene pokusaje prekidanja
 * stringa kroz null bajt u imenu fajla.
 */
class FilenameSanitizerRule implements UploadRule
{
    public function check(UploadedFile $file): ?string
    {
        $names = [
            $file->getClientOriginalName(),
        ];

        if (method_exists($file, 'getClientOriginalPath')) {
            $names[] = $file->getClientOriginalPath();
        }

        foreach ($names as $filename) {
            if (str_contains($filename, "\0")) {
                return 'Ime fajla sadrzi null bajt.';
            }

            if (str_contains($filename, '../') || str_contains($filename, '..\\')) {
                return 'Ime fajla sadrzi path traversal.';
            }

            if (str_starts_with($filename, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $filename) === 1) {
                return 'Apsolutna putanja u imenu fajla nije dozvoljena.';
            }
        }

        return null;
    }
}
