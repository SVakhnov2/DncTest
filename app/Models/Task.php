<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class Task extends Model
{
    use HasFactory;

    protected $table = 'task';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'user_id',
        'parent_id',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_id')->with('subtasks');
    }

    public function allSubtasks()
    {
        return $this->subtasks()->with('allSubtasks');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }
}
