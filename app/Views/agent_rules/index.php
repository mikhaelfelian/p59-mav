<?php if (!isset($current_module)) { $current_module = null; } ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Aturan Cashback/Akumulasi Agen</h5>
        <a href="<?= $config->baseURL ?>agent-rules/form" class="btn btn-sm btn-primary">Tambah Aturan</a>
    </div>
    <div class="card-body">
        <?php if (!empty($message)) show_message($message); ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Agen</th>
                        <th>Jenis</th>
                        <th>Min. Transaksi</th>
                        <th>Nominal Cashback</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $no=1; foreach (($rules ?? []) as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($r->agent_code ?? '') ?> <?= esc($r->agent_name ?? '-') ?></td>
                        <td><span class="badge bg-secondary text-uppercase"><?= esc($r->rule_type) ?></span></td>
                        <td>Rp <?= number_format($r->min_transaction, 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($r->cashback_amount, 0, ',', '.') ?></td>
                        <td><?= esc($r->start_date) ?> s/d <?= esc($r->end_date) ?></td>
                        <td><?= $r->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="<?= $config->baseURL ?>agent-rules/form/<?= $r->id ?>">Ubah</a>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $r->id ?>">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e){
    if(e.target.closest('.btn-delete')){
        const id = e.target.closest('.btn-delete').dataset.id;
        if(confirm('Hapus aturan ini?')){
            fetch('<?= $config->baseURL ?>agent-rules/delete/' + id, {method:'POST'})
                .then(r=>r.json()).then(res=>{ location.reload(); });
        }
    }
});
</script>


