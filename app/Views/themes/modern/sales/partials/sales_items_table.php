<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Partial view for sales items table
 */
?>
<div class="table-responsive">
	<table id="sales-items-table" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>No</th>
				<th>Nama Item</th>
				<th>Qty</th>
				<th>Harga</th>
				<th>Diskon</th>
				<th>Total</th>
				<th>Aksi</th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($sale_items)): ?>
				<?php foreach ($sale_items as $index => $item): ?>
					<tr data-index="<?= $index ?>">
						<td><?= $index + 1 ?></td>
						<td><?= esc($item['item_name']) ?></td>
						<td><?= number_format($item['qty'], 2) ?></td>
						<td><?= number_format($item['price'], 2) ?></td>
						<td><?= number_format($item['discount_value'] ?? 0, 2) ?></td>
						<td><?= number_format($item['total_price'], 2) ?></td>
						<td>-</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="7" class="text-center text-muted">Belum ada item. Tambahkan item terlebih dahulu.</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>

