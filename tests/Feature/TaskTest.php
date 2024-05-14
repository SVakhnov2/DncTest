<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function testTaskCreation()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'status' => 'todo',
            'priority' => 1,
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Test Task',
                'description' => 'This is a test task',
                'status' => 'todo',
                'priority' => 1,
            ]);

        $this->assertDatabaseHas('tasks', $taskData);
    }
}
