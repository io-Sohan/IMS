<!-- Add Customer Modal -->
<div class="modal fade" id="customerCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="customerCreateForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="customerName">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="customerName" class="form-control"
                            placeholder="e.g. John Doe" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="customerEmail">Email</label>
                        <input type="email" name="email" id="customerEmail" class="form-control"
                            placeholder="e.g. john.doe@example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="customerMobile">Mobile  <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" id="customerMobile" class="form-control"
                            placeholder="e.g. 123-456-7890" maxlength="20" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="customerDescription">Description</label>
                        <textarea name="description" id="customerDescription" class="form-control" rows="3"
                            placeholder="Optional description"></textarea>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="customerCreateSubmitBtn">
                        <i class="bi bi-check2-circle me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        async function doCreateCustomer() {
            let nameValue = document.getElementById('customerName').value.trim();
            let emailValue = document.getElementById('customerEmail').value.trim();
            let mobileValue = document.getElementById('customerMobile').value.trim();
            let descriptionValue = document.getElementById('customerDescription').value.trim();
            let saveBtn = document.getElementById('customerCreateSubmitBtn');
            let URL = '{{ url("/api/v1/customers") }}';
            let token = localStorage.getItem('token');
            let obj = {
                name: nameValue,
                email: emailValue || null,
                mobile: mobileValue,
                description: descriptionValue || null
            }

            saveBtn.disabled = true;
            try {
                let response = await axios.post(URL, obj, {
                    headers: {
                        Authorization: 'Bearer ' + token
                    }
                });

                if (response.data && response.data.success) {
                    showSuccessToast(response.data.message || 'Customer created successfully');

                    let modalEl = document.getElementById('customerCreateModal');

                    let modal = window.bootstrap.Modal.getInstance(modalEl);

                    if (modal) modal.hide();

                    document.getElementById('customerCreateForm').reset();

                    if (typeof getCustomers === 'function') getCustomers();
                }else {
                    showErrorToast(getErrorMessage(null, 'Failed to create customer.'));
                }
            } catch (err) {
                showErrorToast(getErrorMessage(err, 'Failed to create customer.'));
            } finally {
                saveBtn.disabled = false;
            }
        }

        document.getElementById('customerCreateForm').addEventListener('submit', function (e) {
            e.preventDefault();
            doCreateCustomer();
        });
    </script>
@endpush
