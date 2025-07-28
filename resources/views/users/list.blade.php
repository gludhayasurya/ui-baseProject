<x-layouts.adminlte-root :title="'Users'" :contentHeader="'Users List'">
    <!-- Add User Button -->
    <x-adminlte-button label="Add User" theme="primary" icon="fas fa-user-plus" id="addUserBtn" class="mb-3"/>

    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="mydataTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr data-id="{{ $user->id }}">
                        <td>{{ $user->id }}</td>
                        <td class="user-name">{{ $user->name }}</td>
                        <td class="user-email">{{ $user->email }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-btn" data-id="{{ $user->id }}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $user->id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Edit User Modal -->
    <x-adminlte-modal id="editUserModal" title="Edit User" theme="teal" size="md" static-backdrop>
        <form id="editUserForm">
            @csrf
            <input type="hidden" id="editUserId" name="id" />
            <x-adminlte-input name="name" label="Name" id="editUserName" required />
            <x-adminlte-input name="email" label="Email" id="editUserEmail" type="email" required />
        </form>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Update" theme="success" id="updateUserBtn" />
            <x-adminlte-button label="Close" theme="danger" data-dismiss="modal" />
        </x-slot>
    </x-adminlte-modal>

    <!-- Add User Modal -->
    <x-adminlte-modal id="addUserModal" title="Add User" theme="purple" size="md" static-backdrop>
        <form id="addUserForm">
            @csrf
            <x-adminlte-input name="name" label="Name" id="addUserName" required />
            <x-adminlte-input name="email" label="Email" id="addUserEmail" type="email" required />
        </form>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Create" theme="success" id="createUserBtn" />
            <x-adminlte-button label="Close" theme="danger" data-dismiss="modal" />
        </x-slot>
    </x-adminlte-modal>

    @push('js')
    <script>
       
        // Open Add User Modal
        document.getElementById('addUserBtn').addEventListener('click', () => {
            document.getElementById('addUserName').value = '';
            document.getElementById('addUserEmail').value = '';
            $('#addUserModal').modal('show');
        });

        // Add User Handler
        document.getElementById('createUserBtn').addEventListener('click', async () => {
            const name = document.getElementById('addUserName').value.trim();
            const email = document.getElementById('addUserEmail').value.trim();

            if (!name || !email) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill out all fields.'
                });
                return;
            }

            const res = await fetch('/users', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json', 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, email })
            });

            if (res.ok) {
                const newUser = await res.json();

                const newRow = table.row.add([
                    newUser.id,
                    newUser.name,
                    newUser.email,
                    `<button class="btn btn-sm btn-primary edit-btn" data-id="${newUser.id}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${newUser.id}">Delete</button>`
                ]).draw().node();

                $(newRow).attr('data-id', newUser.id);

                // Attach event listeners for new buttons
                $(newRow).find('.edit-btn').on('click', editBtnHandler);
                $(newRow).find('.delete-btn').on('click', deleteBtnHandler);

                $('#addUserModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Created!',
                    text: 'User has been created successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to create user.'
                });
            }
        });

        // Edit button handler
        function editBtnHandler() {
            const userId = this.dataset.id;

            fetch(`/users/${userId}`)
                .then(res => res.json())
                .then(user => {
                    document.getElementById('editUserId').value = user.id;
                    document.getElementById('editUserName').value = user.name;
                    document.getElementById('editUserEmail').value = user.email;

                    $('#editUserModal').modal('show');
                });
        }

        // Delete button handler
        function deleteBtnHandler() {
            const userId = this.dataset.id;

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const res = await fetch(`/users/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    });

                    if (res.ok) {
                        const table = $('#mydataTable').DataTable();
                            const row = document.querySelector(`tr[data-id="${userId}"]`);
                            table.row(row).remove().draw();

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'User has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete user.'
                        });
                    }
                }
            });
        }

        // Attach existing edit buttons handler
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', editBtnHandler);
        });

        // Attach existing delete buttons handler
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', deleteBtnHandler);
        });

        // Update User Handler
        document.getElementById('updateUserBtn').addEventListener('click', async () => {
            const id = document.getElementById('editUserId').value;
            const name = document.getElementById('editUserName').value.trim();
            const email = document.getElementById('editUserEmail').value.trim();

            if (!name || !email) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please fill out all fields.'
                });
                return;
            }

            const res = await fetch(`/users/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ name, email })
            });

            if (res.ok) {
                const updated = await res.json();

                const row = document.querySelector(`tr[data-id="${id}"]`);
                row.querySelector('.user-name').textContent = updated.name;
                row.querySelector('.user-email').textContent = updated.email;

                $('#editUserModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'User has been updated successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update user.'
                });
            }
        });
    </script>
    @endpush
</x-layouts.adminlte-root>
