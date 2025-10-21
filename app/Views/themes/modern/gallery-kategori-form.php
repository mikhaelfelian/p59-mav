<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
	
		<?php 
		helper ('html');
		if (!empty($message)) {
			show_message($message);
		}
?>
		<form method="post" action="" class="form-container" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-3 col-xl-2 col-form-label">Judul Kategori</label>
				<div class="col-sm-8 col-xl-6">
					<input class="form-control" type="text" name="judul_kategori" value="<?=set_value('judul_kategori', @$kategori['judul_kategori'])?>" placeholder="Judul Kategori" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-xl-2 col-form-label">Deskripsi</label>
				<div class="col-sm-8 col-xl-6">
					<textarea class="form-control tinymce" rows="5" type="text" name="deskripsi"><?=set_value('deskripsi', @$kategori['deskripsi'])?></textarea>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-xl-2 col-form-label">Layout</label>
				<div class="col-sm-8 col-xl-6">
					<?php
					echo options(['name' => 'layout']
									, ['grid' => 'Grid', 'masonry' => 'Masonry']
									, set_value('layout', @$kategori['layout'])
								);
					?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-xl-2 col-form-label">Aktif</label>
				<div class="col-sm-8 col-xl-6">
					<?php
					echo options(['name' => 'aktif']
									, ['Y' => 'Ya', 'N' => 'Tidak']
									, set_value('aktif', @$kategori['aktif'])
								);
					?>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-8 col-md-8 col-lg-8 col-xl-6">
					<button type="submit" name="submit" id="btn-submit" value="produk" class="btn btn-primary">Save</button>
					<input type="hidden" name="id" value="<?=$id_gallery_kategori?>"/>
					<input type="hidden" name="tab" value="kategori"/>
				</div>
			</div>
		</form>
	</div>
</div>