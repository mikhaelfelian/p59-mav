<?php helper('form'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= esc($title ?? 'Aturan Agen') ?></h5>
    </div>
    <div class="card-body">
        <form method="post" action="<?= $config->baseURL ?>agent-rules/save">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Agen</label>
                    <select name="agent_id" class="form-control" required>
                        <option value="">Pilih Agen</option>
                        <?php foreach (($agents ?? []) as $a): ?>
                        <option value="<?= $a->id ?>" <?= (!empty($rule) && $rule->agent_id == $a->id) ? 'selected' : '' ?>>
                            <?= esc($a->code ?? 'NO_CODE') ?> - <?= esc($a->name ?? '-') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis Aturan</label>
                    <select name="rule_type" class="form-control" required>
                        <option value="cashback" <?= (!empty($rule) && $rule->rule_type=='cashback') ? 'selected' : '' ?>>Cashback</option>
                        <option value="akumulasi" <?= (!empty($rule) && $rule->rule_type=='akumulasi') ? 'selected' : '' ?>>Akumulasi</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Minimal Total Pembelian</label>
                    <input type="text" name="min_transaction" class="form-control price-format" value="<?= set_value('min_transaction', $rule->min_transaction ?? '') ?>" placeholder="0" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nominal Cashback</label>
                    <input type="text" name="cashback_amount" class="form-control price-format" value="<?= set_value('cashback_amount', $rule->cashback_amount ?? '') ?>" placeholder="0" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="<?= set_value('start_date', $rule->start_date ?? '') ?>" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="<?= set_value('end_date', $rule->end_date ?? '') ?>" />
                </div>
                <div class="col-md-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="3"><?= set_value('notes', $rule->notes ?? '') ?></textarea>
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= (!empty($rule) && $rule->is_active=='1') ? 'checked' : '' ?>>
                        <label class="form-check-label">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="<?= $config->baseURL ?>agent-rules" class="btn btn-outline-secondary">Batal</a>
            </div>
            <?php if (!empty($rule->id)): ?>
            <input type="hidden" name="id" value="<?= $rule->id ?>" />
            <?php endif; ?>
        </form>
    </div>
</div>
