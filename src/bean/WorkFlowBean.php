<?php

namespace AzimUt\WorkflowApi\bean;

use stdClass;

class WorkFlowBean
{
    /** @var mixed */
    public object $state;

    /** @var mixed */
    public object $payload;

    /** @var String[] */
    public array $commands = [];

    /** @var RouteBlock[] */
    public array $route = [];

    /** @var String[] */
    public array $log = [];

    public function __construct($data)
    {

        $this->payload = new stdClass();
        $this->state = $data->state;
        $this->route = $data->route;
        $this->commands = $data->commands;
    }


}
