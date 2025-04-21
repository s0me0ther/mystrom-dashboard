<?php

declare(strict_types=1);

namespace s0me0ther\MyStromDashboard\Service;

use Psr\Log\LoggerInterface;
use s0me0ther\MyStromDashboard\Model\MyStromSwitch;
use s0me0ther\MyStromDashboard\Model\MyStromSwitchData;

class MyStrom
{
    private ?string $collectorPath = null;
    private ?LoggerInterface $logger = null;

    public function __construct(
        private readonly \PDO $pdo,
        private readonly int $importTimestamp
    ) {
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function setLogger(?LoggerInterface $logger = null): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function setCollectorPath(string $path): self
    {
        $this->logger?->debug("set collector path [{$path}]");
        $this->collectorPath = $path;

        return $this;
    }

    /**
     * @return MyStromSwitch[]
     *
     * @throws \Exception
     */
    public function fetchActiveSwitches(): array
    {
        $statementSwitches = $this->pdo->query('SELECT * FROM "switch" where "active" = 1');
        if (false === $statementSwitches) {
            throw new \Exception('could not fetch active switches');
        }

        $switches = [];
        while (false !== ($switch = $statementSwitches->fetch(\PDO::FETCH_ASSOC))) {
            /** @var array{id: string, name: string, ip: string, active: string, sort: string, is_input: string} $switch */
            try {
                $switches[] = new MyStromSwitch($switch);
            } catch (\Exception $e) {
                $this->logger?->error("could not init switch object for id [{$switch['id']}] with error [{$e->getMessage()}]");
            }
        }

        $countSwitches = count($switches);
        $switchIds = array_map(fn (MyStromSwitch $switch) => $switch->getId(), $switches);
        $this->logger?->debug("fetched [{$countSwitches}] active switches [".implode(',', $switchIds).']');

        return $switches;
    }

    /**
     * @param MyStromSwitch[] $switches
     *
     * @return MyStromSwitchData[]
     */
    public function fetchDataByCurl(array $switches): array
    {
        $data = [];
        foreach ($switches as $switch) {
            try {
                $switchDataRaw = file_get_contents("http://{$switch->getIp()}/report");
                if (false === $switchDataRaw) {
                    throw new \Exception("could not curl report from [{$switch->getIp()}]");
                }

                /** @var array{power: string, Ws: string, temperature: string} $switchDataArray */
                $switchDataArray = json_decode($switchDataRaw, true, flags: JSON_THROW_ON_ERROR);

                // init data object
                $data[] = new MyStromSwitchData([
                    ...$switchDataArray,
                    'switch_id' => $switch->getId(),
                    'created' => $this->importTimestamp,
                ]);
            } catch (\Exception $e) {
                $this->logger?->error("could not fetch switch data for id [{$switch->getId()}] with error [{$e->getMessage()}]");
            }
        }

        // different return count
        $dataCount = count($data);
        $switchesCount = count($switches);
        if ($dataCount !== $switchesCount) {
            $this->logger?->warning("different amount of switches [{$switchesCount}] and returned curl datas [{$dataCount}]");

            $switchIds = array_map(fn (MyStromSwitch $switch) => $switch->getId(), $switches);
            $switchDataIds = array_map(fn (MyStromSwitchData $switchData) => $switchData->getSwitchId(), $data);
            $this->logger?->warning('requested switch datas for ids ['.implode(',', $switchIds).']');
            $this->logger?->warning('curl switch datas for ids ['.implode(',', $switchDataIds).']');
        }

        return $data;
    }

    /**
     * @param MyStromSwitch[] $switches
     *
     * @return MyStromSwitchData[]
     *
     * @throws \Exception
     */
    public function fetchDataByCollector(array $switches): array
    {
        if (null === $this->collectorPath) {
            throw new \Exception('empty collector path');
        }

        if (!is_file($this->collectorPath) || !is_readable($this->collectorPath)) {
            throw new \Exception("could no access collector path [{$this->collectorPath}]");
        }

        $ips = [];
        $switchIpIdMapping = [];
        foreach ($switches as $switch) {
            $ips[] = $switch->getIp();
            $switchIpIdMapping[$switch->getIp()] = $switch->getId();
        }

        if (0 === count($ips)) {
            throw new \Exception('no ips to collect stats from');
        }

        // collect :)
        $return = shell_exec($this->collectorPath.' '.implode(' ', $ips));
        if (false === $return) {
            throw new \Exception("could not collect stats from collector by path [{$this->collectorPath}]");
        }

        // build datas
        $data = [];

        /** @var array{power: string, Ws: string, temperature: string, ip: string}[] $collectorDatas */
        $collectorDatas = json_decode(trim((string) $return), associative: true, flags: JSON_THROW_ON_ERROR);
        foreach ($collectorDatas as $collectorData) {
            $data[] = new MyStromSwitchData([
                ...$collectorData,
                'switch_id' => $switchIpIdMapping[$collectorData['ip']],
                'created' => $this->importTimestamp,
            ]);
        }

        // different return count
        $dataCount = count($data);
        $switchesCount = count($switches);
        if ($dataCount !== $switchesCount) {
            $this->logger?->warning("different amount of switches [{$switchesCount}] and returned collector datas [{$dataCount}]");

            $switchIds = array_map(fn (MyStromSwitch $switch) => $switch->getId(), $switches);
            $switchDataIds = array_map(fn (MyStromSwitchData $switchData) => $switchData->getSwitchId(), $data);
            $this->logger?->warning('requested switch datas for ids ['.implode(',', $switchIds).']');
            $this->logger?->warning('collected switch datas for ids ['.implode(',', $switchDataIds).']');
        }

        return $data;
    }

    /**
     * @param MyStromSwitchData[] $data
     *
     * @throws \Exception
     */
    public function insertData(array $data): void
    {
        $insertStatement = $this->pdo->prepare('
			INSERT INTO "switch_data" (
				"switch_id",
				"power",
				"ws",
				"temperature",
				"created"
			) VALUES (
				:switchId,
				:power,
				:ws,
				:temperature,
				:created
			)
		');

        // insert rows
        $insertedCount = 0;
        $this->pdo->query('BEGIN TRANSACTION');
        foreach ($data as $row) {
            try {
                $insertStatement->execute([
                    'switchId' => $row->getSwitchId(),
                    'power' => $row->getPower(),
                    'ws' => $row->getWs(),
                    'temperature' => $row->getTemperature(),
                    'created' => $row->getCreated(),
                ]);
                ++$insertedCount;
            } catch (\Exception $e) {
                $this->logger?->error("could not save switch data for id [{$row->getSwitchId()}] with error [{$e->getMessage()}]");
            }
        }
        $this->pdo->query('END TRANSACTION');

        $this->logger?->debug("inserted [{$insertedCount}] rows");
    }
}
