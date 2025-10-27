document.addEventListener("DOMContentLoaded",()=>{
  const form=document.getElementById("warranty-form");
  const modal=document.getElementById("modal");
  const body=document.getElementById("modal-body");
  const guide=document.getElementById("guide");

  form?.addEventListener("submit",(e)=>{
    e.preventDefault();
    const data=new FormData(form);
    const plate=(data.get("plate")||"").toString().trim().toUpperCase();
    const phone=(data.get("phone")||"").toString().trim();
    const mockActive=Math.random()>0.4; // simulate
    body.innerHTML=`<p><strong>Nomor Plat:</strong> ${plate}<br><strong>Telepon:</strong> ${phone}</p>
      <p>Status Garansi: <strong style="color:${mockActive?"#25D366":"#ff6666"}">${mockActive?"Aktif":"Tidak ditemukan / Kedaluwarsa"}</strong></p>`;
    openModal();
  });

  guide?.addEventListener("click",()=>{
    body.innerHTML=`<ol><li>Kunjungi toko resmi terdekat.</li><li>Tunjukkan produk dan bukti kepemilikan.</li><li>Tim kami akan memverifikasi dan memproses klaim.</li></ol>`;
    openModal();
  });

  modal?.addEventListener("click",(e)=>{
    if(e.target instanceof HTMLElement && e.target.dataset.close!==undefined) closeModal();
  });

  function openModal(){ modal.classList.add("active"); modal.setAttribute("open","true"); }
  function closeModal(){ modal.classList.remove("active"); modal.removeAttribute("open"); }
});


