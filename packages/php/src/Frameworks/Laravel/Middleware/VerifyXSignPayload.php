<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Frameworks\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Wanadri\XSignPayload\Core\Config;
use Wanadri\XSignPayload\Core\SignatureVerifier;
use Wanadri\XSignPayload\Exceptions\ExpiredRequestException;
use Wanadri\XSignPayload\Exceptions\InvalidSignatureException;
use Wanadri\XSignPayload\Exceptions\MissingHeadersException;

class VerifyXSignPayload
{
    private SignatureVerifier $verifier;
    private Config $config;

    public function __construct()
    {
        $this->verifier = new SignatureVerifier();
        $this->config = new Config([
            'secret' => config('x-sign-payload.secret'),
            'algorithm' => config('x-sign-payload.algorithm', 'sha256'),
            'enable_timestamp' => config('x-sign-payload.enable_timestamp', true),
            'replay_window' => config('x-sign-payload.replay_window', 10),
        ]);
    }

    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');

        if (empty($signature)) {
            throw new MissingHeadersException('X-Signature header is required');
        }

        if ($this->config->enableTimestamp && empty($timestamp)) {
            throw new MissingHeadersException('X-Timestamp header is required');
        }

        try {
            $this->verifier->verify(
                $signature,
                $request->getContent(),
                $this->config,
                $timestamp
            );
        } catch (ExpiredRequestException $e) {
            throw new ExpiredRequestException($e->getMessage());
        } catch (InvalidSignatureException $e) {
            throw new InvalidSignatureException($e->getMessage());
        }

        return $next($request);
    }
}
