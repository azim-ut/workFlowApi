<?php

namespace AzimUt\WorkflowApi\bean;

class IfElseBlock implements RouteBlock
{

    /** @var int */
    public int $id = 0;

    /** @var string */
    public string $condition;

    /** @var int */
    public int $yes;

    /** @var int */
    public int $no;
}
