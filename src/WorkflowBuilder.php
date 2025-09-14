<?php
namespace AzimUt\WorkflowApi;

use AzimUt\WorkflowApi\bean\WorkFlowBean;

class WorkflowBuilder
{
    protected string $path;
    protected array $commands = [];

    public function fromJsonFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Workflow JSON file not found: $path");
        }

        $this->path = $path;
        return $this;
    }

    public function setCommand(string $name, callable $callback = null): WorkflowBuilder{
        if($callback == null){
            $callback = $name;
        }
        $this->commands[$name] = $callback;
        return $this;
    }

    public function build(): WorkFlowBean
    {
        $raw = file_get_contents($this->path);
        $data = new WorkFlowBean(json_decode($raw, false));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON: " . json_last_error_msg());
        }

        $data->commands = $this->commands;
        // Optional: validate required keys
        foreach (['state', 'commands', 'route', 'log'] as $key) {

            if (!isset($data->$key)) {
                throw new \RuntimeException("Missing required key: $key");
            }
        }

        return $data;
    }
}
