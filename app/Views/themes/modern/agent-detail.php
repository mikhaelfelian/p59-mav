<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: View for displaying agent detail information in read-only format
 * Professional design inspired by modern POS applications in Indonesia
 */
?>
<?php if (!empty($message)): ?>
	<?= show_message($message) ?>
<?php endif; ?>

<!-- Agent Profile Header Card -->
<div class="card shadow-sm mb-4">
	<div class="card-body">
		<div class="row align-items-center">
			<div class="col-md-8">
				<div class="d-flex align-items-center mb-3">
					<div class="flex-shrink-0 me-3">
						<div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 28px; font-weight: bold;">
							<?= strtoupper(substr($agent->name, 0, 2)) ?>
						</div>
					</div>
					<div class="flex-grow-1">
						<h4 class="mb-1 fw-bold"><?= esc($agent->name) ?></h4>
						<p class="text-muted mb-2">
							<i class="fas fa-hashtag me-1"></i> <?= esc($agent->code) ?>
						</p>
						<?php if ($agent->is_active == '1'): ?>
							<span class="badge bg-success fs-6 px-3 py-2">
								<i class="fas fa-check-circle me-1"></i> Aktif
							</span>
						<?php else: ?>
							<span class="badge bg-danger fs-6 px-3 py-2">
								<i class="fas fa-times-circle me-1"></i> Tidak Aktif
							</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-md-4 text-md-end">
				<div class="d-flex flex-column gap-2">
					<div>
						<small class="text-muted d-block">Limit Kredit</small>
						<h5 class="mb-0 text-primary fw-bold">Rp <?= format_angka($agent->credit_limit) ?></h5>
					</div>
					<div>
						<small class="text-muted d-block">Syarat Pembayaran</small>
						<h6 class="mb-0"><?= $agent->payment_terms ?> hari</h6>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Information Cards -->
<div class="row g-4">
	<!-- Contact Information -->
	<div class="col-lg-6">
		<div class="card shadow-sm h-100">
			<div class="card-header bg-light border-bottom">
				<h5 class="card-title mb-0">
					<i class="fas fa-address-card text-primary me-2"></i>
					Informasi Kontak
				</h5>
			</div>
			<div class="card-body">
				<div class="list-group list-group-flush">
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-envelope text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Email</small>
								<span class="fw-medium"><?= $agent->email ? esc($agent->email) : '<span class="text-muted fst-italic">-</span>' ?></span>
							</div>
						</div>
					</div>
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-phone text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Telepon</small>
								<span class="fw-medium"><?= $agent->phone ? esc($agent->phone) : '<span class="text-muted fst-italic">-</span>' ?></span>
							</div>
						</div>
					</div>
					<?php if ($agent->tax_number): ?>
					<div class="list-group-item px-0 py-3 border-0">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-file-invoice text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Nomor Pajak</small>
								<span class="fw-medium"><?= esc($agent->tax_number) ?></span>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Address Information -->
	<div class="col-lg-6">
		<div class="card shadow-sm h-100">
			<div class="card-header bg-light border-bottom">
				<h5 class="card-title mb-0">
					<i class="fas fa-map-marker-alt text-danger me-2"></i>
					Informasi Alamat
				</h5>
			</div>
			<div class="card-body">
				<div class="list-group list-group-flush">
					<?php if ($agent->address): ?>
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-road text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Alamat Lengkap</small>
								<span class="fw-medium"><?= esc($agent->address) ?></span>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-flag text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Negara</small>
								<span class="fw-medium"><?= esc($agent->country) ?></span>
							</div>
						</div>
					</div>
					<?php if (!empty($provinceName)): ?>
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-building text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Provinsi</small>
								<span class="fw-medium"><?= esc($provinceName) ?></span>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<?php if (!empty($regencyName)): ?>
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-city text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Kota/Kabupaten</small>
								<span class="fw-medium"><?= esc($regencyName) ?></span>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<?php if (!empty($districtName)): ?>
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-map text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Kecamatan</small>
								<span class="fw-medium"><?= esc($districtName) ?></span>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<?php if (!empty($villageName)): ?>
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-home text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Kelurahan</small>
								<span class="fw-medium"><?= esc($villageName) ?></span>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<?php if ($agent->postal_code): ?>
					<div class="list-group-item px-0 py-3 border-0">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-mail-bulk text-muted"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Kode Pos</small>
								<span class="fw-medium"><?= esc($agent->postal_code) ?></span>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Additional Information Row -->
<div class="row g-4 mt-0">
	<!-- Business Information -->
	<div class="col-lg-6">
		<div class="card shadow-sm">
			<div class="card-header bg-light border-bottom">
				<h5 class="card-title mb-0">
					<i class="fas fa-briefcase text-info me-2"></i>
					Informasi Bisnis
				</h5>
			</div>
			<div class="card-body">
				<div class="row g-3">
					<div class="col-12">
						<div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
							<div>
								<small class="text-muted d-block mb-1">Limit Kredit</small>
								<h5 class="mb-0 text-primary fw-bold">Rp <?= format_angka($agent->credit_limit) ?></h5>
							</div>
							<i class="fas fa-wallet fa-2x text-primary opacity-50"></i>
						</div>
					</div>
					<div class="col-12">
						<div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
							<div>
								<small class="text-muted d-block mb-1">Syarat Pembayaran</small>
								<h5 class="mb-0 fw-bold"><?= $agent->payment_terms ?> hari</h5>
							</div>
							<i class="fas fa-calendar-alt fa-2x text-info opacity-50"></i>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- System Information -->
	<div class="col-lg-6">
		<div class="card shadow-sm">
			<div class="card-header bg-light border-bottom">
				<h5 class="card-title mb-0">
					<i class="fas fa-info-circle text-secondary me-2"></i>
					Informasi Sistem
				</h5>
			</div>
			<div class="card-body">
				<div class="list-group list-group-flush">
					<div class="list-group-item px-0 py-3 border-0 border-bottom">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-plus-circle text-success"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Dibuat</small>
								<span class="fw-medium"><?= $agent->created_at ? date('d/m/Y H:i', strtotime($agent->created_at)) : '-' ?></span>
							</div>
						</div>
					</div>
					<div class="list-group-item px-0 py-3 border-0">
						<div class="d-flex align-items-start">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-edit text-primary"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Diupdate</small>
								<span class="fw-medium"><?= $agent->updated_at ? date('d/m/Y H:i', strtotime($agent->updated_at)) : '-' ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Location Coordinates (if available) -->
<?php if (!empty($agent->latitude) && !empty($agent->longitude)): ?>
<div class="row mt-4">
	<div class="col-12">
		<div class="card shadow-sm">
			<div class="card-header bg-light border-bottom">
				<h5 class="card-title mb-0">
					<i class="fas fa-map-marked-alt text-warning me-2"></i>
					Koordinat Lokasi
				</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<div class="d-flex align-items-center p-3 bg-light rounded mb-3 mb-md-0">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-latitude fa-2x text-warning"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Latitude</small>
								<span class="fw-bold fs-5"><?= esc($agent->latitude) ?></span>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="d-flex align-items-center p-3 bg-light rounded">
							<div class="flex-shrink-0 me-3">
								<i class="fas fa-longitude fa-2x text-warning"></i>
							</div>
							<div class="flex-grow-1">
								<small class="text-muted d-block mb-1">Longitude</small>
								<span class="fw-bold fs-5"><?= esc($agent->longitude) ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<style>
.avatar-lg {
	box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.card {
	border: none;
	border-radius: 10px;
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.card-header {
	border-radius: 10px 10px 0 0 !important;
	font-weight: 600;
}

.list-group-item {
	transition: background-color 0.2s ease;
}

.list-group-item:hover {
	background-color: #f8f9fa;
}

.bg-light {
	background-color: #f8f9fa !important;
}

@media (max-width: 768px) {
	.avatar-lg {
		width: 60px !important;
		height: 60px !important;
		font-size: 24px !important;
	}
}
</style>