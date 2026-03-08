<!-- Stock In Modal -->
<div class="modal fade" id="stockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="stockInForm">
                <div class="modal-header">
                    <h5 class="modal-title">Stock In</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="stockInProductId">Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="stockInProductId" class="form-select" required>
                                <option value="" selected disabled>Select product</option>

                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="stockInQuantity">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="stockInQuantity" class="form-control" min="1" placeholder="e.g. 10" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="stockInNote">Note</label>
                            <textarea name="note" id="stockInNote" class="form-control" rows="3" placeholder="e.g. New shipment received"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="stockInSaveBtn">
                        <i class="bi bi-check2-circle me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        async function doStockIn() {
            let productId = document.getElementById('stockInProductId').value;
            let quantity = document.getElementById('stockInQuantity').value;
            let note = document.getElementById('stockInNote').value.trim() || null;
            let saveBtn = document.getElementById('stockInSaveBtn');

            let obj = {
                product_id: parseInt(productId, 10),
                quantity: parseInt(quantity, 10) || 1,
                note: note
            };

            let URL = '{{ url("/api/v1/stocks") }}';
            let token = localStorage.getItem('token');

            saveBtn.disabled = true;

            try {
                let response = await axios.post(URL, obj, { headers: { Authorization: 'Bearer ' + token } });

                if (response.data && response.data.success) {
                    showSuccessToast(response.data.message || 'Stock IN created successfully.');
                    let modalEl = document.getElementById('stockInModal');
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    document.getElementById('stockInForm').reset();
                    if (typeof getStocks === 'function') getStocks();
                } else {
                    showErrorToast(getErrorMessage(null, 'Failed to create stock IN.'));
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to create stock IN. Please try again.'));
            } finally {
                saveBtn.disabled = false;
            }
        }

        document.getElementById('stockInForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            await doStockIn();
        });
    </script>
@endpush
