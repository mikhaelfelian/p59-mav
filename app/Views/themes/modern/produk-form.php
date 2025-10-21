<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
		<?php
			if (!empty($message)) {
					show_message($message);
		} ?>
		<form method="post" action="<?=current_url(true)?>" class="form-horizontal" enctype="multipart/form-data">
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Produk</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="nama_produk" value="<?=set_value('nama_produk', @$produk['nama_produk'])?>" required="required"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi Produk</label>
				<div class="col-sm-5">
					<textarea class="form-control" name="deskripsi_produk"><?=set_value('deskripsi_produk', @$produk['deskripsi_produk'])?></textarea>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-5">
					<button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
					<input type="hidden" name="id" value="<?=@$id_produk?>"/>
				</div>
			</div>
		</form>
	</div>
</div>