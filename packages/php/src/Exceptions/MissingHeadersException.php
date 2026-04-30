<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Exceptions;

class MissingHeadersException extends XSignException
{
    public function __construct(string $message = 'Missing required headers')
    {
        parent::__construct($message, 401);
    }
}
