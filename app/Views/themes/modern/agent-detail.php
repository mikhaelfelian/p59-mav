<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: View for displaying agent detail information in read-only format
 * This file represents the View for agent-detail.
 */
?>
		<?php if (!empty($message)): ?>
			<?= show_message($message) ?>
		<?php endif; ?>
		
		<div class="row">
			<div class="col-md-6">
				<table class="table table-borderless">
					<tr>
						<td width="30%"><strong>Kode Agen:</strong></td>
						<td><?= $agent->code ?></td>
					</tr>
					<tr>
						<td><strong>Nama Agen:</strong></td>
						<td><?= $agent->name ?></td>
					</tr>
					<tr>
						<td><strong>Email:</strong></td>
						<td><?= $agent->email ?: '-' ?></td>
					</tr>
					<tr>
						<td><strong>Telepon:</strong></td>
						<td><?= $agent->phone ?: '-' ?></td>
					</tr>
					<tr>
						<td><strong>Alamat:</strong></td>
						<td><?= $agent->address ?: '-' ?></td>
					</tr>
					<tr>
						<td><strong>Negara:</strong></td>
						<td><?= $agent->country ?></td>
					</tr>
					<tr>
						<td><strong>Provinsi:</strong></td>
						<td><?= $provinceName ?? '-' ?></td>
					</tr>
					<tr>
						<td><strong>Kota/Kabupaten:</strong></td>
						<td><?= $regencyName ?? '-' ?></td>
					</tr>
					<tr>
						<td><strong>Kecamatan:</strong></td>
						<td><?= $districtName ?? '-' ?></td>
					</tr>
					<tr>
						<td><strong>Kelurahan:</strong></td>
						<td><?= $villageName ?? '-' ?></td>
					</tr>
				</table>
			</div>
			<div class="col-md-6">
				<table class="table table-borderless">
					<tr>
						<td width="30%"><strong>Kode Pos:</strong></td>
						<td><?= $agent->postal_code ?: '-' ?></td>
					</tr>
					<tr>
						<td><strong>Nomor Pajak:</strong></td>
						<td><?= $agent->tax_number ?: '-' ?></td>
					</tr>
					<tr>
						<td><strong>Limit Kredit:</strong></td>
						<td>Rp <?= number_format($agent->credit_limit, 0, ',', '.') ?></td>
					</tr>
					<tr>
						<td><strong>Syarat Pembayaran:</strong></td>
						<td><?= $agent->payment_terms ?> hari</td>
					</tr>
					<tr>
						<td><strong>Status:</strong></td>
						<td>
							<?php if ($agent->is_active == '1'): ?>
								<span class="badge bg-success">Aktif</span>
							<?php else: ?>
								<span class="badge bg-danger">Tidak Aktif</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><strong>Dibuat:</strong></td>
						<td><?= $agent->created_at ? date('d/m/Y H:i', strtotime($agent->created_at)) : '-' ?></td>
					</tr>
					<tr>
						<td><strong>Diupdate:</strong></td>
						<td><?= $agent->updated_at ? date('d/m/Y H:i', strtotime($agent->updated_at)) : '-' ?></td>
					</tr>
				</table>
			</div>
		</div>
		
		<?php if (!empty($agent->latitude) && !empty($agent->longitude)): ?>
		<div class="row mt-3">
			<div class="col-12">
				<h6><strong>Koordinat Lokasi:</strong></h6>
				<p class="text-muted">
					Latitude: <?= $agent->latitude ?><br>
					Longitude: <?= $agent->longitude ?>
				</p>
			</div>
		</div>
		<?php endif; ?>
