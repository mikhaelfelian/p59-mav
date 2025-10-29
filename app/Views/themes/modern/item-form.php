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
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
<?php endif; ?>
		<?php
			if (!empty($message)) {
				show_message($message);
			} ?>

		<form method="post" action="<?=$config->baseURL?>item/store" class="form-container" enctype="multipart/form-data" id="form-item">
			<!-- Tabs navigation -->
			<ul class="nav nav-tabs" id="itemTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<a class="nav-link active" data-bs-toggle="tab" href="#tab-info" role="tab">Info Produk</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" data-bs-toggle="tab" href="#tab-spec" role="tab">Spesifikasi</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" data-bs-toggle="tab" href="#tab-rule" role="tab">Product Rules</a>
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
								<input class="form-control" type="text" name="sku" value="<?=set_value('sku', @$item->sku)?>" placeholder="SKU akan otomatis dibuat" readonly/>
							</div>
							
							<div class="mb-3">
								<label class="control-label mb-2">Nama Item <span class="text-danger">*</span></label>
								<input class="form-control" type="text" name="name" value="<?=set_value('name', @$item->name)?>" placeholder="Masukkan nama item" required/>
							</div>
							
							<div class="mb-3">
								<label class="control-label mb-2">Slug</label>
								<input class="form-control" type="text" name="slug" value="<?=set_value('slug', @$item->slug)?>" placeholder="Masukkan slug item"/>
							</div>
							
							<div class="mb-3">
								<label class="control-label mb-2">Deskripsi</label>
								<textarea class="form-control tinymce" rows="30" type="text" name="description"><?=set_value('description', @$item->description)?></textarea>
							</div>
							
							<div class="mb-3">
								<label class="control-label mb-2">Deskripsi Singkat</label>
								<textarea class="form-control" rows="2" type="text" name="short_description" placeholder="Masukkan deskripsi singkat item"><?=set_value('short_description', @$item->short_description)?></textarea>
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
								<input class="form-control price-format" type="text" name="price" value="<?=set_value('price', @$item->price)?>" placeholder="0" data-type="currency"/>
							</div>
							
							<div class="mb-3">
								<label class="control-label mb-2">Stockable</label>
								<div class="form-switch">
									<input type="checkbox" name="is_stockable" value="1" class="form-check-input" <?=set_value('is_stockable', @$item->is_stockable) == '1' ? 'checked' : ''?>/>
									<label class="form-check-label">Item dapat di-stock</label>
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
								<input class="form-control" type="file" name="image" accept="image/*"/>
								<?php if (!empty($image) && is_array($image) && !empty($image['nama_file'])): ?>
									<div class="mt-2">
										<img src="<?=base_url('public/uploads/' . $image['nama_file'])?>" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"/>
									</div>
								<?php elseif (!empty($item->image)): ?>
									<div class="mt-2">
										<img src="<?=base_url('public/uploads/' . $item->image)?>" class="img-thumbnail" style="max-width: 200px; max-height: 200px;"/>
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
												<input class="form-control" type="text" name="spec_value[]" value="<?=$existingSpec->value ?? ''?>" placeholder="Masukkan nilai spesifikasi"/>
											</div>
											<div class="col-md-2">
												<a href="javascript:void(0)" <?= $btn_add ?> class="btn <?= $btn_color ?> btn-sm <?= $btn_remove ?>">
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
											<input class="form-control" type="text" name="spec_value[]" value="" placeholder="Masukkan nilai spesifikasi"/>
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

				<!-- Product Rules Tab -->
				<div class="tab-pane fade" id="tab-rule" role="tabpanel">
					<?= view('themes/modern/product-rule-form', ['rules' => $rules ?? [], 'items' => $items ?? []]); ?>
				</div>
			</div>
			
			<div class="row mb-3">
				<div class="col-sm-12">	
					<button type="submit" name="submit" id="btn-submit" value="item" class="btn btn-primary">Simpan</button>
					<input type="hidden" name="id" value="<?=$id?>"/>
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
</script>
