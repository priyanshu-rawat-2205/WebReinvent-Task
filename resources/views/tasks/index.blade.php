<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .completed {
            text-decoration: line-through;
            color: #888;
        }
        .task-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .task-title {
            flex-grow: 1;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Todo List</h1>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="taskInput" class="form-control" placeholder="Enter new task">
                    <button class="btn btn-primary" id="addTask">Add Task</button>
                </div>
            </div>
            <div class="col-md-6">
                <button class="btn btn-secondary" id="showAllTasks">Show All Tasks</button>
            </div>
        </div>

        <div id="taskList" class="list-group">
            @foreach($tasks as $task)
                <div class="task-item" data-id="{{ $task->id }}">
                    <input type="checkbox" class="task-checkbox" {{ $task->completed ? 'checked' : '' }}>
                    <span class="task-title {{ $task->completed ? 'completed' : '' }}">{{ $task->title }}</span>
                    <button class="btn btn-danger btn-sm delete-task">Delete</button>
                </div>
            @endforeach
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add new task
            $('#addTask').click(function() {
                const title = $('#taskInput').val().trim();
                if (title) {
                    $.ajax({
                        url: '/tasks',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            title: title
                        },
                        success: function(response) {
                            const taskHtml = `
                                <div class="task-item" data-id="${response.id}">
                                    <input type="checkbox" class="task-checkbox">
                                    <span class="task-title">${response.title}</span>
                                    <button class="btn btn-danger btn-sm delete-task">Delete</button>
                                </div>
                            `;
                            $('#taskList').prepend(taskHtml);
                            $('#taskInput').val('');
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                alert('This task already exists!');
                            }
                        }
                    });
                }
            });

            // Handle Enter key
            $('#taskInput').keypress(function(e) {
                if (e.which === 13) {
                    $('#addTask').click();
                }
            });

            // Toggle task completion
            $(document).on('change', '.task-checkbox', function() {
                const taskItem = $(this).closest('.task-item');
                const taskId = taskItem.data('id');
                const taskTitle = taskItem.find('.task-title');

                $.ajax({
                    url: `/tasks/${taskId}`,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.completed) {
                            taskTitle.addClass('completed');
                        } else {
                            taskTitle.removeClass('completed');
                        }
                    }
                });
            });

            // Delete task
            $(document).on('click', '.delete-task', function() {
                const taskItem = $(this).closest('.task-item');
                const taskId = taskItem.data('id');

                if (confirm('Are you sure you want to delete this task?')) {
                    $.ajax({
                        url: `/tasks/${taskId}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            taskItem.remove();
                        }
                    });
                }
            });

            // Show all tasks
            $('#showAllTasks').click(function() {
                $.ajax({
                    url: '/tasks/show-all',
                    method: 'GET',
                    success: function(tasks) {
                        $('#taskList').empty();
                        tasks.forEach(function(task) {
                            const taskHtml = `
                                <div class="task-item" data-id="${task.id}">
                                    <input type="checkbox" class="task-checkbox" ${task.completed ? 'checked' : ''}>
                                    <span class="task-title ${task.completed ? 'completed' : ''}">${task.title}</span>
                                    <button class="btn btn-danger btn-sm delete-task">Delete</button>
                                </div>
                            `;
                            $('#taskList').append(taskHtml);
                        });
                    }
                });
            });
        });
    </script>
</body>
</html> 