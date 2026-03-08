<!-- Add Product Modal -->
<div class="modal fade" id="productCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form id="productCreateForm">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary"></i>
                        Add Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-2">
                    <!-- Section: Basic info -->
                    <div class="product-modal-section mb-4">
                        <h6 class="product-modal-section-title text-uppercase text-muted small fw-semibold mb-3">
                            <i class="bi bi-info-circle me-1"></i> Basic information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="productName">Product name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="product_name" id="productName" class="form-control"
                                    placeholder="e.g. iPhone 15 Pro" maxlength="255" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productSku">SKU <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="sku" id="productSku" class="form-control"
                                    placeholder="e.g. APL-IP15P-256" maxlength="255" required>
                                <div class="form-text">Unique product code</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productCategoryId">Category <span
                                        class="text-danger">*</span></label>
                                <select name="category_id" id="productCategoryId" class="form-select" required>
                                    <option value="" selected disabled>Select category</option>

                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Pricing & stock -->
                    <div class="product-modal-section mb-4">
                        <h6 class="product-modal-section-title text-uppercase text-muted small fw-semibold mb-3">
                            <i class="bi bi-currency-dollar me-1"></i> Pricing & stock
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label" for="productUnit">Unit <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="unit" id="productUnit" class="form-control"
                                    placeholder="pcs, kg, box" maxlength="50" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productPrice">Price <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="price" id="productPrice" class="form-control"
                                        step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productStockQty">Stock qty</label>
                                <input type="number" name="stock_qty" id="productStockQty" class="form-control"
                                    min="0" value="0" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productLowStockThreshold">Low stock threshold</label>
                                <input type="number" name="low_stock_threshold" id="productLowStockThreshold"
                                    class="form-control" min="0" value="0" placeholder="Alert when below">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="status" value="1"
                                        id="productStatus" checked>
                                    <label class="form-check-label" for="productStatus">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Optional details -->
                    <div class="product-modal-section">
                        <h6 class="product-modal-section-title text-uppercase text-muted small fw-semibold mb-3">
                            <i class="bi bi-tags me-1"></i> Optional details
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="productImagePath">Image path</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-image"></i></span>
                                    <input type="text" name="image_path" id="productImagePath"
                                        class="form-control" placeholder="Path or URL (upload later)">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productColor">Color</label>
                                <input type="text" name="color" id="productColor" class="form-control"
                                    placeholder="e.g. Black" maxlength="100">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productSize">Size</label>
                                <input type="text" name="size" id="productSize" class="form-control"
                                    placeholder="XL, 256GB" maxlength="100">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productWeight">Weight (kg)</label>
                                <input type="number" name="weight" id="productWeight" class="form-control"
                                    step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 bg-light rounded-bottom">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="productSaveBtn">
                        <i class="bi bi-check2-circle me-1"></i> Save product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@push('scripts')
    <script>
        async function doCreateProduct() {
            let categoryId = document.getElementById('productCategoryId').value;
            let productName = document.getElementById('productName').value.trim();
            let sku = document.getElementById('productSku').value.trim();
            let unit = document.getElementById('productUnit').value.trim();
            let price = document.getElementById('productPrice').value;
            let stockQty = document.getElementById('productStockQty').value || 0;
            let lowStockThreshold = document.getElementById('productLowStockThreshold').value || 0;
            let statusChecked = document.getElementById('productStatus').checked;
            let imagePath = document.getElementById('productImagePath').value.trim() || null;
            let color = document.getElementById('productColor').value.trim() || null;
            let size = document.getElementById('productSize').value.trim() || null;
            let weight = document.getElementById('productWeight').value.trim() || null;
            let saveBtn = document.getElementById('productSaveBtn');

            let obj = {
                category_id: parseInt(categoryId, 10),
                product_name: productName,
                sku: sku,
                unit: unit,
                price: parseFloat(price) || 0,
                stock_qty: parseInt(stockQty, 10) || 0,
                low_stock_threshold: parseInt(lowStockThreshold, 10) || 0,
                status: statusChecked,
                image_path: imagePath,
                color: color,
                size: size,
                weight: weight ? parseFloat(weight) : null
            }

            let URL = '{{ url("/api/v1/products") }}';
            let token = localStorage.getItem('token');

            saveBtn.disabled = true;

            try {
                let response = await axios.post(URL, obj, {
                    headers: {
                        Authorization: 'Bearer ' + token
                    }
                });

                if (response.data && response.data.success) {
                    showSuccessToast(response.data.message || 'Product created successfully');
                    let modalEl = document.getElementById('productCreateModal');
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if(modal) modal.hide();
                    document.getElementById('productCreateForm').reset();
                    document.getElementById('productStatus').checked = true;
                    document.getElementById('productStockQty').value = 0;
                    document.getElementById('productLowStockThreshold').value = 0;
                    if (typeof getProducts === 'function') getProducts();
                } else {
                    showErrorToast(getErrorMessage(null, 'Failed to create product'));
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to create product'));
            } finally {
                saveBtn.disabled = false;
            }
        }

        document.getElementById('productCreateForm').addEventListener('submit',async function(e){
            e.preventDefault();
            await doCreateProduct();
        });

    </script>
@endpush
