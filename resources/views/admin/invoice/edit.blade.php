<!-- Edit Invoice Modal -->
<div class="modal fade" id="invoiceEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editInvoiceId">

                <div class="row g-3 mb-3">
                    <!-- Invoice No -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Invoice No</label>
                        <input type="text" class="form-control" id="editInvoiceNo" readonly>
                    </div>
                    <!-- Invoice Date -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" class="form-control" id="editInvoiceDate">
                    </div>
                    <!-- Customer -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Customer</label>
                        <select class="form-select" id="editCustomerSelect">
                            <option value="">-- Select Customer --</option>
                        </select>
                    </div>
                </div>

                <!-- Items Table -->
                <h6 class="fw-semibold mb-2">Items</h6>
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th style="width:80px;" class="text-center">Qty</th>
                                <th style="width:110px;" class="text-end">Unit Price</th>
                                <th style="width:90px;">Disc Type</th>
                                <th style="width:80px;">Disc Val</th>
                                <th style="width:100px;" class="text-end">Line Total</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="editItemsBody">
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="row justify-content-between align-items-end mt-3">
                    <!-- Invoice Discount -->
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Invoice Discount</label>
                        <div class="input-group">
                            <select class="form-select" id="editInvoiceDiscountType" style="max-width:90px;">
                                <option value="">None</option>
                                <option value="fixed">$ Fixed</option>
                                <option value="percent">% Percent</option>
                            </select>
                            <input type="number" class="form-control" id="editInvoiceDiscountValue" value="0" min="0" placeholder="0">
                        </div>
                    </div>
                    <!-- Summary -->
                    <div class="col-md-5">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted border-0">Subtotal</td>
                                <td class="text-end border-0 fw-semibold" id="editSubtotalDisplay">$ 0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Discount</td>
                                <td class="text-end text-danger" id="editDiscountDisplay">- $ 0.00</td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Grand Total</td>
                                <td class="text-end fs-6 text-success" id="editGrandTotalDisplay">$ 0.00</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="editSaveBtn" onclick="doEditInvoice()">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editCart      = [];
    let editProducts  = [];
    let editCustomers = [];

    // ─── Open Edit Modal ──────────────────────────────────────────────
    async function editInvoice(id) {
        let invoice = invoicesData.find(inv => inv.id === id);
        if (!invoice) { showErrorToast('Invoice not found.'); return; }

        // Reset
        editCart = [];
        document.getElementById('editInvoiceId').value    = invoice.id;
        document.getElementById('editInvoiceNo').value    = invoice.invoice_no || '';
        document.getElementById('editInvoiceDate').value  = invoice.invoice_date ? invoice.invoice_date.substring(0, 10) : '';
        document.getElementById('editInvoiceDiscountType').value  = invoice.discount_type  || '';
        document.getElementById('editInvoiceDiscountValue').value = invoice.discount_value || 0;

        // Load customers & products if not loaded yet
        await Promise.all([loadEditCustomers(), loadEditProducts()]);

        // Set customer
        let custSel = document.getElementById('editCustomerSelect');
        custSel.value = invoice.customer_id || '';

        // Load items into editCart
        let items = invoice.items || [];
        items.forEach(itm => {
            let product = editProducts.find(p => p.id === itm.product_id);
            editCart.push({
                product_id:      itm.product_id,
                product_name:    product ? product.product_name : (itm.product ? itm.product.product_name : ''),
                unit_price:      parseFloat(itm.unit_price || 0),
                quantity:        parseInt(itm.quantity || 1),
                discount_type:   itm.discount_type  || '',
                discount_value:  parseFloat(itm.discount_value  || 0),
                discount_amount: parseFloat(itm.discount_amount || 0),
                line_total:      parseFloat(itm.line_total || 0),
                max_stock:       product ? (parseInt(product.stock_qty) || 999) : 999,
            });
        });

        renderEditItems();

        let modal = new bootstrap.Modal(document.getElementById('invoiceEditModal'));
        modal.show();
    }

    // ─── Load Products for Edit ───────────────────────────────────────
    async function loadEditProducts() {
        if (editProducts.length > 0) return;
        try {
            let res    = await axios.get('{{ url("/api/v1/products") }}', { headers: { Authorization: 'Bearer ' + localStorage.getItem('token') } });
            editProducts = res.data['data'] || [];
        } catch(e) {}
    }

    // ─── Load Customers for Edit ──────────────────────────────────────
    async function loadEditCustomers() {
        let sel = document.getElementById('editCustomerSelect');
        if (editCustomers.length > 0) return;
        try {
            let res    = await axios.get('{{ url("/api/v1/customers") }}', { headers: { Authorization: 'Bearer ' + localStorage.getItem('token') } });
            editCustomers = res.data['data'] || [];
            sel.innerHTML = '<option value="">-- Select Customer --</option>';
            editCustomers.forEach(c => {
                let label = c.name + (c.mobile ? ' — ' + c.mobile : '');
                sel.innerHTML += `<option value="${c.id}">${label}</option>`;
            });
        } catch(e) {}
    }

    // ─── Render Edit Items Table ──────────────────────────────────────
    function renderEditItems() {
        let tbody = document.getElementById('editItemsBody');
        if (editCart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No items. Add a product below.</td></tr>';
            updateEditTotals();
            return;
        }

        tbody.innerHTML = '';
        editCart.forEach((item, idx) => {
            tbody.innerHTML += `
            <tr>
                <td class="text-muted">${idx + 1}</td>
                <td><span class="fw-semibold">${item.product_name}</span></td>
                <td class="text-center">
                    <input type="number" class="form-control form-control-sm text-center px-1"
                        style="width:65px;" value="${item.quantity}" min="1" max="${item.max_stock}"
                        onchange="updateEditQty(${idx}, this.value)">
                </td>
                <td class="text-end">$ ${item.unit_price.toFixed(2)}</td>
                <td>
                    <select class="form-select form-select-sm" onchange="updateEditItemDiscount(${idx}, this.value, editCart[${idx}].discount_value)">
                        <option value="">None</option>
                        <option value="fixed"${item.discount_type === 'fixed' ? ' selected' : ''}>$</option>
                        <option value="percent"${item.discount_type === 'percent' ? ' selected' : ''}>%</option>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" style="width:70px;"
                        value="${item.discount_value}" min="0" step="0.01"
                        oninput="updateEditItemDiscount(${idx}, editCart[${idx}].discount_type, this.value)">
                </td>
                <td class="text-end fw-semibold text-primary">$ ${item.line_total.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger p-1 lh-1" onclick="removeEditItem(${idx})">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            </tr>`;
        });

        // Add product row
        tbody.innerHTML += `
        <tr class="table-light">
            <td colspan="8">
                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm" id="editAddProductSelect" style="max-width:280px;">
                        <option value="">+ Add product...</option>
                        ${editProducts.map(p => `<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock_qty}" data-name="${p.product_name}">${p.product_name} (${p.sku}) — Stock: ${p.stock_qty}</option>`).join('')}
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEditProduct()">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </div>
            </td>
        </tr>`;

        updateEditTotals();
    }

    // ─── Add Product to Edit Cart ─────────────────────────────────────
    function addEditProduct() {
        let sel     = document.getElementById('editAddProductSelect');
        let opt     = sel.options[sel.selectedIndex];
        let prodId  = parseInt(sel.value);
        if (!prodId) return;

        let exists = editCart.find(i => i.product_id === prodId);
        if (exists) { showErrorToast('Product already in list.'); return; }

        let price    = parseFloat(opt.dataset.price) || 0;
        let stock    = parseInt(opt.dataset.stock) || 0;
        let name     = opt.dataset.name || opt.text;

        editCart.push({
            product_id: prodId, product_name: name,
            unit_price: price, quantity: 1,
            discount_type: '', discount_value: 0, discount_amount: 0,
            line_total: price, max_stock: stock,
        });
        recalcEditCart();
        renderEditItems();
    }

    // ─── Remove Item ──────────────────────────────────────────────────
    function removeEditItem(idx) {
        editCart.splice(idx, 1);
        recalcEditCart();
        renderEditItems();
    }

    // ─── Update Qty ───────────────────────────────────────────────────
    function updateEditQty(idx, val) {
        let qty = parseInt(val);
        if (isNaN(qty) || qty < 1) qty = 1;
        if (qty > editCart[idx].max_stock) qty = editCart[idx].max_stock;
        editCart[idx].quantity = qty;
        recalcEditCart();
        renderEditItems();
    }

    // ─── Update Item Discount ─────────────────────────────────────────
    function updateEditItemDiscount(idx, type, value) {
        editCart[idx].discount_type  = type  || '';
        editCart[idx].discount_value = parseFloat(value) || 0;
        recalcEditCart();
        renderEditItems();
    }

    // ─── Recalc ───────────────────────────────────────────────────────
    function recalcEditCart() {
        editCart.forEach(item => {
            let base = item.quantity * item.unit_price;
            let disc = 0;
            if (item.discount_type === 'fixed')   disc = Math.min(item.discount_value * item.quantity, base);
            if (item.discount_type === 'percent') disc = base * (item.discount_value / 100);
            item.discount_amount = Math.round(disc * 100) / 100;
            item.line_total      = Math.round((base - item.discount_amount) * 100) / 100;
        });
        updateEditTotals();
    }

    function getEditSubtotal() { return editCart.reduce((s, i) => s + i.line_total, 0); }

    function getEditInvoiceDiscountAmount() {
        let type  = document.getElementById('editInvoiceDiscountType').value;
        let value = parseFloat(document.getElementById('editInvoiceDiscountValue').value) || 0;
        let sub   = getEditSubtotal();
        if (type === 'fixed')   return Math.min(value, sub);
        if (type === 'percent') return Math.round(sub * value / 100 * 100) / 100;
        return 0;
    }

    function updateEditTotals() {
        let sub   = getEditSubtotal();
        let disc  = getEditInvoiceDiscountAmount();
        let grand = Math.round((sub - disc) * 100) / 100;
        document.getElementById('editSubtotalDisplay').textContent    = '$ ' + sub.toFixed(2);
        document.getElementById('editDiscountDisplay').textContent    = '- $ ' + disc.toFixed(2);
        document.getElementById('editGrandTotalDisplay').textContent  = '$ ' + grand.toFixed(2);
    }

    // ─── Recalc on discount change ────────────────────────────────────
    document.getElementById('editInvoiceDiscountType').addEventListener('change',  () => updateEditTotals());
    document.getElementById('editInvoiceDiscountValue').addEventListener('input', () => updateEditTotals());

    // ─── Save Edit ────────────────────────────────────────────────────
    async function doEditInvoice() {
        let id  = document.getElementById('editInvoiceId').value;
        let btn = document.getElementById('editSaveBtn');

        if (editCart.length === 0) { showErrorToast('কমপক্ষে একটি item থাকতে হবে।'); return; }

        let subtotal       = getEditSubtotal();
        let discountType   = document.getElementById('editInvoiceDiscountType').value;
        let discountValue  = parseFloat(document.getElementById('editInvoiceDiscountValue').value) || 0;
        let discountAmount = getEditInvoiceDiscountAmount();
        let grandTotal     = Math.round((subtotal - discountAmount) * 100) / 100;
        let customerId     = document.getElementById('editCustomerSelect').value || null;
        let invoiceDate    = document.getElementById('editInvoiceDate').value;

        let payload = {
            customer_id:     customerId,
            invoice_date:    invoiceDate,
            items: editCart.map(i => ({
                product_id:      i.product_id,
                quantity:        i.quantity,
                unit_price:      i.unit_price,
                discount_type:   i.discount_type  || null,
                discount_value:  i.discount_value,
                discount_amount: i.discount_amount,
                line_total:      i.line_total,
            })),
            subtotal:        Math.round(subtotal * 100) / 100,
            discount_type:   discountType  || null,
            discount_value:  discountValue,
            discount_amount: Math.round(discountAmount * 100) / 100,
            grand_total:     grandTotal,
        };

        let URL   = '{{ url("/api/v1/invoices") }}/' + id;
        let token = localStorage.getItem('token');

        btn.disabled  = true;
        let origHtml  = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        try {
            let response = await axios.put(URL, payload, { headers: { Authorization: 'Bearer ' + token } });

            if (response.data && response.data.success) {
                showSuccessToast(response.data.message || 'Invoice updated successfully.');
                bootstrap.Modal.getInstance(document.getElementById('invoiceEditModal')).hide();
                getInvoices(); // ← table refresh
            } else {
                showErrorToast(getErrorMessage(null, 'Failed to update invoice.'));
            }
        } catch (err) {
            showErrorToast(getErrorMessage(err, 'Failed to update invoice.'));
        } finally {
            btn.disabled  = false;
            btn.innerHTML = origHtml;
        }
    }
</script>
@endpush
