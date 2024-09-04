<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>To do list task</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body class="antialiased">
    <div class="container mt-5">
        <h3 class="mb-4">Task List</h3>
        <div class="input-group mb-3">
            <input type="text" id="taskName" class="form-control" placeholder="Enter Task">
            <button id="addTask" class="btn btn-primary">Add Task</button>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="taskList">
                @foreach ($tasks as $task)
                    <tr data-id="{{ $task->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td> <span id="t_id_{{ $task->id }}">{{ $task->task }}</span>
                            <input type="text" class="form-control d-none edit-task-name"
                                value="{{ $task->task }}">
                        </td>
                        <td>{{ $task->status ? 'Done' : 'Not Done' }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-task">✎</button>
                            <button class="btn btn-primary btn-sm d-none save-task"
                                onclick="updateTask({{ $task->id }})">💾</button>
                            <button class="btn btn-secondary btn-sm d-none cancel-edit">✖</button>
                            <button class="btn btn-success btn-sm" onclick="toggleTask({{ $task->id }})">✓</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteTask({{ $task->id }})">✗</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button id="showAllTasks" class="btn btn-secondary">Show All Tasks</button>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
    $(document).ready(function() {
        $('#addTask').on('click', function() {
            const taskName = $('#taskName').val();

            if (taskName) {
                $.ajax({
                    url: '{{ route('tasks.store') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        task: taskName
                    }),
                    success: function(task) {
                        appendTask(task);
                        $('#taskName').val('');
                    },
                    error: function() {
                        alert('Task already exists or an error occurred.');
                    }
                });
            }
        });

        $('#showAllTasks').on('click', function() {
            $.ajax({
                url: '{{ route('tasks.showAll') }}',
                method: 'GET',
                success: function(tasks) {
                    $('#taskList').empty();
                    tasks.forEach(function(task) {
                        appendTask(task);
                    });
                }
            });
        });

        function appendTask(task) {
            const taskItem = `
            <tr data-id="${task.id}">
                <td></td>
                <td>
                     <span id="t_id_${task.id}">${task.task}</span>
                            <input type="text" class="form-control d-none edit-task-name"
                                value="${task.task}">
                    </td>
                <td>${task.status ? 'Done' : 'Not Done'}</td>
                <td>
                    <button class="btn btn-warning btn-sm edit-task">✎</button>
                    <button class="btn btn-primary btn-sm d-none save-task" onclick="updateTask(${task.id})">💾</button>
                    <button class="btn btn-secondary btn-sm d-none cancel-edit">✖</button>
                    <button class="btn btn-success btn-sm" onclick="toggleTask(${task.id})">✓</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteTask(${task.id})">✗</button>
                </td>
            </tr>`;
            $('#taskList').append(taskItem);
            updateRowNumbers();
        }

        function updateRowNumbers() {
            $('#taskList tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        window.toggleTask = function(id) {
            $.ajax({
                url: `/tasks/${id}`,
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(task) {
                    $(`tr[data-id="${id}"]`).find('td:eq(2)').text(task.status ? 'Done' :
                        'Not Done');
                }
            });
        }

        window.deleteTask = function(id) {
            if (confirm('Are you sure to delete this task?')) {
                $.ajax({
                    url: `/tasks/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.success) {
                            $(`tr[data-id="${id}"]`).remove();
                            updateRowNumbers();
                        }
                    }
                });
            }
        }

        $('#taskList').on('click', '.edit-task', function() {
            const $row = $(this).closest('tr');
            $row.find('.task-name').addClass('d-none');
            $row.find('.edit-task-name').removeClass('d-none');
            $row.find('.edit-task, .btn-success, .btn-danger').addClass('d-none');
            $row.find('.save-task, .cancel-edit').removeClass('d-none');
        });

        // Function to cancel editing
        $('#taskList').on('click', '.cancel-edit', function() {
            const $row = $(this).closest('tr');
            $row.find('.task-name').removeClass('d-none');
            $row.find('.edit-task-name').addClass('d-none');
            $row.find('.edit-task, .btn-success, .btn-danger').removeClass('d-none');
            $row.find('.save-task, .cancel-edit').addClass('d-none');
        });

        // Function to save the updated task name
        window.updateTask = function(id) {
            const $row = $(`tr[data-id="${id}"]`);
            const newName = $row.find('.edit-task-name').val();
            $.ajax({
                url: `/tasks/${id}`,
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    task: newName
                },
                success: function(task) {
                    $row.find('#t_id_' + task.id).text(task.task).removeClass('d-none');
                    $row.find('.task-name').text(task.task).removeClass('d-none');
                    $row.find('.edit-task-name').addClass('d-none');
                    $row.find('.edit-task, .btn-success, .btn-danger').removeClass('d-none');
                    $row.find('.save-task, .cancel-edit').addClass('d-none');
                },
                error: function() {
                    alert('An error occurred while updating the task.');
                }
            });
        }
    });
</script>

</html>
