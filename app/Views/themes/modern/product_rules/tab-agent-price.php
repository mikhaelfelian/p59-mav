<div class="card">
    <div class="card-header">
        <h6 class="card-title mb-0">Harga Khusus Agen</h6>
    </div>
    <div class="card-body">
        <div id="agent-price-form" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Pilih Agen</label>
                <select name="user_id" class="form-control">
                    <option value="">Pilih Agen</option>
                    <?php if (!empty($agents ?? [])):
                        foreach ($agents as $ag): ?>
                            <option value="<?= $ag->id ?>"><?= esc($ag->code ?? 'NO_CODE') ?> -
                                <?= esc($ag->agent ?? $ag->name ?? '-') ?></option>
                        <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Harga Khusus Agen</label>
                <input type="text" name="agent_special_price" class="form-control price-format" placeholder="0">
            </div>
            <div class="col-md-4 align-self-end">
                <button type="button" class="btn btn-primary" id="btn-add-agent-price">Tambah Harga</button>
            </div>
            <input type="hidden" name="item_id" value="<?= esc($id) ?>">
        </div>

        <hr />
        <div id="agent-price-table" class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Agen</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>