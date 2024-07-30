<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">To Do List</h1>

        <div id="errorAlert" class="alert alert-danger d-none" role="alert">
            <ul id="errorList"></ul>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <form id="addTaskForm" class="mb-4">
                    @csrf
                    <div class="row">
                        <div class="col-auto">
                            <input type="text" id="taskInput" name="task" class="form-control" placeholder="Add new task" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Add Task</button>
                        </div>
                        <div class="col-auto">
                            <button id="showAllTasks" class="btn btn-secondary">Show All Tasks</button>
                        </div>
                    </div>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Task</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        @if($task->status == 0)
                        <tr id="task-{{ $task->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $task->task }}</td>
                            <td>{{ $task->status == 1 ? 'Completed' : 'Non completed' }}</td>
                            <td>
                                
                                <input type="checkbox" class="task-completed" data-id="{{ $task->id }}">
                                
                                <button class="btn btn-danger btn-sm delete-task" data-id="{{ $task->id }}">X</button>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#addTaskForm').on('submit', function(e) {
            e.preventDefault();
            var task = $('#taskInput').val();
            $.ajax({
                url: "{{ route('tasks.store') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    task: task
                },
                success: function(response) {
                    $('#taskInput').val('');
                    $('#errorAlert').addClass('d-none');
                    appendTask(response.task);
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorList = $('#errorList');
                    errorList.empty();
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorList.append('<li>' + value + '</li>');
                        });
                    }
                    $('#errorAlert').removeClass('d-none');
                }
            });
        });

        function appendTask(task) {
            var row = `
            <tr id="task-${task.id}">
                <td>${task.id}</td>
                <td>${task.task}</td>
                <td>${"Non Completed"}</td>
                <td>
                    ${task.status == 1 ? ' ' : `<input type="checkbox" class="task-completed" data-id="${task.id}">`}
                    <button class="btn btn-danger btn-sm delete-task" data-id="${task.id}">X</button>
                </td>
            </tr>
        `;
            $('table tbody').append(row);
        }

        $(document).on('change', '.task-completed', function() {
            var taskId = $(this).data('id');
            var taskStatus = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: `/tasks/${taskId}`,
                type: 'PATCH',
                data: {
                    _token: "{{ csrf_token() }}",
                    status: taskStatus
                },
                success: function(response) {
                    if (taskStatus) {
                        $(`#task-${taskId}`).hide();
                    } else {
                        $(`#task-${taskId}`).show();
                    }
                    $('#successAlert').removeClass('d-none');
                    $('#successMessage').text(response.message);
                },
                error: function(xhr) {
                    $('#errorAlert').removeClass('d-none');
                    $('#errorList').html('<li>An error occurred while updating the task status.</li>');
                }
            });
        });

        $(document).on('click', '.delete-task', function() {
            var taskId = $(this).data('id');

            if (confirm('Are you sure to delete this task?')) {
                $.ajax({
                    url: `/tasks/${taskId}`,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        $(`#task-${taskId}`).remove();
                        $('#successAlert').removeClass('d-none');
                        $('#successMessage').text(response.message);
                    },
                    error: function(xhr) {
                        $('#errorAlert').removeClass('d-none');
                        $('#errorList').html('<li>An error occurred while deleting the task.</li>');
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('showAllTasks').addEventListener('click', function (event) {
        event.preventDefault();
        $('#showAllTasks').on('click', function() {
            $.ajax({
                url: "{{ route('tasks.index') }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('table tbody').empty();
                    response.tasks.forEach(function(task) {
                        var row = `
                    <tr id="task-${task.id}" ${task.is_done ? 'style="display: none;"' : ''}>
                        <td>${task.id}</td>
                        <td>${task.task}</td>
                        <td>${task.status == 0 ? 'Non completed' : 'Completed'}</td>
                        <td>
                            ${task.status == 1 ? ' ' : `<input type="checkbox" class="task-completed" data-id="${task.id}">`}
                            <button class="btn btn-danger btn-sm delete-task" data-id="${task.id}">X</button>
                        </td>
                    </tr>
                `;
                        $('table tbody').append(row);
                    });
                }
            });
        });
    });
});
    </script>
</body>

</html>