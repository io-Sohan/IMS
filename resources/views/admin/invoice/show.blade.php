<!-- View Invoice Modal -->
<div class="modal fade" id="invoiceShowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="invoiceShowBody">
                <div class="text-center py-4 text-muted">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function viewInvoice(id) {
            let invoice = invoicesData.find(inv => inv.id === id);
            let body    = document.getElementById('invoiceShowBody');

            if (!invoice) {
                body.innerHTML = '<div class="text-center py-4 text-danger">Invoice not found.</div>';
                let modalEl = document.getElementById('invoiceShowModal');
                let modal   = new bootstrap.Modal(modalEl);
                modal.show();
                return;
            }

            let status      = invoice.status || 'draft';
            let statusBadge = '';
            if (status === 'finalized') {
                statusBadge = '<span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Finalized</span>';
            } else if (status === 'cancelled') {
                statusBadge = '<span class="badge text-bg-secondary"><i class="bi bi-x-circle me-1"></i>Cancelled</span>';
            } else {
                statusBadge = '<span class="badge text-bg-warning"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
            }

            let invoiceDate = invoice.invoice_date ? invoice.invoice_date.substring(0, 10) : '-';





            let items     = invoice.items || [];
            let itemsRows = '';

            items.forEach((itm, idx) => {
                let productName  = itm.product && itm.product.product_name ? itm.product.product_name : '-';
                let categoryName = itm.product && itm.product.category && itm.product.category.name ? itm.product.category.name : '-';
                let qty          = itm.quantity || 0;
                let unitPrice    = parseFloat(itm.unit_price || 0).toFixed(2);
                let itemDiscount = parseFloat(itm.discount_amount || 0);
                let lineTotal    = parseFloat(itm.line_total || 0).toFixed(2);

                let itemDiscountHtml = '—';
                if (itemDiscount > 0) {
                    let label = '';
                    if (itm.discount_type === 'percent') {
                        label = ' (' + parseFloat(itm.discount_value || 0) + '%)';
                    } else if (itm.discount_type === 'fixed') {
                        label = ' (Fixed)';
                    }
                    itemDiscountHtml = '<span class="text-danger">- $ ' + itemDiscount.toFixed(2) + '</span><span class="text-muted small">' + label + '</span>';
                }

                itemsRows += `
                <tr>
                    <td>${idx + 1}</td>
                    <td>
                        <div class="fw-semibold">${productName}</div>
                        <div class="text-muted small">${categoryName}</div>
                    </td>
                    <td class="text-center">${qty}</td>
                    <td class="text-end">$ ${unitPrice}</td>
                    <td class="text-end">${itemDiscountHtml}</td>
                    <td class="text-end fw-semibold">$ ${lineTotal}</td>
                </tr>`;
            });

            if (items.length === 0) {
                itemsRows = '<tr><td colspan="6" class="text-center text-muted py-3">No items.</td></tr>';
            }

            let subtotal       = parseFloat(invoice.subtotal || 0).toFixed(2);
            let discountAmount = parseFloat(invoice.discount_amount || 0);
            let grandTotal     = parseFloat(invoice.grand_total || 0).toFixed(2);

            let invoiceDiscountHtml = '—';
            if (discountAmount > 0) {
                let label = '';
                if (invoice.discount_type === 'percent') {
                    label = ' (' + parseFloat(invoice.discount_value || 0) + '%)';
                } else if (invoice.discount_type === 'fixed') {
                    label = ' (Fixed)';
                }
                invoiceDiscountHtml = '- $ ' + discountAmount.toFixed(2) + '<span class="text-muted small">' + label + '</span>';
            }

            body.innerHTML = `
            <div id="invoicePrintArea">

                <div class="row g-3 align-items-stretch mb-3">

                    <div class="col-5">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.05em;">Invoice</div>
                            <div class="fw-bold fs-5">${invoice.invoice_no || ''}</div>
                            <div class="text-muted small mt-1"><i class="bi bi-calendar me-1"></i>${invoiceDate}</div>
                        </div>
                    </div>

                    <div class="col-5">
                        ${invoice.customer ? `
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="text-muted fw-normal small mb-1 text-uppercase" style="letter-spacing:.05em;">Bill To</div>
                            <div class="fw-bold fs-6">${invoice.customer.name || ''}</div>
                            ${invoice.customer.mobile ? '<div class="text-muted fw-medium small mt-1"><i class="bi bi-telephone me-1"></i>' + invoice.customer.mobile + '</div>' : ''}
                            ${invoice.customer.email  ? '<div class="text-muted small"><i class="bi bi-envelope me-1"></i>'  + invoice.customer.email  + '</div>' : ''}
                        </div>` : `
                        <div class="p-3 border rounded bg-light h-100 d-flex align-items-center">
                            <span class="text-muted small">No customer assigned</span>
                        </div>`}
                    </div>

                    <div class="col-2 d-flex align-items-start justify-content-end">
                        ${statusBadge}
                    </div>

                </div>

                <hr class="my-3">

                <h6 class="mb-3">Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Product</th>
                                <th class="text-center" style="width: 70px;">Qty</th>
                                <th class="text-end" style="width: 110px;">Unit Price</th>
                                <th class="text-end" style="width: 140px;">Discount</th>
                                <th class="text-end" style="width: 120px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsRows}
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-end mt-3">
                    <div class="col-sm-6 col-md-5">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted border-0">Subtotal</td>
                                <td class="text-end border-0">$ ${subtotal}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Discount</td>
                                <td class="text-end text-danger">${invoiceDiscountHtml}</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Grand Total</td>
                                <td class="text-end fs-5">$ ${grandTotal}</td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>`;

            let modalEl = document.getElementById('invoiceShowModal');
            let modal   = new bootstrap.Modal(modalEl);
            modal.show();
        }

        function printInvoice() {
            let printArea = document.getElementById('invoicePrintArea');
            if (!printArea) return;

            let printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print Invoice</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
                <style>
                    body { padding: 30px; font-size: 14px; }
                    .badge { border: 1px solid #ccc; }
                    @media print {
                        body { padding: 15px; }
                        .badge { border: 1px solid #ccc; }
                    }
                </style>
            </head>
            <body>
                ${printArea.innerHTML}
                <script>
                    window.onload = function() {
                        window.print();
                        window.onafterprint = function() { window.close(); };
                    };
                <\/script>
            </body>
            </html>
            `);
            printWindow.document.close();
        }
    </script>
@endpush
