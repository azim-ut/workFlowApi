<?php

namespace AzimUt\WorkflowApi\bean;

class ActionBlock implements RouteBlock
{
    public int $id;
    public string $state;
    public string $command;
    public int $next;

    public function __construct($data)
    {
        $this->id = $data->id;
        $this->state = $data->state;
        $this->command = $data->command;
        $this->next = $data->next;
    }
}
