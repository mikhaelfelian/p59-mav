<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
		helper ('html');
		if (!empty($msg)) {
			show_message($msg['content'], $msg['status']);
		}
		
		echo btn_link(['attr' => ['class' => 'btn btn-success btn-xs'],
			'url' => $config->baseURL . $current_module['nama_module'] . '/add',
			'icon' => 'fa fa-plus',
			'label' => 'Tambah Data'
		]);
		
		echo btn_link(['attr' => ['class' => 'btn btn-light btn-xs'],
			'url' => $config->baseURL . 'options-dinamis',
			'icon' => 'fa fa-arrow-circle-left',
			'label' => $current_module['judul_module']
		]);
		?>
		<hr/>
		<form method="post" action="" id="form-container" enctype="multipart/form-data">
			<div class="tab-content" id="myTabContent">
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nomor Akta</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="no_akta" value="<?=set_value('no_akta', @$no_akta)?>" placeholder="Nomor Akta" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Tanggal Akta</label>
					<div class="col-sm-5">
						<input class="form-control date-picker" type="text" name="tgl_akta" value="<?=set_value('tgl_akta', @format_tanggal($tgl_akta, 'dd-mm-yyyy'))?>" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Akta</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="nama_akta" value="<?=set_value('nama_akta', @$nama_akta)?>" placeholder="Nama Akta" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Customer</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="nama_customer" value="<?=set_value('nama_customer', @$nama_customer)?>" placeholder="Nama Customer" required="required"/>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Minuta</label>
					<div class="col-sm-5">
						<?php
						
						echo options(['name' => 'minuta']
										, ['Y' => 'Ya', 'N' => 'Tidak']
										, set_value('minuta', @$minuta)
									);
						?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Penghadap (Options Dinamis)</label>
					<div class="col-sm-5">
						<?php
			
						if (!$penghadap) {
							echo '<div class="alert alert-danger">Data penghadap masih kosong, silakan diisi terlebih dahulu</div>';
						} else {
							echo options(['class' => 'select2'
											, 'name' => 'id_penghadap[]'
											, 'multiple' => 'multiple'
											, 'required' => 'required'
											]
										, $penghadap
										, set_value('id_penghadap', @$id_penghadap)
									);
						} ?>
					</div>
				</div>
				
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Penaggung Jawab (Options Dinamis)</label>
					<div class="col-sm-5">
						<?php
						if (!$penanggungjawab) {
							echo '<div class="alert alert-danger">Data penanggung jawab masih kosong, silakan diisi terlebih dahulu</div>';
						} else {
							echo options(['class' => 'select2'
											, 'name' => 'id_penanggung_jawab[]'
											, 'multiple' => 'multiple'
											, 'required' => 'required'
											]
										, $penanggungjawab
										, set_value('id_penanggung_jawab', @$id_penanggung_jawab)
									);
					}?>
						
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<?php 
						$disabled = '';
						if (!$penghadap || !$penanggungjawab) {
							$disabled = 'disabled';
						}
						?>
						<button type="submit" name="submit" id="btn-submit" value="submit" <?=$disabled?> class="btn btn-primary">Submit</button>
						<input type="hidden" name="id" value="<?=@$id_akta?>"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>