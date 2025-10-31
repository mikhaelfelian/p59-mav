<?php
// Expect $items and $id (current item id)
?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold text-primary">
        Aturan Promo Produk (Buy X Get Y)
    </div>
    <div class="card-body">
        <div id="productRuleForm">
            <div class="col-md-6">
                <label class="form-label">Produk Bonus</label>
                <select name="bonus_item_id" class="form-select">
                    <option value="">-- Pilih Produk Bonus --</option>
                    <?php foreach (($items ?? []) as $i): $iid = is_object($i)?$i->id:($i['id']??null); $iname = is_object($i)?$i->name:($i['name']??''); if ($iid == ($id ?? null)) continue; ?>
                        <option value="<?= $iid; ?>"><?= esc($iname); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jumlah Minimum</label>
                <input type="number" name="min_qty" class="form-control" placeholder="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Jumlah Bonus</label>
                <input type="number" name="bonus_qty" class="form-control" placeholder="0">
            </div>
            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="is_multiple" id="is_multiple" value="1">
                    <label class="form-check-label" for="is_multiple">Boleh Kelipatan</label>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan untuk aturan promo ini..."></textarea>
            </div>
            <!-- BUTTON GROUP -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-light border" id="btn-reset-promo">Bersihkan</button>
                <button type="button" class="btn btn-primary px-4" id="btn-save-promo">Simpan Aturan</button>
            </div>
            <input type="hidden" name="item_id" value="<?= esc($id) ?>">
        </div>

        <!-- RULES TABLE -->
        <div class="table-responsive mt-4">
            <table class="table table-striped align-middle" id="promo-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Produk Bonus</th>
                        <th>Min Qty</th>
                        <th>Bonus Qty</th>
                        <th>Kelipatan</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- BOTTOM SAVE BUTTON -->
        <div class="text-end mt-4">
            <button type="button" class="btn btn-success px-4" id="btnSaveAllPromo">Simpan Semua</button>
        </div>
    </div>
</div>

<script>
function loadPromo(){
  fetch('<?= $config->baseURL ?>product-promo/list/<?= $id ?>', {headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
    .then(async r=>{
      const ct = r.headers.get('content-type')||'';
      if(!ct.includes('application/json')) { return { status:'error', data: [] }; }
      return r.json();
    }).then(res=>{
      const tbody = document.querySelector('#promo-table tbody');
      tbody.innerHTML = '';
      if(res.status==='success'){
        res.data.forEach((row,idx)=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${idx+1}</td>
            <td>${row.bonus_name ?? '-'}</td>
            <td>${row.min_qty}</td>
            <td>${row.bonus_qty}</td>
            <td>${row.is_multiple=='1'?'Ya':'Tidak'}</td>
            <td>${row.start_date ?? ''} - ${row.end_date ?? ''}</td>
            <td><span class="badge ${row.status==='active'?'bg-success':'bg-secondary'}">${(row.status||'').toUpperCase()}</span></td>
            <td class="text-center"><button class="btn btn-danger btn-sm" onclick="deletePromo(${row.id})"><i class="fa fa-trash"></i></button></td>`;
          tbody.appendChild(tr);
        })
      }
    });
}
function deletePromo(id){
  if(!confirm('Hapus aturan ini?')) return;
  fetch('<?= $config->baseURL ?>product-promo/delete/'+id, {method:'POST', headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
    .then(()=> loadPromo())
    .catch(()=> loadPromo());
}
function submitPromoForm(){
  const form = document.getElementById('productRuleForm');
  if(!form) return;
  const fd = new FormData();
  const inputs = form.querySelectorAll('input, select, textarea');
  inputs.forEach(inp => {
    if(inp.name && !inp.disabled){
      if(inp.type === 'checkbox') {
        if(inp.checked) fd.append(inp.name, inp.value);
      } else {
        fd.append(inp.name, inp.value || '');
      }
    }
  });
  fetch('<?= $config->baseURL ?>product-promo/save', {method:'POST', body: fd, headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
    .then(()=>{ 
      inputs.forEach(inp => {
        if(inp.type !== 'hidden' && inp.type !== 'checkbox') inp.value = '';
        if(inp.type === 'checkbox') inp.checked = false;
      });
      loadPromo(); 
    })
    .catch(()=>{ loadPromo(); });
}
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('productRuleForm');
  const btnSave = document.getElementById('btn-save-promo');
  const btnReset = document.getElementById('btn-reset-promo');
  const btnAll = document.getElementById('btnSaveAllPromo');
  
  if(btnSave){
    btnSave.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      submitPromoForm();
    });
  }
  if(btnReset){
    btnReset.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
      inputs.forEach(inp => {
        if(inp.type === 'checkbox') inp.checked = false;
        else inp.value = '';
      });
    });
  }
  if(btnAll){
    btnAll.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      submitPromoForm();
    });
  }
  loadPromo();
});
</script>