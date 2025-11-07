<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Product Rules tab for item form (cashback or buy_get)
 */
$productRule = $product_rule ?? null;
$itemId = $id ?? '';
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold text-primary">
        Product Rules
    </div>
    <div class="card-body">
        <div id="productRuleForm">
            <!-- Rule Type Selection -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rule Type <span class="text-danger">*</span></label>
                    <select name="rule_type" id="rule_type" class="form-select">
                        <option value="">-- Pilih Tipe Rule --</option>
                        <option value="cashback" <?= ($productRule && $productRule->rule_type === 'cashback') ? 'selected' : '' ?>>Cashback (Accumulative)</option>
                        <option value="buy_get" <?= ($productRule && $productRule->rule_type === 'buy_get') ? 'selected' : '' ?>>Buy X Get Y</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= ($productRule && $productRule->is_active == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>

            <!-- Cashback Rule Fields -->
            <div id="cashback-fields" style="display: <?= ($productRule && $productRule->rule_type === 'cashback') ? 'block' : 'none' ?>;">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Threshold Amount <span class="text-danger">*</span></label>
                        <input type="number" name="threshold_amount" id="threshold_amount" class="form-control" 
                               step="0.01" min="0" 
                               value="<?= $productRule && $productRule->rule_type === 'cashback' ? esc($productRule->threshold_amount ?? '') : '' ?>" 
                               placeholder="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cashback Amount <span class="text-danger">*</span></label>
                        <input type="number" name="cashback_amount" id="cashback_amount" class="form-control" 
                               step="0.01" min="0" 
                               value="<?= $productRule && $productRule->rule_type === 'cashback' ? esc($productRule->cashback_amount ?? '') : '' ?>" 
                               placeholder="0.00">
                    </div>
                </div>
            </div>

            <!-- Buy Get Rule Fields -->
            <div id="buy_get-fields" style="display: <?= ($productRule && $productRule->rule_type === 'buy_get') ? 'block' : 'none' ?>;">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Minimum Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="min_qty" id="min_qty" class="form-control" 
                               min="1" 
                               value="<?= $productRule && $productRule->rule_type === 'buy_get' ? esc($productRule->min_qty ?? '') : '' ?>" 
                               placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bonus Item <span class="text-danger">*</span></label>
                        <select name="bonus_item_id" id="bonus_item_id" class="form-select">
                            <option value="">-- Pilih Bonus Item --</option>
                            <?php foreach (($items ?? []) as $i): 
                                $iid = is_object($i) ? $i->id : ($i['id'] ?? null);
                                $iname = is_object($i) ? $i->name : ($i['name'] ?? '');
                                if ($iid == $itemId) continue; 
                            ?>
                                <option value="<?= $iid ?>" <?= ($productRule && $productRule->rule_type === 'buy_get' && $productRule->bonus_item_id == $iid) ? 'selected' : '' ?>>
                                    <?= esc($iname) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Bonus Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="bonus_qty" id="bonus_qty" class="form-control" 
                               min="1" 
                               value="<?= $productRule && $productRule->rule_type === 'buy_get' ? esc($productRule->bonus_qty ?? '') : '' ?>" 
                               placeholder="0">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_multiple" id="is_multiple" value="1" 
                                   <?= ($productRule && $productRule->rule_type === 'buy_get' && $productRule->is_multiple == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="is_multiple">Allow Multiple (Multiplier)</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Catatan tambahan untuk aturan ini..."><?= $productRule ? esc($productRule->notes ?? '') : '' ?></textarea>
                </div>
            </div>

            <!-- Save Button -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-primary px-4" id="btn-save-product-rule">Simpan</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="item_id" value="<?= esc($itemId) ?>">
        </div>

        <!-- Current Rule Display -->
        <?php if ($productRule): ?>
        <div class="alert alert-info mt-4">
            <h6 class="fw-bold">Current Rule:</h6>
            <p class="mb-1"><strong>Type:</strong> <?= ucfirst($productRule->rule_type) ?></p>
            <?php if ($productRule->rule_type === 'cashback'): ?>
                <p class="mb-1"><strong>Threshold:</strong> <?= number_format($productRule->threshold_amount ?? 0, 2) ?></p>
                <p class="mb-1"><strong>Cashback:</strong> <?= number_format($productRule->cashback_amount ?? 0, 2) ?></p>
            <?php elseif ($productRule->rule_type === 'buy_get'): ?>
                <p class="mb-1"><strong>Min Qty:</strong> <?= $productRule->min_qty ?></p>
                <p class="mb-1"><strong>Bonus Qty:</strong> <?= $productRule->bonus_qty ?></p>
                <p class="mb-1"><strong>Allow Multiple:</strong> <?= $productRule->is_multiple ? 'Yes' : 'No' ?></p>
            <?php endif; ?>
            <p class="mb-0"><strong>Status:</strong> <?= $productRule->is_active ? 'Active' : 'Inactive' ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function(){
    const baseURL = '<?= ($config->baseURL ?? base_url()) ?>';
    const itemId = '<?= ($itemId ?? '') ?>';
    
    // Get CSRF token if available
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
    
    // Toggle fields based on rule type
    function toggleRuleFields() {
        const ruleType = document.getElementById('rule_type').value;
        const cashbackFields = document.getElementById('cashback-fields');
        const buyGetFields = document.getElementById('buy_get-fields');
        
        if (ruleType === 'cashback') {
            cashbackFields.style.display = 'block';
            buyGetFields.style.display = 'none';
            // Clear buy_get required fields
            document.getElementById('min_qty').removeAttribute('required');
            document.getElementById('bonus_item_id').removeAttribute('required');
            document.getElementById('bonus_qty').removeAttribute('required');
            // Set cashback required fields
            document.getElementById('threshold_amount').setAttribute('required', 'required');
            document.getElementById('cashback_amount').setAttribute('required', 'required');
        } else if (ruleType === 'buy_get') {
            cashbackFields.style.display = 'none';
            buyGetFields.style.display = 'block';
            // Clear cashback required fields
            document.getElementById('threshold_amount').removeAttribute('required');
            document.getElementById('cashback_amount').removeAttribute('required');
            // Set buy_get required fields
            document.getElementById('min_qty').setAttribute('required', 'required');
            document.getElementById('bonus_item_id').setAttribute('required', 'required');
            document.getElementById('bonus_qty').setAttribute('required', 'required');
        } else {
            cashbackFields.style.display = 'none';
            buyGetFields.style.display = 'none';
        }
    }
    
    // Submit form
    function submitProductRuleForm(){
        // Check if item is saved first
        if(!itemId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Silakan simpan item terlebih dahulu sebelum menambahkan product rule'
                });
            } else {
                alert('Silakan simpan item terlebih dahulu sebelum menambahkan product rule');
            }
            return;
        }
        
        const form = document.getElementById('productRuleForm');
        if(!form) return;
        
        const ruleType = document.getElementById('rule_type').value;
        
        // Validate rule type
        if(!ruleType) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: 'Rule Type harus dipilih'
                });
            } else {
                alert('Rule Type harus dipilih');
            }
            document.getElementById('rule_type').focus();
            return;
        }
        
        // Validate based on rule type
        if(ruleType === 'cashback') {
            const thresholdAmount = document.getElementById('threshold_amount').value;
            const cashbackAmount = document.getElementById('cashback_amount').value;
            
            if(!thresholdAmount || parseFloat(thresholdAmount) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'Threshold Amount harus diisi dan lebih dari 0'
                    });
                } else {
                    alert('Threshold Amount harus diisi dan lebih dari 0');
                }
                document.getElementById('threshold_amount').focus();
                return;
            }
            
            if(!cashbackAmount || parseFloat(cashbackAmount) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'Cashback Amount harus diisi dan lebih dari 0'
                    });
                } else {
                    alert('Cashback Amount harus diisi dan lebih dari 0');
                }
                document.getElementById('cashback_amount').focus();
                return;
            }
        } else if(ruleType === 'buy_get') {
            const minQty = document.getElementById('min_qty').value;
            const bonusItemId = document.getElementById('bonus_item_id').value;
            const bonusQty = document.getElementById('bonus_qty').value;
            
            if(!minQty || parseInt(minQty) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'Minimum Quantity harus diisi dan lebih dari 0'
                    });
                } else {
                    alert('Minimum Quantity harus diisi dan lebih dari 0');
                }
                document.getElementById('min_qty').focus();
                return;
            }
            
            if(!bonusItemId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'Bonus Item harus dipilih'
                    });
                } else {
                    alert('Bonus Item harus dipilih');
                }
                document.getElementById('bonus_item_id').focus();
                return;
            }
            
            if(!bonusQty || parseInt(bonusQty) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validasi Gagal',
                        text: 'Bonus Quantity harus diisi dan lebih dari 0'
                    });
                } else {
                    alert('Bonus Quantity harus diisi dan lebih dari 0');
                }
                document.getElementById('bonus_qty').focus();
                return;
            }
        }
        
        const fd = new FormData();
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(inp => {
            if(inp.name && !inp.disabled){
                if(inp.type === 'checkbox') {
                    if(inp.checked) fd.append(inp.name, inp.value);
                    else fd.append(inp.name, '0');
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
        const btnSave = document.getElementById('btn-save-product-rule');
        if(btnSave) btnSave.disabled = true;
        
        fetch(baseURL + 'item/saveProductRule/' + itemId, {
            method:'POST', 
            body: fd, 
            headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(async response => {
            if(!response.ok) {
                const text = await response.text();
                return { 
                    status: 'error', 
                    message: 'Server error: ' + response.status,
                    raw: text.substring(0, 200)
                };
            }
            
            const text = await response.text();
            const trimmedText = text.trim();
            if(trimmedText.startsWith('{') || trimmedText.startsWith('[')) {
                try {
                    return JSON.parse(text);
                } catch(e) {
                    return { 
                        status: 'error', 
                        message: 'Invalid JSON response: ' + e.message
                    };
                }
            }
            
            return { 
                status: 'error', 
                message: 'Unexpected response format from server'
            };
        })
        .then(res => {
            if(btnSave) btnSave.disabled = false;
            
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
                // Show success message and reload page to show updated rule
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
                        html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> Product rule berhasil disimpan</div>'
                    });
                } else {
                    alert('Product rule berhasil disimpan');
                }
                
                // Reload page after a short delay to show updated rule
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                let errorMsg = res.message || 'Gagal menyimpan product rule';
                if(res.errors && typeof res.errors === 'object') {
                    const errorList = Object.values(res.errors).flat().join(', ');
                    if(errorList) errorMsg += ': ' + errorList;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMsg
                    });
                } else {
                    alert('Gagal menyimpan product rule: ' + errorMsg);
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
        const ruleTypeSelect = document.getElementById('rule_type');
        const btnSave = document.getElementById('btn-save-product-rule');
        
        if(ruleTypeSelect){
            ruleTypeSelect.addEventListener('change', toggleRuleFields);
            // Initialize on page load
            toggleRuleFields();
        }
        
        if(btnSave){
            btnSave.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                submitProductRuleForm();
            });
        }
    });
})();
</script>

