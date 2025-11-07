<div class="card-body dashboard">
    <?php if ($message['status'] == 'error') {
        show_message($message);
    } ?>
    <div class="row">
        <!-- Total Item Terjual -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-primary shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title h4"><?= !empty($total_item_terjual['jml']) ? format_number($total_item_terjual['jml']) : 0 ?></h5>
                        <p class="card-text">Total Item Terjual</p>
                    </div>
                    <div class="icon bg-warning-light">
                        <i class="material-icons">local_shipping</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            if (!empty($total_item_terjual['growth'])) {
                                $class = $total_item_terjual['growth'] > 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                                echo '<i class="fas ' . $class . '"></i>';
                            } else {
                                $total_item_terjual['growth'] = 0;
                            }
                            ?>
                        </div>
                        <p><?= $total_item_terjual['growth'] ? round($total_item_terjual['growth']) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= !empty($list_tahun) ? max($list_tahun) : '' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Transaksi -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-success shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title"><?= !empty($total_jumlah_transaksi['jml']) ? format_number($total_jumlah_transaksi['jml']) : 0 ?></h5>
                        <p class="card-text">Total Transaksi</p>
                    </div>
                    <div class="icon">
                        <i class="material-icons">local_mall</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            if (!empty($total_jumlah_transaksi['growth'])) {
                                $class = $total_jumlah_transaksi['growth'] > 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                                echo '<i class="fas ' . $class . '"></i>';
                            } else {
                                $total_jumlah_transaksi['growth'] = 0;
                            }
                            ?>
                        </div>
                        <p><?= $total_jumlah_transaksi['growth'] ? round($total_jumlah_transaksi['growth']) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= !empty($list_tahun) ? max($list_tahun) : '' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Income -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-warning shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title"><?= !empty($total_nilai_penjualan['jml']) ? format_number($total_nilai_penjualan['jml']) : 0 ?></h5>
                        <p class="card-text">Total Income</p>
                    </div>
                    <div class="icon">
                        <i class="material-icons">payments</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            if (!empty($total_nilai_penjualan['growth'])) {
                                $class = $total_nilai_penjualan['growth'] > 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                                echo '<i class="fas ' . $class . '"></i>';
                            } else {
                                $total_nilai_penjualan['growth'] = 0;
                            }
                            ?>
                        </div>
                        <p><?= $total_nilai_penjualan['growth'] ? round($total_nilai_penjualan['growth']) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= !empty($list_tahun) ? max($list_tahun) : '' ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Pelanggan Aktif -->
        <div class="col-lg-3 col-sm-6 col-xs-12 mb-4">
            <div class="card text-white bg-danger shadow">
                <div class="card-body card-stats">
                    <div class="description">
                        <h5 class="card-title"><?= !empty($total_pelanggan_aktif['jml']) ? format_number($total_pelanggan_aktif['jml']) : 0 ?></h5>
                        <p class="card-text">Total Pelanggan Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="material-icons">person</i>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="card-footer-left">
                        <div class="icon me-2">
                            <?php
                            if (!empty($total_pelanggan_aktif['growth'])) {
                                $class = $total_pelanggan_aktif['growth'] > 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                                echo '<i class="fas ' . $class . '"></i>';
                            } else {
                                $total_pelanggan_aktif['growth'] = 0;
                            }
                            ?>
                        </div>
                        <p><?= $total_pelanggan_aktif['growth'] ? round($total_pelanggan_aktif['growth']) . '%' : '-' ?></p>
                    </div>
                    <div class="card-footer-right">
                        <p><?= !empty($list_tahun) ? max($list_tahun) : '' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /row -->

    <div class="row">
        <!-- Penjualan Perbulan -->
        <div class="col-12 col-md-12 col-lg-12 col-xl-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-start">
                        <h6 class="card-title">Penjualan Perbulan</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div style="overflow: auto">
                        <canvas id="bar-container" style="min-width:500px;margin:auto;width:100%"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- Penjualan Petahun -->
        <div class="col-12 col-md-12 col-lg-12 col-xl-4 mb-4">
            <div class="card" style="height:100%">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Penjualan Petahun</h5>
                    </div>
                </div>
                <div class="card-body" style="display:flex">
                    <canvas id="chart-total-penjualan" style="margin:auto;max-width:350px;width:100%"></canvas>
                </div>
            </div>
        </div>
    </div><!-- /row summary charts -->

    <div class="row">
        <!-- Penjualan Barang Terbesar -->
        <div class="col-md-12 col-lg-8 mb-4">
            <div class="card" style="height:100%">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Penjualan Barang Terbesar</h5>
                    </div>
                    <div class="card-header-end">
                        <?php if (!empty($list_tahun)) {
                            echo '<form method="get" action="" class="d-flex">'
                                . options(['name' => 'tahun', 'id' => 'tahun-barang-terlaris'], $list_tahun, $tahun)
                                . '</form>';
                        } ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($penjualan)) {
                        echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
                    } else { ?>
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
                            $settings = [];
                            $settings['order'] = [4, 'desc'];
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
                            <table id="tabel-penjualan-terbesar" class="table display table-striped table-hover" style="width:100%">
                                <thead><tr><?= $th ?></tr></thead>
                                <tbody>
                                    <tr>
                                        <td colspan="<?= count($column) ?>" class="text-center">Loading data...</td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php
                            $column_dt = [];
                            foreach ($column as $key => $val) {
                                $column_dt[] = ['data' => $key];
                            }
                            ?>
                            <span id="penjualan-terbesar-column" style="display:none"><?= json_encode($column_dt) ?></span>
                            <span id="penjualan-terbesar-setting" style="display:none"><?= json_encode($settings) ?></span>
                            <span id="penjualan-terbesar-url" style="display:none"><?= current_url() . '/getDataDTPenjualanTerbesar?tahun=' . (!empty($list_tahun) ? max($list_tahun) : 0) ?></span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- Paling Banyak Terjual -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card" style="height:100%">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Paling Banyak Terjual</h5>
                    </div>
                    <div class="card-header-end">
                        <?php if (!empty($list_tahun)) {
                            echo '<form method="get" action="" class="d-flex">'
                                . options(['name' => 'tahun', 'id' => 'tahun-item-terjual'], $list_tahun, $tahun)
                                . '</form>';
                        } ?>
                    </div>
                </div>
                <div class="card-body" style="display:flex; justify-content: center; align-items: center;">
                    <div style="overflow: auto; width:100%">
                        <?php
                        if (!empty($penjualan)) {
                            echo '<canvas id="pie-container" style="margin:auto"></canvas>';
                        } else {
                            echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /row barang -->

    <div class="row">
        <!-- Kategori Terlaris -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card" style="height:100%">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Kategori Terlaris</h5>
                    </div>
                    <div class="card-header-end">
                        <?php if (!empty($list_tahun)) {
                            echo '<form method="get" action="" class="d-flex">'
                                . options(['name' => 'tahun', 'id' => 'tahun-kategori-terjual'], $list_tahun, $tahun)
                                . '</form>';
                        } ?>
                    </div>
                </div>
                <div class="card-body" style="display:flex; justify-content: center; align-items: center;">
                    <div style="overflow: auto; width:100%">
                        <?php
                        if (!empty($penjualan)) {
                            echo '<canvas id="chart-kategori" style="margin:auto"></canvas>';
                        } else {
                            echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Penjualan Terbesar Per Kategori -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Penjualan Terbesar</h5>
                    </div>
                    <div class="card-header-end">
                        <?php if (!empty($list_tahun)) {
                            echo '<form method="get" action="" class="d-flex">'
                                . options(['name' => 'tahun', 'id' => 'tahun-kategori-terjual-detail'], $list_tahun, $tahun)
                                . '</form>';
                        } ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    if (empty($penjualan)) {
                        echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
                    } else { ?>
                        <div class="table-responsive">
                            <table class="table table-border table-hover item">
                                <thead>
                                    <tr>
                                        <th colspan="2">Nama Kategori</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($kategori_terjual)) {
                                        foreach ($kategori_terjual as $val) {
                                            echo '<tr>
                                                <td><span class="text-warning h5"><i class="fas fa-folder"></i></span></td>
                                                <td>' . $val['nama_kategori'] . '</td>
                                                <td class="text-end">' . format_number($val['nilai']) . '</td>
                                            </tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- Item Terbaru -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card" style="height:100%">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Item Terbaru</h5>
                    </div>
                </div>
                <div class="card-body" style="display:flex">
                    <?php
                    if (empty($item_terbaru)) {
                        echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
                    } else { ?>
                        <div class="table-responsive">
                            <table class="table table-border table-hover item">
                                <tr>
                                    <th colspan="2">Nama Barang</th>
                                </tr>
                                <?php
                                foreach ($item_terbaru as $val) {
                                    $image = !empty($val['image']) ? $val['image'] : 'noimage.png';
                                    $nama_barang = $val['nama_barang'] ?? 'N/A';
                                    $harga_jual = $val['harga_jual'] ?? '0';
                                    echo '<tr>
                                            <td><img src="' . base_url() . '/public/images/produk/' . $image . '"/></td>
                                            <td>
                                                <div style="position:relative">
                                                    ' . htmlspecialchars($nama_barang) . '<span class="badge rounded-pill bg-primary" style="position:absolute; right: 5px">' . $harga_jual . '</span>
                                                </div>
                                            </td>
                                        </tr>';
                                }
                                ?>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div><!-- /row kategori dan baru -->

    <div class="row">
        <!-- Pelanggan Terbesar -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Pelanggan Terbesar</h5>
                    </div>
                    <div class="card-header-end">
                        <?php if (!empty($list_tahun)) {
                            echo '<form method="get" action="" class="d-flex">'
                                . options(['name' => 'tahun', 'id' => 'tahun-pelanggan-terbesar'], $list_tahun, $tahun)
                                . '</form>';
                        } ?>
                    </div>
                </div>
                <div class="card-body" style="display:flex">
                    <?php
                    if (empty($pelanggan_terbesar)) {
                        echo '<div class="alert alert-danger w-100">Data tidak ditemukan</div>';
                    } else { ?>
                        <div class="table-responsive">
                            <table class="table table-border table-hover">
                                <thead>
                                    <tr>
                                        <th colspan="2">Nama</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($pelanggan_terbesar as $val) {
                                        $foto = (!empty($val['foto']) && file_exists(ROOTPATH . '/public/images/pelanggan/' . $val['foto'])) ?
                                            $val['foto'] : 'noimage.png';
                                        echo '<tr>
                                                <td><img src="' . base_url() . '/public/images/pelanggan/' . $foto . '"></td>
                                                <td>' . ($val['nama_pelanggan'] ?: 'Umum') . '</td>
                                                <td class="text-end">' . format_number($val['total_harga']) . '</td>
                                            </tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <!-- Penjualan Terbaru -->
        <div class="col-md-12 col-lg-8 mb-4">
            <div class="card" style="height:100%">
                <div class="card-header">
                    <div class="card-header-start">
                        <h5 class="card-title">Penjualan Terbaru</h5>
                    </div>
                    <div class="card-header-end">
                        <?php if (!empty($list_tahun)) {
                            echo '<form method="get" action="" class="d-flex">'
                                . options(['name' => 'tahun', 'id' => 'tahun-penjualan-terbaru'], $list_tahun, $tahun)
                                . '</form>';
                        } ?>
                    </div>
                </div>
                <div class="card-body" style="display:flex">
                    <?php
                    if (empty($sales_terbaru)) {
                        echo '<div class="alert alert-danger w-100">Data tidak ditemukan</div>';
                    } else { ?>
                        <div class="table-responsive">
                            <table class="table table-border table-hover" id="penjualan-terbaru">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pembeli</th>
                                        <th>Jml. Item</th>
                                        <th>Nilai</th>
                                        <th>Tanggal Transaksi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($sales_terbaru as $row) {
                                        // Get customer name (from penjualanTerbaru method returns nama_pelanggan)
                                        $customer = isset($row['nama_pelanggan']) ? $row['nama_pelanggan'] : 'Umum';
                                        // Quantity (jumlah_item or jml_barang from penjualanTerbaru)
                                        $qty = isset($row['jumlah_item']) ? format_number($row['jumlah_item']) : 
                                               (isset($row['jml_barang']) ? format_number($row['jml_barang']) : '0');
                                        // Nilai (grand_total from sales)
                                        $nilai = isset($row['grand_total']) ? format_number($row['grand_total']) : '0';
                                        // Tanggal transaksi (created_at or tgl_transaksi from sales)
                                        $tanggal = '';
                                        if (!empty($row['created_at'])) {
                                            $tanggal = date('d-m-Y H:i', strtotime($row['created_at']));
                                        } elseif (!empty($row['tgl_transaksi'])) {
                                            $tanggal = date('d-m-Y H:i', strtotime($row['tgl_transaksi']));
                                        }
                                        // Status pembayaran
                                        $status = 'Belum Lunas';
                                        if (isset($row['payment_status'])) {
                                            if ($row['payment_status'] == '2') $status = 'Lunas';
                                            else if ($row['payment_status'] == '1') $status = 'Sebagian';
                                        }
                                        echo '<tr>
                                                <td>' . $no++ . '</td>
                                                <td>' . htmlspecialchars($customer) . '</td>
                                                <td class="text-end">' . $qty . '</td>
                                                <td class="text-end">' . $nilai . '</td>
                                                <td>' . $tanggal . '</td>
                                                <td>' . $status . '</td>
                                            </tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare chart data based on MariaDB structure
$data_penjualan = [];
$data_total_penjualan = [];
$label_kategori = [];
$jumlah_item_kategori = [];
$jumlah = [];
$nama = [];

if (!empty($penjualan)) {
    foreach ($penjualan as $tahun => $arr) {
        if (is_array($arr)) {
            foreach ($arr as $val) {
                if (isset($val['total'])) {
                    $data_penjualan[$tahun][] = floatval($val['total']);
                }
            }
        }
    }

    foreach ($total_penjualan as $tahun => $arr) {
        // getSeriesTotalPenjualan returns a single row array per year
        if (is_array($arr) && isset($arr['total'])) {
            $data_total_penjualan[$tahun] = floatval($arr['total']);
        } else {
            $data_total_penjualan[$tahun] = 0;
        }
    }
}

if (!empty($item_terjual) && is_array($item_terjual)) {
    foreach ($item_terjual as $val) {
        if (isset($val['jml']) && isset($val['nama_barang'])) {
            $jumlah[] = intval($val['jml']);
            $nama[] = $val['nama_barang'];
        }
    }
}

if (!empty($kategori_terjual) && is_array($kategori_terjual)) {
    foreach ($kategori_terjual as $val) {
        if (isset($val['nama_kategori']) && isset($val['jml'])) {
            $label_kategori[] = $val['nama_kategori'];
            $jumlah_item_kategori[] = intval($val['jml']);
        }
    }
}
?>

<script type="text/javascript">
let data_penjualan = <?=json_encode($data_penjualan)?>;
let total_penjualan = <?=json_encode($data_total_penjualan)?>;
let item_terjual = <?=json_encode($jumlah)?>;
let item_terjual_label = <?=json_encode($nama)?>;

// Helper from previous code
function dynamicColors() {
    var r = Math.floor(Math.random() * 255);
    var g = Math.floor(Math.random() * 255);
    var b = Math.floor(Math.random() * 255);
    return "rgba(" + r + "," + g + "," + b + ", 0.8)";
}

let label_kategori = <?=json_encode($label_kategori)?>;
let jumlah_item_kategori = <?=json_encode($jumlah_item_kategori)?>;
</script>