<?php

namespace PivotLibre\Tideman;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;

class LoggerFactory
{
    private $logFile;

    public function __construct($logFile = null)
    {
        if (empty($logFile)) {
            $logFile = getenv('TIDEMAN_TEST_LOGFILE');
        }
        if (empty($logFile)) {
            $logFile = __DIR__ . '/../build/logs/out.log';
        }
        $this->logFile = $logFile;
    }

    private function logger($channel) : Logger
    {
        $logger = new Logger($channel);
        $logger->pushHandler(new StreamHandler($this->logFile));

        return $logger;
    }

    public function __invoke($logAwareObject)
    {
        if ($logAwareObject instanceof LoggerAwareInterface) {
            $logAwareObject->setLogger($this->logger(get_class($logAwareObject)));
        }
    }
}
