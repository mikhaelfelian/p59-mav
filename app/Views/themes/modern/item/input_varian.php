<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Description: View for managing product variants
 */
helper('form');
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold text-primary">
        Input Varian Produk
    </div>
    <div class="card-body">
        <form id="variant-form">
            <input type="hidden" name="id" id="variant_id">
            <input type="hidden" name="item_id" value="<?= esc($item_id) ?>">
            
            <!-- Row 1: Variant Name and SKU -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Nama Varian <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="variant_name" id="variant_name" class="form-control" 
                        placeholder="Contoh: 500ml, Hitam, Ukuran L" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        SKU Varian <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="sku_variant" id="sku_variant" class="form-control" 
                        placeholder="SKU unik untuk varian ini" required>
                </div>
            </div>

            <!-- Row 2: Price and Stock -->
            <div class="row g-3 mb-3">
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

            <!-- Row 3: Buttons -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-secondary px-4" id="btn-clear-variant">
                            <i class="fas fa-eraser me-1"></i> Bersihkan Form
                        </button>
                        <button type="submit" class="btn btn-primary px-4" id="btn-save-variant">
                            <i class="fas fa-save me-1"></i> Simpan Varian
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <hr class="my-4">

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

<script>
(function() {
    const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
    const itemId = '<?= esc($item_id) ?>';

    const form = document.getElementById('variant-form');
    const btnSave = document.getElementById('btn-save-variant');
    const btnClear = document.getElementById('btn-clear-variant');

    // Clear form handler
    if (btnClear) {
        btnClear.addEventListener('click', function() {
            form.reset();
            document.getElementById('variant_id').value = '';
            document.getElementById('variant_price').value = '0';
            document.getElementById('variant_stock').value = '0';
        });
    }

    // Form submit handler
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const variantName = document.getElementById('variant_name').value.trim();
            const skuVariant = document.getElementById('sku_variant').value.trim();

            // Validation
            if (!variantName) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'Nama Varian harus diisi'
                    });
                } else {
                    alert('Nama Varian harus diisi');
                }
                return;
            }

            if (!skuVariant) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'SKU Varian harus diisi'
                    });
                } else {
                    alert('SKU Varian harus diisi');
                }
                return;
            }

            // Disable button
            if (btnSave) {
                btnSave.disabled = true;
                btnSave.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
            }

            // Prepare form data
            const formData = new FormData(form);
            const priceInput = document.getElementById('variant_price');
            
            if (priceInput) {
                const numericPrice = priceInput.value.replace(/[^\d]/g, '');
                formData.set('price', numericPrice || '0');
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
                    btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Varian';
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

                    // Clear form
                    form.reset();
                    document.getElementById('variant_id').value = '';
                    document.getElementById('variant_price').value = '0';
                    document.getElementById('variant_stock').value = '0';
                    
                    // Reload variant table
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
                    btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Varian';
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
    }

    // Load variants table function
    window.loadVariantTable = function() {
        if (!$('#variant-table').length) return;
        
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
    
    // Initial load table
    setTimeout(function() {
        if (typeof loadVariantTable === 'function') {
            loadVariantTable();
        }
    }, 500);
})();
</script>
