<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Zasticeni upload endpoint.
 *
 * Zlonamerne fajlove odbija UploadFirewall middleware pre nego sto
 * zahtev stigne do ovog kontrolera.
 */
class SecureUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! $request->hasFile('file')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nije poslat nijedan fajl (polje "file").',
            ], 422);
        }

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $targetDir    = public_path('uploads_secure');

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $originalName);

        return response()->json([
            'status'        => 'uploaded',
            'message'       => 'Fajl je prosao kroz WAF i sacuvan.',
            'original_name' => $originalName,
            'declared_mime' => $file->getClientMimeType(),
            'public_url'    => url('uploads_secure/'.$originalName),
        ]);
    }
}
