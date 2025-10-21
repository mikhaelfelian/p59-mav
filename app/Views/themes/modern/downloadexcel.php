<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
		helper(['html', 'format']);
		if (!empty($message)) {
			show_message($message);
		}
		?>
		<form method="get" action="" class="form-horizontal" enctype="multipart/form-data">
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Pilih Tabel</label>
					<div class="col-sm-5">
						<?=options(['name' => 'nama_tabel', 'id' => 'nama-tabel'], $list_tabel, $used_tabel)?>
						
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Total Data</label>
					<div class="col-sm-5">
						<div class="mt-2" id="jms-data"><strong><?=format_ribuan($total_data)?></strong></div>
					</div>
				</div>
				<?php
					$display = $disabled = $display_error = '';
					if ($total_data <= $max_data) {
						$display = ' style="display:none"';
						$disabled = ' disabled="disabled"';
					}
					
					$display_error = ' style="display: none"';
					if ($selisih > $max_data) {
						$display_error = '';
					}
							
				?>
				<div <?=$display?> id="download-range-container">
					<div class="row mb-1">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Rentang Data</label>
						<div class="col-sm-3">
							<div class="input-group">
							 <input type="text" size="2" id="data-awal" name="data_awal" <?=$disabled?> value="<?=set_value('data_awal', 1)?>" class="form-control text-right" aria-label="Jumlah Data Awal">
								<div class="input-group-middle">
									<span class="input-group-text">s.d</span>
								</div>
							  <input type="text" size="2" id="data-akhir" name="data_akhir" <?=$disabled?> value="<?=set_value('data_akhir', format_ribuan($max_data))?>" class="form-control text-right" aria-label="Jumlah Data Akhir">
							  </div>
						</div>
					</div>
					<div class="row mb-1" id="error-container" <?=$display_error?>>
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label"></label>
						<div class="col-sm-5">
							 <small class="alert alert-danger d-block mb-0"></small>
						</div>
					</div>
					<div class="row mb-3">
						<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label"></label>
						<div class="col-sm-5">
							<div class="input-group">
							 <div class="text-muted">Sekali download maksimal <?=format_ribuan($max_data)?> data, silakan isikan no urut data awal dan akhir yang ingin di download
							</div>
							  </div>
						</div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Kolom</label>
					<div class="col-sm-5">
						<div class="d-flex flex-wrap field-option-container" style="font-size: 90%">
						<?php
						foreach ($fields as $field_name) {
							$checked = '';
							if (!empty($_GET)) {
								if (!empty($_GET['fields']) && in_array($field_name, $_GET['fields'])) {
									$checked = 'checked';
								}
							} else {
								$checked = substr($field_name, 0, 3) == 'id_' ? '' : 'checked';
							}
							
							echo '<input type="checkbox" name="fields[]" value="' . $field_name . '" class="btn-check" id="btn-check-' . $field_name . '" autocomplete="off" ' . $checked .'>
							<label class="btn btn-outline-secondary me-2 mb-2 px-2 py-1" for="btn-check-' . $field_name . '">' . $field_name . '</label><br>';
						}
						
						?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>