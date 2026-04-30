<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Exceptions;

class ExpiredRequestException extends XSignException
{
    public function __construct(string $message = 'Request has expired')
    {
        parent::__construct($message, 401);
    }
}
