<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	<div class="card-body">
		<?php 
		helper('html');
		echo btn_link([
			'url' => $config->baseURL . 'gallery/addKategori',
			'attr' => ['class' => 'btn btn-success btn-xs'],
			'label' => '<i class="fas fa-plus"></i> Tambah Data'
		]);
		
		echo '<hr/>';
		if (empty($gallery_active) && empty($gallery_inactive)) {
			show_message(['status' => 'error', 'message' => 'Data tidak ditemukan']);
		} else {
			?>
				
			<div class="row panel">
				<?php
				$status = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif'];
				foreach ($status as $status_name => $status_val) {
				?>
				<div class="col-md-12 col-lg-6">
					<div class="container-panel <?=$status_name?>-panel">
						<h5 class="panel-title"><?=$status_val?></h5> 
						<hr/>
						<div id="<?=$status_name?>-panel">
						<?php
						foreach (${'gallery_' . $status_name} as $val) 
						{
							$id_label = 'heading-' . $val['id_gallery_kategori'];
							?>
							<div class="card kategori-container shadow-sm mb-4" id="<?=$id_label?>">
								<ul class="toolbox">
									<li>
										<div class="grip-handler"><i class="fas fa-grip-horizontal"></i></div>
									</li>
									<li>
										<a class="bg-secondary btn-edit text-white small" href="<?=$config->baseURL . 'gallery/editGallery?id_kategori=' . $val['id_gallery_kategori']?>"><i class="fas fa-images"></i></a>
									</li>
									<li>
										<a class="bg-success btn-edit text-white small" href="<?=$config->baseURL . 'gallery/editKategori?id=' . $val['id_gallery_kategori']?>"><i class="fas fa-pencil-alt"></i></a>
									</li>
									<li>
										<button type="submit" data-delete-title="Hapus Kategori Gallery: <strong><?=$val['judul_kategori']?></strong> ?<br/>Data gallery di dalam kategori ini akan ikut terhapus" class="bg-danger btn-delete-kategori text-white small">
											<i class="far fa-trash-alt"></i>
										</button>
									</li>
								</ul>
								<div class="body">
									<div class="row col-sm-12 item-container">
										<div class="img-container col-sm-4" id="kategori-<?=$val['id_gallery_kategori']?>">
											<?php
											// Image Kategori
											$image_url = $config->baseURL . 'public/images/folder.png';
											$img_class = ' img-empty';
											if (key_exists($val['id_gallery_kategori'], $gallery)) 
											{
												$filename = $gallery[$val['id_gallery_kategori']][0]['nama_file'];
												if ($filename) {
													$img_class = '';
													$filepicker = new \Config\Filepicker();
													$image_url = $filepicker->uploadURL . $filename;
												}
											}
											?>
											<div class="img-cover">
												<a class="bg-secondary btn-edit text-white small" href="<?=$config->baseURL . 'gallery/edit-gallery?id_kategori=' . $val['id_gallery_kategori']?>">
													<img class="card-img-top<?=$img_class?>" src="<?=$image_url?>"/>
													<span class="jms-img"><i class="fas fa-images"></i>&nbsp;&nbsp;<?=$val['jml_gambar']?></span>
												</a>
											</div>
										</div>
										<div class="col-sm-8">
											<h5 class="card-title"><?=$val['judul_kategori']?></h5>
											<div class="card-text">
												<p><?=$val['deskripsi']?></p>
											</div>
										</div>
									</div>
								</div>
								<input type="hidden" name="urut[]" value="<?=$val['id_gallery_kategori']?>"/>
							</div>
						<?php
						}?>
						</div>
					</div>
				</div>
				<?php
				}
				?>
			</div>
		<?php
		}
		?>
	</div>
</div>