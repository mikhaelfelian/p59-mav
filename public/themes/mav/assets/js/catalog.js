document.addEventListener("DOMContentLoaded",()=>{
  const grid=document.getElementById("catalog-grid");
  const products=[
    {name:"GP Cyclops AFS",price:"IDR 6,000,000",image:"https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=1200&q=80&auto=format&fit=crop"},
    {name:"MK 1 PRO",price:"IDR 1,500,000",image:"https://images.unsplash.com/photo-1523961131990-5ea7c61b2107?w=1200&q=80&auto=format&fit=crop"},
    {name:"GR-30",price:"IDR 600,000",image:"https://images.unsplash.com/photo-1518779578993-ec3579fee39f?w=1200&q=80&auto=format&fit=crop"},
    {name:"GR 2.0 3 Color",price:"IDR 850,000",image:"https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80&auto=format&fit=crop"},
    {name:"FM 5 Mode",price:"IDR 1,050,000",image:"https://images.unsplash.com/photo-1520975916090-3105956dac38?w=1200&q=80&auto=format&fit=crop"},
    {name:"GP 5500K",price:"IDR 750,000",image:"https://images.unsplash.com/photo-1517059224940-d4af9eec41e5?w=1200&q=80&auto=format&fit=crop"}
  ];
  products.forEach(p=>{
    const card=document.createElement("article");
    card.className="card";
    card.innerHTML=`<img src="${p.image}" alt="${p.name}" loading="lazy"/><div class="card-body"><div>${p.name}</div><div class="price">${p.price}</div></div>`;
    grid.appendChild(card);
  });
});


