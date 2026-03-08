@extends('layouts.admin')

@section('title', 'POS')

@section('content')
    <div class="row g-4">
        <!-- Left: Products -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search product by name or SKU..." id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="categoryFilter">
                                <option value="">All categories</option>
                            </select>
                        </div>
                        <!-- Customer Dropdown -->
                        <div class="col-md-4">
                            <select class="form-select" id="customerSelect">
                                <option value="">-- Select Customer --</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-grid me-2"></i>Products</span>
                    <span class="text-muted small">Click to add to cart</span>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="productGrid">
                        <div class="col-12 text-center text-muted py-4">Loading products...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Cart -->
        <div class="col-lg-4">
            <div class="card cart-sticky">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <span><i class="bi bi-cart3 me-2"></i>Cart</span>
                    <span class="badge bg-white text-primary" id="cartBadge">0 items</span>
                </div>
                <div class="card-body p-0">

                    <!-- Selected Customer Block -->
                    <div id="cartCustomerInfo" class="px-3 pt-3" style="display:none;">
                        <div class="d-flex align-items-center gap-2 p-2 bg-light border rounded">
                            <i class="bi bi-person-circle text-primary fs-5"></i>
                            <div class="grow">
                                <div class="fw-semibold small" id="cartCustomerName"></div>
                                <div class="text-muted small" id="cartCustomerMobile"></div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearCustomer()" title="Remove">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div class="p-3" style="max-height: 320px; overflow-y: auto;" id="cartItemsContainer">
                        <div class="text-center text-muted py-4">Cart is empty</div>
                    </div>

                    <!-- Totals -->
                    <div class="border-top p-3 bg-light" id="totalsSection" style="display: none">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span id="subtotalDisplay">$ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger" id="itemDiscountRow" style="display: none">
                            <span>Item Discounts</span>
                            <span id="itemDiscountDisplay">- $ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Invoice Discount</span>
                            <div class="d-flex align-items-center gap-1">
                                <select class="form-select form-select-sm" id="invoiceDiscountType" style="width: 65px;">
                                    <option value="">None</option>
                                    <option value="fixed" selected>$</option>
                                    <option value="percent">%</option>
                                </select>
                                <input type="number" class="form-control form-control-sm" id="invoiceDiscountValue" style="width: 70px;" value="0" min="0">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger" id="invoiceDiscountRow" style="display: none">
                            <span></span>
                            <span id="invoiceDiscountDisplay">- $ 0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 fw-bold">
                            <span>Grand Total</span>
                            <span class="text-success" id="grandTotalDisplay">$ 0.00</span>
                        </div>
                    </div>

                    <!-- Invoice Info & Actions -->
                    <div class="border-top p-3">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">Invoice No</label>
                                <input type="text" class="form-control form-control-sm" id="invoiceNoInput" value="" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1">Date</label>
                                <input type="date" class="form-control form-control-sm" id="invoiceDateInput">
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-lg" id="finalizeBtn" disabled>
                                <i class="bi bi-check-circle me-2"></i>Finalize Invoice
                            </button>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-primary w-100" id="saveDraftBtn" disabled>
                                        <i class="bi bi-save me-1"></i>Save Draft
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-secondary w-100" id="clearCartBtn">
                                        <i class="bi bi-x-lg me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let productsUrl   = '{{ url("/api/v1/products") }}';
            let categoriesUrl = '{{ url("/api/v1/categories") }}';
            let customersUrl  = '{{ url("/api/v1/customers") }}';
            let invoicesUrl   = '{{ url("/api/v1/invoices") }}';

            let allProducts      = [];
            let allCategories    = [];
            let allCustomers     = [];
            let cart             = [];
            let selectedCustomer = null;

            function getToken()    { return localStorage.getItem('token') || ''; }
            function authHeaders() { return { headers: { Authorization: 'Bearer ' + getToken() } }; }
            function formatMoney(n) { return '$ ' + parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
            function todayDate() {
                let d = new Date();
                return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            }
            function escapeHtml(text) {
                let div = document.createElement('div');
                div.textContent = text == null ? '' : text;
                return div.innerHTML;
            }

            // ─── Load Categories ──────────────────────────────────────────
            async function loadCategories() {
                try {
                    let res = await axios.get(categoriesUrl, authHeaders());
                    allCategories = res.data['data'] || [];
                    let sel = document.getElementById('categoryFilter');
                    sel.innerHTML = '<option value="">All categories</option>';
                    allCategories.forEach(c => {
                        sel.innerHTML += `<option value="${c.id}">${escapeHtml(c.name)}</option>`;
                    });
                } catch(err) { showErrorToast(getErrorMessage(err, 'Failed to load categories.')); }
            }

            // ─── Load Customers ───────────────────────────────────────────
            async function loadCustomers() {
                try {
                    let res = await axios.get(customersUrl, authHeaders());
                    allCustomers = res.data['data'] || [];
                    let sel = document.getElementById('customerSelect');
                    sel.innerHTML = '<option value="">-- Select Customer --</option>';
                    allCustomers.forEach(c => {
                        let label = escapeHtml(c.name) + (c.mobile ? ' — ' + escapeHtml(c.mobile) : '');
                        sel.innerHTML += `<option value="${c.id}">${label}</option>`;
                    });
                } catch(err) { showErrorToast(getErrorMessage(err, 'Failed to load customers.')); }
            }

            // ─── Customer Select Change ───────────────────────────────────
            document.getElementById('customerSelect').addEventListener('change', function() {
                let id = this.value;
                if (!id) { selectedCustomer = null; renderCustomerBlock(); return; }
                let found = allCustomers.find(c => String(c.id) === String(id));
                if (found) {
                    selectedCustomer = { id: found.id, name: found.name, mobile: found.mobile || '' };
                    renderCustomerBlock();
                }
            });

            // ─── Render Customer Block ────────────────────────────────────
            function renderCustomerBlock() {
                let block = document.getElementById('cartCustomerInfo');
                if (selectedCustomer) {
                    document.getElementById('cartCustomerName').textContent   = selectedCustomer.name;
                    document.getElementById('cartCustomerMobile').textContent = selectedCustomer.mobile;
                    block.style.display = 'block';
                } else {
                    document.getElementById('cartCustomerName').textContent   = '';
                    document.getElementById('cartCustomerMobile').textContent = '';
                    block.style.display = 'none';
                }
            }

            function clearCustomer() {
                selectedCustomer = null;
                document.getElementById('customerSelect').value = '';
                renderCustomerBlock();
            }

            // ─── Load Products ────────────────────────────────────────────
            async function loadProducts() {
                let grid = document.getElementById('productGrid');
                try {
                    let res = await axios.get(productsUrl, authHeaders());
                    allProducts = res.data['data'] || [];
                    renderProducts();
                } catch(err) {
                    grid.innerHTML = '<div class="col-12 text-center text-muted py-4">Failed to load products.</div>';
                    showErrorToast(getErrorMessage(err, 'Failed to load products.'));
                }
            }

            function renderProducts() {
                let searchText = (document.getElementById('searchInput').value || '').toLowerCase();
                let categoryId = document.getElementById('categoryFilter').value;
                let grid = document.getElementById('productGrid');

                let filtered = allProducts.filter(p => {
                    let ms = !searchText || (p.product_name||'').toLowerCase().includes(searchText) || (p.sku||'').toLowerCase().includes(searchText);
                    let mc = !categoryId || String(p.category_id) === String(categoryId);
                    return ms && mc;
                });

                if (filtered.length === 0) {
                    grid.innerHTML = '<div class="col-12 text-center text-muted py-4">No products found.</div>';
                    return;
                }

                grid.innerHTML = '';
                filtered.forEach(product => {
                    let stockQty = parseInt(product.stock_qty) || 0;
                    let price    = parseFloat(product.price) || 0;
                    let isOut    = stockQty <= 0;
                    let catName  = product.category ? product.category.name : '';
                    let badgeCls = stockQty <= 0 ? 'text-bg-secondary' : (stockQty <= 5 ? 'text-bg-warning' : 'text-bg-success');

                    let col = document.createElement('div');
                    col.className = 'col-6 col-md-4 col-xl-3';
                    col.innerHTML =
                        `<div class="card pos-product-card h-100${isOut ? ' out-of-stock' : ''}" data-product-id="${product.id}">
                            <div class="product-image"><i class="bi bi-box"></i></div>
                            <div class="card-body p-2">
                                <div class="fw-semibold small text-truncate" title="${escapeHtml(product.product_name)}">${escapeHtml(product.product_name)}</div>
                                <div class="text-muted small">${escapeHtml(product.sku)}</div>
                                <div class="text-muted small">${escapeHtml(catName)}</div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fw-bold text-primary">${formatMoney(price)}</span>
                                    <span class="badge ${badgeCls}">${stockQty}</span>
                                </div>
                            </div>
                        </div>`;
                    if (!isOut) col.querySelector('.pos-product-card').addEventListener('click', () => addToCart(product));
                    grid.appendChild(col);
                });
            }

            // ─── Cart ─────────────────────────────────────────────────────
            function addToCart(product) {
                let stockQty = parseInt(product.stock_qty) || 0;
                if (stockQty <= 0) return;
                let unitPrice = parseFloat(product.price) || 0;
                let existing  = cart.find(i => i.product_id === product.id);
                if (existing) {
                    if (existing.quantity >= stockQty) return;
                    existing.quantity += 1;
                } else {
                    cart.push({ product_id: product.id, product_name: product.product_name||'', sku: product.sku||'',
                        unit_price: unitPrice, quantity: 1, discount_type: '', discount_value: 0,
                        discount_amount: 0, line_total: unitPrice, max_stock: stockQty });
                }
                recalcCart(); renderCart();
            }

            function removeFromCart(productId) {
                cart = cart.filter(i => i.product_id !== productId);
                if (cart.length === 0) {
                    document.getElementById('invoiceDiscountType').value  = '';
                    document.getElementById('invoiceDiscountValue').value = '0';
                }
                recalcCart(); renderCart();
            }

            function changeQuantity(productId, delta) {
                let item = cart.find(x => x.product_id === productId);
                if (!item) return;
                let qty = item.quantity + delta;
                if (qty < 1) { removeFromCart(productId); return; }
                if (qty > item.max_stock) qty = item.max_stock;
                item.quantity = qty;
                recalcCart(); renderCart();
            }

            function setQuantity(productId, val) {
                let item = cart.find(x => x.product_id === productId);
                if (!item) return;
                let qty = parseInt(val);
                if (isNaN(qty) || qty < 1) qty = 1;
                if (qty > item.max_stock) qty = item.max_stock;
                item.quantity = qty;
                recalcCart(); renderCart();
            }

            function updateItemDiscount(productId, discountType, discountValue) {
                let item = cart.find(x => x.product_id === productId);
                if (!item) return;
                item.discount_type  = discountType || '';
                item.discount_value = parseFloat(discountValue) || 0;
                recalcCart(); renderCart();
            }

            function recalcCart() {
                cart.forEach(item => {
                    let base = item.quantity * item.unit_price;
                    let disc = 0;
                    if (item.discount_type === 'fixed')   disc = Math.min(item.discount_value * item.quantity, base);
                    if (item.discount_type === 'percent') disc = base * (item.discount_value / 100);
                    item.discount_amount = Math.round(disc * 100) / 100;
                    item.line_total      = Math.round((base - item.discount_amount) * 100) / 100;
                });
                updateTotals();
            }

            function getSubtotal()           { return cart.reduce((s,i) => s + i.line_total, 0); }
            function getItemDiscountsTotal() { return cart.reduce((s,i) => s + i.discount_amount, 0); }
            function getInvoiceDiscountAmount() {
                let type  = document.getElementById('invoiceDiscountType').value;
                let value = parseFloat(document.getElementById('invoiceDiscountValue').value) || 0;
                let sub   = getSubtotal();
                if (type === 'fixed')   return Math.min(value, sub);
                if (type === 'percent') return Math.round(sub * value / 100 * 100) / 100;
                return 0;
            }

            function updateTotals() {
                let sub   = getSubtotal();
                let iDisc = getItemDiscountsTotal();
                let invDisc = getInvoiceDiscountAmount();
                let grand = Math.round((sub - invDisc) * 100) / 100;

                document.getElementById('subtotalDisplay').textContent       = formatMoney(sub);
                document.getElementById('grandTotalDisplay').textContent     = formatMoney(grand);
                document.getElementById('itemDiscountDisplay').textContent   = '- ' + formatMoney(iDisc);
                document.getElementById('invoiceDiscountDisplay').textContent = '- ' + formatMoney(invDisc);
                document.getElementById('itemDiscountRow').style.display     = iDisc > 0    ? 'flex' : 'none';
                document.getElementById('invoiceDiscountRow').style.display  = invDisc > 0  ? 'flex' : 'none';
                document.getElementById('cartBadge').textContent      = cart.length + ' item' + (cart.length !== 1 ? 's' : '');
                document.getElementById('totalsSection').style.display = cart.length > 0 ? 'block' : 'none';
                document.getElementById('finalizeBtn').disabled        = cart.length === 0;
                document.getElementById('saveDraftBtn').disabled       = cart.length === 0;
            }

            function renderCart() {
                let container = document.getElementById('cartItemsContainer');
                if (cart.length === 0) {
                    container.innerHTML = '<div class="text-center text-muted py-4">Cart is empty</div>';
                    updateTotals(); return;
                }
                let html = '';
                cart.forEach(item => {
                    html += `<div class="pos-cart-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1 me-2">
                                <div class="fw-semibold">${escapeHtml(item.product_name)}</div>
                                <div class="text-muted small">${formatMoney(item.unit_price)} × ${item.quantity}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger p-1 lh-1" onclick="removeFromCart(${item.product_id})"><i class="bi bi-x"></i></button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="width: 100px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(${item.product_id}, -1)">−</button>
                                <input type="number" class="form-control text-center px-1" value="${item.quantity}" min="1" max="${item.max_stock}" onchange="setQuantity(${item.product_id}, this.value)">
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(${item.product_id}, 1)">+</button>
                            </div>
                            <div class="flex-grow-1 text-end fw-semibold">${formatMoney(item.line_total)}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2 bg-light rounded p-2 mt-2">
                            <span class="small text-muted">Discount:</span>
                            <select class="form-select form-select-sm" style="width: 80px;" onchange="updateItemDiscount(${item.product_id}, this.value, this.parentElement.querySelector('input').value)">
                                <option value="">None</option>
                                <option value="fixed"${item.discount_type==='fixed'?' selected':''}>$</option>
                                <option value="percent"${item.discount_type==='percent'?' selected':''}>%</option>
                            </select>
                            <input type="number" class="form-control form-control-sm" style="width: 60px;" value="${item.discount_value}" min="0" step="0.01" oninput="updateItemDiscount(${item.product_id}, this.parentElement.querySelector('select').value, this.value)">
                            <span class="small text-danger">-${formatMoney(item.discount_amount)}</span>
                        </div>
                    </div>`;
                });
                container.innerHTML = html;
                updateTotals();
            }

            function resetCart() {
                cart = [];
                document.getElementById('invoiceNoInput').value            = '';
                document.getElementById('invoiceDateInput').value          = todayDate();
                document.getElementById('invoiceDiscountType').value       = '';
                document.getElementById('invoiceDiscountValue').value      = '0';
                document.getElementById('itemDiscountDisplay').textContent = '- $ 0.00';
                clearCustomer();
                renderCart();
            }

            function buildInvoicePayload(status) {
                let sub    = getSubtotal();
                let dType  = document.getElementById('invoiceDiscountType').value;
                let dValue = parseFloat(document.getElementById('invoiceDiscountValue').value) || 0;
                let dAmt   = getInvoiceDiscountAmount();
                let grand  = Math.round((sub - dAmt) * 100) / 100;
                return {
                    customer_id:     selectedCustomer ? selectedCustomer.id : null,
                    invoice_no:      document.getElementById('invoiceNoInput').value || null,
                    invoice_date:    document.getElementById('invoiceDateInput').value,
                    items: cart.map(i => ({
                        product_id: i.product_id, quantity: i.quantity, unit_price: i.unit_price,
                        discount_type: i.discount_type||null, discount_value: i.discount_value,
                        discount_amount: i.discount_amount, line_total: i.line_total
                    })),
                    subtotal:        Math.round(sub * 100) / 100,
                    discount_type:   dType || null,
                    discount_value:  dValue,
                    discount_amount: Math.round(dAmt * 100) / 100,
                    grand_total:     grand,
                    status:          status
                };
            }

            async function submitInvoice(status) {
                if (cart.length === 0) { showErrorToast('Cart is empty.'); return; }
                let payload = buildInvoicePayload(status);
                if (!payload.invoice_date) { showErrorToast('Please set the invoice date.'); return; }

                let btn = status === 'finalized' ? document.getElementById('finalizeBtn') : document.getElementById('saveDraftBtn');
                let orig = btn.innerHTML;
                btn.disabled  = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
                try {
                    let res = await axios.post(invoicesUrl, payload, authHeaders());
                    if (res.data.success) { showSuccessToast(res.data.message || 'Invoice saved.'); resetCart(); }
                    else showErrorToast(res.data.message || 'Failed to save invoice.');
                } catch(err) {
                    showErrorToast(getErrorMessage(err, 'Failed to save invoice.'));
                } finally { btn.disabled = false; btn.innerHTML = orig; }
            }

            // Init
            document.getElementById('invoiceDateInput').value = todayDate();
            loadCategories();
            loadCustomers();
            loadProducts();

            // Events
            document.getElementById('searchInput').addEventListener('input', renderProducts);
            document.getElementById('categoryFilter').addEventListener('change', renderProducts);
            document.getElementById('invoiceDiscountType').addEventListener('change', () => { recalcCart(); renderCart(); });
            document.getElementById('invoiceDiscountValue').addEventListener('input', () => { recalcCart(); renderCart(); });
            document.getElementById('clearCartBtn').addEventListener('click', () => { resetCart(); showSuccessToast('Cart cleared.'); });
            document.getElementById('finalizeBtn').addEventListener('click', () => submitInvoice('finalized'));
            document.getElementById('saveDraftBtn').addEventListener('click', () => submitInvoice('draft'));
        </script>
    @endpush
@endsection
