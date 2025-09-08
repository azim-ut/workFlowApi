<?php
namespace AzimUt\WorkflowApi;

class WorkflowExecutor
{
    public function run(array $workflowConfig): array
    {
        $state = $workflowConfig['state'];
        $actions = $workflowConfig['actions'];
        $blocks = collect($workflowConfig['workflow'])->keyBy('id');
        $log = [];

        $currentId = 1;

        while ($currentId !== 0) {
            $block = $blocks[$currentId];
            $log[] = "Executing block ID {$currentId} ({$block['type']})";

            switch ($block['type']) {
                case 'ACTION':
                    $action = $block['action'];
                    $log[] = "Running action: $action";
                    // Here you can inject an ActionResolver or use a closure map
                    $state = $this->fakeActionExecutor($action, $state);
                    $currentId = $block['next'] ?? 0;
                    break;

                case 'IF_ELSE':
                    $result = $this->evaluateCondition($block['condition'], $state);
                    $log[] = "Condition `{$block['condition']}` evaluated as " . ($result ? 'true' : 'false');
                    $currentId = $result ? $block['yes'] : $block['no'];
                    break;

                default:
                    throw new \RuntimeException("Unknown block type: {$block['type']}");
            }
        }

        return [
            'state' => $state,
            'log' => $log
        ];
    }

    protected function evaluateCondition(string $expression, array $state): bool
    {
        // WARNING: This is a basic evaluator for demonstration
        // For safety, use a proper expression parser later
        extract(['state' => $state]);
        return eval("return {$expression};");
    }

    protected function fakeActionExecutor(string $action, array $state): array
    {
        // Simulate state changes for demo
        return match ($action) {
            'ClearOverdueInvites' => array_merge($state, ['overdueInvitesCleared' => true]),
            default => $state
        };
    }
}
