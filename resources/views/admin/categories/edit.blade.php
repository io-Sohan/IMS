<!-- Edit Category Modal -->
<div class="modal fade" id="categoryEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="categoryEditForm">
                <input type="hidden" id="categoryEditId" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="categoryEditName">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="categoryEditName" class="form-control" placeholder="e.g. Electronics" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="categoryEditDescription">Description</label>
                        <textarea name="description" id="categoryEditDescription" class="form-control" rows="3" placeholder="Optional description"></textarea>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" id="categoryEditStatus" checked>
                        <label class="form-check-label" for="categoryEditStatus">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="categoryEditSaveBtn">
                        <i class="bi bi-check2-circle me-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>

        async function getCategoryInfo(id){
            let URL = '{{ url("/api/v1/categories") }}/' + id;
            let token = localStorage.getItem('token');

            try{
                let response = await axios.get(URL, {
                    headers: { Authorization: 'Bearer ' + token }
                });
                let data = response.data['data'];
                if(data){
                    document.getElementById('categoryEditId').value = data['id'] || id;
                    document.getElementById('categoryEditName').value = data['name'] || '';
                    document.getElementById('categoryEditDescription').value = data['description'] || '';
                    document.getElementById('categoryEditStatus').checked = data['status'];
                }
            }catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to load category.'));
            }
        }

        async function editCategory(id){
            document.getElementById('categoryEditId').value = id;
            await getCategoryInfo(id);
            let modalEl = document.getElementById('categoryEditModal');
            let modal = new bootstrap.Modal(modalEl);
            modal.show();

        }

        async function doEditCategory(){
            let id = document.getElementById('categoryEditId').value.trim();
            let nameValue = document.getElementById('categoryEditName').value.trim();
            let descriptionValue = document.getElementById('categoryEditDescription').value.trim();
            let statusChecked = document.getElementById('categoryEditStatus').checked;
            let saveBtn = document.getElementById('categoryEditSaveBtn');

            let obj = {
                name: nameValue,
                description: descriptionValue || null,
                status: statusChecked
            }

            let URL = '{{ url("/api/v1/categories") }}' +'/' + id;
            let token = localStorage.getItem('token');

            saveBtn.disabled = true;

            try{
                let response = await axios.put(URL,obj,{
                    headers: { Authorization: 'Bearer ' + token }
                });

                if(response.data && response.data.success){
                    showSuccessToast(response.data.message || 'Category updated successfully');

                    let modalEl = document.getElementById('categoryEditModal');

                    let modal = window.bootstrap.Modal.getInstance(modalEl);

                    if(modal) modal.hide();

                    document.getElementById('categoryEditForm').reset();
                    document.getElementById('categoryEditId').value = '';

                    document.getElementById('categoryEditStatus').checked = true;

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

        document.getElementById('categoryEditForm').addEventListener('submit',async function(e){
            e.preventDefault();
            await doEditCategory();
        });



    </script>
@endpush


