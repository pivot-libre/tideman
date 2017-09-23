<?php
declare(strict_types=1);

date_default_timezone_set('UTC');
$loader = @include __DIR__ . '/../vendor/autoload.php';

if (false === $loader) {
    die(<<<'EOT'
You must set up the project dependencies by running the following commands:

curl -s http://getcomposer.org/installer | php
php composer.phar install

EOT
    );
}

\bitExpert\Slf4PsrLog\LoggerFactory::registerFactoryCallback(function ($channel) {
    if (!\Monolog\Registry::hasLogger($channel)) {
        $logger = new \Monolog\Logger($channel);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler('build/logs/out.log'));
        \Monolog\Registry::addLogger($logger);
    }
    return \Monolog\Registry::getInstance($channel);
});
