@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Product List</span>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#productCreateModal">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 70px;">#</th>
                            <th>Product</th>
                            <th style="width: 140px;">SKU</th>
                            <th style="width: 160px;">Category</th>
                            <th style="width: 100px;">Unit</th>
                            <th style="width: 120px;">Price</th>
                            <th style="width: 110px;">Stock</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 130px;">Created</th>
                            <th class="text-end" style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">



                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.products.create')
    @include('admin.products.edit')
    @include('admin.products.delete')


    @push('scripts')
        <script>
            getProducts();
            loadProductCategories();

            async function loadProductCategories() {
                let URL = '{{ url("/api/v1/categories") }}';
                let token = localStorage.getItem('token');

                try {
                    let response = await axios.get(URL,{
                        headers: { Authorization: 'Bearer ' + token}
                    });

                    let categories = response.data['data'] || [];
                    let createSelect = document.getElementById('productCategoryId');
                    let editSelect = document.getElementById('productEditCategoryId');

                    if(createSelect){
                        createSelect.innerHTML = '<option value="" selected disabled>Select Category</option>';
                        categories.forEach((c) => {
                            createSelect.innerHTML += '<option value="' + c.id + '">' + (c.name) + '</option>';
                        })
                    }


                    if(editSelect){
                        editSelect.innerHTML = '<option value="" selected disabled>Select Category</option>';
                        categories.forEach((c) => {
                            editSelect.innerHTML += '<option value="' + c.id + '">' + (c.name) + '</option>';
                        })
                    }

                } catch (err) {
                    showErrorToast(getErrorMessage(err, 'Failed to load categories.'))
                }



            }

            async function getProducts() {
                let URL = '{{ url("/api/v1/products") }}';
                let token = localStorage.getItem('token');
                let tbody = document.getElementById('productTableBody')
                try {
                        let response = await axios.get(URL, {
                            headers: {
                                Authorization: 'Bearer ' + token
                            }
                        });
                    let products = response.data['data'] || [];
                    tbody.innerHTML = '';
                    products.forEach((item) => {
                        let created = item['created_at'] ? item['created_at'].substring(0, 10) : '-';
                        let statusBadge = item['status'] ? '<span class="badge text-bg-success">Active</span>' :
                            '<span class="badge text-bg-secondary">Inactive</span>';
                        let categoryName = item['category'] && item['category']['name'] ? item['category']['name'] : '-';
                        let price = item['price'] != null ? parseFloat(item['price']).toFixed(2) : '0.00';
                        let stock = item['stock_qty'] != null ? item['stock_qty'] : 0;
                        let stockBadge = stock > 0 ? '<span class=" badge text-bg-success">' + stock + '</span>' : '<span class="badge text-bg-danger">' + stock + '</span>';
                        let subtext = [];
                        if(item['color']) subtext.push('Color: ' + item['color']);
                        if(item['size']) subtext.push('Size: ' + item['size']);
                        if(item['weight']) subtext.push('Weight: ' + item['weight'] + 'kg ');
                        let subtextHtml = subtext.length ? '<div class="text-muted small">' + subtext.join(' . ') + '</div>' : '' ;
                        tbody.innerHTML += (`
                          <tr>
                            <td>${item['id']}</td>
                                <td>
                                    <div class="fw-semibold">${item['product_name']}</div>
                                    ${subtextHtml}
                                </td>
                            <td class="text-muted">${item['sku'] || ''}</td>
                            <td class="fw-semibold">${categoryName}</td>
                            <td class="text-muted">${item['unit'] || ''}</td>
                            <td class="fw-semibold">${price}</td>
                            <td>
                                <span class="badge text-bg-success">${stock}</span>
                            </td>
                            <td>${statusBadge}</td>
                            <td class="text-muted">${created}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editProduct(${item['id']})">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${item['id']})">Delete</button>
                            </td>
                        </tr>
                        `)

                    });

                } catch (err) {
                    tbody.innerHTML =
                        '<tr><td colspan="10" class="text-center text-muted py-4">Failed to load products.</td></tr>';
                    showErrorToast(getErrorMessage(err, 'Failed to load products.'))

                }

            }
        </script>
    @endpush
@endsection
