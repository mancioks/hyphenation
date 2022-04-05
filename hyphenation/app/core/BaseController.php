<?php

namespace Core;

use Logger\Logger;
use Psr\Log\LoggerInterface;

class BaseController
{
    protected $logger;

    public function __construct(LoggerInterface $logger = new Logger())
    {
        $this->logger = $logger;
    }
}