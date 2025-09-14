<?php
namespace AzimUt\WorkflowApi;

use AzimUt\WorkflowApi\bean\CommandBlock;
use AzimUt\WorkflowApi\bean\IfElseBlock;
use AzimUt\WorkflowApi\bean\StateBlock;
use AzimUt\WorkflowApi\bean\WorkFlowBean;

class WorkflowExecutor
{
    public function run(WorkFlowBean $workflowConfig, object $payload): array
    {

        $state = $workflowConfig->state;
        $commands = $workflowConfig->commands;
        $route = $workflowConfig->route;
        $log = [];

        // Convert blocks to map by ID
        $blocks = [];
        foreach ($route as $block) {
            $blocks[$block->id] = $block;
        }

        $currentId = 1;
        while ($currentId !== 0) {
            var_dump("Block №". $currentId);
            if (!isset($blocks[$currentId])) {
                throw new \RuntimeException("Workflow block ID {$currentId} not found");
            }

            $block = $blocks[$currentId];
            $log[] = "Executing block ID {$currentId} ({$block->type})";

            switch ($block->type) {
                case 'COMMAND':
                    /** @var $block CommandBlock */

                    $parsed = $this->parseCommand($block->command, $state, $payload);
                    $commandName = $parsed->name;
                    $args = $parsed->args;

                    if (!isset($commands[$commandName])) {
                        throw new \RuntimeException("Command '{$commandName}' is not registered");
                    }

                    $result = call_user_func_array($commands[$commandName], $args);
                    $log[] = "Command `{$commandName}` executed with result: " . var_export($result, true);

                    if (isset($block->target)) {
                        $state->{$block->target} = $result;
                        $log[] = "State[{$block->target}] = " . var_export($result, true);
                    }

                    $currentId = $block->next ?? 0;
                    break;

                case 'IF_ELSE':
                    /** @var IfElseBlock $block */
                    $condition = $block->condition;
                    $result = $this->evaluateCondition($condition, $state, $payload);
                    $log[] = "Condition `{$condition}` evaluated as " . ($result ? 'true' : 'false');
                    $currentId = $result ? $block->yes : $block->no;
                    break;

                case 'STATE':
                    /** @var StateBlock $block */
                    $val = $block->val;
                    $nextBlockId = $block->next;
                    $result = $this->deepField(explode(".", $block->target));

                    $state->{$result} = $val;
                    $log[] = "State field `{$block->next}` set as " . $val;
                    $currentId = $nextBlockId;
                    break;

                default:
                    throw new \RuntimeException("Unknown block type: {$block->type}");
            }
        }

        return [
            'state' => $state,
            'log' => $log
        ];
    }
    protected function parseCommand(string $commandLine, object $state, object $payload): object
    {
        // Split command into tokens — supports quoted strings
        preg_match_all('/\'[^\']*\'|"[^"]*"|\S+/', $commandLine, $matches);
        $tokens = $matches[0];

        $commandName = array_shift($tokens);
        $args = [];

        foreach ($tokens as $token) {
            $token = trim($token, "\"'");

            if (preg_match('/^\$\{(.+?)\}$/', $token, $m)) {
                $path = explode('.', $m[1]);
                $value = null;

                if ($path[0] === 'state') {
                    $value = $this->deepGet($state, array_slice($path, 1));
                } elseif ($path[0] === 'payload') {
                    $value = $this->deepGet($payload, array_slice($path, 1));
                }

                $args[] = $value;
            } else {
                // Literal string
                $args[] = $token;
            }
        }

        return (object) [
            'name' => $commandName,
            'args' => $args
        ];
    }

    protected function evaluateCondition(string $expression, object $state, object $payload): bool
    {
        var_dump($expression);
        $conditionPatterns = [
            ">=",
            "<=",
            "!=",
            "=",
            ">",
            "<",
        ];

        $myPattern = "";
        foreach($conditionPatterns as $conditionPattern){
            if(mb_strpos($expression, $conditionPattern)>0){
                $myPattern = $conditionPattern;
                break;
            }
        }
        $expression = str_replace(" ", "", $expression);

        $arr = explode($myPattern, $expression);

        if(sizeof($arr)>1){

            $left = $this->parseForValue($arr[0], $state, $payload);
            $right = $this->parseForValue($arr[1], $state, $payload);
            if(in_array($myPattern, $conditionPatterns)){
                $left = $left*1;
                $right = $right*1;
            }
            return eval("return {$left}{$myPattern}{$right};");
        }
        return eval("return {$expression};");
    }

    protected function parseForValue(string $strData, object $state, object $payload){
        $value = $strData;
        if (preg_match('/^\$\{(.+?)\}$/', $strData, $m)) {
            $path = explode('.', $m[1]);

            if ($path[0] === 'state') {
                $value = $this->deepGet($state, array_slice($path, 1));
            } elseif ($path[0] === 'payload') {
                $value = $this->deepGet($payload, array_slice($path, 1));
            }
        }
        return $value;
    }

    protected function deepGet(object $data, array $path)
    {
        foreach ($path as $key) {
            if (!is_object($data) || !isset($data->$key)) {
                return null;
            }
            $data = $data->$key;
        }
        return $data;
    }

    protected function deepField(array $path): string
    {
        return join("->", $path);
    }
}
