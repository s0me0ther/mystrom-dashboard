<?php

declare(strict_types=1);

namespace s0me0ther\MyStromDashboard\Service;

use Psr\Log\LoggerInterface;

class Manager
{
    private readonly Cache $cache;

    public function __construct(
        private readonly \PDO $pdo,
        private readonly LoggerInterface $logger
    ) {
        $this->cache = new Cache();
    }

    public function getMyStromService(): MyStrom
    {
        return $this->cache->get(MyStrom::class, function (): MyStrom {
            return (
                new MyStrom(
                    $this->pdo,
                    time()
                )
            )->setLogger($this->logger);
        });
    }

    public function getMigrateService(): Migrate
    {
        return $this->cache->get(Migrate::class, function (): Migrate {
            return new Migrate(
                $this->pdo,
                $this->logger
            );
        });
    }
}
