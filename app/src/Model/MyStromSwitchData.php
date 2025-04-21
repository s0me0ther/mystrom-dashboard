<?php

declare(strict_types=1);

namespace s0me0ther\MyStromDashboard\Model;

class MyStromSwitchData
{
    private int $switchId;
    private float $power;
    private float $ws;
    private float $temperature;
    private int $created;

    /**
     * @param array{switch_id: int|string, power: string, Ws: string, temperature: string, created: int|string} $data
     */
    public function __construct(array $data)
    {
        $this->switchId = (int) $data['switch_id'];
        $this->power = (float) $data['power'];
        $this->ws = (float) $data['Ws'];
        $this->temperature = (float) $data['temperature'];
        $this->created = (int) $data['created'];
    }

    public function getSwitchId(): int
    {
        return $this->switchId;
    }

    public function getPower(): float
    {
        return $this->power;
    }

    public function getWs(): float
    {
        return $this->ws;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getCreated(): int
    {
        return $this->created;
    }
}
