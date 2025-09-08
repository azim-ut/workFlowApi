<?php
namespace AzimUt\WorkflowApi;

class WorkflowBuilder
{
    protected string $path;

    public function fromJsonFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Workflow JSON file not found: $path");
        }

        $this->path = $path;
        return $this;
    }

    public function build(): array
    {
        $raw = file_get_contents($this->path);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON: " . json_last_error_msg());
        }

        // Optional: validate required keys
        foreach (['payload', 'state', 'actions', 'workflow', 'log'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \RuntimeException("Missing required key: $key");
            }
        }

        return $data;
    }
}
