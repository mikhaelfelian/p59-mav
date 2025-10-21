<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	
	<div class="card-body">
		<a href="<?=$config->baseURL?>produk/add" class="btn btn-success btn-xs"><i class="fas fa-plus pe-1"></i> Tambah Data</a>
		<hr/>
		<?php
		if (!empty($message)) {
			if (empty($result)) {
				show_message($message);
			} else {
				show_alert($message);
			}
		} 
		
		if ($result) {?>
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama Produk</th>
							<th>Deskripsi Produk</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody>
					<?php
					helper('html');
					$no = 1;
					foreach ($result as $val) {
						echo 
						'<tr>
							<td>' . $no . '</td>
							<td>' . $val['nama_produk'] . '</td>
									<td>' . $val['deskripsi_produk'] . '</td>
							<td>' . btn_action([
									 'edit' => ['url' => $config->baseURL . 'produk/edit?id='. $val['id_produk']]
									, 'delete' => ['url' => ''
												, 'id' =>  $val['id_produk']
												, 'delete-title' => 'Hapus data produk: <strong>'.$val['nama_produk'].'</strong> ?'
											]
								]) .
							'</td>
						</tr>';
						$no++;
					}
					?>
					</tbody>
				</table>
			</div>
		<?php
		}
		?>
	</div>
</div>
