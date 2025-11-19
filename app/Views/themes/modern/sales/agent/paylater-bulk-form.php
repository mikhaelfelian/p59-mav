<?php
/**
 * Bulk Paylater Form (Bootbox content)
 * Expects variables:
 * - $mode ('single'|'multiple')
 * - $selected (array of selected sales)
 * - $totalOutstanding
 * - $platformsManualTransfer
 * - $platformsPaymentGateway
 * - $csrfName, $csrfHash
 */
?>
<form id="bulkPaymentForm">
	<input type="hidden" name="<?= esc($csrfName) ?>" value="<?= esc($csrfHash) ?>">
	<input type="hidden" name="mode" value="<?= esc($mode) ?>">
	<?php foreach ($selected as $sale): ?>
		<input type="hidden" name="sale_ids[]" value="<?= esc($sale['id']) ?>">
	<?php endforeach; ?>

	<div class="alert alert-info mb-3">
		<?php if ($mode === 'multiple'): ?>
			<i class="fas fa-layer-group me-2"></i>Mode Multi Invoice: seluruh sisa hutang akan dibayar lunas dalam satu transaksi.
		<?php else: ?>
			<i class="fas fa-file-invoice-dollar me-2"></i>Mode Satu Invoice: dapat melakukan pembayaran penuh atau sebagian.
		<?php endif; ?>
	</div>

	<div class="table-responsive mb-3">
		<table class="table table-sm table-striped align-middle">
			<thead>
				<tr>
					<th>No</th>
					<th>No Nota</th>
					<th>Pelanggan</th>
					<th>Total</th>
					<th>Sisa Hutang</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($selected as $index => $sale): ?>
					<tr>
						<td><?= $index + 1 ?></td>
						<td><?= esc($sale['invoice_no']) ?></td>
						<td><?= esc($sale['customer_name']) ?></td>
						<td><?= format_angka($sale['grand_total'], 2) ?></td>
						<td><?= format_angka($sale['outstanding'], 2) ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php if ($mode === 'single'): ?>
		<div class="mb-3">
			<label class="form-label fw-semibold">Nominal Pembayaran</label>
			<div class="input-group">
				<span class="input-group-text">Rp</span>
				<input type="number"
					class="form-control"
					name="amount"
					id="bulkPaymentAmountInput"
					min="0"
					max="<?= esc($selected[0]['outstanding']) ?>"
					step="0.01"
					value="<?= esc($selected[0]['outstanding']) ?>"
					required>
			</div>
			<small class="text-muted">* Untuk pembayaran sebagian, gunakan platform transfer manual.</small>
		</div>
	<?php else: ?>
		<input type="hidden" name="amount" value="<?= esc($totalOutstanding) ?>">
		<div class="d-flex justify-content-between mb-3">
			<span class="fw-semibold">Total Pembayaran</span>
			<span class="fw-bold text-primary"><?= format_angka($totalOutstanding, 2) ?></span>
		</div>
	<?php endif; ?>

	<div class="mb-3">
		<label class="form-label fw-semibold">Platform Pembayaran</label>
		<select class="form-select" name="platform_id" id="bulkPlatformIdInput" required>
			<option value="">-- Pilih Platform --</option>
			<?php if (!empty($platformsPaymentGateway)): ?>
				<optgroup label="Payment Gateway">
					<?php foreach ($platformsPaymentGateway as $platform): ?>
						<option value="<?= esc($platform['id']) ?>" data-gw-status="1"><?= esc($platform['platform']) ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
			<?php if (!empty($platformsManualTransfer)): ?>
				<optgroup label="Transfer Manual / Bank">
					<?php foreach ($platformsManualTransfer as $platform): ?>
						<option value="<?= esc($platform['id']) ?>" data-gw-status="0"><?= esc($platform['platform']) ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
		</select>
	</div>

	<div class="mb-0">
		<label class="form-label">Catatan (opsional)</label>
		<textarea class="form-control" name="note" rows="2" placeholder="Misal: Transfer manual via ATM"></textarea>
	</div>
</form>

