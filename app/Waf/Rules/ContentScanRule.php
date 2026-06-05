<?php

namespace App\Waf\Rules;

use Illuminate\Http\UploadedFile;

/**
 * Skenira sadrzaj fajla na poznate zlonamerne potpise.
 *
 * Pravilo hvata web shell konstrukcije, PHP kod sakriven u slikama i
 * JavaScript u SVG fajlovima, ukljucujuci jednostavne string i regex potpise.
 */
class ContentScanRule implements UploadRule
{
    public function check(UploadedFile $file): ?string
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            return 'Nije moguce procitati sadrzaj fajla.';
        }

        foreach (config('waf.content_signatures', []) as $signature) {
            if ($this->matchesSignature($content, $signature)) {
                return 'Sadrzaj fajla sadrzi zlonamerni potpis: '.$signature;
            }
        }

        return null;
    }

    private function matchesSignature(string $content, string $signature): bool
    {
        if ($this->isRegex($signature)) {
            return preg_match($signature, $content) === 1;
        }

        return stripos($content, $signature) !== false;
    }

    private function isRegex(string $signature): bool
    {
        return strlen($signature) > 2 && $signature[0] === '/' && strrpos($signature, '/') > 0;
    }
}
