<?php

declare(strict_types=1);

namespace s0me0ther\MyStromDashboard\Model;

class MyStromSwitch
{
    private int $id;
    private string $name;
    private string $ip;
    private bool $active;
    private int $sort;
    private bool $isInput;

    /**
     * @param array{id: string, name: string, ip: string, active: string, sort: string, is_input: string} $data
     */
    public function __construct(array $data)
    {
        $this->id = (int) $data['id'];
        $this->name = $data['name'];
        $this->ip = $data['ip'];
        $this->active = (bool) $data['active'];
        $this->sort = (int) $data['sort'];
        $this->isInput = (bool) $data['is_input'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function isInput(): bool
    {
        return $this->isInput;
    }
}
