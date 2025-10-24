<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-23
 * Github: github.com/mikhaelfelian
 * Description: Form for bulk editing agent prices (item-agent)
 * This file represents the modern bulk agent price edit view.
 */
helper('form');
helper('html');
$isModal = $isModal ?? false;
?>
<?php if (!$isModal): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Input Harga Agen</h5>
        </div>
        <div class="card-body">
        <?php endif; ?>

        <?php if (!empty($message))
            show_message($message); ?>

        <div class="mb-3 d-flex flex-wrap gap-2">
            <a href="<?= $config->baseURL ?>item-agent/add" class="btn btn-success btn-xs btn-add">
                <i class="fa fa-plus pe-1"></i> Tambah
            </a>
            <a href="#" class="btn btn-light btn-xs"><i class="fa fa-upload pe-1"></i> Upload</a>
            <a href="<?= $config->baseURL ?>item-agent" class="btn btn-light btn-xs"><i
                    class="fa fa-dollar-sign pe-1"></i> Daftar Harga</a>
        </div>

        <form method="post" action="<?= $form_action ?? $config->baseURL . 'item-agent/store' ?>"
            id="form-item-agent">
            
            <!-- Agent Selection -->
            <div class="mb-3 row align-items-center">
                <label for="agent_id" class="col-auto col-form-label fw-bold">Agen:</label>
                <div class="col-sm-4">
                    <select name="agent_id" id="agent_id" class="form-control" required>
                        <option value="">Pilih Agen</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent['id'] ?>" <?= (isset($agent_id) && $agent_id == $agent['id']) ? 'selected' : '' ?>>
                                <?= $agent['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr/>

            <!-- Dynamic Input Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Produk</th>
                            <th width="15%">Harga Default</th>
                            <th width="15%">Harga Agen</th>
                            <th width="10%">Status</th>
                            <th width="20%">Catatan</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="form-container">
                        <?php
                        if (empty($_POST['item_id'])) {
                            $_POST['item_id'][0] = '';
                            $_POST['agent_price'][0] = '';
                            $_POST['is_active'][0] = '1';
                            $_POST['notes'][0] = '';
                        }
                        
                        foreach ($_POST['item_id'] as $key => $val) {
                            $btn_icon = $key == 0 ? 'fa-plus' : 'fa-times';
                            $btn_add = $key == 0 ? 'id="add-row"' : '';
                            $btn_remove = $key == 0 ? '' : 'delete-row';
                            $btn_color = $key == 0 ? 'btn-success' : 'btn-danger';
                            
                            // Get item details for display
                            $item_name = '';
                            $item_price = '';
                            if (!empty($val)) {
                                foreach ($items as $item) {
                                    if ($item->id == $val) {
                                        $item_name = $item->name . ' - ' . $item->brand_name . ' - ' . $item->category_name;
                                        $item_price = number_format($item->price, 0, ',', '.');
                                        break;
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td class="text-center"><?= $key + 1 ?></td>
                                <td>
                                    <select name="item_id[]" class="form-control item-select" required>
                                        <option value="">Pilih Produk</option>
                                        <?php foreach ($items as $item): ?>
                                            <option value="<?= $item->id ?>" <?= ($val == $item->id) ? 'selected' : '' ?>>
                                                <?= $item->name ?> - <?= $item->brand_name ?> - <?= $item->category_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="text-end">
                                    <input type="text" class="form-control text-end" value="<?= $item_price ?>" readonly>
                                </td>
                                <td>
                                    <input type="text" name="agent_price[]" class="form-control text-end agent-price-input" 
                                           value="<?= set_value('agent_price['.$key.']', '') ?>" 
                                           placeholder="Harga Agen" required>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active[]" 
                                               value="1" <?= (set_value('is_active['.$key.']', '1') == '1') ? 'checked' : '' ?>>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="notes[]" class="form-control" 
                                           value="<?= set_value('notes['.$key.']', '') ?>" 
                                           placeholder="Catatan">
                                </td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" <?= $btn_add ?> class="btn btn-sm <?= $btn_color ?> <?= $btn_remove ?>">
                                        <i class="fas <?= $btn_icon ?>"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan Semua</button>
                <a href="<?= $config->baseURL ?>item-agent" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>

        <?php if (!$isModal): ?>
        </div>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Add row functionality
    $(document).on('click', '#add-row', function() {
        var rowCount = $('#form-container tr').length;
        var newRow = `
            <tr>
                <td class="text-center">${rowCount + 1}</td>
                <td>
                    <select name="item_id[]" class="form-control item-select" required>
                        <option value="">Pilih Produk</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item->id ?>"><?= $item->name ?> - <?= $item->brand_name ?> - <?= $item->category_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="text-end">
                    <input type="text" class="form-control text-end" readonly>
                </td>
                <td>
                    <input type="text" name="agent_price[]" class="form-control text-end agent-price-input" 
                           placeholder="Harga Agen" required>
                </td>
                <td class="text-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active[]" value="1" checked>
                    </div>
                </td>
                <td>
                    <input type="text" name="notes[]" class="form-control" placeholder="Catatan">
                </td>
                <td class="text-center">
                    <a href="javascript:void(0)" class="btn btn-sm btn-danger delete-row">
                        <i class="fas fa-times"></i>
                    </a>
                </td>
            </tr>
        `;
        $('#form-container').append(newRow);
        updateRowNumbers();
    });

    // Delete row functionality
    $(document).on('click', '.delete-row', function() {
        $(this).closest('tr').remove();
        updateRowNumbers();
    });

    // Update row numbers
    function updateRowNumbers() {
        $('#form-container tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Handle item selection change
    $(document).on('change', '.item-select', function() {
        var selectedItemId = $(this).val();
        var row = $(this).closest('tr');
        var priceInput = row.find('td:nth-child(4) input');
        
        if (selectedItemId) {
            // Find the item price from the options
            var itemPrice = '';
            <?php foreach ($items as $item): ?>
                if (selectedItemId == '<?= $item->id ?>') {
                    itemPrice = '<?= number_format($item->price, 0, ',', '.') ?>';
                }
            <?php endforeach; ?>
            row.find('td:nth-child(3) input').val(itemPrice);
        } else {
            row.find('td:nth-child(3) input').val('');
        }
    });

    // Format price input
    $(document).on('keyup', '.agent-price-input', function() {
        let val = $(this).val();
        val = val.replace(/\./g, ''); // Remove existing dots
        val = val.replace(/[^0-9]/g, ''); // Keep only numbers
        if (val) {
            $(this).val(new Intl.NumberFormat('id-ID').format(val));
        }
    });
});
</script>