<?php
// Expect $items and $id (current item id)
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold text-primary">
        Aturan Promo Produk (Buy X Get Y)
    </div>
    <div class="card-body">
        <div id="productRuleForm">
            <!-- Row 1: Produk Bonus, Jumlah Minimum, Jumlah Bonus -->
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Produk Bonus</label>
                    <select name="bonus_item_id" class="form-select">
                        <option value="">-- Pilih Produk Bonus --</option>
                        <?php foreach (($items ?? []) as $i): $iid = is_object($i)?$i->id:($i['id']??null); $iname = is_object($i)?$i->name:($i['name']??''); if ($iid == ($id ?? null)) continue; ?>
                            <option value="<?= $iid; ?>"><?= esc($iname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jumlah Minimum</label>
                    <input type="number" name="min_qty" class="form-control" placeholder="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jumlah Bonus</label>
                    <input type="number" name="bonus_qty" class="form-control" placeholder="0">
                </div>
            </div>

            <!-- Row 2: Boleh Kelipatan (Switch), Status (Dropdown), Tanggal Mulai, Tanggal Selesai -->
            <div class="row g-3 mt-3 align-items-center">
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_multiple" id="is_multiple" value="1">
                        <label class="form-check-label fw-semibold" for="is_multiple">Boleh Kelipatan</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
            </div>

            <!-- Row 3: Catatan (Full Width) -->
            <div class="row g-3 mt-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan untuk aturan promo ini..."></textarea>
                </div>
            </div>

            <!-- Row 4: Button Aligned Right -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-primary px-4" id="btn-save-promo">Simpan</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="item_id" value="<?= esc($id) ?>">
        </div>

        <!-- RULES TABLE -->
        <div class="table-responsive mt-4">
            <table class="table table-striped align-middle" id="promo-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Produk Bonus</th>
                        <th>Min Qty</th>
                        <th>Bonus Qty</th>
                        <th>Kelipatan</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>

<script>
(function(){
    const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
    const itemId = '<?= ($id ?? '') ?>';
    
    // Get CSRF token if available - check form first, then meta tag
    let csrfToken = '';
    const csrfTokenName = '<?= csrf_token() ?? "csrf_token" ?>';
    const csrfInput = document.querySelector('input[name="' + csrfTokenName + '"]') || 
                      document.querySelector('#form-item input[name="' + csrfTokenName + '"]');
    if(csrfInput) {
        csrfToken = csrfInput.value;
    } else {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if(metaToken) {
            csrfToken = metaToken.getAttribute('content');
        }
    }
    
    function loadPromo(){
        if(!itemId) {
            const tbody = document.querySelector('#promo-table tbody');
            if(tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Simpan item terlebih dahulu untuk menambahkan aturan promo</td></tr>';
            return;
        }
        
        fetch(baseURL + 'item/promoList/' + itemId, {
            headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(async r=>{
            const ct = r.headers.get('content-type')||'';
            if(!ct.includes('application/json')) { 
                return { status:'error', data: [] }; 
            }
            return r.json();
        })
        .then(res=>{
            const tbody = document.querySelector('#promo-table tbody');
            if(!tbody) return;
            tbody.innerHTML = '';
            
            if(res.status==='success' && res.data && res.data.length > 0){
                res.data.forEach((row,idx)=>{
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${idx+1}</td>
                        <td>${row.bonus_name ?? '-'}</td>
                        <td>${row.min_qty}</td>
                        <td>${row.bonus_qty}</td>
                        <td>${row.is_multiple=='1'?'Ya':'Tidak'}</td>
                        <td>${row.start_date ?? ''} - ${row.end_date ?? ''}</td>
                        <td><span class="badge ${row.status==='active'?'bg-success':'bg-secondary'}">${(row.status||'').toUpperCase()}</span></td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-delete-promo" data-id="${row.id}"><i class="fa fa-trash"></i></button></td>`;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Belum ada aturan promo</td></tr>';
            }
        })
        .catch(function(){
            const tbody = document.querySelector('#promo-table tbody');
            if(tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>';
        });
    }
    
    function deletePromo(id){
        if(!id) return;
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang sudah dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete(id);
                }
            });
        } else {
            if(confirm('Hapus aturan ini?')) {
                performDelete(id);
            }
        }
    }
    
    function performDelete(id){
        fetch(baseURL + 'item/promoDelete/' + id, {
            method:'POST', 
            headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(async r => {
            if(!r.ok) throw new Error('Network error');
            return r.json();
        })
        .then(res => {
            loadPromo();
            if(res.status === 'success'){
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
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                    Toast.fire({
                        html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Data berhasil dihapus</div>'
                    });
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Gagal menghapus data'
                    });
                }
            }
        })
        .catch(function(err){
            loadPromo();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan: ' + err.message
                });
            }
        });
    }
    
    function submitPromoForm(){
        // Check if item is saved first
        if(!itemId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silakan simpan item terlebih dahulu sebelum menambahkan aturan promo'
                });
            } else {
                alert('Silakan simpan item terlebih dahulu sebelum menambahkan aturan promo');
            }
            return;
        }
        
        const form = document.getElementById('productRuleForm');
        if(!form) return;
        
        // Validate required fields
        const bonusItemId = form.querySelector('select[name="bonus_item_id"]');
        const minQty = form.querySelector('input[name="min_qty"]');
        const bonusQty = form.querySelector('input[name="bonus_qty"]');
        
        if(!bonusItemId || !bonusItemId.value){
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: 'Produk Bonus harus dipilih'
                });
            } else {
                alert('Produk Bonus harus dipilih');
            }
            bonusItemId?.focus();
            return;
        }
        
        if(!minQty || !minQty.value || parseInt(minQty.value) <= 0){
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: 'Jumlah Minimum harus diisi dan lebih dari 0'
                });
            } else {
                alert('Jumlah Minimum harus diisi dan lebih dari 0');
            }
            minQty?.focus();
            return;
        }
        
        if(!bonusQty || !bonusQty.value || parseInt(bonusQty.value) <= 0){
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: 'Jumlah Bonus harus diisi dan lebih dari 0'
                });
            } else {
                alert('Jumlah Bonus harus diisi dan lebih dari 0');
            }
            bonusQty?.focus();
            return;
        }
        
        const fd = new FormData();
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(inp => {
            if(inp.name && !inp.disabled){
                if(inp.type === 'checkbox') {
                    if(inp.checked) fd.append(inp.name, inp.value);
                } else {
                    fd.append(inp.name, inp.value || '');
                }
            }
        });
        
        // Add CSRF token if available
        if(csrfToken) {
            fd.append(csrfTokenName, csrfToken);
        }
        
        // Disable save button during submission
        const btnSave = document.getElementById('btn-save-promo');
        if(btnSave) btnSave.disabled = true;
        
        fetch(baseURL + 'item/promoStore', {
            method:'POST', 
            body: fd, 
            headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(async response => {
            // First check if response is ok (status 200-299)
            if(!response.ok) {
                const text = await response.text();
                return { 
                    status: 'error', 
                    message: 'Server error: ' + response.status + ' ' + response.statusText,
                    raw: text.substring(0, 200)
                };
            }
            
            // Get response text first
            const text = await response.text();
            
            // Check if it looks like JSON (starts with { or [)
            const trimmedText = text.trim();
            if(trimmedText.startsWith('{') || trimmedText.startsWith('[')) {
                try {
                    return JSON.parse(text);
                } catch(e) {
                    // JSON parse failed even though it looks like JSON
                    return { 
                        status: 'error', 
                        message: 'Invalid JSON response: ' + e.message,
                        raw: text.substring(0, 200)
                    };
                }
            }
            
            // If it's HTML or other format
            // Check if it contains error messages
            if(text.includes('<html') || text.includes('<!DOCTYPE')) {
                // Try to extract error message from HTML
                const errorMatch = text.match(/<title[^>]*>([^<]+)<\/title>/i) || 
                                  text.match(/<h1[^>]*>([^<]+)<\/h1>/i) ||
                                  text.match(/error[^<]*>([^<]+)</i);
                const errorMsg = errorMatch ? errorMatch[1] : 'Server returned HTML instead of JSON';
                
                return { 
                    status: 'error', 
                    message: errorMsg + ' (Server returned HTML response)',
                    raw: text.substring(0, 300)
                };
            }
            
            // Unknown format
            return { 
                status: 'error', 
                message: 'Unexpected response format from server',
                raw: text.substring(0, 200)
            };
        })
        .then(res => {
            if(btnSave) btnSave.disabled = false;
            
            // Check if res exists and has status
            if(!res) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'No response from server'
                    });
                } else {
                    alert('No response from server');
                }
                return;
            }
            
            if(res.status === 'success'){
                // Clear form
                inputs.forEach(inp => {
                    if(inp.type !== 'hidden' && inp.type !== 'checkbox') inp.value = '';
                    if(inp.type === 'checkbox') inp.checked = false;
                });
                // Reload table
                loadPromo();
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
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                    Toast.fire({
                        html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Aturan promo berhasil disimpan</div>'
                    });
                } else {
                    alert('Aturan promo berhasil disimpan');
                }
            } else {
                // Show error message with more details
                let errorMsg = res.message || 'Gagal menyimpan aturan promo';
                
                // Add validation errors if present
                if(res.errors && typeof res.errors === 'object') {
                    const errorList = Object.values(res.errors).flat().join(', ');
                    if(errorList) errorMsg += ': ' + errorList;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMsg + (res.raw ? '<br><small class="text-muted">Response: ' + res.raw + '</small>' : '')
                    });
                } else {
                    alert('Gagal menyimpan aturan promo: ' + errorMsg);
                }
            }
        })
        .catch(function(err){
            if(btnSave) btnSave.disabled = false;
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
    
    document.addEventListener('DOMContentLoaded', function(){
        const btnSave = document.getElementById('btn-save-promo');
        
        if(btnSave){
            btnSave.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                submitPromoForm();
            });
        }
        
        // Handle delete button clicks using event delegation
        document.addEventListener('click', function(e){
            const deleteBtn = e.target.closest('.btn-delete-promo');
            if(deleteBtn){
                e.preventDefault();
                e.stopPropagation();
                const id = deleteBtn.getAttribute('data-id');
                if(id) deletePromo(id);
            }
        });
        
        // Load promo rules on page load
        loadPromo();
    });
})();
</script>