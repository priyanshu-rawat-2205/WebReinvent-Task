<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .completed {
            text-decoration: line-through;
            color: #888;
        }
        .task-row {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f1f1f1;
            padding: 0.5rem 0.75rem;
            background: #fff;
        }
        .task-row:last-child {
            border-bottom: none;
        }
        .task-title {
            flex: 1;
            margin-left: 0.5rem;
        }
        .task-meta {
            color: #888;
            font-size: 0.9em;
            margin-left: 0.5rem;
        }
        .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.5rem;
        }
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .input-group .form-control {
            border-radius: 2rem 0 0 2rem;
        }
        .input-group .btn {
            border-radius: 0 2rem 2rem 0;
        }
        .show-all-label {
            margin-bottom: 0;
            font-weight: 500;
            color: #3498db;
        }
    </style>
</head>
<body style="background: #f7f7f7;">
    <div class="container py-4">
        <div class="card mx-auto" style="max-width: 700px;">
            <div class="card-body p-3 pb-2">
                <div class="d-flex align-items-center mb-3">
                    <input type="checkbox" id="showAllTasks" class="form-check-input me-2" checked>
                    <label for="showAllTasks" class="show-all-label">Show All Tasks</label>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-collection"></i></span>
                    <input type="text" id="taskInput" class="form-control" placeholder="Project # To Do">
                    <button class="btn btn-success" id="addTask">Add</button>
                </div>
                <div id="taskList">
                    @foreach($tasks as $task)
                        <div class="task-row" data-id="{{ $task->id }}">
                            <input type="checkbox" class="form-check-input task-checkbox me-2" {{ $task->completed ? 'checked' : '' }}>
                            <span class="task-title {{ $task->completed ? 'completed' : '' }}">{{ $task->title }}</span>
                            <span class="task-meta">{{ $task->created_at->diffForHumans() }}</span>
                            <!-- <img src="https://i.pravatar.cc/28?u={{ $task->id }}" class="avatar ms-2" alt="avatar"> -->
                            <button class="btn btn-link text-danger p-0 ms-2 delete-task" title="Delete"><i class="bi bi-trash" style="font-size: 1.2rem;"></i></button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function renderTask(task) {
            return `
                <div class="task-row" data-id="${task.id}">
                    <input type="checkbox" class="form-check-input task-checkbox me-2" ${task.completed ? 'checked' : ''}>
                    <span class="task-title ${task.completed ? 'completed' : ''}">${task.title}</span>
                    <span class="task-meta">${task.created_at_human || 'just now'}</span>
                    <button class="btn btn-link text-danger p-0 ms-2 delete-task" title="Delete"><i class="bi bi-trash" style="font-size: 1.2rem;"></i></button>
                </div>
            `;
        }

        $(document).ready(function() { 
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
                            response.created_at_human = 'just now';
                            $('#taskList').prepend(renderTask(response));
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
            $('#taskInput').keypress(function(e) {
                if (e.which === 13) {
                    $('#addTask').click();
                }
            });
            $(document).on('change', '.task-checkbox', function() {
                const taskItem = $(this).closest('.task-row');
                const taskId = taskItem.data('id');
                const taskTitle = taskItem.find('.task-title');
                const showAll = $('#showAllTasks').is(':checked');

                $.ajax({
                    url: `/tasks/${taskId}`,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.completed) {
                            taskTitle.addClass('completed');
                            if (!showAll) {
                                taskItem.remove();
                            }
                        } else {
                            taskTitle.removeClass('completed');
                        }
                    }
                });
            });

            // Delete task
            $(document).on('click', '.delete-task', function() {
                const taskItem = $(this).closest('.task-row');
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

            // Show all tasks (checkbox toggle)
            $('#showAllTasks').change(function() {
                if ($(this).is(':checked')) {
                    $.ajax({
                        url: '/tasks/show-all',
                        method: 'GET',
                        success: function(tasks) {
                            $('#taskList').empty();
                            tasks.forEach(function(task) {
                                // Add human readable time
                                task.created_at_human = moment(task.created_at).fromNow();
                                $('#taskList').append(renderTask(task));
                            });
                        }
                    });
                } else {
                    // Optionally, you can filter to show only incomplete tasks
                    $.ajax({
                        url: '/tasks/show-all',
                        method: 'GET',
                        success: function(tasks) {
                            $('#taskList').empty();
                            tasks.filter(t => !t.completed).forEach(function(task) {
                                task.created_at_human = moment(task.created_at).fromNow();
                                $('#taskList').append(renderTask(task));
                            });
                        }
                    });
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
</body>
</html> 