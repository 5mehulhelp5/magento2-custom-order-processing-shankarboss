<?php
namespace Vendor\CustomOrderProcessing\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Handler extends StreamHandler
{
    /**
     * Constructor
     *
     * @param string $stream
     * @param int $level
     */
    public function __construct()
    {
        parent::__construct(BP . '/var/log/rate_limit.log', Logger::WARNING);
    }
}
