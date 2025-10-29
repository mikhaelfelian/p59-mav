<?php
/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-28
 * Github: github.com/mikhaelfelian
 * description: Single product rule configuration form (not multiple rules)
 * This file represents the View for product-rule-form.
 */
?>
<div class="card shadow-sm p-4">
    <h5 class="fw-bold mb-4">Product Rule</h5>

    <?php
    // Determine rule type for display
    $ruleType = $rule['rule_type'] ?? '';
    $isActive = $rule['is_active'] ?? '1';
    ?>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Rule Type</label>
            <select name="rule_type" id="ruleType" class="form-select">
                <option value="">-- Select Rule Type --</option>
                <option value="cashback" <?= set_select('rule_type', 'cashback', $ruleType == 'cashback') ?>>Cashback (Accumulative)</option>
                <option value="buy_get" <?= set_select('rule_type', 'buy_get', $ruleType == 'buy_get') ?>>Buy X Get Y</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Status</label>
            <select name="rule_is_active" class="form-select">
                <option value="1" <?= set_select('rule_is_active', '1', $isActive == '1') ?>>Active</option>
                <option value="0" <?= set_select('rule_is_active', '0', $isActive == '0') ?>>Inactive</option>
            </select>
        </div>
    </div>

    <!-- Cashback Fields -->
    <div id="cashbackFields" style="display:<?= ($ruleType == 'cashback') ? 'block' : 'none' ?>;">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Threshold Amount (Rp)</label>
                <input type="number" name="rule_threshold_amount" step="0.01" class="form-control" 
                       value="<?= set_value('rule_threshold_amount', $rule['threshold_amount'] ?? '') ?>" 
                       placeholder="0.00">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Cashback Amount (Rp)</label>
                <input type="number" name="rule_cashback_amount" step="0.01" class="form-control" 
                       value="<?= set_value('rule_cashback_amount', $rule['cashback_amount'] ?? '') ?>" 
                       placeholder="0.00">
            </div>
        </div>
    </div>

    <!-- Buy X Get Y Fields -->
    <div id="buyGetFields" style="display:<?= ($ruleType == 'buy_get') ? 'block' : 'none' ?>;">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Minimum Qty</label>
                <input type="number" name="rule_min_qty" class="form-control" 
                       value="<?= set_value('rule_min_qty', $rule['min_qty'] ?? '') ?>" 
                       placeholder="0" min="1">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Bonus Item</label>
                <select name="rule_bonus_item_id" class="form-select select2">
                    <option value="">-- Select Bonus Item --</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item->id ?>" <?= set_select('rule_bonus_item_id', $item->id, isset($rule['bonus_item_id']) && $rule['bonus_item_id'] == $item->id) ?>>
                            <?= esc($item->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Bonus Qty</label>
                <input type="number" name="rule_bonus_qty" class="form-control" 
                       value="<?= set_value('rule_bonus_qty', $rule['bonus_qty'] ?? '') ?>" 
                       placeholder="0" min="1">
            </div>
        </div>
        <div class="form-check mt-2">
            <input type="checkbox" name="rule_is_multiple" value="1" class="form-check-input" 
                   <?= set_checkbox('rule_is_multiple', '1', isset($rule['is_multiple']) && $rule['is_multiple'] == '1') ?>>
            <label class="form-check-label fw-semibold">Apply for multiples</label>
        </div>
    </div>

    <div class="mt-4">
        <label class="form-label fw-semibold">Notes</label>
        <textarea name="rule_notes" rows="3" class="form-control" 
                  placeholder="Optional notes for this product rule"><?= set_value('rule_notes', $rule['notes'] ?? '') ?></textarea>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ruleType = document.getElementById('ruleType');
        const cashback = document.getElementById('cashbackFields');
        const buyget = document.getElementById('buyGetFields');

        if (ruleType) {
            ruleType.addEventListener('change', function(e) {
                const value = e.target.value;
                cashback.style.display = (value === 'cashback') ? 'block' : 'none';
                buyget.style.display = (value === 'buy_get') ? 'block' : 'none';
            });
        }
    });
    </script>
</div>

