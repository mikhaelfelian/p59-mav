<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-07
 * Description: Dot matrix print view for sales receipt (continuous form paper)
 * Format: Standard dot matrix continuous form (80 characters width)
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nota - <?= esc($sale['invoice_no'] ?? '') ?></title>
    <style>
        @media print {
            @page {
                size: auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', 'Monaco', monospace;
            font-size: 12pt;
            line-height: 1.0;
            width: 80ch;
            max-width: 80ch;
            margin: 0 auto;
            padding: 0;
            background: white;
            color: black;
        }
        
        .receipt {
            width: 100%;
        }
        
        .line {
            white-space: pre;
            font-family: 'Courier New', monospace;
            font-size: 12pt;
            line-height: 1.0;
        }
        
        .center {
            text-align: center;
        }
        
        .right {
            text-align: right;
        }
        
        .divider {
            border-top: 1px solid #000;
            margin: 2px 0;
        }
        
        .divider-double {
            border-top: 2px solid #000;
            margin: 3px 0;
        }
        
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        
        .no-print button {
            padding: 10px 20px;
            font-size: 14px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 5px;
        }
        
        .no-print button:hover {
            background: #0056b3;
        }
        
        .cut-line {
            margin: 5px 0;
            text-align: center;
            font-size: 10pt;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print();">Cetak</button>
        <button onclick="window.close();">Tutup</button>
    </div>
    
    <div class="receipt">
        <pre class="line center"><?= str_pad(esc($config->appName ?? 'TOKO'), 80, ' ', STR_PAD_BOTH) ?></pre>
        <?php if (!empty($config->companyAddress)): ?>
        <pre class="line center"><?= str_pad(esc($config->companyAddress), 80, ' ', STR_PAD_BOTH) ?></pre>
        <?php endif; ?>
        <?php if (!empty($config->companyPhone)): ?>
        <pre class="line center"><?= str_pad('Telp: ' . esc($config->companyPhone), 80, ' ', STR_PAD_BOTH) ?></pre>
        <?php endif; ?>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <pre class="line"></pre>
        
        <pre class="line">No. Invoice: <?= str_pad(esc($sale['invoice_no'] ?? '-'), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <pre class="line">Tanggal    : <?= str_pad(!empty($sale['created_at']) ? date('d/m/Y H:i', strtotime($sale['created_at'])) : '-', 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php if (!empty($sale['user_name'])): ?>
        <pre class="line">Kasir      : <?= str_pad(esc($sale['user_name']), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php endif; ?>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <pre class="line"></pre>
        
        <?php if (!empty($sale['customer_name'])): ?>
        <pre class="line">PELANGGAN</pre>
        <pre class="line">Nama       : <?= str_pad(esc($sale['customer_name']), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php if (!empty($sale['plat_code']) && !empty($sale['plat_number'])): ?>
        <pre class="line">Plat       : <?= str_pad(esc($sale['plat_code']) . '-' . esc($sale['plat_number']) . (!empty($sale['plat_last']) ? '-' . esc($sale['plat_last']) : ''), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php endif; ?>
        <?php if (!empty($sale['customer_phone'])): ?>
        <pre class="line">Telp       : <?= str_pad(esc($sale['customer_phone']), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php endif; ?>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <pre class="line"></pre>
        <?php endif; ?>
        
        <?php if (!empty($sale['agent_name'])): ?>
        <pre class="line">Agen       : <?= str_pad(esc($sale['agent_name']), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <pre class="line"></pre>
        <?php endif; ?>
        
        <pre class="line">ITEM</pre>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $index => $item): ?>
                <?php
                $itemName = esc($item['item'] ?? $item['item_name'] ?? '-');
                $qty = esc($item['qty'] ?? $item['quantity'] ?? '0');
                $price = number_format($item['price'] ?? 0, 0, ',', '.');
                $amount = number_format($item['amount'] ?? $item['subtotal'] ?? 0, 0, ',', '.');
                $disc = !empty($item['disc']) || !empty($item['discount']) ? number_format($item['disc'] ?? $item['discount'] ?? 0, 0, ',', '.') : 0;
                
                // Item name (max 50 chars, wrap if needed)
                $itemNameLines = str_split($itemName, 50);
                foreach ($itemNameLines as $nameLine) {
                    echo '<pre class="line">' . str_pad($nameLine, 80, ' ') . '</pre>';
                }
                
                // Qty, Price, Amount
                $qtyPriceLine = $qty . 'x @ ' . $price;
                $amountLine = str_pad($amount, 20, ' ', STR_PAD_LEFT);
                echo '<pre class="line">' . str_pad($qtyPriceLine, 60, ' ') . $amountLine . '</pre>';
                
                // Discount if exists
                if ($disc > 0) {
                    echo '<pre class="line">' . str_pad('Diskon: ' . $disc, 80, ' ', STR_PAD_LEFT) . '</pre>';
                }
                
                // Serial numbers
                if (!empty($item['sns'])) {
                    foreach ($item['sns'] as $sn) {
                        $snValue = esc($sn['sn'] ?? '');
                        echo '<pre class="line">  SN: ' . str_pad($snValue, 76, ' ') . '</pre>';
                    }
                }
                echo '<pre class="line"></pre>';
            endforeach; ?>
        <?php else: ?>
            <pre class="line center"><?= str_pad('Tidak ada item', 80, ' ', STR_PAD_BOTH) ?></pre>
        <?php endif; ?>
        <pre class="line"><?= str_repeat('=', 80) ?></pre>
        <pre class="line"></pre>
        
        <pre class="line">Subtotal   : <?= str_pad(number_format($sale['total_amount'] ?? $sale['subtotal'] ?? 0, 0, ',', '.'), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php if (!empty($sale['discount_amount']) && $sale['discount_amount'] > 0): ?>
        <pre class="line">Diskon     : <?= str_pad(number_format($sale['discount_amount'] ?? $sale['discount'] ?? 0, 0, ',', '.'), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php endif; ?>
        <?php if (!empty($sale['tax_amount']) && $sale['tax_amount'] > 0): ?>
        <pre class="line">Pajak      : <?= str_pad(number_format($sale['tax_amount'] ?? $sale['tax'] ?? 0, 0, ',', '.'), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php endif; ?>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <pre class="line">TOTAL      : <?= str_pad('Rp ' . number_format($sale['grand_total'] ?? 0, 0, ',', '.'), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <pre class="line"><?= str_repeat('=', 80) ?></pre>
        <pre class="line"></pre>
        
        <?php if (!empty($payment)): ?>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <pre class="line">PEMBAYARAN</pre>
        <?php
        $methodLabels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'credit' => 'Kredit',
            'other' => 'Lainnya'
        ];
        $methodLabel = $methodLabels[$payment['method'] ?? 'other'] ?? 'Lainnya';
        ?>
        <pre class="line">Metode     : <?= str_pad(esc($methodLabel), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php if (!empty($payment['platform_name'])): ?>
        <pre class="line">Platform   : <?= str_pad(esc($payment['platform_name']), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        <?php endif; ?>
        <pre class="line">Jumlah     : <?= str_pad('Rp ' . number_format($payment['amount'] ?? 0, 0, ',', '.'), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
        
        <?php if (!empty($gatewayResponse)): ?>
            <?php
            $status = strtoupper($gatewayResponse['status'] ?? 'UNKNOWN');
            ?>
            <pre class="line">Status     : <?= str_pad(esc($status), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
            <?php if (!empty($gatewayResponse['paymentCode'])): ?>
            <pre class="line">Kode       : <?= str_pad(esc($gatewayResponse['paymentCode']), 80 - 13, ' ', STR_PAD_LEFT) ?></pre>
            <?php endif; ?>
        <?php endif; ?>
        <pre class="line"><?= str_repeat('-', 80) ?></pre>
        <pre class="line"></pre>
        <?php endif; ?>
        
        <pre class="line center"><?= str_pad('Terima Kasih Atas Kunjungan Anda', 80, ' ', STR_PAD_BOTH) ?></pre>
        <pre class="line center"><?= str_pad('Barang yang sudah dibeli tidak dapat ditukar/dikembalikan', 80, ' ', STR_PAD_BOTH) ?></pre>
        <pre class="line"></pre>
        <pre class="line center"><?= str_pad(date('d/m/Y H:i:s'), 80, ' ', STR_PAD_BOTH) ?></pre>
        <pre class="line"></pre>
        
        <pre class="line cut-line"><?= str_pad(str_repeat('-', 40), 80, ' ', STR_PAD_BOTH) ?></pre>
    </div>
    
    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>
