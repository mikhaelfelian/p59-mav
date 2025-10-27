document.addEventListener("DOMContentLoaded",()=>{
  const toggle=document.querySelector(".nav-toggle");
  const menu=document.querySelector("#nav-menu");
  if(toggle&&menu){
    toggle.addEventListener("click",()=>{
      const open=menu.classList.toggle("open");
      toggle.setAttribute("aria-expanded",String(open));
    });
  }
});


