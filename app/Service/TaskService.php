<?php

namespace App\Service;

use App\Models\Task;
use App\Repositories\TaskRepository;
use App\DTO\TaskData;
use Illuminate\Support\Facades\Auth;

class TaskService
{

    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getAllTasks(array $filters): array
    {
        return $this->taskRepository->getAll($filters);
    }

    public function createTask(TaskData $data): Task
    {
        $data->user_id = Auth::id();
        return $this->taskRepository->create($data);
    }

    public function getTaskById(int $id): Task
    {
        return $this->taskRepository->getById($id);
    }

    public function updateTask(TaskData $data, int $id): Task
    {
        $task = $this->taskRepository->getById($id);
        $task->title = $data->title;
        $task->description = $data->description;
        $task->status = $data->status;
        $task->priority = $data->priority;
        $task->user_id = Auth::id();
        $task->parent_id = isset($data->parent_id) ? $data->parent_id : null;
        $task->updated_at = now();
        $task->save();
        return $task;
    }

    public function deleteTask(int $id): void
    {
        $this->taskRepository->delete($id);
    }

    public function markTaskAsCompleted(int $id): Task
    {
        return $this->taskRepository->markAsCompleted($id);
    }

    public function createSubtask(TaskData $data, int $parentId): Task
    {
        $data->parent_id = $parentId;
        $data->completed_at = $data->completed_at ?? null;
        return $this->createTask($data);
    }

    public function updateSubtask(TaskData $data, int $id): Task
    {
        $task = $this->taskRepository->getById($id);
        $data->parent_id = $task->parent_id;
        $data->user_id = Auth::id();
        $data->updatedAt = $data->updatedAt ?? now();
        return $this->updateTask($data, $id);
    }

    public function deleteSubtask(int $id): void
    {
        $task = $this->taskRepository->getById($id);
        if ($task->subtasks()->where('status', 'todo')->exists()) {
            throw new \Exception('Cannot delete a task that has uncompleted subtasks');
        }
        $this->taskRepository->delete($id);
    }

    public function markSubtaskAsCompleted(int $id): Task
    {
        $task = $this->taskRepository->getById($id);
        if ($task->subtasks()->where('status', 'todo')->exists()) {
            throw new \Exception('Cannot mark a task as completed if it has uncompleted subtasks');
        }
        return $this->taskRepository->markAsCompleted($id);
    }

}
