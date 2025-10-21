<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
			helper ('html');
		?>
		<form method="get" action="<?=current_url()?>" class="form-container">
			<div class="row mb-3">
				<div class="col-xs-12 col-sm-2">
					<label>Kategori</label>
				</div>
				<div class="col-xs-12 col-sm-6">
				<?=options(['name' => 'id_kategori', 'id' => 'list-kategori'], $kategori, $id_kategori)?>
				</div>
			</div>
		</form>
		<hr/>
		
		<div class="gallery-container">
			<?php
			if (!empty($message)) {
				show_message($message);
			}
			
			$initial_item = false;
			if (!$gallery) {
				$initial_item = true;
				$gallery[] = ['id_gallery' => '', 'id_file_picker' => '', 'thumbnail' => ['url' => '']];
			}
			// echo '<pre>'; print_r($gallery);
			$display = $initial_item ? ' style="display:none"' : '';
			echo '<ul id="list-image-container" class="list-image-container">';
			foreach ($gallery as $val) 
			{
				$data_initial_item = $initial_item ? ' data-initial-item="true"' : '';
				?>
				<li class="thumbnail-item"<?=$data_initial_item?> id="gallery-<?=$val['id_gallery']?>"<?=$display?> data-id-file-picker="<?=$val['id_file_picker']?>">
					<div class="toolbox">
						<?php if (@$id_kategori != '') { ?>
							<div class="grip"><i class="fas fa-grip-horizontal"></i></div>
						<?php } ?>
						<ul class="right-menu">
							<li><a class="text-warning change-category" data-bs-toggle="tooltip" data-bs-placement="top" title="Pindah Kategori" href="javascript:void(0)"><i class="fas fa-folder"></i></a>
							<li><a class="text-danger delete-image" href="javascript:void(0)"><i class="fas fa-times"></i></a>
						</ul>
					</div>
					<div class="img-container">
						<img class="jwd-img-thumbnail" src="<?=$val['thumbnail']['url']?>" />
					</div>
				</li>	
			<?php 
			} 
			echo '</ul><hr/>';
			?>
			<a class="btn btn-secondary" id="add-image" href="javascript:void(0)">Add Image</a>
		</div>
	</div>
</div>