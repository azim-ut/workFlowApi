<?php

namespace AzimUt\WorkflowApi\bean;

class SwitchBlock implements RouteBlock
{

    /** @var int */
    public int $id = 0;

    /** @var string */
    public string $target;

    /** @var CaseSubBlock[] */
    public array $case = [];

    /** @var CaseSubBlock */
    public CaseSubBlock $default;
}
