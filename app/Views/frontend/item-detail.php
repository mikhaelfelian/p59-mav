<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: Frontend item detail page view using tanpalogin template layout
 * This file represents the View.
 */
?>
<!DOCTYPE HTML>
<html lang="en">
<title><?=$title?></title>
<meta name="description" content="<?=$item->short_description ?? $item->name?>"/>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<link rel="shortcut icon" href="<?=$config->baseURL?>public/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>public/vendors/fontawesome/css/all.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>public/vendors/bootstrap/css/bootstrap.min.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>public/themes/modern/builtin/css/bootstrap-custom.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>public/themes/modern/css/tanpalogin.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>public/vendors/overlayscrollbars/OverlayScrollbars.min.css?r=<?=time()?>"/>
<link rel="stylesheet" id="font-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/fonts/'.$app_layout['font_family'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="font-size-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/fonts/font-size-'.$app_layout['font_size'].'.css?r='.time()?>"/>

<script type="text/javascript" src="<?=$config->baseURL?>public/vendors/jquery/jquery.min.js"></script>
<script type="text/javascript" src="<?=$config->baseURL?>public/themes/modern/js/site.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>public/vendors/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?=$config->baseURL?>public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js"></script>
<script type="text/javascript">
	var base_url = "<?=$config->baseURL?>";
</script>

</head>
<body>
	<div class="site-container">
	<header class="shadow-sm">
		<div class="menu-wrapper wrapper clearfix">
			<a href="#" id="mobile-menu-btn" class="show-mobile">
				<i class="fa fa-bars"></i>
			</a>
			<div class="nav-left">
				<a href="<?=$config->baseURL?>" class="logo-header" title="Frontend">
					<img src="<?=$config->baseURL?>public/images/logo_login.png" alt="Frontend"/>
				</a>
			</div>
			<nav class="nav-right nav-header">
				<ul class="main-menu">
					<li class="menu">
						<a class="depth-0" href="<?=$config->baseURL?>frontend">
							<i class="menu-icon fas fa-home"></i>Home 
						</a>
					</li>
					<li class="menu">
						<a class="depth-0" href="<?=$config->baseURL?>frontend/about">
							<i class="menu-icon fas fa-info-circle"></i>Tentang
						</a>
					</li>
					<li class="menu">
						<a class="depth-0" href="<?=$config->baseURL?>frontend/contact">
							<i class="menu-icon fas fa-envelope"></i>Kontak
						</a>
					</li>
					<li class="menu">
						<a class="depth-0" href="<?=$config->baseURL?>">
							<i class="menu-icon fas fa-sign-in-alt"></i>Admin
						</a>
					</li>
				</ul>
			</nav>
			<div class="clearfix"></div>
		</div>
	</header>
	<div class="page-container">
		<div class="title-container shadow-lg">
			<div class="wrapper wrapper-post-single">
				<h1 class="post-title"><?=$item->name?></h1>
				<div class="clearfix post-meta-single">
					<p class="post-description"><?=$item->short_description ?? 'Detail produk terbaru dari koleksi kami'?></p>
				</div>
			</div>
		</div>
		<div class="wrapper">
			<div class="row article-single-container">
				<div class="col-md-6">
					<div class="card">
						<div class="card-body text-center">
							<?php if (!empty($item->image)): ?>
								<img src="<?=base_url('public/uploads/' . $item->image)?>" class="img-fluid rounded" alt="<?=$item->name?>" style="max-height: 400px; object-fit: cover;">
							<?php else: ?>
								<div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 400px;">
									<i class="fas fa-image fa-5x text-muted"></i>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				
				<div class="col-md-6">
					<div class="card">
						<div class="card-header">
							<h3>Informasi Produk</h3>
						</div>
						<div class="card-body">
							<table class="table table-borderless">
								<tr>
									<td><strong>SKU:</strong></td>
									<td><?=$item->sku?></td>
								</tr>
								<tr>
									<td><strong>Nama:</strong></td>
									<td><?=$item->name?></td>
								</tr>
								<tr>
									<td><strong>Brand:</strong></td>
									<td><?=$item->brand_name ?? 'N/A'?></td>
								</tr>
								<tr>
									<td><strong>Kategori:</strong></td>
									<td><?=$item->category_name ?? 'N/A'?></td>
								</tr>
								<tr>
									<td><strong>Harga:</strong></td>
									<td><span class="h4 text-primary">Rp <?=number_format($item->price, 0, ',', '.')?></span></td>
								</tr>
								<tr>
									<td><strong>Status Stock:</strong></td>
									<td>
										<?php if ($item->is_stockable == '1'): ?>
											<span class="badge bg-success">Tersedia</span>
										<?php else: ?>
											<span class="badge bg-warning">Tidak Tersedia</span>
										<?php endif; ?>
									</td>
								</tr>
							</table>
							
							<div class="d-grid gap-2">
								<button class="btn btn-primary btn-lg">
									<i class="fas fa-shopping-cart me-2"></i>Beli Sekarang
								</button>
								<button class="btn btn-outline-secondary">
									<i class="fas fa-heart me-2"></i>Tambah ke Wishlist
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<?php if (!empty($item->description)): ?>
			<div class="row mt-4">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3>Deskripsi Produk</h3>
						</div>
						<div class="card-body">
							<?=$item->description?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
			
			<div class="row mt-4">
				<div class="col-12">
					<div class="card">
						<div class="card-header">
							<h3>Produk Terkait</h3>
						</div>
						<div class="card-body">
							<p class="text-muted">Produk terkait akan ditampilkan di sini.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer>
		<div class="footer-desc">
		<div class="wrapper">
			<div class="row mb-0">
				<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 mb-2">
					<h2 class="widget-title">Contact us</h2>
					<ul class="list">
						<li><i class="fa fa-envelope me-2"></i>Email: support@example.com</li>
						<li><i class="fas fa-file-signature me-2"></i><a href="<?=$config->baseURL?>frontend/contact">Via Contact form</a></li>
					</ul>
				</div>
				<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 mb-2">
					<h2 class="widget-title">About</h2>
					<p>Platform terbaik untuk menemukan item berkualitas tinggi dengan harga terbaik.</p>
					<ul class="list">
						<li><i class="fab fa-facebook-square me-2"></i><a href="#" target="_blank">facebook</a></li>
					</ul>
				</div>
				<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4">
					<h2 class="widget-title">More Info</h2>
					<ul class="list">
						<li><i class="fa fa-user-plus me-2"></i><a href="#" target="_blank">Premium Member</a></li>
						<li><i class="fas fa-external-link-alt me-2"></i><a href="<?=$config->baseURL?>frontend" target="_blank">Home</a></li>
					</ul>
				</div>
			</div>
		</div>
		</div>
		<div class="footer-menu-container">
			<div class="wrapper clearfix">
				<div class="nav-left">Copyright &copy; <?=date('Y')?> <a title="Frontend" href="<?=$config->baseURL?>frontend">Frontend</a>
				</div>
				<nav class="nav-right nav-footer">
					<ul class=footer-menu>
						<li class="menu">
							<a class="depth-0" href="<?=$config->baseURL?>frontend">Home</a>
						</li>
						<li class="menu">
							<a class="depth-0" href="<?=$config->baseURL?>frontend/about">About</a>
						</li>
						<li class="menu">
							<a class="depth-0" href="<?=$config->baseURL?>frontend/contact">Contact</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>
	</footer>
	</div><!-- site-container -->
</body>
</html>
