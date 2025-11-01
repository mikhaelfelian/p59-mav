<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22
 * Github: github.com/mikhaelfelian
 * Description: View for item form (add/edit) with fixed and dynamic fields using CI 4.3.1 form helpers
 * This file represents the View for item-form.
 */

$isModal = $isModal ?? false;
?>
<?php if (!$isModal): ?>
	<div class="card">
		<div class="card-header">
			<h5 class="card-title"><?= $title ?></h5>
		</div>
		<div class="card-body">
		<?php endif; ?>
		<?php
		if (!empty($message)) {
			show_message($message);
		} ?>

		<form method="post" action="<?= $config->baseURL ?>item/store" class="form-container"
			enctype="multipart/form-data" id="form-item" onsubmit="return convertPriceValuesBeforeSubmit(this);">
			<!-- Tabs navigation -->
				<ul class="nav nav-tabs" id="itemTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<a class="nav-link active" data-bs-toggle="tab" href="#tab-info" role="tab">Info Produk</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" data-bs-toggle="tab" href="#tab-spec" role="tab">Spesifikasi</a>
				</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" data-bs-toggle="tab" href="#tab-promo" role="tab">Aturan Promo Produk</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" data-bs-toggle="tab" href="#tab-agent-price" role="tab">Harga Khusus Agen</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" data-bs-toggle="tab" href="#tab-input-sn" role="tab">Input SN</a>
					</li>
			</ul>

			<div class="tab-content mt-3">
				<!-- Info Produk Tab -->
				<div class="tab-pane fade show active" id="tab-info" role="tabpanel">
					<div class="row mb-3">
						<div class="col-md-9">
							<!-- FIXED FIELDS -->
							<div class="mb-3">
								<label class="control-label mb-2">SKU</label>
								<input class="form-control" type="text" name="sku"
									value="<?= set_value('sku', @$item->sku) ?>" placeholder="SKU akan otomatis dibuat"
									readonly />
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Nama Item <span class="text-danger">*</span></label>
								<input class="form-control" type="text" name="name"
									value="<?= set_value('name', @$item->name) ?>" placeholder="Masukkan nama item"
									required />
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Slug</label>
								<input class="form-control" type="text" name="slug" id="item-slug"
									value="<?= set_value('slug', @$item->slug) ?>" placeholder="Slug akan otomatis dibuat dari nama item" />
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Deskripsi</label>
								<textarea class="form-control tinymce" rows="30" type="text"
									name="description"><?= set_value('description', @$item->description) ?></textarea>
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Deskripsi Singkat</label>
								<textarea class="form-control" rows="2" type="text" name="short_description"
									placeholder="Masukkan deskripsi singkat item"><?= set_value('short_description', @$item->short_description) ?></textarea>
							</div>
						</div>

						<div class="col-md-3">
							<div class="mb-3">
								<label class="control-label mb-2">Brand <span class="text-danger">*</span></label>
								<div>
									<?php
									if (empty($brands)) {
										echo '<div class="alert alert-danger">Data brand masih kosong</div>';
									} else {
										$brandOptions = [];
										foreach ($brands as $brand) {
											$brandOptions[$brand->id] = $brand->name;
										}
										echo options(['class' => 'form-control select2', 'name' => 'brand_id', 'required' => 'required'], $brandOptions, set_value('brand_id', @$item->brand_id));
									} ?>
								</div>
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Kategori <span class="text-danger">*</span></label>
								<div>
									<?php
									if (empty($categories)) {
										echo '<div class="alert alert-danger">Data kategori masih kosong</div>';
									} else {
										$categoryOptions = [];
										foreach ($categories as $category) {
											$categoryOptions[$category->id] = $category->category;
										}
										echo options(['class' => 'form-control select2', 'name' => 'category_id', 'required' => 'required'], $categoryOptions, set_value('category_id', @$item->category_id));
									} ?>
								</div>
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Harga</label>
								<?= form_input([
									'name' => 'price',
									'class' => 'form-control price-format',
									'value' => format_angka(@$item->price, 0),
									'placeholder' => '0',
									'data-type' => 'currency'
								]) ?>
							</div>
							<div class="mb-3">
								<label class="control-label mb-2">Harga Agen</label>
								<?= form_input([
									'name' => 'agent_price',
									'class' => 'form-control price-format',
									'value' => format_angka(@$item->agent_price, 0),
									'placeholder' => '0',
									'data-type' => 'currency'
								]) ?>
							</div>
							<div class="mb-3">
								<label class="control-label mb-2">Tampilkan di Web</label>
								<div class="form-switch">
									<input type="checkbox" name="is_catalog" value="1" class="form-check-input"
										<?= set_value('is_catalog', @$item->is_catalog) == '1' ? 'checked' : '' ?> />
									<label class="form-check-label">Item ini ditampilkan di katalog web</label>
								</div>
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Status</label>
								<div class="form-check form-switch">
									<?php $statusValue = set_value('status', @$item->status ?? '1'); ?>
									<input type="hidden" name="status" value="0" id="status-hidden">
									<input type="checkbox" name="status" value="1" class="form-check-input" id="item-status"
										<?= $statusValue == '1' ? 'checked' : '' ?> />
									<label class="form-check-label" for="item-status">
										<span class="badge bg-success me-2 <?= $statusValue == '1' ? '' : 'd-none' ?>" id="status-badge">
											<i class="fas fa-check-circle me-1"></i> Aktif
										</span>
										<span class="badge bg-danger me-2 <?= $statusValue == '1' ? 'd-none' : '' ?>" id="status-badge-inactive">
											<i class="fas fa-times-circle me-1"></i> Tidak Aktif
										</span>
									</label>
								</div>
							</div>

							<div class="mb-3">
								<label class="control-label mb-2">Feature Image</label>
								<input class="form-control" type="file" name="image" accept="image/*" />
								<?php if (!empty($image) && is_array($image) && !empty($image['nama_file'])): ?>
									<div class="mt-2">
										<img src="<?= base_url('public/uploads/' . $image['nama_file']) ?>"
											class="img-thumbnail" style="max-width: 200px; max-height: 200px;" />
									</div>
								<?php elseif (!empty($item->image)): ?>
									<div class="mt-2">
										<img src="<?= base_url('public/uploads/' . $item->image) ?>" class="img-thumbnail"
											style="max-width: 200px; max-height: 200px;" />
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>

				<!-- Spesifikasi Tab -->
				<div class="tab-pane fade" id="tab-spec" role="tabpanel">
					<!-- DYNAMIC FIELD INPUT - SPECIFICATIONS -->
					<div class="row mb-3">
						<div class="col-12">
							<h6>Spesifikasi Item</h6>
							<div id="specification-container">
								<?php
								// Create specification options
								$specOptions = [];
								foreach ($specifications as $spec) {
									$specOptions[$spec->id] = $spec->name;
								}

								// Handle existing specifications for edit mode
								if (!empty($existing_specifications)) {
									// Edit mode - show existing specifications
									foreach ($existing_specifications as $key => $existingSpec) {
										$btn_icon = $key == 0 ? 'fa-plus' : 'fa-times';
										$btn_add = $key == 0 ? 'id="add-spec"' : '';
										$btn_remove = $key == 0 ? '' : 'delete-spec';
										$btn_color = $key == 0 ? 'btn-success' : 'btn-danger';
										?>
										<div class="row mb-2 spec-row">
											<div class="col-md-5">
												<?php
												echo options(['class' => 'form-control spec-select', 'name' => 'spec_name[]'], $specOptions, $existingSpec->item_spec_id);
												?>
											</div>
											<div class="col-md-5">
												<input class="form-control" type="text" name="spec_value[]"
													value="<?= $existingSpec->value ?? '' ?>"
													placeholder="Masukkan nilai spesifikasi" />
											</div>
											<div class="col-md-2">
												<a href="javascript:void(0)" <?= $btn_add ?>
													class="btn <?= $btn_color ?> btn-sm <?= $btn_remove ?>">
													<i class="fas <?= $btn_icon ?>"></i>
												</a>
											</div>
										</div>
										<?php
									}
								} else {
									// Add mode - show empty row
									?>
									<div class="row mb-2 spec-row">
										<div class="col-md-5">
											<?php
											echo options(['class' => 'form-control spec-select', 'name' => 'spec_name[]'], $specOptions, '');
											?>
										</div>
										<div class="col-md-5">
											<input class="form-control" type="text" name="spec_value[]" value=""
												placeholder="Masukkan nilai spesifikasi" />
										</div>
										<div class="col-md-2">
											<a href="javascript:void(0)" id="add-spec" class="btn btn-success btn-sm">
												<i class="fas fa-plus"></i>
											</a>
										</div>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				</div>


				<!-- Aturan Promo Produk Tab -->
				<div class="tab-pane fade" id="tab-promo" role="tabpanel">
					<?= view('themes/modern/product_rules/tab', [
						'items' => $items ?? [], 
						'id' => $id ?? '',
						'config' => $config ?? null
					]); ?>
				</div>

				<!-- Harga Khusus Agen Tab -->
				<div class="tab-pane fade" id="tab-agent-price" role="tabpanel">
					<?= view('themes/modern/product_rules/tab-agent-price', [
						'items' => $items ?? [], 
						'id' => $id ?? '',
						'agents' => $agents ?? []
					]); ?>
				</div>

				<!-- Input SN Tab -->
				<div class="tab-pane fade" id="tab-input-sn" role="tabpanel">
					<?= view('themes/modern/product_rules/tab-input-sn', [
						'id' => $id ?? '',
						'config' => $config ?? null
					]); ?>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-sm-12">
					<button type="submit" name="submit" id="btn-submit" value="item"
						class="btn btn-primary" style="position: relative; z-index: 10; pointer-events: auto;">Simpan</button>
					<input type="hidden" name="id" value="<?= $id ?>" />
				</div>
			</div>
		</form>
		<?php if (!$isModal): ?>
		</div>
	</div>
<?php endif; ?>

<script>
	// Pass PHP data to JavaScript
	var specOptions = <?= json_encode($specOptions) ?>;
	
	// CRITICAL: Convert formatted price values to plain numbers BEFORE form submits
	// This function MUST run before form submission to ensure price values are sent correctly
	function convertPriceValuesBeforeSubmit(form) {
		// Find ONLY the main price-format inputs (price and agent_price), NOT the agent price tab input
		// Exclude inputs inside #agent-price-form (they're handled separately via AJAX)
		const priceInputs = form.querySelectorAll('.price-format:not(#agent-price-form .price-format)');
		
		priceInputs.forEach(function(input) {
			// Skip if this input is inside the agent-price-form container
			if (input.closest('#agent-price-form')) {
				return;
			}
			
			const originalValue = input.value || '';
			
			// Remove ALL non-digit characters (dots, commas, spaces, currency symbols, etc.)
			// This converts "6.000" or "6,000" or "Rp 6.000" to "6000"
			let numericValue = originalValue.replace(/[^\d]/g, '');
			
			// ALWAYS set the numeric value - even if it's "0" or empty
			// This ensures we send the actual value, not the formatted display value
			input.value = numericValue || '';
			
			// Production: Remove console.debug/log output
		});
		
		// Allow form to submit normally
		return true;
	}
	
	// Auto-slug generation from item name
	document.addEventListener('DOMContentLoaded', function() {
		const nameInput = document.querySelector('input[name="name"]');
		const slugInput = document.getElementById('item-slug');
		if(nameInput && slugInput) {
			let slugManuallyEdited = slugInput.value ? true : false;
			slugInput.addEventListener('input', function() { slugManuallyEdited = true; });
			nameInput.addEventListener('input', function() {
				if(!slugManuallyEdited || !slugInput.value) {
					const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
					slugInput.value = slug;
				}
			});
			slugInput.addEventListener('blur', function() { if(!this.value) slugManuallyEdited = false; });
		}
		
		// Status switch toggle handler
		const statusSwitch = document.getElementById('item-status');
		const statusBadge = document.getElementById('status-badge');
		const statusBadgeInactive = document.getElementById('status-badge-inactive');
		
		if(statusSwitch && statusBadge && statusBadgeInactive) {
			// Update badge on toggle
			function updateStatusBadge() {
				if(statusSwitch.checked) {
					statusBadge.classList.remove('d-none');
					statusBadgeInactive.classList.add('d-none');
				} else {
					statusBadge.classList.add('d-none');
					statusBadgeInactive.classList.remove('d-none');
				}
			}
			
			// Initial state
			updateStatusBadge();
			
			// Listen for changes
			statusSwitch.addEventListener('change', updateStatusBadge);
		}
		
		// Ensure submit button works AND ensure price conversion happens
		const formItem = document.getElementById('form-item');
		const btnSubmit = document.getElementById('btn-submit');
		if(formItem && btnSubmit) {
			btnSubmit.disabled = false;
			btnSubmit.style.pointerEvents = 'auto';
			
			// Handle status value before form submission
			const statusHidden = document.getElementById('status-hidden');
			const statusSwitch = document.getElementById('item-status');
			
			// Form submit handler
			const handleFormSubmit = function() {
				convertPriceValuesBeforeSubmit(formItem);
				
				// Ensure correct status value is sent
				if(statusSwitch && statusHidden) {
					if(statusSwitch.checked) {
						// When checked, disable hidden input so only checkbox value (1) is sent
						statusHidden.disabled = true;
					} else {
						// When unchecked, disable checkbox so only hidden input value (0) is sent
						statusSwitch.disabled = true;
					}
				}
			};
			
			// DOUBLE CHECK: Also add jQuery submit handler as backup
			if (typeof jQuery !== 'undefined') {
				jQuery(formItem).on('submit', handleFormSubmit);
			}
			
			// TRIPLE CHECK: Also intercept button click
			btnSubmit.addEventListener('click', function(e) {
				handleFormSubmit();
			}, true);
			
			// Also handle form's native submit
			formItem.addEventListener('submit', handleFormSubmit);
		}
	});
    // Agent price management functionality
    document.addEventListener('DOMContentLoaded', function(){
        const itemId = '<?= $id ?>';
        const baseURL = '<?= $config->baseURL ?>';
        
        // Function to load agent prices for current item
        function loadAgentPrices(){
            if(!itemId) {
                // If no item ID, clear the table
                const tbody = document.querySelector('#agent-price-table tbody');
                if(tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Simpan item terlebih dahulu untuk menambahkan harga agen</td></tr>';
                return;
            }
            
            fetch(baseURL + 'item/listAgentPrices/' + itemId, {
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
                const tbody = document.querySelector('#agent-price-table tbody');
                if(!tbody) return;
                tbody.innerHTML = '';
                
                if(res.status==='success' && res.data && res.data.length > 0){
                    res.data.forEach((row,idx)=>{
                        const tr = document.createElement('tr');
                        // Debug: log the row to see the actual structure
                        // console.log('Row data:', row);
                        
                        // Get ID - CodeIgniter returns objects, so try different property access methods
                        const rowId = row.id || row.item_agent?.id || (typeof row === 'object' && 'id' in row ? row.id : null);
                        
                        if(!rowId) {
                            // If no ID found, log the row structure for debugging
                            console.error('No ID found in row:', row);
                        }
                        
                        tr.innerHTML = `<td>${idx+1}</td>
                            <td>${(row.agent_code||'')+' '+(row.agent_name||'-')}</td>
                            <td>Rp ${new Intl.NumberFormat('id-ID').format(row.price||0)}</td>
                            <td>${row.is_active=='1'?'Aktif':'Nonaktif'}</td>
                            <td><button type="button" class="btn btn-sm btn-danger btn-delete-agent-price" data-id="${rowId || ''}">Hapus</button></td>`;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Belum ada harga agen khusus</td></tr>';
                }
            })
            .catch(function(){
                const tbody = document.querySelector('#agent-price-table tbody');
                if(tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading data</td></tr>';
            });
        }
        
        // Load agent prices on page load
        loadAgentPrices();
        
        // Handle "Add Agent Price" button click - use event delegation for dynamically loaded content
        document.addEventListener('click', function(e) {
            // Check if clicked element is the add button or inside it
            const addBtn = e.target.closest('#btn-add-agent-price');
            if(addBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                // Check if item is saved first
                if(!itemId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Silakan simpan item terlebih dahulu sebelum menambahkan harga agen khusus'
                        });
                    } else {
                        alert('Silakan simpan item terlebih dahulu sebelum menambahkan harga agen khusus');
                    }
                    return;
                }
                
                const form = document.getElementById('agent-price-form');
                if(!form) {
                    return;
                }
                
                const userIdSelect = form.querySelector('select[name="user_id"]');
                const priceInput = form.querySelector('input[name="agent_special_price"]');
                const itemIdInput = form.querySelector('input[name="item_id"]');
                
                // Validation
                if(!userIdSelect || !userIdSelect.value){
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            text: 'Pilih Agen harus diisi'
                        });
                    } else {
                        alert('Pilih Agen harus diisi');
                    }
                    userIdSelect?.focus();
                    return;
                }
                if(!priceInput || !priceInput.value){
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            text: 'Harga Khusus Agen harus diisi'
                        });
                    } else {
                        alert('Harga Khusus Agen harus diisi');
                    }
                    priceInput?.focus();
                    return;
                }
                
                // Convert formatted price to number before sending
                const numericPrice = priceInput.value.replace(/[^\d]/g, '');
                if(!numericPrice || parseFloat(numericPrice) <= 0){
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            text: 'Harga Khusus Agen harus lebih dari 0'
                        });
                    } else {
                        alert('Harga Khusus Agen harus lebih dari 0');
                    }
                    priceInput?.focus();
                    return;
                }
                
                // Disable button during submission
                addBtn.disabled = true;
                addBtn.textContent = 'Menyimpan...';
                
                // Prepare form data
                const fd = new FormData();
                fd.append('item_id', itemIdInput ? itemIdInput.value : itemId);
                fd.append('user_id', userIdSelect.value);
                fd.append('price', numericPrice);
                fd.append('is_active', '1'); // Default to active
                
                // Send request
                fetch(baseURL + 'item/storeAgentPrice', {
                    method:'POST', 
                    body: fd, 
                    headers: {'X-Requested-With':'XMLHttpRequest'}
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    if(contentType.includes('application/json')) {
                        return response.json();
                    }
                    return { status: 'error', message: 'Invalid response' };
                })
                .then(res => {
                    addBtn.disabled = false;
                    addBtn.textContent = 'Tambah Harga';
                    
                    if(res.status === 'success'){
                        // Clear form
                        userIdSelect.value = '';
                        priceInput.value = '';
                        // Reload table
                        loadAgentPrices();
                        // Show success toast message
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
                                html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + (res.message || 'Harga agen berhasil ditambahkan') + '</div>'
                            });
                        } else {
                            alert(res.message || 'Harga agen berhasil ditambahkan');
                        }
                    } else {
                        // Show error message
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: res.message || 'Gagal menambahkan harga agen'
                            });
                        } else {
                            alert('Gagal menambahkan harga agen: ' + (res.message || 'Unknown error'));
                        }
                    }
                })
                .catch(function(err) {
                    addBtn.disabled = false;
                    addBtn.textContent = 'Tambah Harga';
                    // Show error message
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
                return;
            }
            
            // Handle delete agent price
            const deleteBtn = e.target.closest('.btn-delete-agent-price');
            if(deleteBtn){
                e.preventDefault();
                e.stopPropagation();
                
                // Get the ID from the button
                const agentPriceId = deleteBtn.getAttribute('data-id');
                if(!agentPriceId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'ID tidak ditemukan'
                        });
                    } else {
                        alert('ID tidak ditemukan');
                    }
                    return;
                }
                
                // Show confirmation dialog
                const confirmDelete = function() {
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
                                performDelete(agentPriceId);
                            }
                        });
                    } else {
                        if (confirm('Yakin ingin menghapus harga agen ini?')) {
                            performDelete(agentPriceId);
                        }
                    }
                };
                
                const performDelete = function(agentPriceId) {
                    // Log for debugging
                    // console.log('Attempting to delete agent price with ID:', agentPriceId);
                    
                    // Convert to number to ensure it's a valid ID
                    const id = parseInt(agentPriceId, 10);
                    if(!id || isNaN(id)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'ID tidak valid: ' + agentPriceId
                            });
                        } else {
                            alert('ID tidak valid: ' + agentPriceId);
                        }
                        return;
                    }
                    
                    // Use FormData instead of JSON (CodeIgniter prefers form data)
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    fetch(baseURL + 'item/deleteAgentPrice', {
                        method:'POST',
                        headers: {'X-Requested-With':'XMLHttpRequest'},
                        body: formData
                    })
                    .then(r => {
                        if(!r.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return r.json();
                    })
                    .then(res => {
                        if(res.status === 'success'){
                            loadAgentPrices();
                            // Show success toast message
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
                                    html: '<div class="toast-content"><i class="far fa-check-circle me-2"></i> ' + (res.message || 'Data berhasil dihapus') + '</div>'
                                });
                            } else {
                                alert(res.message || 'Data berhasil dihapus');
                            }
                        } else {
                            // Show error message
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: res.message || 'Gagal menghapus data'
                                });
                            } else {
                                alert('Gagal menghapus: ' + (res.message || 'Unknown error'));
                            }
                        }
                    })
                    .catch(function(err) {
                        // Show error message
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
                };
                
                confirmDelete();
            }
        });
    });
</script>