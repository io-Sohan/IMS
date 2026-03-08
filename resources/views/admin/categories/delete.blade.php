<!-- Delete Category Modal -->
<div class="modal fade" id="categoryDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="categoryDeleteId" value="">
                <p class="mb-0">Are you sure you want to delete this category?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="categoryDeleteBtn" onclick="doDeleteCategory()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>


@push('scripts')

<script>

    function deleteCategory(id){
            document.getElementById('categoryDeleteId').value = id;
            let modalEl = document.getElementById('categoryDeleteModal');
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
        async function doDeleteCategory() {
            let id = document.getElementById('categoryDeleteId').value.trim();
            let deleteBtn = document.getElementById('categoryDeleteBtn');

            let URL = '{{ url("/api/v1/categories") }}/' + id;
            let token = localStorage.getItem('token');

            deleteBtn.disabled = true;

            try {
                let response = await axios.delete(URL, { headers: { Authorization: 'Bearer ' + token } });

                if (response.data && response.data.success) {
                    showSuccessToast(response.data.message || 'Category deleted successfully.');
                    let modalEl = document.getElementById('categoryDeleteModal');
                    let modal = window.bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    document.getElementById('categoryDeleteId').value = '';
                    if (typeof getCategories === 'function') getCategories();
                } else {
                    showErrorToast(getErrorMessage(null, 'Failed to delete category.'));
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to delete category. Please try again.'));
            } finally {
                deleteBtn.disabled = false;
            }
        }
</script>

@endpush
