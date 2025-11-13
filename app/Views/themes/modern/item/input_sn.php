<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Description: View for inputting Serial Numbers (SN) for items
 */
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold text-primary">
        Input Serial Number (SN)
    </div>
    <div class="card-body">
        <form id="sn-form">
            <input type="hidden" name="item_id" value="<?= esc($item_id) ?>">
            <input type="hidden" name="agent_id" value="0">
            <?php if (!empty($variant_id)): ?>
                <input type="hidden" name="variant_id" value="<?= esc($variant_id) ?>">
                <?php if (!empty($variant_info)): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Varian:</strong> <?= esc($variant_info['variant_name']) ?> 
                        (SKU: <?= esc($variant_info['sku_variant']) ?>)
                        <br><small>Serial Number yang dimasukkan akan dikaitkan dengan varian ini.</small>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Row 1: Information & Template Download -->
            <div class="row g-3 mb-3">
                <div class="col-md-12 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div class="mb-2 mb-md-0">
                        <div class="fw-semibold text-secondary">
                            Seluruh SN yang diinput dimiliki oleh pusat.
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            SN akan otomatis dialihkan ke agen saat transaksi penjualan berlangsung.
                        </small>
                    </div>
                    <a href="<?= rtrim($config->baseURL, '/') ?>/item-sn/downloadTemplate" class="btn btn-info btn-sm">
                        <i class="fas fa-download me-1"></i> Download Template Excel
                    </a>
                </div>
            </div>

            <!-- Row 2: SN Input Options -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Input Manual Serial Number
                    </label>
                    <textarea name="sn_list" id="sn_list" class="form-control" rows="5" 
                        placeholder="Masukkan SN (satu per baris atau pisahkan dengan koma):&#10;SN001234567&#10;SN001234568&#10;SN001234569"></textarea>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Pisahkan setiap SN dengan baris baru atau koma
                    </small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Upload File Excel (.xlsx, .xls)
                    </label>
                    <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx,.xls">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Format: SN | SN_Replaced
                        <br>Note: Status aktivasi dan tanggal tidak dapat diubah dari form ini
                    </small>
                </div>
            </div>

            <!-- Row 3: Buttons -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-secondary px-4" id="btn-clear-sn">
                            <i class="fas fa-eraser me-1"></i> Bersihkan Form
                        </button>
                        <button type="submit" class="btn btn-primary px-4" id="btn-save-sn">
                            <i class="fas fa-save me-1"></i> Simpan SN
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <!-- SN Listing Table -->
        <?= view('themes/modern/item/partials/sn_table', ['item_id' => $item_id, 'config' => $config]) ?>
    </div>
</div>

<script>
(function() {
    const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
    const itemId = '<?= esc($item_id) ?>';

    const form = document.getElementById('sn-form');
    const btnSave = document.getElementById('btn-save-sn');
    const btnClear = document.getElementById('btn-clear-sn');
    const excelFile = document.getElementById('excel_file');
    const snList = document.getElementById('sn_list');

    // Clear form handler
    if (btnClear) {
        btnClear.addEventListener('click', function() {
            form.reset();
            if (excelFile) excelFile.value = '';
            if (snList) snList.value = '';
        });
    }

    // Form submit handler
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const hasSnList = snList && snList.value.trim();
            const hasExcelFile = excelFile && excelFile.files.length > 0;

            // Validation
            if (!hasSnList && !hasExcelFile) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'Input SN manual atau file Excel harus diisi'
                    });
                } else {
                    alert('Input SN manual atau file Excel harus diisi');
                }
                return;
            }

            // Disable button
            if (btnSave) {
                btnSave.disabled = true;
                btnSave.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
            }

            // Determine if using Excel or manual input
            if (hasExcelFile) {
                // Import Excel
                importExcel();
            } else {
                // Manual input
                saveSnManual();
            }
        });
    }

    // Save manual SN input
    function saveSnManual() {
        const formData = new FormData(form);
        
        fetch(baseURL + 'item-sn/store', {
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
                btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan SN';
            }

            if (res.status === 'success' || res.status === 'partial') {
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
                if (snList) snList.value = '';
                
                // Reload SN table
                if (typeof loadSnTable === 'function') {
                    loadSnTable();
                }
            } else {
                // Show error
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message || 'Gagal menyimpan SN'
                    });
                } else {
                    alert(res.message || 'Gagal menyimpan SN');
                }
            }
        })
        .catch(err => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan SN';
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
    }

    // Import Excel file
    function importExcel() {
        const formData = new FormData(form);
        const file = excelFile.files[0];
        
        if (!file) {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan SN';
            }
            return;
        }

        formData.append('excel_file', file);

        fetch(baseURL + 'item-sn/importExcel', {
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
                btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan SN';
            }

            if (res.status === 'success' || res.status === 'partial') {
                // Show success message
                if (typeof Swal !== 'undefined') {
                    let message = res.message;
                    if (res.errors && res.errors.length > 0) {
                        message += '<br><br>Errors:<br>' + res.errors.slice(0, 5).map(e => 
                            `Baris ${e.row}: ${e.message || e}`
                        ).join('<br>');
                    }
                    
                    Swal.fire({
                        icon: res.status === 'success' ? 'success' : 'warning',
                        title: res.status === 'success' ? 'Sukses!' : 'Sebagian Berhasil',
                        html: message
                    });
                } else {
                    alert(res.message);
                }

                // Clear form
                form.reset();
                if (excelFile) excelFile.value = '';
                
                // Reload SN table
                if (typeof loadSnTable === 'function') {
                    loadSnTable();
                }
            } else {
                // Show error
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message || 'Gagal mengimpor SN'
                    });
                } else {
                    alert(res.message || 'Gagal mengimpor SN');
                }
            }
        })
        .catch(err => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="fas fa-save me-1"></i> Simpan SN';
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
    }

})();
</script>
