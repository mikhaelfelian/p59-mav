<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22
 * Github: github.com/mikhaelfelian
 * Description: View for item form (add/edit) with fixed and dynamic fields using CI 4.3.1 form helpers
 * This file represents the View for item-form.
 */

helper('form');
helper('html');
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
			enctype="multipart/form-data" id="form-item">
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
								<label class="control-label mb-2">Stockable</label>
								<div class="form-switch">
									<input type="checkbox" name="is_stockable" value="1" class="form-check-input"
										<?= set_value('is_stockable', @$item->is_stockable) == '1' ? 'checked' : '' ?> />
									<label class="form-check-label">Item dapat di-stock</label>
								</div>
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
								<div>
									<?php
									echo options(['class' => 'form-control', 'name' => 'status'], ['1' => 'Aktif', '0' => 'Tidak Aktif'], set_value('status', @$item->status));
									?>
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
					<?= view('themes/modern/product_rules/tab', ['items' => $items ?? [], 'id' => $id ?? '']); ?>
				</div>

				<!-- Harga Khusus Agen Tab -->
				<div class="tab-pane fade" id="tab-agent-price" role="tabpanel">
					<div class="card">
						<div class="card-header"><h6 class="card-title mb-0">Harga Khusus Agen</h6></div>
						<div class="card-body">
							<div id="agent-price-form" class="row g-3">
								<div class="col-md-4">
									<label class="form-label">Pilih Agen</label>
									<select name="user_id" class="form-control">
										<option value="">Pilih Agen</option>
										<?php if (!empty($agents ?? [])): foreach ($agents as $ag): ?>
										<option value="<?= $ag->id ?>"><?= esc($ag->code ?? 'NO_CODE') ?> - <?= esc($ag->agent ?? $ag->name ?? '-') ?></option>
										<?php endforeach; endif; ?>
									</select>
								</div>
								<div class="col-md-4">
									<label class="form-label">Harga Khusus Agen</label>
									<input type="text" name="price" class="form-control price-format" placeholder="0">
								</div>
								<div class="col-md-4 align-self-end">
									<button type="button" class="btn btn-primary" id="btn-add-agent-price">Tambah Harga</button>
								</div>
								<input type="hidden" name="item_id" value="<?= esc($id) ?>">
							</div>

							<hr/>
							<div id="agent-price-table" class="table-responsive">
								<table class="table table-bordered"><thead><tr>
									<th>#</th><th>Agen</th><th>Harga</th><th>Status</th><th>Aksi</th>
								</tr></thead><tbody></tbody></table>
							</div>
						</div>
					</div>
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
	});
	
	// Ensure main form submission works
	document.addEventListener('DOMContentLoaded', function() {
		const formItem = document.getElementById('form-item');
		const btnSubmit = document.getElementById('btn-submit');
		
		if(formItem && btnSubmit) {
			// Force enable button
			btnSubmit.disabled = false;
			btnSubmit.style.pointerEvents = 'auto';
			btnSubmit.style.cursor = 'pointer';
			btnSubmit.style.position = 'relative';
			btnSubmit.style.zIndex = '1000';
			
			// Use capture phase to ensure form submits before any preventDefault
			formItem.addEventListener('submit', function(e) {
				// Don't prevent - allow natural submission
				return true;
			}, true);
			
			// Ensure button click works - only validate, don't prevent
			btnSubmit.addEventListener('click', function(e) {
				if(formItem && !formItem.checkValidity()) {
					formItem.reportValidity();
				}
				// Don't prevent default - let form submit naturally
			}, true);
		}
	});
    // Load agent prices for current item
    (function(){
        const itemId = '<?= $id ?>';
        if(!itemId) return;
        function loadAgentPrices(){
            fetch('<?= $config->baseURL ?>item-agent/list-by-item/'+itemId, {headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
            .then(async r=>{
                const ct = r.headers.get('content-type')||'';
                if(!ct.includes('application/json')) { return { status:'error', data: [] }; }
                return r.json();
            }).then(res=>{
                const tbody = document.querySelector('#agent-price-table tbody');
                if(!tbody) return;
                tbody.innerHTML = '';
                if(res.status==='success'){
                    res.data.forEach((row,idx)=>{
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${idx+1}</td>
                            <td>${(row.agent_code||'')+' '+(row.agent_name||'-')}</td>
                            <td>Rp ${new Intl.NumberFormat('id-ID').format(row.price||0)}</td>
                            <td>${row.is_active=='1'?'Aktif':'Nonaktif'}</td>
                            <td></td>`;
                        tbody.appendChild(tr);
                    });
                }
            }).catch(()=>{});
        }
        document.addEventListener('DOMContentLoaded', function(){
            loadAgentPrices();
            const btnAddAgentPrice = document.getElementById('btn-add-agent-price');
            if(btnAddAgentPrice){
                btnAddAgentPrice.addEventListener('click', function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    const form = document.getElementById('agent-price-form');
                    if(!form) return;
                    const userIdSelect = form.querySelector('select[name="user_id"]');
                    const priceInput = form.querySelector('input[name="price"]');
                    
                    // Manual validation since required attribute removed to avoid form validation errors
                    if(!userIdSelect || !userIdSelect.value){
                        alert('Pilih Agen harus diisi');
                        userIdSelect?.focus();
                        return;
                    }
                    if(!priceInput || !priceInput.value || parseFloat(priceInput.value.replace(/[^\d]/g, '')) <= 0){
                        alert('Harga Khusus Agen harus diisi');
                        priceInput?.focus();
                        return;
                    }
                    
                    const fd = new FormData();
                    fd.append('item_id', form.querySelector('input[name="item_id"]').value);
                    fd.append('user_id', userIdSelect.value);
                    fd.append('price', priceInput.value);
                    fetch('<?= $config->baseURL ?>item-agent/store', {method:'POST', body: fd, headers: {'X-Requested-With':'XMLHttpRequest'}})
                        .then(()=>{ 
                            userIdSelect.value = '';
                            priceInput.value = '';
                            loadAgentPrices(); 
                        });
                });
            }
        });
    })();
</script>