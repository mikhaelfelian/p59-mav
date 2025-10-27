document.addEventListener("DOMContentLoaded",()=>{
  const map=L.map("map",{zoomControl:true}).setView([-2.5,117.5],5);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:"&copy; OpenStreetMap"}).addTo(map);
  const icon=L.icon({iconUrl:"https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-yellow.png",shadowUrl:"https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],shadowSize:[41,41]});

  const stores=[
    {name:"Beebot Asia",lat:-6.256,lon:106.653,city:"Tangerang Selatan",distance:"6.08 km"},
    {name:"Fit Audio BSD",lat:-6.303,lon:106.654,city:"BSD",distance:"10.2 km"},
    {name:"Nurmaras Autolamp",lat:-6.173,lon:106.83,city:"Jakarta",distance:"29.4 km"},
  ];

  const ul=document.getElementById("stores");
  stores.forEach(s=>{
    const li=document.createElement("li");
    li.className="store";
    li.innerHTML=`<div class="name">${s.name}</div><div class="meta">${s.distance} • ${s.city}</div>`;
    li.addEventListener("click",()=>{map.setView([s.lat,s.lon],12)});
    ul.appendChild(li);
    L.marker([s.lat,s.lon],{icon}).addTo(map).bindPopup(`<b>${s.name}</b><br>${s.city}`);
  });
});


