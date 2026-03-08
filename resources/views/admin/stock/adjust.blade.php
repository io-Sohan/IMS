<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockAdjustModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="stockAdjustForm">
                <div class="modal-header">
                    <h5 class="modal-title">Stock Adjustment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="stockAdjustProductId">Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="stockAdjustProductId" class="form-select" required>
                                <option value="" selected disabled>Select product</option>

                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="stockAdjustQuantity">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="stockAdjustQuantity" class="form-control" min="1" placeholder="e.g. 2" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="stockAdjustType">Type <span class="text-danger">*</span></label>
                            <select name="type" id="stockAdjustType" class="form-select" required>
                                <option value="OUT" selected>OUT (decrease stock)</option>
                                <option value="IN">IN (increase stock)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="stockAdjustNote">Note</label>
                            <textarea name="note" id="stockAdjustNote" class="form-control" rows="3" placeholder="e.g. Damaged items, inventory correction"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="stockAdjustSaveBtn">
                        <i class="bi bi-check2-circle me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        async function doStockAdjustment(){
            let productId = document.getElementById('stockAdjustProductId').value;
            let quantity = document.getElementById('stockAdjustQuantity').value;
            let type = document.getElementById('stockAdjustType').value;
            let note = document.getElementById('stockAdjustNote').value.trim() || null;
            let saveBtn = document.getElementById('stockAdjustSaveBtn');

            let obj = {
                product_id: parseInt(productId, 10),
                quantity: parseInt(quantity, 10) || 1,
                type: type,
                note: note
            };

            let URL = '{{ url("/api/v1/stocks/adjustment") }}';
            let token = localStorage.getItem('token');

            saveBtn.disabled = true;

            try {
                let response = await axios.post(URL, obj, { headers: { Authorization: 'Bearer ' + token } });

                if (response.data && response.data.success) {
                    showSuccessToast(response.data.message || 'Stock adjustment created successfully.');
                    let modalEl = document.getElementById('stockAdjustModal');
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    document.getElementById('stockAdjustForm').reset();
                    document.getElementById('stockAdjustType').value = 'OUT';
                    if (typeof getStocks === 'function') getStocks();
                } else {
                    showErrorToast(getErrorMessage(null, 'Failed to create stock adjustment.'));
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to create stock adjustment. Please try again.'));
            } finally {
                saveBtn.disabled = false;
            }
        }

        document.getElementById('stockAdjustForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            await doStockAdjustment();
        });
    </script>
@endpush
