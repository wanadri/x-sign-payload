<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Exceptions;

class InvalidSignatureException extends XSignException
{
    public function __construct(string $message = 'Invalid signature')
    {
        parent::__construct($message, 401);
    }
}
