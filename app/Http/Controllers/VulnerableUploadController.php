<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * NAMERNO RANJIV kontroler za demonstraciju file upload napada.
 *
 * Ne radi NIKAKVU validaciju: prihvata bilo koji fajl, cuva ga sa
 * originalnim imenom i ekstenzijom direktno u web-dostupan folder
 * (public/uploads). Pod realnim web serverom (Apache/XAMPP) ovo vodi
 * do RCE-a jer se npr. shell.php izvrsava kao PHP.
 *
 * NE KORISTITI U PRODUKCIJI.
 */
class VulnerableUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! $request->hasFile('file')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nije poslat nijedan fajl (polje "file").',
            ], 422);
        }

        $file = $request->file('file');

        // Ranjivost: koristi se originalno ime klijenta, bez sanitizacije.
        $originalName = $file->getClientOriginalName();
        $targetDir    = public_path('uploads');

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Ranjivost: nema provere ekstenzije, MIME tipa, sadrzaja ni velicine.
        // move() sa originalnim imenom omogucava i path traversal preko imena.
        $file->move($targetDir, $originalName);

        return response()->json([
            'status'        => 'uploaded',
            'message'       => 'Fajl je sacuvan bez ikakve provere.',
            'original_name' => $originalName,
            'declared_mime' => $file->getClientMimeType(),
            'public_url'    => url('uploads/'.$originalName),
        ]);
    }
}
