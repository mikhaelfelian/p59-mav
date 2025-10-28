<?= $this->extend('themes/mav/layout/main') ?>

<?= $this->section('content') ?>

<div class="section">
  <div class="container warranty">
    <div class="card form-card">
      <h1>Cek Garansi</h1>
      <p>Masukkan nomor plat Anda untuk memeriksa status garansi produk Anda</p>
      
      <?php if (!empty($msg)): ?>
        <div class="alert alert-info">
          <?= esc($msg) ?>
        </div>
      <?php endif; ?>
      
      <form id="warranty-form" class="form" action="<?= base_url('cek-garansi') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="ajax" value="1">
        
        <label class="input" for="plate">
          <span class="icon">#</span>
          <input id="plate" name="plate" placeholder="Masukkan nomor plat" required>
        </label>
        
        <label class="input" for="phone">
          <span class="icon">📞</span>
          <input id="phone" name="phone" type="tel" placeholder="Masukkan nomor telepon Anda" required>
        </label>
        
        <button class="btn btn-amber btn-lg" type="submit">Cek Garansi</button>
      </form>
      
      <div class="muted">Kami menghormati privasi Anda. Informasi Anda hanya digunakan untuk memeriksa status garansi.</div>
      
      <div class="actions-inline">
        <button class="btn btn-outline" id="guide">Tata Cara Klaim Garansi</button>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-backdrop" data-close></div>
  <div class="modal-card" role="document">
    <button class="modal-close" aria-label="Tutup" data-close>×</button>
    <h3>Hasil Pemeriksaan Garansi</h3>
    <div id="modal-body"></div>
  </div>
</div>

<style>
.warranty {
  max-width: 500px;
  margin: 0 auto;
  padding: 2rem 0;
}

.form-card {
  padding: 2rem;
  background: #141416;
  border: 1px solid #222327;
  border-radius: 12px;
}

.form-card h1 {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  color: #e7e7ea;
}

.form-card p {
  color: #7b7d86;
  margin-bottom: 2rem;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.input {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: #1b1b1d;
  border: 1px solid #222327;
  border-radius: 8px;
  color: #e7e7ea;
}

.input .icon {
  font-size: 1.25rem;
  color: #7b7d86;
}

.input input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: #e7e7ea;
  font-family: inherit;
}

.input input::placeholder {
  color: #4c4e52;
}

.btn-lg {
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
}

.muted {
  margin-top: 1rem;
  font-size: 0.875rem;
  color: #7b7d86;
  text-align: center;
}

.actions-inline {
  margin-top: 1.5rem;
  display: flex;
  justify-content: center;
}

.btn-outline {
  background: transparent;
  border: 1px solid #222327;
  color: #e7e7ea;
  padding: 0.625rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #1b1b1d;
  border-color: #ffc12e;
  color: #ffc12e;
}

.modal {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: none;
  align-items: center;
  justify-content: center;
}

.modal[aria-hidden="false"] {
  display: flex;
}

.modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
}

.modal-card {
  position: relative;
  background: #141416;
  border: 1px solid #222327;
  border-radius: 12px;
  padding: 2rem;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  color: #e7e7ea;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: transparent;
  border: none;
  color: #7b7d86;
  font-size: 2rem;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #1b1b1d;
  color: #e7e7ea;
}

.modal-card h3 {
  margin: 0 0 1.5rem 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-body {
  line-height: 1.6;
}

#modal-body {
  margin-top: 1rem;
}

.alert {
  padding: 0.875rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.alert-info {
  background: #1a2a3a;
  border: 1px solid #2a4a6a;
  color: #7bb3ff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('warranty-form');
  const modal = document.getElementById('modal');
  const modalBody = document.getElementById('modal-body');
  const modalClose = document.querySelectorAll('[data-close]');
  
  // Form submission
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(form);
    const plate = formData.get('plate');
    const phone = formData.get('phone');
    
    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: formData
      });
      
      const result = await response.json();
      
      // Simulate warranty check result (replace with actual API call)
      let warrantyData = {
        status: 'found',
        plate: plate,
        phone: phone,
        warrantyExpiry: '2025-12-31',
        product: 'Multi Automobile Vision - Premium Package',
        registeredDate: '2024-01-15'
      };
      
      displayWarrantyResult(warrantyData);
    } catch (error) {
      console.error('Error checking warranty:', error);
      modalBody.innerHTML = '<div style="color: #ff6b6b;"><strong>Error:</strong> Terjadi kesalahan saat memeriksa garansi. Silakan coba lagi.</div>';
      openModal();
    }
  });
  
  // Open modal
  function openModal() {
    modal.setAttribute('aria-hidden', 'false');
  }
  
  // Close modal
  modalClose.forEach(btn => {
    btn.addEventListener('click', function() {
      modal.setAttribute('aria-hidden', 'true');
    });
  });
  
  // Click outside to close
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.setAttribute('aria-hidden', 'true');
    }
  });
  
  // Display warranty result
  function displayWarrantyResult(data) {
    let html = '';
    
    if (data.status === 'found') {
      html = `
        <div style="color: #4ade80;">
          <strong>✓ Garansi Aktif</strong>
        </div>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #222327;">
          <p><strong>Produk:</strong> ${data.product || 'Multi Automobile Vision'}</p>
          <p><strong>Nomor Plat:</strong> ${data.plate}</p>
          <p><strong>Tanggal Registrasi:</strong> ${data.registeredDate || 'N/A'}</p>
          <p><strong>Masa Garansi Berakhir:</strong> <span style="color: #4ade80;">${data.warrantyExpiry || 'N/A'}</span></p>
        </div>
      `;
    } else if (data.status === 'expired') {
      html = `
        <div style="color: #ffc12e;">
          <strong>⚠ Garansi Kedaluwarsa</strong>
        </div>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #222327;">
          <p><strong>Nomor Plat:</strong> ${data.plate}</p>
          <p>Garansi Anda telah berakhir pada: ${data.warrantyExpiry}</p>
        </div>
      `;
    } else {
      html = `
        <div style="color: #ff6b6b;">
          <strong>✗ Garansi Tidak Ditemukan</strong>
        </div>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #222327;">
          <p>Maaf, kami tidak menemukan data garansi untuk nomor plat <strong>${data.plate}</strong>.</p>
          <p style="margin-top: 1rem;">Pastikan nomor plat dan nomor telepon yang Anda masukkan sudah benar.</p>
        </div>
      `;
    }
    
    modalBody.innerHTML = html;
    openModal();
  }
  
  // Guide button
  const guideBtn = document.getElementById('guide');
  if (guideBtn) {
    guideBtn.addEventListener('click', function() {
      alert('Tata Cara Klaim Garansi:\n\n1. Pastikan produk Anda masih dalam masa garansi\n2. Bawa produk ke agen terdekat\n3. Tunjukkan nomor plat dan nomor telepon registrasi\n4. Tim kami akan memproses klaim Anda');
    });
  }
});
</script>

<?= $this->endSection() ?>

