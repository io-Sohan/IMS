@extends('layouts.admin')

@section('title', 'Stocks')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Stock In</div>
                <div class="card-body">
                    <p class="text-muted small">Record new stock received. Form will be wired to API later.</p>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#stockInModal">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Stock In
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Stock Adjustment</div>
                <div class="card-body">
                    <p class="text-muted small">Adjust quantity (corrections / damage). Form will be wired to API later.</p>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#stockAdjustModal">
                        <i class="bi bi-pencil-square me-1"></i> Adjust
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Stock Movements</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#stockInModal">
                    <i class="bi bi-plus-lg me-1"></i> Stock In
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#stockAdjustModal">
                    <i class="bi bi-sliders me-1"></i> Adjustment
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">#</th>
                        <th>Product</th>
                        <th style="width: 140px;">Category</th>
                        <th style="width: 110px;">Type</th>
                        <th style="width: 100px;">Quantity</th>
                        <th>Note</th>
                        <th style="width: 120px;">Invoice</th>
                        <th style="width: 130px;">Date</th>
                    </tr>
                    </thead>
                    <tbody id="stocksTableBody">
                    <!-- Static demo data (design only) -->
                    <tr>
                        <td>1</td>
                        <td class="fw-semibold">iPhone 15 Pro</td>
                        <td class="text-muted">Electronics</td>
                        <td><span class="badge text-bg-success">IN</span></td>
                        <td class="fw-semibold">+20</td>
                        <td class="text-muted">New shipment received</td>
                        <td class="text-muted">—</td>
                        <td class="text-muted">2026-02-01</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.stock.stock_in')
    @include('admin.stock.adjust')

    @push('scripts')
        <script>
            getStocks();
            loadProductsForStock();

            async function getStocks(){
                let URL = '{{ url("/api/v1/stocks") }}';
                let token = localStorage.getItem('token');
                let tbody = document.getElementById('stocksTableBody');
                try {
                    let response = await axios.get(URL, { headers: { Authorization: 'Bearer ' + token } });
                    let stocks = response.data['data'] || [];
                    tbody.innerHTML = '';
                    stocks.forEach((item) => {
                        let created = item['created_at'] ? item['created_at'].substring(0, 10) : '-';
                        let productName = item['product'] && item['product']['product_name'] ? item['product']['product_name'] : '-';
                        let categoryName = item['product'] && item['product']['category'] && item['product']['category']['name'] ? item['product']['category']['name'] : '-';
                        let typeBadge = item['type'] === 'IN' ? '<span class="badge text-bg-success">IN</span>' : '<span class="badge text-bg-danger">OUT</span>';
                        let qty = item['quantity'] || 0;
                        let qtyDisplay = item['type'] === 'IN' ? '+' + qty : '-' + qty;
                        let invoiceDisplay = item['invoice_id'] ? '<span class="text-muted">INV-' + item['invoice_id'] + '</span>' : '<span class="text-muted">—</span>';
                        tbody.innerHTML += (`
                    <tr>
                        <td>${item['id']}</td>
                        <td class="fw-semibold">${productName}</td>
                        <td class="text-muted">${categoryName}</td>
                        <td>${typeBadge}</td>
                        <td class="fw-semibold">${qtyDisplay}</td>
                        <td class="text-muted">${item['note'] || '—'}</td>
                        <td>${invoiceDisplay}</td>
                        <td class="text-muted">${created}</td>
                    </tr>
                `);
                    });
                } catch (err) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Failed to load stock movements.</td></tr>';
                    showErrorToast(getErrorMessage(err, 'Failed to load stock movements.'));
                }
            }

            async function loadProductsForStock() {
                let URL = '{{ url("/api/v1/products") }}';
                let token = localStorage.getItem('token');
                try {
                    let response = await axios.get(URL, { headers: { Authorization: 'Bearer ' + token } });
                    let products = response.data['data'] || [];
                    let stockInSelect = document.getElementById('stockInProductId');
                    let stockAdjustSelect = document.getElementById('stockAdjustProductId');
                    if (stockInSelect) {
                        stockInSelect.innerHTML = '<option value="" selected disabled>Select product</option>';
                        products.forEach((p) => {
                            let cat = p.category && p.category.name ? p.category.name : '';
                            let stock = p.stock_qty != null ? p.stock_qty : 0;
                            stockInSelect.innerHTML += '<option value="' + p.id + '">' + (p.product_name || '') + ' (' + cat + ') -> Stock: ' + stock + '</option>';
                        });
                    }
                    if (stockAdjustSelect) {
                        stockAdjustSelect.innerHTML = '<option value="" selected disabled>Select product</option>';
                        products.forEach((p) => {
                            let cat = p.category && p.category.name ? p.category.name : '';
                            let stock = p.stock_qty != null ? p.stock_qty : 0;
                            stockAdjustSelect.innerHTML += '<option value="' + p.id + '">' + (p.product_name || '') + ' (' + cat + ') -> Stock: ' + stock + '</option>';
                        });
                    }
                } catch (err) {
                    showErrorToast(getErrorMessage(err, 'Failed to load products.'));
                }
            }
        </script>
    @endpush
@endsection
