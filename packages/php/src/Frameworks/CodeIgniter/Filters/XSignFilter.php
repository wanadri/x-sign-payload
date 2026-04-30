<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Frameworks\CodeIgniter\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\XSign as XSignConfig;
use Wanadri\XSignPayload\Core\Config;
use Wanadri\XSignPayload\Core\SignatureVerifier;
use Wanadri\XSignPayload\Exceptions\ExpiredRequestException;
use Wanadri\XSignPayload\Exceptions\InvalidSignatureException;
use Wanadri\XSignPayload\Exceptions\MissingHeadersException;

class XSignFilter implements FilterInterface
{
    private SignatureVerifier $verifier;
    private Config $config;

    public function __construct()
    {
        $this->verifier = new SignatureVerifier();
        
        $ciConfig = new XSignConfig();
        
        $this->config = new Config([
            'secret' => $ciConfig->secret,
            'algorithm' => $ciConfig->algorithm,
            'enable_timestamp' => $ciConfig->enableTimestamp,
            'replay_window' => $ciConfig->replayWindow,
        ]);
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $signature = $request->getHeaderLine('X-Signature');
        $timestamp = $request->getHeaderLine('X-Timestamp');

        if (empty($signature)) {
            throw new MissingHeadersException('X-Signature header is required');
        }

        if ($this->config->enableTimestamp && empty($timestamp)) {
            throw new MissingHeadersException('X-Timestamp header is required');
        }

        try {
            $this->verifier->verify(
                $signature,
                (string) $request->getBody(),
                $this->config,
                $timestamp
            );
        } catch (ExpiredRequestException | InvalidSignatureException $e) {
            throw $e;
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
