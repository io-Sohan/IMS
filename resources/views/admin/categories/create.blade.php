<!-- Add Category Modal -->
<div class="modal fade" id="categoryCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="categoryCreateForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="categoryName">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="categoryName" class="form-control" placeholder="e.g. Electronics" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="categoryDescription">Description</label>
                        <textarea name="description" id="categoryDescription" class="form-control" rows="3" placeholder="Optional description"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="categoryStatus" checked>
                        <label class="form-check-label" for="categoryStatus">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="categorySaveBtn">
                        <i class="bi bi-check2-circle me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        async function doCreateCategory(){
            let nameValue = document.getElementById('categoryName').value.trim();
            let descriptionValue = document.getElementById('categoryDescription').value.trim();
            let statusChecked = document.getElementById('categoryStatus').checked;
            let saveBtn = document.getElementById('categorySaveBtn');

            let obj = {
                name: nameValue,
                description: descriptionValue || null,
                status: statusChecked
            }

            let URL = '{{ url("/api/v1/categories") }}';
            let token = localStorage.getItem('token');

            saveBtn.disabled = true;

            try{
                let response = await axios.post(URL,obj,{
                    headers: { Authorization: 'Bearer ' + token }
                });

                if(response.data && response.data.success){
                    showSuccessToast(response.data.message || 'Category created successfully');

                    let modalEl = document.getElementById('categoryCreateModal');

                    let modal = window.bootstrap.Modal.getInstance(modalEl);

                    if(modal) modal.hide();

                    document.getElementById('categoryCreateForm').reset();

                    document.getElementById('categoryStatus').checked = true;

                    if(typeof getCategories === 'function') getCategories();

                }else{
                    showErrorToast(getErrorMessage(null,'Failed to create category'));
                }
            }catch (err){
                showErrorToast(getErrorMessage(err,'Failed to create category'));
            }finally {
                saveBtn.disabled = false;
            }
        }

        document.getElementById('categoryCreateForm').addEventListener('submit',async function(e){
            e.preventDefault();
            await doCreateCategory();
        });


    </script>
@endpush
