document.addEventListener("DOMContentLoaded",function(){const n=Array.from(document.querySelectorAll(".card-collapsible")),i=Array.from(document.querySelectorAll(".card-expand")),u=Array.from(document.querySelectorAll(".card-close")),s=document.getElementById("sortable-4");n.forEach(function(e){e.addEventListener("click",function(r){r.preventDefault(),new bootstrap.Collapse(e.closest(".card").querySelector(".collapse")),e.closest(".card-header").classList.toggle("collapsed"),Helpers._toggleClass(e.firstElementChild,"ri-arrow-down-s-line","ri-arrow-up-s-line")})}),i.forEach(function(e){e.addEventListener("click",function(r){r.preventDefault(),Helpers._toggleClass(e.firstElementChild,"ri-fullscreen-line","ri-fullscreen-exit-line"),e.closest(".card").classList.toggle("card-fullscreen")})}),document.addEventListener("keyup",function(e){if(e.preventDefault(),e.key==="Escape"){const r=document.querySelector(".card-fullscreen");r&&(Helpers._toggleClass(r.querySelector(".card-expand").firstElementChild,"ri-fullscreen-line","ri-fullscreen-exit-line"),r.classList.toggle("card-fullscreen"))}}),u.forEach(function(e){e.addEventListener("click",function(r){r.preventDefault(),e.closest(".card").classList.add("d-none")})}),s&&Sortable.create(s,{animation:500,handle:".card"});const l=document.querySelectorAll(".card-reload");l&&(document.querySelectorAll(".card-action").forEach((r,c)=>{r.dataset.cardId=`card-${c+1}`}),l.forEach(r=>{r.addEventListener("click",function(c){c.preventDefault();const t=r.closest(".card-action");if(!t){console.error("Closest card with .card-action class not found!");return}const a=t.dataset.cardId;Block.standard(`[data-card-id="${a}"]`,{backgroundColor:document.documentElement.getAttribute("data-bs-theme")==="dark"?"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)":"rgba("+window.Helpers.getCssVar("white-rgb")+", 0.5)",svgSize:"0px"});const f=`
          <div class="sk-fold sk-primary">
            <div class="sk-fold-cube"></div>
            <div class="sk-fold-cube"></div>
            <div class="sk-fold-cube"></div>
            <div class="sk-fold-cube"></div>
          </div>
          <h5>LOADING...</h5>
        `,o=t.querySelector(".notiflix-block");o&&(o.innerHTML=f),setTimeout(function(){Block.remove(`[data-card-id="${a}"]`);const d=t.querySelector(".card-alert");d&&(d.innerHTML=`
              <div class="alert alert-solid-danger alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <span class="fw-medium">Holy grail!</span> Your success/error message here.
              </div>
            `)},2500)})}))});
