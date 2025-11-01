<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	<?php
	helper('html');
	?>
	<div class="card-body">
		<a href="<?=current_url()?>/add" class="btn btn-success btn-xs"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
		<hr/>
		<form method="get" action="" class="form-horizontal p-3 form-data">
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Nama Pelanggan</label>
				<div class="col-sm-5">
					<?php
					if ($customer) {
						echo options(['name' => 'id_customer', 'class' => 'select2', 'id' => 'id-customer'], $customer, @$_GET['id_customer']);
					} else {
						echo 'Data tidak ditemukan';
					}						
					?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-2 col-form-label">Nama Petugas</label>
				<div class="col-sm-5">
					<?php
					if ($petugas) {
						echo options(['name' => 'id_user_petugas', 'class' => 'select2', 'id' => 'id-user-petugas'], $petugas, @$_GET['id_user_petugas']);
					} else {
						echo 'Data tidak ditemukan';
					}						
					?>
				</div>
			</div>
			<?php 
			if (!empty($msg)) {
				show_alert($msg);
			}
				
			$column =[
						'ignore_urut' => 'No'
						, 'nama_customer' => 'Nama Customer'
						, 'no_invoice' => 'No. Invoice'
						, 'tgl_penjualan' => 'Tgl. Transkasi'
						, 'sub_total' => 'Total'
						, 'diskon' => 'Diskon'
						, 'neto' => 'Neto'
						, 'status' => 'Status'
						, 'ignore_action' => 'Action'
						, 'ignore_invoice' => 'Invoice'
					];
			
			$settings['order'] = [3,'desc'];
			$index = 0;
			$th = '';
			foreach ($column as $key => $val) {
				$th .= '<th>' . $val . '</th>'; 
				if (strpos($key, 'ignore') !== false) {
					$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
				}
				$index++;
			}
			
			?>
			<div class="d-flex mb-3" style="justify-content:flex-end">
				<div class="btn-group">
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-pdf" disabled="disabled"><i class="fas fa-file-pdf me-2"></i>PDF</button>
					<button class="btn btn-outline-secondary me-0 btn-export btn-xs" type="button" id="btn-excel" disabled="disabled"><i class="fas fa-file-excel me-2"></i>XLSX</button>
				</div>
			</div>
			<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
			<thead>
				<tr>
					<?=$th?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?=$th?>
				</tr>
			</tfoot>
			</table>
			<?php
				foreach ($column as $key => $val) {
					$column_dt[] = ['data' => $key];
				}
				/* echo '<pre>';
				print_r($_SERVER);
				die; */
			?>
			<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
			<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
			<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDTPenjualan' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '')?></span>
		</form>
	</div>
</div>