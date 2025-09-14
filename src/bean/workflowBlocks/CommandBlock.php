<?php

namespace AzimUt\WorkflowApi\bean;

class CommandBlock
{
    public string $name;
    public string $command;

    public function __construct($data)
    {
        $this->name = $data->name;
        $this->command = $data->command;
    }
}
