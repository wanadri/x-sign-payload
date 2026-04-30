<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Frameworks\CakePHP\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wanadri\XSignPayload\Core\Config;
use Wanadri\XSignPayload\Core\SignatureVerifier;
use Wanadri\XSignPayload\Exceptions\ExpiredRequestException;
use Wanadri\XSignPayload\Exceptions\InvalidSignatureException;
use Wanadri\XSignPayload\Exceptions\MissingHeadersException;

class XSignMiddleware implements MiddlewareInterface
{
    private SignatureVerifier $verifier;
    private Config $config;

    public function __construct()
    {
        $this->verifier = new SignatureVerifier();
        $this->config = new Config([
            'secret' => env('X_SIGN_SECRET', ''),
            'algorithm' => env('X_SIGN_ALGORITHM', 'sha256'),
            'enable_timestamp' => filter_var(
                env('X_SIGN_ENABLE_TIMESTAMP', 'true'),
                FILTER_VALIDATE_BOOLEAN
            ),
            'replay_window' => (int) env('X_SIGN_REPLAY_WINDOW', 10),
        ]);
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $signature = $request->getHeaderLine('X-Signature');
        $timestamp = $request->getHeaderLine('X-Timestamp');

        if (empty($signature)) {
            throw new MissingHeadersException('X-Signature header is required');
        }

        if ($this->config->enableTimestamp && empty($timestamp)) {
            throw new MissingHeadersException('X-Timestamp header is required');
        }

        $body = (string) $request->getBody();

        try {
            $this->verifier->verify($signature, $body, $this->config, $timestamp);
        } catch (ExpiredRequestException | InvalidSignatureException $e) {
            throw $e;
        }

        return $handler->handle($request);
    }
}
