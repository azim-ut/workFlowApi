<?php
namespace AzimUt\WorkflowApi;

class WorkflowService
{
    public static function builder(): WorkflowBuilder
    {
        return new WorkflowBuilder();
    }

    public static function executor(): WorkflowExecutor
    {
        return new WorkflowExecutor();
    }
}
