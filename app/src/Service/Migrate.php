<?php

declare(strict_types=1);

namespace s0me0ther\MyStromDashboard\Service;

use Psr\Log\LoggerInterface;

class Migrate
{
    public function __construct(
        private readonly \PDO $pdo,
        private readonly LoggerInterface $logger
    ) {}

    public function migrate(): void
    {
        $this->logger->debug('Start migrating');

        $migrated = [];

        try {
            $migrated = $this->getMigrated();
        } catch (\Exception $e) {
            $this->logger->debug("Could not fetch migrate table data: [{$e->getMessage()}]");
        }

        $migrateDirectory = ROOT_PATH.'/src/Migrate';
        foreach (scandir($migrateDirectory) as $migrateFile) {
            // do not migrate folders :)
            if (in_array($migrateFile, ['.', '..'], true)) {
                continue;
            }

            $name = $migrateFile;

            if (!array_key_exists($name, $migrated)) {
                $this->logger->debug("Migrate [{$name}]");

                $migrationFilePath = "{$migrateDirectory}/{$migrateFile}";
                $content = file_get_contents($migrationFilePath);

                if (false === $content) {
                    throw new \Exception("could not load migration file content [{$migrationFilePath}]");
                }

                // migrate
                $this->pdo->exec(trim($content));

                // log the migration
                $this->pdo->exec('INSERT INTO "migrate" ("name", "created") VALUES (\''.$name.'\', \''.time().'\')');
            }
        }
    }

    /**
     * @return array<string, int>
     *
     * @throws \Exception
     */
    private function getMigrated(): array
    {
        $migrated = [];
        $query = $this->pdo->query('SELECT * FROM "migrate"', \PDO::FETCH_ASSOC);

        if (false === $query) {
            throw new \Exception('could not fetch migrations from database');
        }

        foreach ($query as $row) {
            if (!is_string($row['name']) || !is_int($row['created'])) {
                throw new \UnexpectedValueException('Invalid migration row format');
            }

            $migrated[$row['name']] = $row['created'];
        }

        return $migrated;
    }
}
