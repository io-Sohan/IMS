<!-- Delete Product Modal -->
<div class="modal fade" id="productDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="productDeleteId" value="">
                <p class="mb-0">Are you sure you want to delete this product?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="productDeleteBtn" onclick="doDeleteProduct()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>


@push('scripts')

<script>

    function deleteProduct(id){
            document.getElementById('productDeleteId').value = id;
            let modalEl = document.getElementById('productDeleteModal');
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        async function doDeleteProduct() {
            let id = document.getElementById('productDeleteId').value.trim();
            let deleteBtn = document.getElementById('productDeleteBtn');

            let URL = '{{ url("/api/v1/products") }}/' + id;
            let token = localStorage.getItem('token');

            deleteBtn.disabled = true;

            try {
                let response = await axios.delete(URL, { headers: { Authorization: 'Bearer ' + token } });

                if (response.data && response.data.success) {
                    showSuccessToast(response.data.message || 'Product deleted successfully.');
                    let modalEl = document.getElementById('productDeleteModal');
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    document.getElementById('productDeleteId').value = '';
                     if (typeof getProducts === 'function') getProducts();
                } else {
                    showErrorToast(getErrorMessage(null, 'Failed to delete product.'));
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to delete product. Please try again.'));
            } finally {
                deleteBtn.disabled = false;
            }
        }
</script>

@endpush
