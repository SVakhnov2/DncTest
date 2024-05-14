<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use App\DTO\TaskData;
use App\Service\TaskService;
use App\Http\Requests\TaskRequest;
use Illuminate\Support\Facades\Auth;
use App\Policies\TaskPolicy;
use App\Models\User;
use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     title="Todo List API",
 *     version="0.0.1",
 *     description="A simple API to manage tasks and subtasks",
 *     @OA\Contact(
 *         email="ssvakhnov@gmail.com",
 *         name="Stanislav Vakhnov"
 *     )
 * )
 */

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="status", type="string", enum={"todo", "done"}),
 *     @OA\Property(property="priority", type="integer", format="int32", minimum=1, maximum=5),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true)
 * ),
 * @OA\Schema(
 *     schema="Subtask",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="status", type="string", enum={"todo", "done"}),
 *     @OA\Property(property="priority", type="integer", format="int32", minimum=1, maximum=5),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="parent_id", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true)
 * )
 */

class TaskController extends Controller
{

    use AuthorizesRequests;
    protected $taskService;
    protected $taskRepository;

    public function __construct(TaskService $taskService, TaskRepository $taskRepository)
    {
        $this->taskService = $taskService;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @OA\Get(
     *     path="/tasks",
     *     summary="Get all tasks",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Task::query();

        $query->where('user_id', Auth::id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('sort_by')) {
            $sortFields = explode(',', $request->sort_by);
            $direction = $request->has('sort_direction') ? $request->sort_direction : 'asc';

            foreach ($sortFields as $field) {
                $query->orderBy(trim($field), $direction);
            }
        }

        return $query->get();
    }

    /**
     * @OA\Post(
     *     path="/tasks",
     *     summary="Create a new task",
     *     @OA\RequestBody(
     *         request="Task",
     *         description="Task object that needs to be added to the store",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "description", "status", "priority"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"todo", "done"}),
     *             @OA\Property(property="priority", type="integer", format="int32", minimum=1, maximum=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(TaskRequest $request)
    {
        $task = new Task;
        $task->title = $request->title;
        $task->description = $request->description;
        $task->status = $request->status;
        $task->priority = $request->priority;
        $task->user_id = Auth::id();
        $task->save();

        return response()->json($task, 201);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{task}",
     *     summary="Get task by id",
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of task to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function show(Task $task)
    {
        $task->load('allSubtasks');

        return response()->json($task);
    }

    /**
     * @OA\Put(
     *     path="/tasks/{task}",
     *     summary="Update task by id",
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of task to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Task",
     *         description="Task object that needs to be updated",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "description", "status", "priority"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"todo", "done"}),
     *             @OA\Property(property="priority", type="integer", format="int32", minimum=1, maximum=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function update(TaskRequest $request, $id)
    {
        $task = $this->taskService->getTaskById($id);

        $this->authorize('update', $task);

        $taskData = new TaskData($request->validated());
        return $this->taskService->updateTask($taskData, $id);
    }

    /**
     * @OA\Delete(
     *     path="/tasks/{task}",
     *     summary="Delete task by id",
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of task to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $task = $this->taskService->getTaskById($id);

        if ($task->status == 'completed') {
            return response()->json(['error' => 'Completed tasks cannot be deleted'], 403);
        }

        $this->authorize('delete', $task);

        return $this->taskService->deleteTask($id);
    }

    /**
     * @OA\Put(
     *     path="/tasks/{task}/complete",
     *     summary="Mark task as completed",
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of task to mark as completed",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task marked as completed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function markAsCompleted($id)
    {
        $task = $this->taskService->getTaskById($id);

        if ($task->subtasks()->where('status', '!=', 'done')->exists()) {
            return response()->json(['error' => 'Tasks with uncompleted subtasks cannot be marked as completed'], 403);
        }

        $this->authorize('update', $task);

        $task->status = TaskStatus::DONE;
        $task->completed_at = now();
        $task->save();

        return response()->json($task);
    }

    /**
     * @OA\Post(
     *     path="/tasks/{task}/subtasks",
     *     summary="Create a new subtask",
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID of task to add subtask to",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Subtask",
     *         description="Subtask object that needs to be added to the store",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "description", "status", "priority"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"todo", "done"}),
     *             @OA\Property(property="priority", type="integer", format="int32", minimum=1, maximum=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subtask created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Subtask")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function storeSubtask(TaskRequest $request, $parentId)
    {
        $taskData = new TaskData($request->validated());
        return $this->taskService->createSubtask($taskData, $parentId);
    }

    /**
     * @OA\Put(
     *     path="/subtasks/{subtask}",
     *     summary="Update subtask by id",
     *     @OA\Parameter(
     *         name="subtask",
     *         in="path",
     *         description="ID of subtask to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         request="Subtask",
     *         description="Subtask object that needs to be updated",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "description", "status", "priority"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"todo", "done"}),
     *             @OA\Property(property="priority", type="integer", format="int32", minimum=1, maximum=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subtask updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Subtask")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subtask not found"
     *     )
     * )
     */
    public function updateSubtask(TaskRequest $request, $id)
    {
        $subtask = $this->taskService->getTaskById($id);
        $this->authorize('update', $subtask);

        $taskData = new TaskData($request->validated());
        return $this->taskService->updateSubtask($taskData, $id);
    }

    /**
     * @OA\Delete(
     *     path="/subtasks/{subtask}",
     *     summary="Delete subtask by id",
     *     @OA\Parameter(
     *         name="subtask",
     *         in="path",
     *         description="ID of subtask to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Subtask deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subtask not found"
     *     )
     * )
     */
    public function destroySubtask($id)
    {
        $subtask = $this->taskService->getTaskById($id);
        $this->authorize('delete', $subtask);

        return $this->taskService->deleteSubtask($id);
    }

    /**
     * @OA\Put(
     *     path="/subtasks/{subtask}/complete",
     *     summary="Mark subtask as completed",
     *     @OA\Parameter(
     *         name="subtask",
     *         in="path",
     *         description="ID of subtask to mark as completed",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subtask marked as completed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Subtask")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function markSubtaskAsCompleted($id)
    {
        $task = $this->taskService->getTaskById($id);
        $this->authorize('update', $task);

        if ($task->subtasks()->where('status', 'todo')->exists()) {
            return response()->json(['error' => 'Tasks with uncompleted subtasks cannot be marked as completed'], 403);
        }

        $task->completed_at = now();
        $task->save();

        return $this->taskService->markSubtaskAsCompleted($id);
    }
}
