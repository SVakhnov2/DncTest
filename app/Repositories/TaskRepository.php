<?php

namespace App\Repositories;

use App\DTO\TaskData;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use \Exeption;

class TaskRepository
{
    public function getAll(array $filters): array
    {
        $query = Task::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['sort'])) {
            $sortFields = explode(':', $filters['sort']);
            foreach ($sortFields as $field) {
                $direction = starts_with($field, '-') ? 'desc' : 'asc';
                $field = ltrim($field, '-');
                $query->orderBy($field, $direction);
            }
        }

        $query->with('subtasks');

        return $query->get();
    }

    public function create(TaskData $data): Task
    {
        return Task::create([
            'title' => $data->title,
            'description' => $data->description,
            'status' => $data->status,
            'priority' => $data->priority,
            'user_id' => $data->user_id,
            'parent_id' => $data->parent_id,
        ]);
    }

    public function getById(int $id): Task
    {
        return Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function update(TaskData $data, int $id): Task
    {
        $task = $this->getById($id);
        $task->update([
            'title' => $data->title,
            'description' => $data->description,
            'status' => $data->status,
            'priority' => $data->priority,
            'user_id' => $data->user_id,
            'parent_id' => $data->parent_id,
        ]);
        return $task;
    }

    public function delete(int $id): void
    {
        $task = $this->getById($id);

        if ($task->user_id !== Auth::id()) {
            throw new \Exception('You are not allowed to delete this task');
        }

        if ($task->status === 'done') {
            throw new \Exception('Cannot delete completed task');
        }

        $task->delete();
    }

    public function markAsCompleted(int $id): Task
    {
        $task = $this->getById($id);

        $task->load('subtasks');

        foreach ($task->subtasks as $subtask) {
            if ($subtask->status != 'done') {
                throw new \Exception('Cannot mark a task as completed if it has uncompleted subtasks');
            }
        }

        $task->update(['status' => 'done', 'completed_at' => now()]);
        return $task;
    }

    public function getSubtasks(int $id): Task
    {
        $task = $this->getById($id);
        $task->load(['subtasks' => function ($query) {
            $query->with('subtasks');
        }]);
        return $task;
    }

}
