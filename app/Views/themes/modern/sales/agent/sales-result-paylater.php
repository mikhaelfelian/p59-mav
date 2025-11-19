<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: View for agent paylater sales list/result with DataTables
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Data Pembayaran Paylater</h5>
	</div>

	<div class="card-body">
		<?php
		if (!empty($msg)) {
			show_alert($msg);
		}
		?>

		<!-- Bayar Semua Button -->
		<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
			<div>
				<button type="button" class="btn btn-primary btn-bayar-semua" id="btnBayarSemua" disabled>
				<i class="fas fa-money-bill-wave me-2"></i>Bayar Semua
			</button>
			</div>
			<div class="text-muted" id="selectedCountInfo">0 invoice dipilih</div>
		</div>

		<?php
		// Define columns for DataTables
		$column = [
			'ignore_search_select'   => '<input type="checkbox" class="form-check-input" id="selectAllPaylater" title="Pilih semua di halaman ini">',
			'ignore_search_urut'    => 'No',
			'invoice_no'            => 'No Nota',
			'customer_name'         => 'Pelanggan',
			'grand_total'           => 'Total',
			'balance_due'           => 'Sisa Hutang',
			'created_at'            => 'Tanggal',
			'ignore_search_action'  => 'Aksi'
		];

		$settings['order'] = [6, 'desc']; // Order by created_at descending
		$index = 0;
		$th = '';
		
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>';
			if (strpos($key, 'ignore_search') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		?>

		<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
			<thead>
				<tr>
					<?= $th ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<?= $th ?>
				</tr>
			</tfoot>
		</table>
		
		<?php
		// Prepare column data for DataTables
		foreach ($column as $key => $val) {
			$column_dt[] = ['data' => $key];
		}
		?>
		
		<span id="dataTables-column" style="display:none"><?= json_encode($column_dt) ?></span>
		<span id="dataTables-setting" style="display:none"><?= json_encode($settings) ?></span>
		<span id="dataTables-url" style="display:none"><?= $config->baseURL ?>agent/sales-paylater/getDataDT</span>
	</div>
</div>

<script>
$(document).ready(function() {
	const baseURL = '<?= $config->baseURL ?>';
	const csrfName = '<?= csrf_token() ?>';
	let csrfHash = '<?= csrf_hash() ?>';

	let selectedInvoices = {};
	let bulkMode = 'single';
	let bootboxDialog = null;
	let isSubmitting = false;

	// Initialize DataTables
	var column = JSON.parse($('#dataTables-column').text());
	var settings = JSON.parse($('#dataTables-setting').text());
	var url = $('#dataTables-url').text();

	var table = $('#table-result').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": url,
			"type": "POST"
		},
		"columns": column,
		"order": settings.order,
		"columnDefs": settings.columnDefs,
		"pageLength": 10,
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
		"language": {
			"processing": "Memuat...",
			"emptyTable": "Tidak ada data",
			"zeroRecords": "Data tidak ditemukan"
		}
	});

	table.on('draw', function() {
		$('#table-result .select-paylater').each(function() {
			var saleId = $(this).data('sale-id').toString();
			if (selectedInvoices[saleId]) {
				$(this).prop('checked', true);
			}
		});
		updateSelectAllState();
		updateSelectedInfo();
	});

	$('#table-result').on('change', '.select-paylater', function() {
		var saleId = $(this).data('sale-id').toString();
		if ($(this).is(':checked')) {
			selectedInvoices[saleId] = {
				sale_id: saleId,
				invoice: $(this).data('invoice'),
				customer: $(this).data('customer'),
				agent: $(this).data('agent'),
				total: parseFloat($(this).data('total') || 0),
				outstanding: parseFloat($(this).data('outstanding') || 0)
			};
		} else {
			delete selectedInvoices[saleId];
		}
		updateSelectAllState();
		updateSelectedInfo();
	});

	$('#selectAllPaylater').on('change', function() {
		var checked = $(this).is(':checked');
		$('#table-result .select-paylater').each(function() {
			$(this).prop('checked', checked).trigger('change');
		});
	});

	$('#btnBayarSemua').on('click', function() {
		openBulkPaymentDialog();
	});

	function openBulkPaymentDialog() {
		var saleIds = Object.values(selectedInvoices).map(item => item.sale_id);
		if (!saleIds.length) {
			showSwalWarning('Silakan pilih minimal satu invoice terlebih dahulu.');
			return;
		}

		bootboxDialog = bootbox.dialog({
			title: '<i class="fas fa-cash-register me-2"></i>Pembayaran Paylater',
			message: '<div class="text-center text-secondary py-4"><div class="spinner-border"></div></div>',
			size: 'lg',
			buttons: {
				cancel: {
					label: 'Batal'
				},
				success: {
					label: '<i class="fas fa-check me-1"></i>Proses Pembayaran',
					className: 'btn-success',
					callback: function() {
						return submitBulkPayment(bootboxDialog);
					}
				}
			}
		});

		var payload = {
			sale_ids: saleIds
		};
		payload[csrfName] = csrfHash;

		$.ajax({
			url: baseURL + 'agent/sales-paylater/bulk-form',
			type: 'POST',
			data: payload,
			dataType: 'json',
			success: function(response) {
				if (response.csrf_hash) {
					csrfHash = response.csrf_hash;
				}

				if (response.status === 'success') {
					bootboxDialog.find('.modal-body').html(response.html);
					attachBulkFormEvents(bootboxDialog);
				} else {
					bootboxDialog.modal('hide');
					showSwalError(response.message || 'Gagal memuat form pembayaran.');
				}
			},
			error: function(xhr) {
				bootboxDialog.modal('hide');
				var errorMsg = 'Gagal memuat form pembayaran.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				showSwalError(errorMsg);
			}
		});
	}

	function attachBulkFormEvents(dialog) {
		const amountInput = dialog.find('#bulkPaymentAmountInput');
		const platformSelect = dialog.find('#bulkPlatformIdInput');

		if (amountInput.length && platformSelect.length) {
			platformSelect.on('change', function() {
				var gwStatus = $('option:selected', this).data('gw-status');
				if (gwStatus === 1 || gwStatus === '1') {
					amountInput.val(amountInput.attr('max')).prop('readonly', true);
				} else {
					amountInput.prop('readonly', false);
				}
			});
		}
	}

	function submitBulkPayment(dialog) {
		var form = dialog.find('#bulkPaymentForm');
		if (!form.length || isSubmitting) {
			return false;
		}

		var formData = form.serializeArray();
		formData.push({ name: csrfName, value: csrfHash });

		var submitBtn = dialog.find('.btn-success');
		var originalHtml = submitBtn.html();
		submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');
		isSubmitting = true;

		$.ajax({
			url: baseURL + 'agent/paylater/pay-bulk',
			type: 'POST',
			data: $.param(formData),
			dataType: 'json',
			success: function(response) {
				isSubmitting = false;
				submitBtn.prop('disabled', false).html(originalHtml);
				if (response.csrf_hash) {
					csrfHash = response.csrf_hash;
				}

				if (response.status === 'success') {
					dialog.modal('hide');
					handleBulkSuccess(response);
				} else {
					showSwalError(response.message || 'Gagal memproses pembayaran.');
				}
			},
			error: function(xhr) {
				isSubmitting = false;
				submitBtn.prop('disabled', false).html(originalHtml);
				var errorMsg = 'Terjadi kesalahan saat memproses pembayaran.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				showSwalError(errorMsg);
			}
		});

		return false;
	}

	function updateSelectAllState() {
		var allCheckboxes = $('#table-result .select-paylater');
		if (!allCheckboxes.length) {
			$('#selectAllPaylater').prop('checked', false).prop('indeterminate', false);
			return;
		}

		var checkedBoxes = $('#table-result .select-paylater:checked');
		if (checkedBoxes.length === 0) {
			$('#selectAllPaylater').prop('checked', false).prop('indeterminate', false);
		} else if (checkedBoxes.length === allCheckboxes.length) {
			$('#selectAllPaylater').prop('checked', true).prop('indeterminate', false);
		} else {
			$('#selectAllPaylater').prop('indeterminate', true);
		}
	}

	function updateSelectedInfo() {
		var count = Object.keys(selectedInvoices).length;
		$('#selectedCountInfo').text(count + ' invoice dipilih');
		$('#btnBayarSemua').prop('disabled', count === 0);
	}

	function handleBulkSuccess(response) {
		var data = response.data || {};
		if (data.gateway && data.gateway.url) {
			let gateway = data.gateway;
			let totalReceiveText = '';
			if (gateway.originalAmount !== undefined && gateway.chargeCustomerForPaymentGatewayFee !== undefined) {
				let totalReceive = 0;
				if (gateway.chargeCustomerForPaymentGatewayFee === true || gateway.chargeCustomerForPaymentGatewayFee === 'true') {
					totalReceive = parseFloat(gateway.originalAmount) || 0;
				} else {
					let originalAmount = parseFloat(gateway.originalAmount) || 0;
					let adminFee = parseFloat(gateway.paymentGatewayAdminFee) || 0;
					totalReceive = originalAmount - adminFee;
				}
				if (totalReceive > 0) {
					totalReceiveText = '<p class="text-muted small"><strong>Total Diterima: Rp ' + totalReceive.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></p>';
				}
			}

			Swal.fire({
				title: 'QR Code Pembayaran',
				html: `
					<div class="text-center">
						<p class="mb-3">Silakan scan QR code berikut untuk melakukan pembayaran:</p>
						<img src="${gateway.url}" alt="QR Code" class="img-fluid mb-3" style="max-width: 300px;">
						<p class="text-muted small mb-2">Status: <strong>${gateway.status}</strong></p>
						${gateway.paymentGatewayAdminFee > 0 ? '<p class="text-muted small">Biaya Admin: Rp ' + gateway.paymentGatewayAdminFee.toLocaleString('id-ID') + '</p>' : ''}
						${totalReceiveText}
						<a href="${gateway.url}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
							<i class="fas fa-external-link-alt"></i> Buka QR Code
						</a>
					</div>
				`,
				icon: 'info',
				confirmButtonText: 'OK',
				confirmButtonColor: '#4e73df',
				width: '500px'
			}).then(() => {
				showSwalSuccess(response.message || 'Pembayaran berhasil diproses.');
				resetSelection();
				table.ajax.reload(null, false);
			});
		} else {
			showSwalSuccess(response.message || 'Pembayaran berhasil diproses.');
			resetSelection();
			table.ajax.reload(null, false);
		}
	}

	function resetSelection() {
		selectedInvoices = {};
		updateSelectedInfo();
		updateSelectAllState();
	}

	function showSwalWarning(message) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'warning',
				title: 'Peringatan',
				text: message,
				confirmButtonColor: '#f6c23e'
			});
		} else {
			alert(message);
		}
	}

	function showSwalError(message) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: message,
				confirmButtonColor: '#e74a3b'
			});
		} else {
			alert(message);
		}
	}

	function showSwalSuccess(message) {
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'success',
				title: 'Berhasil',
				text: message,
				confirmButtonColor: '#1cc88a'
			});
		} else {
			alert(message);
		}
	}

	function formatCurrency(amount) {
		var num = parseFloat(amount) || 0;
		return 'Rp ' + num.toLocaleString('id-ID', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	}

	function escapeHtml(text) {
		if (!text) return '';
		return $('<div>').text(text).html();
	}
});
</script>

