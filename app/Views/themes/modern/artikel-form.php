<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
		<?php
		helper('html');
			if (!empty($message)) {
					show_message($message);
		} ?>

		<form method="post" action="" class="form-container" enctype="multipart/form-data">
			<div class="row mb-3">
				<div class="col-md-9">
					<div class="mb-3">
						<label class="control-label mb-2">Judul</label>
						<input class="form-control" type="text" name="judul_artikel" value="<?=set_value('judul_artikel', @$judul_artikel)?>" placeholder="Judul Artikel" required/>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Slug</label>
						<input class="form-control" type="text" name="slug" value="<?=set_value('slug', @$slug)?>" placeholder="Slug" required/>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Konten</label>
						<textarea class="form-control tinymce" rows="30" type="text" name="konten"><?=set_value('konten', @$konten)?></textarea>
					</div>
				</div>
				<div class="col-md-3">
					<div class="mb-3">
						<label class="control-label mb-2">Meta Description</label>
						<textarea class="form-control overlayscollbar" rows="5" type="text" name="meta_description"><?=set_value('meta_description', @$meta_description)?></textarea>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Excerp</label>
						<textarea class="form-control overlayscollbar" rows="5" type="text" name="excerp"><?=set_value('excerp', @$excerp)?></textarea>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Search Engine Index</label>
						<div>
							<?php
							echo options(['class' => 'form-control'
											, 'name' => 'search_engine_index'
											, 'required' => 'required'
											]
										, ['Y' => 'Ya', 'N' => 'Tidak']
										, set_value('search_engine_index', @$search_engine_index)
									);
							?>
						</div>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Kategori</label>
						<div>
							<?php
							if (!$ref_kategori) {
								echo '<div class="alert alert-danger">Data kategori masih kosong</div>';
							} else {
								echo options(['class' => 'form-control select2'
												, 'name' => 'id_kategori[]'
												, 'multiple' => 'multiple'
												, 'required' => 'required'
												]
											, $ref_kategori
											, set_value('id_kategori', @$id_kategori)
										);
							} ?>
						</div>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Author</label>
						<div>
							<?php
				
							if (!$ref_author) {
								echo '<div class="alert alert-danger">Data author masih kosong, silakan diisi terlebih dahulu</div>';
							} else {
								echo options(['class' => 'form-control select2'
												, 'name' => 'id_author[]'
												, 'multiple' => 'multiple'
												, 'required' => 'required'
												]
											, $ref_author
											, set_value('id_author', @$id_author)
										);
							} ?>
						</div>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Status</label>
						<div>
							<?php
				
							if (!$ref_author) {
								echo '<div class="alert alert-danger">Data author masih kosong, silakan diisi terlebih dahulu</div>';
							} else {
								echo options(['class' => 'form-control'
												, 'name' => 'status'
												, 'required' => 'required'
												]
											, ['draft' => 'Draft', 'published' => 'Published']
											, set_value('status', @$status)
										);
							} ?>
						</div>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Tgl. Terbit</label>
						<?php
						if (!empty($tgl_terbit)) {
							if ($tgl_terbit == '0000-00-00 00:00:00') {
								$tgl_terbit = date('Y-m-d H:i');
							} else {
								$tgl_terbit = date('Y-m-d H:i', strtotime($tgl_terbit));
							}
						}
						?>
						<input class="form-control flatpickr" type="text" name="tgl_terbit" value="<?=set_value('tgl_terbit', @$tgl_terbit)?>" placeholder="Tgl. Terbit" required/>
					</div>
					<div class="mb-3">
						<label class="control-label mb-2">Feature Image</label>
						<div class="feature-image">
							
							<input type="hidden" name="feature_image" value="<?=set_value('feature_image', @$feature_image['id_file_picker'])?>" required/>
							<?php
							$display_btn = ' style="display:none"';
							$display_text = '';
							if (!empty($feature_image)) {
								$display_btn = '';
								$display_text = ' style="display:none"';
								$filepicker = new \Config\Filepicker();
								echo '<img class="jwd-img-thumbnail" src="' . $filepicker->uploadURL . $feature_image['nama_file'] . '"/>';
							}
							?>
							
							<a href="javascript:void(0)" class="btn btn-danger btn-square-xs btn-remove btn-top-right" <?=$display_btn?>><i class="fas fa-times"></i></a>
							<span class="text" <?=$display_text?>>Choose Image</span>
						</div>
					</div>
				</div>
			</div>
			<div class="row mb-3">
				<div class="col-sm-12">	
					<button type="submit" name="submit" id="btn-submit" value="produk" class="btn btn-primary">Simpan</button>
					<input type="hidden" name="id" value="<?=$id_artikel?>"/>
					<input type="hidden" name="tab" value="produk"/>
				</div>
			</div>
		</form>
	</div>
</div>