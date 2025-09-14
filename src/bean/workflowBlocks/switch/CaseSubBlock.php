<?php

namespace AzimUt\WorkflowApi\bean;

class CaseSubBlock
{

    /** @var string */
    public string $condition;

    /** @var int */
    public int $next;

    public function __construct($data = null)
    {
        $this->next = 0;
        $this->condition = "";
    }


}
