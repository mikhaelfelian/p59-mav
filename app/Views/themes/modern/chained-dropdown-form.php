<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
			helper ('html');
			echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
				'url' => $config->baseURL . $current_module['nama_module'] . '/add',
				'icon' => 'fa fa-plus',
				'label' => 'Tambah Data'
			]);
			
			echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
				'url' => $config->baseURL . $current_module['nama_module'],
				'icon' => 'fa fa-arrow-circle-left',
				'label' => $current_module['judul_module']
			]);
		?>
		<hr/>
		<?php
		if (@$tgl_lahir) {
			$exp = explode('-', $tgl_lahir);
			$tgl_lahir = $exp[2] . '-' . $exp[1] . '-' . $exp[0];
		}
		if (!empty($msg)) {
			show_message($msg['content'], $msg['status']);
		}
		?>
		<form method="post" action="" class="form-horizontal" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Siswa</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="nama" value="<?=set_value('nama', @$nama)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tempat Lahir</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="tempat_lahir" value="<?=set_value('tempat_lahir', @$tempat_lahir)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tgl. Lahir</label>
					<div class="col-sm-5">
						<input class="form-control date-picker" type="text" name="tgl_lahir" value="<?=set_value('tgl_lahir', @$tgl_lahir)?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">NPM</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="npm" value="<?=set_value('npm', @$npm)?>"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Prodi</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="prodi" value="<?=set_value('prodi', @$prodi)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Fakultas</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="fakultas" value="<?=set_value('fakultas', @$fakultas)?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Alamat</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="alamat" value="<?=set_value('alamat', @$alamat)?>" required="required"/>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Propinsi</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_wilayah_propinsi', 'class' => 'propinsi select2'], $propinsi, set_value('id_wilayah_propinsi', $id_wilayah_propinsi) )?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kabupaten</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_wilayah_kabupaten', 'class' => 'kabupaten select2'], $kabupaten, set_value('id_wilayah_kabupaten', $id_wilayah_kabupaten))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kecamatan</label>
					<div class="col-sm-5">
						<?=options(['name' => 'id_wilayah_kecamatan', 'class' => 'kecamatan select2'], $kecamatan, set_value('id_wilayah_kecamatan',$id_wilayah_kecamatan))?>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kelurahan</label>
					<div class="col-sm-5" style="position:relative">
						<?=options(['name' => 'id_wilayah_kelurahan', 'class' => 'kelurahan select2'], $kelurahan, set_value('id_wilayah_kelurahan', $id_wilayah_kelurahan))?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Foto (Image Upload)</label>
					<div class="col-sm-5">
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
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
						<input type="hidden" name="id" value="<?=@$_GET['id']?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>