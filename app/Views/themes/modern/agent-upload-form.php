<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-24
 * Github: github.com/mikhaelfelian
 * Description: View for uploading Excel/CSV files to import agent data
 * This file represents the View for agent-upload-form.
 */
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?= $current_module['judul_module'] ?> - Import Data</h5>
	</div>
	<div class="card-body">
		<?php if (!empty($msg)): ?>
			<?= $msg ?>
		<?php endif; ?>
		
		<div class="row">
			<div class="col-md-8">
				<?= form_open_multipart('', ['class' => 'form-horizontal', 'id' => 'form-upload']) ?>
					<div class="mb-3">
						<label class="form-label">Pilih File Excel/CSV</label>
						<?= form_upload([
							'name' => 'file',
							'class' => 'form-control',
							'accept' => '.xlsx,.xls,.csv',
							'required' => 'required'
						]) ?>
						<div class="form-text">Format yang didukung: .xlsx, .xls, .csv</div>
					</div>
					
					<div class="mb-3">
						<button type="submit" class="btn btn-primary">
							<i class="fa fa-upload me-2"></i>Upload & Import
						</button>
						<a href="<?= $config->baseURL ?>agent" class="btn btn-secondary">
							<i class="fa fa-arrow-left me-2"></i>Kembali
						</a>
					</div>
				<?= form_close() ?>
			</div>
			
			<div class="col-md-4">
				<div class="card bg-light">
					<div class="card-header">
						<h6 class="card-title mb-0">Format File</h6>
					</div>
					<div class="card-body">
						<p class="small mb-2">Kolom yang harus ada (urutan):</p>
						<ol class="small">
							<li><strong>Nama Agen</strong> (Wajib)</li>
							<li><strong>Email</strong> (Opsional)</li>
							<li><strong>Telepon</strong> (Opsional)</li>
							<li><strong>Alamat</strong> (Opsional)</li>
							<li><strong>Negara</strong> (Default: Indonesia)</li>
							<li><strong>Nomor Pajak</strong> (Opsional)</li>
							<li><strong>Limit Kredit</strong> (Default: 0)</li>
							<li><strong>Syarat Pembayaran</strong> (Default: 0)</li>
						</ol>
						<p class="small text-muted mt-3">
							<strong>Catatan:</strong> Kode agen akan otomatis dibuat. 
							Baris kosong akan dilewati.
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row mt-4">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h6 class="card-title mb-0">Contoh Format Excel/CSV</h6>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-bordered table-sm">
								<thead class="table-dark">
									<tr>
										<th>Nama Agen</th>
										<th>Email</th>
										<th>Telepon</th>
										<th>Alamat</th>
										<th>Negara</th>
										<th>Nomor Pajak</th>
										<th>Limit Kredit</th>
										<th>Syarat Pembayaran</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>PT. Contoh Agen</td>
										<td>info@contoh.com</td>
										<td>08123456789</td>
										<td>Jl. Contoh No. 123</td>
										<td>Indonesia</td>
										<td>123456789</td>
										<td>10000000</td>
										<td>30</td>
									</tr>
									<tr>
										<td>CV. Agen Sukses</td>
										<td>admin@sukses.com</td>
										<td>08198765432</td>
										<td>Jl. Sukses No. 456</td>
										<td>Indonesia</td>
										<td>987654321</td>
										<td>5000000</td>
										<td>14</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
    // Handle form submit
    $('#form-upload').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Memproses...');
        
        $.ajax({
            url: '<?= current_url(true) ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'html',
            success: function(response) {
                // Reload page to show results
                window.location.reload();
            },
            error: function(xhr, status, error) {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengupload file.'
                });
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
