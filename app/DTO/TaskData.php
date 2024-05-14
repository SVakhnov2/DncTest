<?php

namespace App\DTO;

class TaskData
{
    public $title;
    public $description;
    public $status;
    public $priority;
    public $completedAt;
    public ?int $parent_id;

    public function __construct(array $data)
    {
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->priority = $data['priority'] ?? null;
        $this->completedAt = $data['completedAt'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'completedAt' => $this->completedAt,
        ];
    }
}
