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
            <x-adminlte-input name="password" label="Password" id="addUserPassword" type="password" required />
            <x-adminlte-input name="email" label="Email" id="addUserEmail" type="email" required />
        </form>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Create" theme="success" id="createUserBtn" />
            <x-adminlte-button label="Close" theme="danger" data-dismiss="modal" />
        </x-slot>
    </x-adminlte-modal>

    @push('js')
    <script>
        // Ensure script runs only once
        $(document).ready(function() {
            // Remove any existing event listeners to prevent duplicates
            $('#addUserBtn').off('click');
            $('#createUserBtn').off('click');
            $('#updateUserBtn').off('click');
            
            let isSubmitting = false; // Flag to prevent double submissions

            // Open Add User Modal
            $('#addUserBtn').on('click', function(e) {
                e.preventDefault();
                document.getElementById('addUserForm').reset();
                $('#addUserModal').modal('show');
            });

            // Add User Handler with prevention of double clicks
            $('#createUserBtn').on('click', async function(e) {
                e.preventDefault();
                
                // Prevent double submissions
                if (isSubmitting) {
                    return;
                }
                
                isSubmitting = true;
                $(this).prop('disabled', true).text('Creating...'); // Disable button and change text

                const name = document.getElementById('addUserName').value.trim();
                const password = document.getElementById('addUserPassword').value.trim();
                const email = document.getElementById('addUserEmail').value.trim();

                if (!name || !password || !email) {
                    showToast('warning', 'Please fill out all fields.');
                    
                    // Reset button state
                    isSubmitting = false;
                    $(this).prop('disabled', false).text('Create');
                    return;
                }

                try {
                    const res = await fetch('/users', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json', 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ name, password, email })
                    });

                    const responseData = await res.json();

                    if (res.ok && responseData.success) {
                        const newUser = responseData.data;

                        // Add new row to DataTable with correct structure
                        const newRow = table.row.add([
                            newUser.id,
                            newUser.name,
                            newUser.email,
                            `<button class="btn btn-sm btn-primary edit-btn" data-id="${newUser.id}">Edit</button>
                             <button class="btn btn-sm btn-danger delete-btn" data-id="${newUser.id}">Delete</button>`
                        ]).draw().node();

                        // Set data-id attribute on the new row
                        $(newRow).attr('data-id', newUser.id);
                        
                        // Add classes to the cells for easier targeting
                        $(newRow).find('td:eq(1)').addClass('user-name');
                        $(newRow).find('td:eq(2)').addClass('user-email');

                        // Close modal
                        $('#addUserModal').modal('hide');

                        // Show success toast
                        showToast('success', responseData.message);
                    } else {
                        // Handle validation errors or other failures
                        let errorMessage = responseData.message || 'Failed to create user.';
                        
                        if (responseData.errors) {
                            // Display first validation error
                            const firstError = Object.values(responseData.errors)[0][0];
                            errorMessage = firstError;
                        }
                        
                        showToast('error', errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('error', 'An unexpected error occurred. Please try again.');
                } finally {
                    // Always reset button state
                    isSubmitting = false;
                    $('#createUserBtn').prop('disabled', false).text('Create');
                }
            });

            // Prevent form submission on Enter key
            $('#addUserForm').on('submit', function(e) {
                e.preventDefault();
                return false;
            });

            // Edit button handler
            function editBtnHandler(e) {
                e.preventDefault();
                const userId = this.dataset.id;

                fetch(`/users/${userId}`)
                    .then(res => res.json())
                    .then(responseData => {
                        if (responseData.success && responseData.data) {
                            const user = responseData.data;
                            document.getElementById('editUserId').value = user.id;
                            document.getElementById('editUserName').value = user.name;
                            document.getElementById('editUserEmail').value = user.email;

                            $('#editUserModal').modal('show');
                        } else {
                            showToast('error', responseData.message || 'Failed to load user data.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'Failed to load user data. Please try again.');
                    });
            }

            // Delete button handler
            function deleteBtnHandler(e) {
                e.preventDefault();
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
                        try {
                            const res = await fetch(`/users/${userId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                }
                            });

                            const responseData = await res.json();

                            if (res.ok && responseData.success) {
                                // Remove row from DataTable
                                const row = document.querySelector(`tr[data-id="${userId}"]`);
                                table.row(row).remove().draw();

                                // Show success toast
                                showToast('success', responseData.message);
                            } else {
                                showToast('error', responseData.message || 'Failed to delete user.');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            showToast('error', 'An unexpected error occurred. Please try again.');
                        }
                    }
                });
            }

            // Use event delegation for dynamically added buttons
            $(document).off('click', '.edit-btn').on('click', '.edit-btn', editBtnHandler);
            $(document).off('click', '.delete-btn').on('click', '.delete-btn', deleteBtnHandler);

            // Update User Handler
            $('#updateUserBtn').on('click', async function(e) {
                e.preventDefault();
                
                // Prevent double submissions
                if (isSubmitting) {
                    return;
                }
                
                isSubmitting = true;
                $(this).prop('disabled', true).text('Updating...');

                const id = document.getElementById('editUserId').value;
                const name = document.getElementById('editUserName').value.trim();
                const email = document.getElementById('editUserEmail').value.trim();

                if (!name || !email) {
                    showToast('warning', 'Please fill out all fields.');
                    
                    // Reset button state
                    isSubmitting = false;
                    $(this).prop('disabled', false).text('Update');
                    return;
                }

                try {
                    const res = await fetch(`/users/${id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ name, email })
                    });

                    const responseData = await res.json();

                    if (res.ok && responseData.success) {
                        const updated = responseData.data;

                        // Update the row in the DataTable
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.querySelector('.user-name').textContent = updated.name;
                            row.querySelector('.user-email').textContent = updated.email;
                        }

                        $('#editUserModal').modal('hide');

                        // Show success toast
                        showToast('success', responseData.message);
                    } else {
                        // Handle validation errors or other failures
                        let errorMessage = responseData.message || 'Failed to update user.';
                        
                        if (responseData.errors) {
                            // Display first validation error
                            const firstError = Object.values(responseData.errors)[0][0];
                            errorMessage = firstError;
                        }
                        
                        showToast('error', errorMessage);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('error', 'An unexpected error occurred. Please try again.');
                } finally {
                    // Always reset button state
                    isSubmitting = false;
                    $('#updateUserBtn').prop('disabled', false).text('Update');
                }
            });

            // Prevent form submission on Enter key for edit form
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();
                return false;
            });
        });
    </script>
    @endpush
</x-layouts.adminlte-root>