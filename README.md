# Project Name

This project is a Task Management API that allows users to manage their task list. It’s built using PHP 8.1 and developed with the Laravel framework.

The API provides the following functionalities:

    Task Management: Users can create, edit, mark as completed, and delete their tasks.
    Task Filtering: When retrieving a list of tasks, users can filter by status, priority, and title/description fields. Full-text search is implemented for the title and description fields.
    Task Sorting: Users can sort tasks by createdAt, completedAt, and priority fields. The API supports sorting by two fields simultaneously, such as priority (desc) and createdAt (asc).
    Subtasks: Any task can have an unlimited number of nested subtasks.

Each task has the following properties:

    Status: Indicates whether the task is to-do or done.
    Priority: A number from 1 to 5 indicating the task’s priority.
    Title: The title of the task.
    Description: A detailed description of the task.
    createdAt: The date and time when the task was created.
    completedAt: The date and time when the task was marked as completed.

There are also some restrictions to ensure the integrity and privacy of tasks:

    Users cannot modify or delete tasks created by others.
    Users cannot delete a task that has already been marked as completed.
    Users cannot mark a task as completed if it has uncompleted subtasks.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

What things you need to install the software and how to install them. For example:

- PHP
- Composer
- Node.js
- NPM

### Installation

A step by step series of examples that tell you how to get a development environment running.

1. Clone the repo: `git clone https://github.com/username/projectname.git`
2. Install PHP dependencies: `composer install`
3. Install JavaScript dependencies: `npm install`
4. Copy the example env file and make the required configuration changes in the .env file: `cp .env.example .env`
5. Generate a new application key: `php artisan key:generate`
6. Run the database migrations: `php artisan migrate`
7. Start the local development server: `php artisan serve`

## Usage

This project provides several endpoints that you can use to interact with the application. Here are some of the main endpoints:

### Task Endpoints

- `GET /api/tasks`: Fetch all tasks.
- `GET /api/tasks/{id}`: Fetch a single task by its ID.
- `POST /api/tasks`: Create a new task. Required parameters: `title`, `description`, `status`, `priority`.
- `PUT /api/tasks/{id}`: Update a task. Required parameters: `title`, `description`, `status`, `priority`.
- `PUT /api/tasks/{id}/complete`: Mark task as completed.
- `DELETE /api/tasks/{id}`: Delete a task.

### Subtask Endpoints

- `GET /api/tasks/{taskId}/subtasks`: Create a new sub task. Required parameters: `title`, `description`, `status`, `priority`. 
- `PUT /api/subtasks/{subtaskId}`: Update a sub task. Required parameters: `title`, `description`, `status`, `priority`.
- `PUT /api/subtasks/{subtaskId}/complete`: Mark sub task as completed.
- `DELETE /api/subtasks/{subtask}`: Delete a subtask for a specific task.

Please note that all `POST`, `PUT`, and `DELETE` requests must be accompanied by a valid authentication token in the `Authorization` header.