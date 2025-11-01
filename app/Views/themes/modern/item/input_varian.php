<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Description: View for managing product variants
 */
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold text-primary d-flex justify-content-between align-items-center">
        <span>Varian Produk</span>
        <button type="button" class="btn btn-sm btn-primary" id="btn-add-variant" data-item-id="<?= esc($item_id) ?>">
            <i class="fas fa-plus me-1"></i> Tambah Varian
        </button>
    </div>
    <div class="card-body">
        <!-- Variants Table -->
        <div class="table-responsive">
            <table id="variant-table" class="table table-striped table-bordered" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Variant</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Variant - Will be moved to body on load -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-labelledby="variantModalLabel" aria-hidden="true" data-modal-moved="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="variantModalLabel">Tambah Varian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="variantForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="variant_id">
                    <input type="hidden" name="item_id" id="variant_item_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Varian <span class="text-danger">*</span></label>
                        <input type="text" name="variant_name" id="variant_name" class="form-control" 
                            placeholder="Contoh: 500ml, Hitam, Ukuran L" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">SKU Varian <span class="text-danger">*</span></label>
                        <input type="text" name="sku_variant" id="sku_variant" class="form-control" 
                            placeholder="SKU unik untuk varian ini" required>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga</label>
                            <?= form_input([
                                'name' => 'price',
                                'id' => 'variant_price',
                                'class' => 'form-control price-format',
                                'value' => '0',
                                'placeholder' => '0',
                                'data-type' => 'currency'
                            ]) ?>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Stock</label>
                            <input type="number" name="stock" id="variant_stock" class="form-control" 
                                value="0" placeholder="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-variant">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Ensure modal has proper z-index above tabs and other content */
#variantModal.modal {
    z-index: 1060 !important;
}

#variantModal.modal.show {
    z-index: 1060 !important;
}

/* Ensure backdrop is behind modal but above page content */
.modal-backdrop {
    z-index: 1055 !important;
}

/* Ensure modal dialog is visible */
.modal.show .modal-dialog {
    z-index: 1060 !important;
}

/* Fix for modals inside tabs - ensure they're not clipped */
.tab-content .modal {
    position: fixed !important;
}
</style>

<script>
(function() {
    const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
    const itemId = '<?= esc($item_id) ?>';
    
    // Wait for DOM and jQuery to be ready
    function initVariantModule() {
        // Move modal to body to avoid z-index issues with tab containers
        // Make it globally accessible
        window.ensureModalInBody = function() {
            const modalElement = document.getElementById('variantModal');
            if (modalElement && !modalElement.getAttribute('data-modal-moved')) {
                // Check if modal is not already in body
                if (!document.body.contains(modalElement) || modalElement.closest('.tab-content')) {
                    // Clone the modal
                    const clonedModal = modalElement.cloneNode(true);
                    clonedModal.setAttribute('data-modal-moved', 'true');
                    clonedModal.id = 'variantModal';
                    
                    // Remove old modal if it exists in body (avoid duplicates)
                    const existingModal = document.getElementById('variantModal');
                    if (existingModal && document.body.contains(existingModal) && existingModal !== modalElement) {
                        existingModal.remove();
                    }
                    
                    // Remove original from its current location
                    if (modalElement.parentNode) {
                        modalElement.remove();
                    }
                    
                    // Append to body
                    document.body.appendChild(clonedModal);
                    
                    return true; // Modal was moved
                } else {
                    modalElement.setAttribute('data-modal-moved', 'true');
                    return false; // Modal already in body
                }
            }
            return false;
        };
        
        const ensureModalInBody = window.ensureModalInBody;
        
        // Ensure modal is in body when this script runs
        ensureModalInBody();
        
        // Re-ensure modal position after any DOM changes
        setTimeout(ensureModalInBody, 100);
        
        // Initialize modal after ensuring it's in body
        let variantModalEl = document.getElementById('variantModal');
        let variantModal;
        if (variantModalEl) {
            variantModal = new bootstrap.Modal(variantModalEl, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
        }
        
        // Store modal instance globally
        window.variantModalInstance = variantModal;
    
        // Close any open modals when switching tabs
        $(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function() {
            if (window.variantModalInstance && window.variantModalInstance._isShown) {
                window.variantModalInstance.hide();
            }
            // Also hide any backdrop
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        });
        
        // Load variants table function
    window.loadVariantTable = function() {
        if (!$('#variant-table').length) return;
        
        // Destroy existing DataTable if exists
        if ($.fn.DataTable.isDataTable('#variant-table')) {
            $('#variant-table').DataTable().destroy();
        }

        $('#variant-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: baseURL + 'item-varian/getByItem/' + itemId,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataSrc: function(json) {
                    return json.data || [];
                }
            },
            columns: [
                { data: null, render: function(data, type, row, meta) {
                    return meta.row + 1;
                }},
                { data: 'variant_name' },
                { data: 'sku_variant' },
                { data: 'price', render: function(data) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(data || 0);
                }},
                { data: 'stock', render: function(data) {
                    return new Intl.NumberFormat('id-ID').format(data || 0);
                }},
                { data: null, orderable: false, render: function(data, type, row) {
                    return '<div class="btn-group btn-group-sm" role="group">' +
                           '<a href="' + baseURL + 'item-sn/' + itemId + '?variant_id=' + row.id + '" class="btn btn-info btn-sm" title="Manage SN">' +
                           '<i class="fas fa-tag"></i> Manage SN' +
                           '</a>' +
                           '<button type="button" class="btn btn-danger btn-sm btn-delete-variant" data-id="' + row.id + '" title="Hapus">' +
                           '<i class="fas fa-trash"></i>' +
                           '</button>' +
                           '</div>';
                }}
            ],
            language: {
                processing: 'Memuat...',
                emptyTable: 'Belum ada data varian',
                zeroRecords: 'Data tidak ditemukan'
            },
            order: [[1, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
        });
    };

        // Add variant button click - use direct event handler for better reliability
        const btnAddVariant = document.getElementById('btn-add-variant');
        if (btnAddVariant) {
            // Remove any existing listeners first
            const newBtn = btnAddVariant.cloneNode(true);
            btnAddVariant.parentNode.replaceChild(newBtn, btnAddVariant);
            
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Ensure modal is in body before showing
                ensureModalInBody();
                
                // Re-initialize modal reference in case DOM changed
                const modalEl = document.getElementById('variantModal');
                if (modalEl) {
                    // Dispose old instance if exists
                    if (window.variantModalInstance) {
                        try {
                            window.variantModalInstance.dispose();
                        } catch(e) {
                            // Ignore disposal errors
                        }
                    }
                    
                    // Create new modal instance
                    window.variantModalInstance = new bootstrap.Modal(modalEl, {
                        backdrop: true,
                        keyboard: true,
                        focus: true
                    });
                    
                    const currentItemId = this.getAttribute('data-item-id') || itemId;
                    document.getElementById('variantModalLabel').textContent = 'Tambah Varian';
                    const form = document.getElementById('variantForm');
                    if (form) {
                        form.reset();
                    }
                    const variantIdInput = document.getElementById('variant_id');
                    const variantItemIdInput = document.getElementById('variant_item_id');
                    if (variantIdInput) variantIdInput.value = '';
                    if (variantItemIdInput) variantItemIdInput.value = currentItemId;
                    
                    // Show modal
                    if (window.variantModalInstance) {
                        window.variantModalInstance.show();
                    }
                }
            });
        }
        
        // Initial load - wait a bit for DOM to settle
        setTimeout(function() {
            if (typeof loadVariantTable === 'function') {
                loadVariantTable();
            }
        }, 300);
    }
    
    // Initialize when DOM is ready
    if (typeof jQuery !== 'undefined' && jQuery.fn) {
        // jQuery is available
        $(document).ready(function() {
            setTimeout(initVariantModule, 100);
        });
    } else {
        // Fallback if jQuery not ready yet
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initVariantModule, 100);
            });
        } else {
            // DOM already loaded
            setTimeout(initVariantModule, 100);
        }
    }

    // Form submit handler - use event delegation to work with moved modal
    $(document).on('submit', '#variantForm', function(e) {
        e.preventDefault();
        
        const formElement = document.getElementById('variantForm');
        if (!formElement) return;
        
        const formData = new FormData(formElement);
        const btnSave = document.getElementById('btn-save-variant');
        const id = formData.get('id');
        
        // Convert formatted price to number
        const priceInput = document.getElementById('variant_price');
        if (priceInput) {
            const numericPrice = priceInput.value.replace(/[^\d]/g, '');
            formData.set('price', numericPrice || '0');
        }
        
        // Disable button
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
        }

        fetch(baseURL + 'item-varian/store', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
            }

            if (res.status === 'success') {
                // Show success message
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        iconColor: 'white',
                        customClass: {
                            popup: 'bg-success text-light toast p-2'
                        }
                    });
                    Toast.fire({
                        html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + res.message + '</div>'
                    });
                } else {
                    alert(res.message);
                }

                // Close modal and reload table
                if (window.variantModalInstance) {
                    window.variantModalInstance.hide();
                }
                // Clean up backdrop
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                $('body').css('padding-right', '');
                
                if (typeof loadVariantTable === 'function') {
                    loadVariantTable();
                }
            } else {
                // Show error
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message || 'Gagal menyimpan varian'
                    });
                } else {
                    alert(res.message || 'Gagal menyimpan varian');
                }
            }
        })
        .catch(err => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan';
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + err.message
                });
            } else {
                alert('Error: ' + err.message);
            }
        });
    });

    // Delete variant handler
    $(document).on('click', '.btn-delete-variant', function() {
        const variantId = $(this).data('id');
        if (!variantId) return;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Varian yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteVariant(variantId);
                }
            });
        } else {
            if (confirm('Yakin ingin menghapus varian ini?')) {
                deleteVariant(variantId);
            }
        }
    });

    function deleteVariant(id) {
        fetch(baseURL + 'item-varian/delete/' + id, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        iconColor: 'white',
                        customClass: {
                            popup: 'bg-success text-light toast p-2'
                        }
                    });
                    Toast.fire({
                        html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + res.message + '</div>'
                    });
                } else {
                    alert(res.message);
                }
                
                // Reload table
                if (typeof loadVariantTable === 'function') {
                    loadVariantTable();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message || 'Gagal menghapus varian'
                    });
                } else {
                    alert(res.message || 'Gagal menghapus varian');
                }
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + err.message
                });
            } else {
                alert('Error: ' + err.message);
            }
        });
    }
})();
</script>

