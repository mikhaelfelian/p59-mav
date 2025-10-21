<form method="post" action="" class="form-horizontal p-3" enctype="multipart/form-data">
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Siswa</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="nama" value="<?=@$nama?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Tempat Lahir</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="tempat_lahir" value="<?=@$tempat_lahir?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Tgl. Lahir</label>
			<div class="col-sm-9">
				<?php 
					$tanggal = '';
					if (!empty($tgl_lahir) && $tgl_lahir != '0000-00-00') {
						$tanggal = date('d-m-Y', strtotime($tgl_lahir));
					}
				?>
				<input class="form-control flatpickr" type="text" name="tgl_lahir" value="<?=@$tanggal?>"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">NPM</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="npm" value="<?=@$npm?>"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Prodi</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="prodi" value="<?=@$prodi?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Fakultas</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="fakultas" value="<?=@$fakultas?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Alamat</label>
			<div class="col-sm-9">
				<input class="form-control" type="text" name="alamat" value="<?=@$alamat?>" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Foto (Image Upload)</label>
			<div class="col-sm-9">
				<?php
		
				if (!empty($foto) ) 
				{
					$note = '';
					if (file_exists(ROOTPATH . 'public/images/foto/' . $foto)) {
						$image = $config->baseURL . 'public/images/foto/' . $foto;
					} else {
						$image = $config->baseURL . 'public/images/foto/noimage.png';
						$note = '<small><b>Note</strong>: File <strong>public/images/foto/' . $foto . '</strong> tidak ditemukan</small>';
					}
					echo '<div class="img-choose" style="margin:inherit;margin-bottom:10px">
							<div class="img-choose-container">
								<img src="'. $image . '?r=' . time() . '"/>
								<a href="javascript:void(0)" class="remove-img"><i class="fas fa-times"></i></a>
							</div>
						</div>
						' . $note .'
						';
				}
				?>
				<input type="hidden" class="foto-delete-img" name="foto_delete_img" value="0">
				<input type="hidden" class="foto-max-size" name="foto_max_size" value="300000"/>
				<input type="file" class="file form-control" name="foto">
					<?php if (!empty($form_errors['foto'])) echo '<small class="alert alert-danger">' . $form_errors['foto'] . '</small>'?>
					<small class="small" style="display:block">Maksimal 300Kb, Minimal 100px x 100px, Tipe file: .JPG, .JPEG, .PNG</small>
				<div class="upload-file-thumb"><span class="file-prop"></span></div>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
</form>