@extends('layouts.admin')

@section('title', 'Categories')

@section('content')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Category List</span>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#categoryCreateModal">
                <i class="bi bi-plus-lg me-1"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 130px;">Created</th>
                            <th class="text-end" style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody">


                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.categories.create')
    @include('admin.categories.edit')
    @include('admin.categories.delete')



@push('scripts')
        <script>
            getCategories();

            async function getCategories(){
                let URL = '{{ url("/api/v1/categories") }}';
                let token = localStorage.getItem('token');
                let tbody = document.getElementById('categoriesTableBody');

                try{
                    let response = await axios.get(URL,{
                        headers: { Authorization: 'Bearer ' + token }
                    });

                    let categories = response.data['data'] || [];
                    tbody.innerHTML = '';
                    categories.forEach((item)=>{
                        let created = item['created_at'] ? item['created_at'].substring(0, 10) : '-';
                        let statusBadge = item['status'] ? '<span class="badge text-bg-success">Active</span>':'<span class="badge text-bg-secondary">Inactive</span>';
                        tbody.innerHTML += (`
                        <tr>
                            <td>${item['id']}</td>
                            <td class="fw-semibold">${item['name']}</td>
                            <td class="text-muted">${item['description']}</td>
                            <td>${statusBadge}</td>
                            <td class="text-muted">${created}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editCategory(${item['id']})">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCategory(${item['id']})">Delete</button>
                            </td>
                        </tr>
                        `);
                    });
                }catch (err){
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Failed to load categories.</td></tr>';
                    showErrorToast(getErrorMessage(err,'Failed to load categories.'));
                }
            }

        </script>
    @endpush



@endsection
