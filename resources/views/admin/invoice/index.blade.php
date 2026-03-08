@extends('layouts.admin')

@section('title', 'Invoices')

@section('content')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Invoice List</span>
            <a href="{{route('pos')}}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i> New Invoice (POS)
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="invoiceTable" class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width: 70px;">#</th>
                        <th style="width: 160px;">Invoice No</th>
                        <th style="width: 120px;">Date</th>
                        <th style="width: 150px;">Customer</th>
                        <th style="width: 80px;">Items</th>
                        <th style="width: 120px;">Subtotal</th>
                        <th style="width: 120px;">Discount</th>
                        <th style="width: 130px;">Grand Total</th>
                        <th style="width: 110px;">Status</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="invoicesTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.invoice.show')
    @include('admin.invoice.edit')
    @include('admin.invoice.delete')
    @include('admin.invoice.finalize')

    @push('scripts')
        <script>
            let invoicesData     = [];
            let invoiceDataTable = null;

            getInvoices();

            async function getInvoices() {
                let URL   = '{{ url("/api/v1/invoices") }}';
                let token = localStorage.getItem('token');
                let tbody = document.getElementById('invoicesTableBody');

                try {
                    let response = await axios.get(URL, { headers: { Authorization: 'Bearer ' + token } });
                    invoicesData = response.data['data'] || [];

                    if (invoiceDataTable) { invoiceDataTable.destroy(); invoiceDataTable = null; }

                    tbody.innerHTML = '';

                    if (invoicesData.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No invoices found.</td></tr>';
                        invoiceDataTable = new DataTable('#invoiceTable');
                        return;
                    }

                    invoicesData.forEach((item) => {
                        let invoiceDate    = item['invoice_date'] ? item['invoice_date'].substring(0, 10) : '-';
                        let itemsCount     = item['items'] ? item['items'].length : 0;
                        let subtotal       = parseFloat(item['subtotal'] || 0).toFixed(2);
                        let discountAmount = parseFloat(item['discount_amount'] || 0);
                        let grandTotal     = parseFloat(item['grand_total'] || 0).toFixed(2);
                        let status         = item['status'] || 'draft';

                        // Customer
                        let customerHtml = '<span class="text-muted">—</span>';
                        if (item['customer']) {
                            customerHtml = `
                                <div class="fw-semibold">${item['customer']['name'] || ''}</div>
                                <div class="text-muted small"><i class="bi bi-phone me-1"></i>${item['customer']['mobile'] || ''}</div>`;
                        }

                        // Discount
                        let discountHtml = '—';
                        if (discountAmount > 0) {
                            let discountLabel = '';
                            if (item['discount_type'] === 'percent') discountLabel = parseFloat(item['discount_value'] || 0) + '%';
                            else if (item['discount_type'] === 'fixed') discountLabel = 'Fixed';
                            discountHtml = '<span class="text-danger">- $ ' + discountAmount.toFixed(2) + '</span>';
                            if (discountLabel) discountHtml += '<div class="text-muted small">' + discountLabel + '</div>';
                        }

                        // Status
                        let statusBadge     = '';
                        let grandTotalClass = 'fw-semibold';
                        let rowClass        = '';
                        let isCancelled     = false;
                        let isFinalized     = false;

                        if (status === 'finalized') {
                            statusBadge     = '<span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Finalized</span>';
                            grandTotalClass = 'fw-semibold text-success';
                            isFinalized     = true;
                        } else if (status === 'cancelled') {
                            statusBadge = '<span class="badge text-bg-secondary"><i class="bi bi-x-circle me-1"></i>Cancelled</span>';
                            rowClass    = 'class="table-light"';
                            isCancelled = true;
                        } else {
                            statusBadge = '<span class="badge text-bg-warning"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                        }

                        let invoiceNoHtml = isCancelled
                            ? '<span class="fw-semibold text-muted text-decoration-line-through">' + (item['invoice_no'] || '') + '</span>'
                            : '<span class="fw-semibold text-primary">' + (item['invoice_no'] || '') + '</span>';

                        // Action Buttons
                        let actionsHtml = `
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewInvoice(${item['id']})" title="View">
                                <i class="bi bi-eye"></i>
                            </button>`;

                        if (status === 'draft') {
                            actionsHtml += `
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="editInvoice(${item['id']})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="finalizeInvoice(${item['id']})" title="Finalize">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(${item['id']})" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>`;
                        } else if (isFinalized) {
                            actionsHtml += `
                            <button type="button" class="btn btn-sm btn-outline-warning" disabled title="Cannot edit finalized">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" disabled title="Cannot delete finalized">
                                <i class="bi bi-trash"></i>
                            </button>`;
                        } else {
                            actionsHtml += `
                            <button type="button" class="btn btn-sm btn-outline-warning" disabled>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                                <i class="bi bi-trash"></i>
                            </button>`;
                        }

                        tbody.innerHTML += `
                        <tr ${rowClass}>
                            <td${isCancelled ? ' class="text-muted"' : ''}>${item['id']}</td>
                            <td>${invoiceNoHtml}</td>
                            <td class="text-muted">${invoiceDate}</td>
                            <td>${customerHtml}</td>
                            <td><span class="badge bg-secondary rounded-pill">${itemsCount}</span></td>
                            <td${isCancelled ? ' class="text-muted"' : ''}>$ ${subtotal}</td>
                            <td${isCancelled ? ' class="text-muted"' : ''}>${discountHtml}</td>
                            <td class="${grandTotalClass}${isCancelled ? ' text-muted' : ''}">$ ${grandTotal}</td>
                            <td>${statusBadge}</td>
                            <td class="text-end">${actionsHtml}</td>
                        </tr>`;
                    });

                    invoiceDataTable = new DataTable('#invoiceTable');

                } catch (err) {
                    tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Failed to load invoices.</td></tr>';
                    showErrorToast(getErrorMessage(err, 'Failed to load invoices.'));
                }
            }
        </script>
    @endpush
@endsection
