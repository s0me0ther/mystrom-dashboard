<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use s0me0ther\MyStromDashboard\Service\Manager;

include_once __DIR__.'/bootstrap.php';

// define configs
$sqliteDBPath = '/data/app/solarstats.db';
$collectorPath = '/usr/local/bin/mystrom-collector';

$logger = new Logger('solarstats');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
$logger->debug("begin solarstats processing with sqlite db [{$sqliteDBPath}]");

try {
    $pdo = new PDO("sqlite:{$sqliteDBPath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    $logger->error("could not open sqlite database file [{$e->getMessage()}] with error [{$e->getMessage()}]");

    exit;
}

// instance manager
$manager = new Manager($pdo, $logger);

// migrate db
$migrateService = $manager->getMigrateService();
$migrateService->migrate();

// get myStrom service
$myStromService = $manager->getMyStromService();
$myStromService->setCollectorPath($collectorPath);

// fetch active switches
$activeSwitches = $myStromService->fetchActiveSwitches();
if (0 === count($activeSwitches)) {
    $logger->info('no active switches found, nothing to do..');

    exit;
}

$imported = false;

try {
    $myStromService->insertData($myStromService->fetchDataByCollector($activeSwitches));
    $imported = true;
} catch (Exception $e) {
    $logger->error("could not handle fetch / insert by collector [{$e->getMessage()}]");
}

if (!$imported) {
    try {
        $logger->notice('fallback to curl fetch mode');
        $myStromService->insertData($myStromService->fetchDataByCurl($activeSwitches));
    } catch (Exception $e) {
        $logger->error("could not handle fetch / insert by curl [{$e->getMessage()}]");
    }
}

$logger->debug('done processing solarstats');
