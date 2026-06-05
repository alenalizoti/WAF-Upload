<?php

namespace App\Http\Middleware;

use App\Waf\Rules\ContentScanRule;
use App\Waf\Rules\ExtensionRule;
use App\Waf\Rules\FilenameSanitizerRule;
use App\Waf\Rules\MagicBytesRule;
use App\Waf\Rules\MimeTypeRule;
use App\Waf\Rules\SizeRule;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * WAF middleware za zastitu file upload rute.
 *
 * Middleware presrece fajl iz polja "file", pokrece ukljucena WAF pravila
 * redom i prekida zahtev cim prvo pravilo prijavi razlog za blokadu.
 */
class UploadFirewall
{
    /**
     * @var array<string, class-string>
     */
    private array $rules = [
        'filename' => FilenameSanitizerRule::class,
        'size' => SizeRule::class,
        'extension' => ExtensionRule::class,
        'mime' => MimeTypeRule::class,
        'magic_bytes' => MagicBytesRule::class,
        'content_scan' => ContentScanRule::class,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasFile('file')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nije poslat nijedan fajl (polje "file").',
            ], 422);
        }

        $file = $request->file('file');

        foreach ($this->rules as $configKey => $ruleClass) {
            if (! config('waf.rules.'.$configKey, true)) {
                continue;
            }

            $rule = app($ruleClass);
            $reason = $rule->check($file);

            if ($reason !== null) {
                return $this->block($request, $ruleClass, $reason);
            }
        }

        return $next($request);
    }

    private function block(Request $request, string $ruleClass, string $reason): JsonResponse
    {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $rule = class_basename($ruleClass);
        $status = $ruleClass === SizeRule::class ? 413 : 403;

        Log::channel('waf')->warning('Blokiran upload pokusaj.', [
            'ip' => $request->ip(),
            'filename' => $filename,
            'rule' => $rule,
            'reason' => $reason,
        ]);

        return response()->json([
            'status' => 'blocked',
            'message' => $reason,
            'rule' => $rule,
            'filename' => $filename,
        ], $status);
    }
}
