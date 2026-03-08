<!-- Edit Product Modal -->
<div class="modal fade" id="productEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form id="productEditForm">
                 <input type="hidden" id="productEditId" value="">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary"></i>
                        Edit Product
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
                                <label class="form-label" for="productEditName">Product name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="product_name" id="productEditName" class="form-control"
                                    placeholder="e.g. iPhone 15 Pro" maxlength="255" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productEditSku">SKU <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="sku" id="productEditSku" class="form-control"
                                    placeholder="e.g. APL-IP15P-256" maxlength="255" required>
                                <div class="form-text">Unique product code</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productEditCategoryId">Category <span
                                        class="text-danger">*</span></label>
                                <select name="category_id" id="productEditCategoryId" class="form-select" required>
                                    <option value="" selected disabled>Select category</option>
                                    <option value="1">Electronics</option>
                                    <option value="2">Groceries</option>
                                    <option value="3">Stationery</option>
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
                                <label class="form-label" for="productEditUnit">Unit <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="unit" id="productEditUnit" class="form-control"
                                    placeholder="pcs, kg, box" maxlength="50" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productEditPrice">Price <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="price" id="productEditPrice" class="form-control"
                                        step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productEditStockQty">Stock qty</label>
                                <input type="number" name="stock_qty" id="productEditStockQty" class="form-control"
                                    min="0" value="0" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="productEditLowStockThreshold">Low stock threshold</label>
                                <input type="number" name="low_stock_threshold" id="productEditLowStockThreshold"
                                    class="form-control" min="0" value="0" placeholder="Alert when below">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="status" value="1"
                                        id="productEditStatus" checked>
                                    <label class="form-check-label" for="productEditStatus">Active</label>
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
                                <label class="form-label" for="productEditImagePath">Image path</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-image"></i></span>
                                    <input type="text" name="image_path" id="productEditImagePath"
                                        class="form-control" placeholder="Path or URL (upload later)">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productEditColor">Color</label>
                                <input type="text" name="color" id="productEditColor" class="form-control"
                                    placeholder="e.g. Black" maxlength="100">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productEditSize">Size</label>
                                <input type="text" name="size" id="productEditSize" class="form-control"
                                    placeholder="XL, 256GB" maxlength="100">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="productEditWeight">Weight (kg)</label>
                                <input type="number" name="weight" id="productEditWeight" class="form-control"
                                    step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 bg-light rounded-bottom">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="productEditSaveBtn">
                        <i class="bi bi-check2-circle me-1"></i> Update product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@push('scripts')
    <script>

        async function getProductInfo(id) {
            let URL = '{{ url("/api/v1/products") }}/' + id;
            let token = localStorage.getItem('token');
            try {
                let response = await axios.get(URL,{
                    headers: { Authorization: 'Bearer ' + token}
                });

                let data = response.data['data'];
                if(data){
                    document.getElementById('productEditId').value = data['id'] || id;
                    document.getElementById('productEditCategoryId').value = data['category_id'] || '';
                    document.getElementById('productEditName').value = data['product_name'] || '';
                    document.getElementById('productEditSku').value = data['sku'] || '';
                    document.getElementById('productEditUnit').value = data['unit'] || '';
                    document.getElementById('productEditPrice').value = data['price'] || '';
                    document.getElementById('productEditStockQty').value = data['stock_qty'] || '';
                    document.getElementById('productEditLowStockThreshold').value = data['low_stock_threshold'] || '';
                    document.getElementById('productEditStatus').checked = data['status'];
                    document.getElementById('productEditImagePath').value = data['image_path'] || '';
                    document.getElementById('productEditColor').value = data['color'] || '';
                    document.getElementById('productEditSize').value = data['size'] || '';
                    document.getElementById('productEditWeight').value = data['weight'] || '';
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to load product.'));
            }
        }


        async function editProduct(id){
            document.getElementById('productEditId').value = id;
            await getProductInfo(id);
            let modalEl = document.getElementById('productEditModal');
            let modal = new bootstrap.Modal(modalEl);
            modal.show();

        }


        async function doEditProduct() {
            let id = document.getElementById('productEditId').value.trim();
            let categoryId = document.getElementById('productEditCategoryId').value;
            let productName = document.getElementById('productEditName').value.trim();
            let sku = document.getElementById('productEditSku').value.trim();
            let unit = document.getElementById('productEditUnit').value.trim();
            let price = document.getElementById('productEditPrice').value;
            let stockQty = document.getElementById('productEditStockQty').value || 0;
            let lowStockThreshold = document.getElementById('productEditLowStockThreshold').value || 0;
            let statusChecked = document.getElementById('productEditStatus').checked;
            let imagePath = document.getElementById('productEditImagePath').value.trim() || null;
            let color = document.getElementById('productEditColor').value.trim() || null;
            let size = document.getElementById('productEditSize').value.trim() || null;
            let weight = document.getElementById('productEditWeight').value.trim() || null;
            let saveBtn = document.getElementById('productEditSaveBtn');

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

            let URL = '{{ url("/api/v1/products") }}' + '/' + id;
            let token = localStorage.getItem('token');

            saveBtn.disabled = true;

            try {
                let response = await axios.put(URL, obj, {
                    headers: {
                        Authorization: 'Bearer ' + token
                    }
                });

                if (response.data && response.data.success) {
                    showSuccessToast(response.data.message || 'Product updated successfully');
                    let modalEl = document.getElementById('productEditModal');
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if(modal) modal.hide();
                    document.getElementById('productEditForm').reset();
                    document.getElementById('productEditStatus').checked = true;
                    document.getElementById('productEditStockQty').value = 0;
                    document.getElementById('productEditLowStockThreshold').value = 0;
                    if (typeof getProducts === 'function') getProducts();
                } else {
                    showErrorToast(getErrorMessage(null, 'Failed to update product'));
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to update product'));
            } finally {
                saveBtn.disabled = false;
            }
        }

        document.getElementById('productEditForm').addEventListener('submit',async function(e){
            e.preventDefault();
            await doEditProduct();
        });

    </script>
@endpush
