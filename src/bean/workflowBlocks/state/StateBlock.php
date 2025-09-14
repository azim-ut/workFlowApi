<?php

namespace AzimUt\WorkflowApi\bean;

class StateBlock implements RouteBlock
{
    public int $id;
    public string $target;
    public string $val;
    public int $next;
}
