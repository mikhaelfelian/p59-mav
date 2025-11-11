<div class="card-body dashboard">
    <?php if (!empty($message) && $message['status'] == 'error') {
        show_message($message);
    } ?>

    <?php if (!empty($list_tahun)): ?>
    <div class="row">
        <!-- Total Item Terjual -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-primary shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title h4"><?= format_number($total_item_terjual['jml'] ?? 0) ?></h5>
                        <p class="card-text">Item Terjual</p>
                    </div>
                    <div class="icon bg-warning-light">
                        <i class="material-icons">inventory_2</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            $growth = $total_item_terjual['growth'] ?? 0;
                            if ($growth > 0) {
                                echo '<i class="fas fa-arrow-trend-up"></i>';
                            } elseif ($growth < 0) {
                                echo '<i class="fas fa-arrow-trend-down"></i>';
                            } else {
                                echo '<i class="fas fa-minus"></i>';
                            }
                            ?>
                        </div>
                        <p><?= $growth ? round($growth) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= $tahun ?? '' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Nilai Transaksi -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-success shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title"><?= format_number($total_jumlah_transaksi['jml'] ?? 0) ?></h5>
                        <p class="card-text">Nilai Transaksi</p>
                    </div>
                    <div class="icon">
                        <i class="material-icons">attach_money</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            $growth = $total_jumlah_transaksi['growth'] ?? 0;
                            if ($growth > 0) {
                                echo '<i class="fas fa-arrow-trend-up"></i>';
                            } elseif ($growth < 0) {
                                echo '<i class="fas fa-arrow-trend-down"></i>';
                            } else {
                                echo '<i class="fas fa-minus"></i>';
                            }
                            ?>
                        </div>
                        <p><?= $growth ? round($growth) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= $tahun ?? '' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Grand Total Penjualan -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-warning shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title"><?= format_number($total_nilai_penjualan['jml'] ?? 0) ?></h5>
                        <p class="card-text">Grand Total</p>
                    </div>
                    <div class="icon">
                        <i class="material-icons">payments</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            $growth = $total_nilai_penjualan['growth'] ?? 0;
                            if ($growth > 0) {
                                echo '<i class="fas fa-arrow-trend-up"></i>';
                            } elseif ($growth < 0) {
                                echo '<i class="fas fa-arrow-trend-down"></i>';
                            } else {
                                echo '<i class="fas fa-minus"></i>';
                            }
                            ?>
                        </div>
                        <p><?= $growth ? round($growth) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= $tahun ?? '' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pelanggan Aktif -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-danger shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title"><?= format_number($total_pelanggan_aktif['jml'] ?? 0) ?></h5>
                        <p class="card-text">Pelanggan Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="material-icons">groups</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            $growth = $total_pelanggan_aktif['growth'] ?? 0;
                            if ($growth > 0) {
                                echo '<i class="fas fa-arrow-trend-up"></i>';
                            } elseif ($growth < 0) {
                                echo '<i class="fas fa-arrow-trend-down"></i>';
                            } else {
                                echo '<i class="fas fa-minus"></i>';
                            }
                            ?>
                        </div>
                        <p><?= $growth ? round($growth) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= $tahun ?? '' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h6 class="card-title">Penjualan Perbulan</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div style="overflow:auto">
                        <canvas id="bar-container" style="min-width:500px;margin:auto;width:100%"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Total Penjualan Tahunan</h5>
                    </div>
                </div>
                <div class="card-body d-flex">
                    <canvas id="chart-total-penjualan" style="margin:auto;max-width:350px;width:100%"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Produk Terlaris</h5>
                    </div>
                    <div class="card-header-end">
                        <?= !empty($list_tahun)
                            ? '<form method="get" class="d-flex">' . options(['name' => 'tahun', 'id' => 'tahun-barang-terlaris'], $list_tahun, $tahun) . '</form>'
                            : '' ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <?php
                        $column = [
                            'ignore_search_urut' => 'No',
                            'nama_barang' => 'Nama Barang',
                            'harga_satuan' => 'Harga Satuan',
                            'jml_terjual' => 'Jumlah',
                            'total_harga' => 'Total',
                            'kontribusi' => 'Kontribusi'
                        ];
                        $settings = ['order' => [4, 'desc']];
                        $th = '';
                        $columnDefs = [];
                        $index = 0;
                        foreach ($column as $key => $label) {
                            $th .= '<th>' . $label . '</th>';
                            if (strpos($key, 'ignore_search') !== false) {
                                $columnDefs[] = ['targets' => $index, 'orderable' => false];
                            }
                            $index++;
                        }
                        $settings['columnDefs'] = $columnDefs;
                        ?>
                        <table id="tabel-penjualan-terbesar" class="table display table-striped table-hover" style="width:100%">
                            <thead><tr><?= $th ?></tr></thead>
                            <tbody><tr><td colspan="<?= count($column) ?>" class="text-center">Memuat data...</td></tr></tbody>
                        </table>
                        <?php
                        $columnDt = array_map(
                            static fn($key) => ['data' => $key],
                            array_keys($column)
                        );
                        ?>
                        <span id="penjualan-terbesar-column" style="display:none"><?= json_encode($columnDt) ?></span>
                        <span id="penjualan-terbesar-setting" style="display:none"><?= json_encode($settings) ?></span>
                        <span id="penjualan-terbesar-url" style="display:none">
                            <?= $config->baseURL ?>agent/dashboard/getDataDTPenjualanTerbesar?tahun=<?= $tahun ?? date('Y') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Share Penjualan Produk</h5>
                    </div>
                    <div class="card-header-end">
                        <?= !empty($list_tahun)
                            ? '<form method="get" class="d-flex">' . options(['name' => 'tahun', 'id' => 'tahun-item-terjual'], $list_tahun, $tahun) . '</form>'
                            : '' ?>
                    </div>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="pie-container" style="margin:auto"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Kategori Terlaris</h5>
                    </div>
                    <div class="card-header-end">
                        <?= !empty($list_tahun)
                            ? '<form method="get" class="d-flex">' . options(['name' => 'tahun', 'id' => 'tahun-kategori-terjual'], $list_tahun, $tahun) . '</form>'
                            : '' ?>
                    </div>
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="chart-kategori" style="margin:auto"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Total per Kategori</h5>
                    </div>
                    <div class="card-header-end">
                        <?= !empty($list_tahun)
                            ? '<form method="get" class="d-flex">' . options(['name' => 'tahun', 'id' => 'tahun-kategori-terjual-detail'], $list_tahun, $tahun) . '</form>'
                            : '' ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-border table-hover item">
                            <thead>
                                <tr>
                                    <th colspan="2">Kategori</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($kategori_terjual)): ?>
                                    <?php foreach ($kategori_terjual as $row): ?>
                                        <tr>
                                            <td><span class="text-warning h5"><i class="fas fa-folder"></i></span></td>
                                            <td><?= esc($row['nama_kategori'] ?? 'Tidak diketahui') ?></td>
                                            <td class="text-end"><?= format_number($row['nilai'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">Belum ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Produk Terbaru</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-border table-hover item">
                            <thead><tr><th colspan="2">Nama Produk</th></tr></thead>
                            <tbody>
                                <?php if (!empty($item_terbaru)): ?>
                                    <?php foreach ($item_terbaru as $row): ?>
                                        <?php
                                        $image = !empty($row['image']) && file_exists(FCPATH . 'public/images/item/' . $row['image'])
                                            ? $row['image']
                                            : 'noimage.png';
                                        ?>
                                        <tr>
                                            <td><img src="<?= base_url('public/images/item/' . $image) ?>" alt="<?= esc($row['nama_barang']) ?>" width="48"></td>
                                            <td>
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <span><?= esc($row['nama_barang']) ?></span>
                                                    <span class="badge bg-primary"><?= format_number($row['harga_jual'] ?? 0) ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="2" class="text-center">Belum ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Pelanggan Terbaik</h5>
                    </div>
                    <div class="card-header-end">
                        <?= !empty($list_tahun)
                            ? '<form method="get" class="d-flex">' . options(['name' => 'tahun', 'id' => 'tahun-pelanggan-terbesar'], $list_tahun, $tahun) . '</form>'
                            : '' ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-border table-hover">
                            <thead>
                                <tr>
                                    <th colspan="2">Nama</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="table-pelanggan-terbesar">
                                <tr><td colspan="3" class="text-center">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Penjualan Terbaru</h5>
                    </div>
                    <div class="card-header-end">
                        <?= !empty($list_tahun)
                            ? '<form method="get" class="d-flex">' . options(['name' => 'tahun', 'id' => 'tahun-penjualan-terbaru'], $list_tahun, $tahun) . '</form>'
                            : '' ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-border table-hover" id="penjualan-terbaru">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pembeli</th>
                                    <th>Jml. Item</th>
                                    <th>Nilai</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($sales_terbaru)): ?>
                                    <?php $no = 1; foreach ($sales_terbaru as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= esc($row['nama_pelanggan'] ?? 'Umum') ?></td>
                                            <td class="text-end"><?= format_number($row['jumlah_item'] ?? $row['jml_barang'] ?? 0) ?></td>
                                            <td class="text-end"><?= format_number($row['grand_total'] ?? 0) ?></td>
                                            <td><?= !empty($row['created_at']) ? date('d-m-Y H:i', strtotime($row['created_at'])) : '-' ?></td>
                                            <td>
                                                <?php
                                                $status = $row['payment_status'] ?? '0';
                                                echo $status === '2' ? 'Lunas' : ($status === '1' ? 'Sebagian' : 'Belum Lunas');
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">Belum ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$data_penjualan = [];
$data_total_penjualan = [];
$label_kategori = [];
$jumlah_item_kategori = [];
$jumlah = [];
$nama = [];

if (!empty($penjualan)) {
    foreach ($penjualan as $tahunKey => $records) {
        if (is_array($records)) {
            foreach ($records as $row) {
                if (isset($row['total'])) {
                    $data_penjualan[$tahunKey][] = floatval($row['total']);
                }
            }
        }
    }

    foreach ($total_penjualan as $tahunKey => $row) {
        $data_total_penjualan[$tahunKey] = isset($row['total']) ? floatval($row['total']) : 0;
    }
}

if (!empty($item_terjual)) {
    foreach ($item_terjual as $row) {
        if (isset($row['jml'], $row['nama_barang'])) {
            $jumlah[] = (int)$row['jml'];
            $nama[] = $row['nama_barang'];
        }
    }
}

if (!empty($kategori_terjual)) {
    foreach ($kategori_terjual as $row) {
        if (isset($row['nama_kategori'], $row['jml'])) {
            $label_kategori[] = $row['nama_kategori'];
            $jumlah_item_kategori[] = (int)$row['jml'];
        }
    }
}
?>

<script>
let data_penjualan = <?= json_encode($data_penjualan) ?>;
let total_penjualan = <?= json_encode($data_total_penjualan) ?>;
let item_terjual = <?= json_encode($jumlah) ?>;
let item_terjual_label = <?= json_encode($nama) ?>;
let label_kategori = <?= json_encode($label_kategori) ?>;
let jumlah_item_kategori = <?= json_encode($jumlah_item_kategori) ?>;

const agentDashboardConfig = {
    baseUrl: '<?= $config->baseURL ?>agent/dashboard/',
    tahunAktif: '<?= $tahun ?? date('Y') ?>'
};
</script>

