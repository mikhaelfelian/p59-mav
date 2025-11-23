<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
		helper ('html');
		if (!empty($message)) {
			show_message($message);
		}
		
		// Menghindari error pada set_value
		$list = ['background_logo', 'btn_login', 'deskripsi_web'];
		foreach ($list as $val) {
			if (empty($$val)) {
				$$val = '';
			}
		}

		?>
		<form method="post" action="" id="form-setting" enctype="multipart/form-data">
			<div class="tab-content">
				<div class="bg-lightgrey p-3 ps-4">
				<h5>Login</h5>
				</div>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Logo Login</label>
					<div class="col-sm-5">
						<?php
						if (!empty($logo_login) && file_exists($config->imagesPath . $logo_login))
						echo '<div class="edit-logo-login-container"><img src="'. $config->imagesURL . $logo_login . '?r='.time().'"/></div>';
						
						?>
						<input type="file" class="file form-control" name="logo_login">
							<?php if (!empty($form_errors['logo_login'])) echo '<small class="alert alert-danger">' . $form_errors['logo_login'] . '</small>'?>
							<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-file-thumb"><span class="file-prop"></span></div>
					</div>
					
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Background Logo</label>
					<div class="col-sm-5 form-inline">
						<input name="background_logo" class="form-control colorpicker" value="<?=set_value('background_logo', @$background_logo)?>" />
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Button</label>
					<div class="col-sm-5">
						<ul class="list-inline list-btn-login">
							<?php
							$list = ['btn-primary', 'btn-secondary', 'btn-success', 'btn-danger', 'btn-warning', 'btn-info', 'btn-light', 'btn-dark'];
							foreach ($list as $val) {
								$check = @$btn_login == $val ? '<i class="fa fa-check check"></i>' : ''; 
								echo '<li class="list-inline-item"><a data-class="'. $val . '" href="javascript:void(0)" class="theme-btn-login btn '.$val.'">' . $check . '</a></li>';
							}
							?>	
						</ul>
						<input type="hidden" name="btn_login" value="<?=set_value('btn_login', @$btn_login)?>">
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Footer</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="footer_login"><?=set_value('footer_login', @$footer_login)?></textarea>
					</div>
				</div>
				<div class="bg-lightgrey p-3 mt-5 ps-4">
				<h5>Website</h5>
				</div>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul Web</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="judul_web"><?=set_value('judul_web', @$judul_web)?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi Web</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="deskripsi_web"><?=set_value('deskripsi_web', @$deskripsi_web)?></textarea>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Fav Icon</label>
					<div class="col-sm-5">
						<?php
						if (!empty($favicon) && file_exists($config->imagesPath . $favicon))
						echo '<div style="margin:inherit;margin-bottom:10px"><img src="'. $config->imagesURL . $favicon . '?r='.time().'"/></div>';
						
						?>
						<input type="file" class="file form-control" name="favicon">
							<?php if (!empty($form_errors['favicon'])) echo '<small class="alert alert-danger">' . $form_errors['favicon'] . '</small>'?>
							<small class="form-text text-muted"><strong>Gunakan file PNG transparan, width dan height sama, misal: 64px x 64px</strong></small>
						<div class="upload-file-thumb"><span class="file-prop"></span></div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Logo Aplikasi</label>
					<div class="col-sm-5">
						<?php
						if (!empty($logo_app) && file_exists($config->imagesPath . $logo_app))
						echo '<div style="margin:inherit;margin-bottom:10px"><img src="' . $config->imagesURL . $logo_app . '?r='.time().'"/></div>';
						
						?>
						<input type="file" class="file form-control" name="logo_app">
							<?php if (!empty($form_errors['logo_app'])) echo '<small class="alert alert-danger">' . $form_errors['logo_app'] . '</small>'?>
							<small class="form-text text-muted"><strong>Gunakan file PNG transparan</strong>. Maksimal 300Kb, Minimal 50px x 50px, Tipe file: .JPG, .JPEG, .PNG</small>
						<div class="upload-file-thumb"><span class="file-prop"></span></div>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Background Logo</label>
					<div class="col-sm-5">
						Ubah di menu setting tampilan
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Footer</label>
					<div class="col-sm-5">
						<textarea class="form-control" name="footer_app"><?=set_value('footer_app', @$footer_app)?></textarea>
					</div>
				</div>
				<div class="bg-lightgrey p-3 ps-4">
				<h5>Landing</h5>
				</div>
				<hr/>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul Landing Page</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="hero_title"
							value="<?= set_value('hero_title', @$hero_title) ?>" 
							placeholder="Masukkan judul landing page" />
						<?php if (!empty($form_errors['hero_title'])) echo '<small class="alert alert-danger">' . $form_errors['hero_title'] . '</small>'?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi</label>
					<div class="col-sm-5">
						<textarea class="form-control tinymce" rows="10" name="hero_subtitle"
							placeholder="Masukkan deskripsi landing page"><?= set_value('hero_subtitle', @$hero_subtitle) ?></textarea>
						<?php if (!empty($form_errors['hero_subtitle'])) echo '<small class="alert alert-danger">' . $form_errors['hero_subtitle'] . '</small>'?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">CTA Button</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="hero_cta_text"
							value="<?= set_value('hero_cta_text', @$hero_cta_text) ?>" 
							placeholder="Masukkan teks tombol CTA" />
						<?php if (!empty($form_errors['hero_cta_text'])) echo '<small class="alert alert-danger">' . $form_errors['hero_cta_text'] . '</small>'?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">CTA Button URI</label>
					<div class="col-sm-5">
						<input class="form-control" type="text" name="hero_cta_link"
							value="<?= set_value('hero_cta_link', @$hero_cta_link) ?>" 
							placeholder="Masukkan URI/link tombol CTA" />
						<?php if (!empty($form_errors['hero_cta_link'])) echo '<small class="alert alert-danger">' . $form_errors['hero_cta_link'] . '</small>'?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Features</label>
					<div class="col-sm-9 col-md-10 col-lg-9 col-xl-10">
						<?php if (!empty($form_errors['features'])) echo '<small class="alert alert-danger">' . $form_errors['features'] . '</small>'?>
						
						<!-- Hidden field to store JSON -->
						<input type="hidden" name="features" id="features-json" value="">
						
						<!-- Container for feature items -->
						<div id="features-container" class="mb-3">
							<!-- Features will be dynamically added here -->
						</div>
						
						<!-- Add Feature Button -->
						<button type="button" class="btn btn-sm btn-success" id="add-feature">
							<i class="fas fa-plus"></i> Tambah Feature
						</button>
						
						<small class="form-text text-muted d-block mt-2">
							Icon harus menggunakan class FontAwesome lengkap (contoh: fa-lock, fa-bolt, fa-comments)
						</small>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-5">
						<button type="submit" name="submit" id="btn-submit" value="submit" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
// Initialize TinyMCE for description field
document.addEventListener('DOMContentLoaded', function() {
    // Wait for TinyMCE to be loaded
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.tinymce',
            plugins: 'advlist lists link wordcount codesample',
            toolbar: 'styleselect | bold italic underline strikethrough | forecolor | numlist bullist | codesample',
            branding: false,
            statusbar: false,
            height: 300,
            codesample_content_css: base_url + "public/vendors/prism/themes/prism-dark.css",
        });
    } else {
        // Retry after a short delay if TinyMCE isn't loaded yet
        setTimeout(function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.tinymce',
                    plugins: 'advlist lists link wordcount codesample',
                    toolbar: 'styleselect | bold italic underline strikethrough | forecolor | numlist bullist | codesample',
                    branding: false,
                    statusbar: false,
                    height: 300,
                    codesample_content_css: base_url + "public/vendors/prism/themes/prism-dark.css",
                });
            }
        }, 500);
    }
    
    // Features Management
    const featuresContainer = document.getElementById('features-container');
    const addFeatureBtn = document.getElementById('add-feature');
    const featuresJsonField = document.getElementById('features-json');
    
    // Load existing features from database
    <?php 
    $features_data = [];
    if (!empty($features)) {
        $decoded = json_decode($features, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $features_data = $decoded;
        }
    }
    ?>
    const existingFeatures = <?= json_encode($features_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    
    // Function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Function to create a feature item HTML
    function createFeatureItem(feature = {icon: '', title: '', desc: ''}) {
        const index = featuresContainer.children.length;
        const icon = escapeHtml(feature.icon || '');
        const title = escapeHtml(feature.title || '');
        const desc = escapeHtml(feature.desc || '');
        
        const itemHtml = `
            <div class="feature-item card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Feature #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-danger remove-feature">
                            <i class="fas fa-times"></i> Hapus
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Icon (FontAwesome Class)</label>
                            <input type="text" class="form-control feature-icon" 
                                   placeholder="fa-lock" value="${icon}" 
                                   data-field="icon">
                            <small class="form-text text-muted">Contoh: fa-lock, fa-bolt, fa-comments</small>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Judul</label>
                            <input type="text" class="form-control feature-title" 
                                   placeholder="Masukkan judul feature" value="${title}" 
                                   data-field="title">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control feature-desc" rows="2" 
                                      placeholder="Masukkan deskripsi feature" 
                                      data-field="desc">${desc}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
        return itemHtml;
    }
    
    // Load existing features
    if (existingFeatures && existingFeatures.length > 0) {
        existingFeatures.forEach(function(feature) {
            featuresContainer.insertAdjacentHTML('beforeend', createFeatureItem(feature));
        });
    }
    
    // Add new feature
    addFeatureBtn.addEventListener('click', function() {
        featuresContainer.insertAdjacentHTML('beforeend', createFeatureItem());
        updateFeatureNumbers();
    });
    
    // Remove feature
    featuresContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-feature')) {
            e.target.closest('.feature-item').remove();
            updateFeatureNumbers();
            updateFeaturesJson();
        }
    });
    
    // Update feature numbers
    function updateFeatureNumbers() {
        const items = featuresContainer.querySelectorAll('.feature-item');
        items.forEach(function(item, index) {
            item.querySelector('h6').textContent = `Feature #${index + 1}`;
            item.setAttribute('data-index', index);
        });
    }
    
    // Convert form data to JSON
    function updateFeaturesJson() {
        const items = featuresContainer.querySelectorAll('.feature-item');
        const features = [];
        
        items.forEach(function(item) {
            const icon = item.querySelector('.feature-icon').value.trim();
            const title = item.querySelector('.feature-title').value.trim();
            const desc = item.querySelector('.feature-desc').value.trim();
            
            if (icon || title || desc) {
                features.push({
                    icon: icon,
                    title: title,
                    desc: desc
                });
            }
        });
        
        featuresJsonField.value = JSON.stringify(features);
    }
    
    // Update JSON on input change
    featuresContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('feature-icon') || 
            e.target.classList.contains('feature-title') || 
            e.target.classList.contains('feature-desc')) {
            updateFeaturesJson();
        }
    });
    
    // Update JSON before form submit
    document.getElementById('form-setting').addEventListener('submit', function(e) {
        updateFeaturesJson();
    });
    
    // Initial JSON update
    updateFeaturesJson();
});
</script>