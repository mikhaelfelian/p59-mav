<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	<div class="card-body">
		<h6>List Examples:</h6>
		<ol class="ms-3 mt-2">
		<?php
			foreach ($list_examples as $url => $label) {
				echo '<li><a href="' . $url . '" target="_blank" title="' . $label . '">' . $label . '</a>';
			}
		?>
		</ol>
    </div>
</div>
