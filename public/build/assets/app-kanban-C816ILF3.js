(async function(){let k;const o=document.querySelector(".kanban-update-item-sidebar"),w=document.querySelector(".kanban-wrapper"),A=document.querySelector(".comment-editor"),b=document.querySelector(".kanban-add-new-board"),p=[].slice.call(document.querySelectorAll(".kanban-add-board-input")),y=document.querySelector(".kanban-add-board-btn"),h=document.querySelector("#due-date"),g=$(".select2"),f=document.querySelector("html").getAttribute("data-assets-path"),B=new bootstrap.Offcanvas(o),v=await fetch(f+"json/kanban.json");v.ok||console.error("error",v),k=await v.json(),h&&h.flatpickr({monthSelectorType:"static",static:!0,altInput:!0,altFormat:"j F, Y",dateFormat:"Y-m-d"});//! TODO: Update Event label and guest code to JS once select removes jQuery dependency
if(g.length){let e=function(t){if(!t.id)return t.text;var a="<div class='badge "+$(t.element).data("color")+" rounded-pill'> "+t.text+"</div>";return a};var O=e;g.each(function(){var t=$(this);select2Focus(t),t.wrap().select2({placeholder:"Select Label",dropdownParent:t.parent(),templateResult:e,templateSelection:e,escapeMarkup:function(a){return a}})})}A&&new Quill(A,{modules:{toolbar:".comment-toolbar"},placeholder:"Write a Comment...",theme:"snow"});const S=()=>`
  <div class="dropdown">
      <i class="dropdown-toggle icon-base ri ri-more-2-fill cursor-pointer"
         id="board-dropdown"
         data-bs-toggle="dropdown"
         aria-haspopup="true"
         aria-expanded="false">
      </i>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="board-dropdown">
          <a class="dropdown-item delete-board" href="javascript:void(0)">
              <i class="icon-base ri ri-delete-bin-7-line icon-sm me-1"></i>
              <span class="align-middle">Delete</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ri ri-edit-2-line icon-sm me-1"></i>
              <span class="align-middle">Rename</span>
          </a>
          <a class="dropdown-item" href="javascript:void(0)">
              <i class="icon-base ri ri-archive-line icon-sm me-1"></i>
              <span class="align-middle">Archive</span>
          </a>
      </div>
  </div>
`,E=()=>`
<div class="dropdown kanban-tasks-item-dropdown">
    <i class="dropdown-toggle icon-base ri ri-more-2-fill icon-sm"
       id="kanban-tasks-item-dropdown"
       data-bs-toggle="dropdown"
       aria-haspopup="true"
       aria-expanded="false">
    </i>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="kanban-tasks-item-dropdown">
        <a class="dropdown-item" href="javascript:void(0)">Copy task link</a>
        <a class="dropdown-item" href="javascript:void(0)">Duplicate task</a>
        <a class="dropdown-item delete-task" href="javascript:void(0)">Delete</a>
    </div>
</div>
`,C=(e,t)=>`
<div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
    <div class="item-badges">
        <div class="badge rounded-pill bg-label-${e}">${t}</div>
    </div>
    ${E()}
</div>
`,L=(e="",t=!1,a="",n="",r="")=>{const s=t?" pull-up":"",c=a?`avatar-${a}`:"",u=r?r.split(","):[];return e?e.split(",").map((d,l,H)=>{const M=n&&l!==H.length-1?` me-${n}`:"",F=u[l]||"";return`
            <div class="avatar ${c}${M} w-px-26 h-px-26"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="${F}">
                <img src="${f}img/avatars/${d}"
                     alt="Avatar"
                     class="rounded-circle${s}">
            </div>
        `}).join(""):""},D=(e,t,a,n)=>`
<div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
    <div class="d-flex">
        <span class="d-flex align-items-center me-4">
            <i class="icon-base ri ri-attachment-2 me-1"></i>
            <span class="attachments">${e}</span>
        </span>
        <span class="d-flex align-items-center">
            <i class="icon-base ri ri-wechat-line me-1"></i>
            <span>${t}</span>
        </span>
    </div>
    <div class="avatar-group d-flex align-items-center assigned-avatar">
        ${L(a,!0,"xs",null,n)}
    </div>
</div>
`,i=new jKanban({element:".kanban-wrapper",gutter:"12px",widthBoard:"250px",dragItems:!0,boards:k,dragBoards:!0,addItemButton:!0,buttonContent:"+ Add Item",itemAddOptions:{enabled:!0,content:"+ Add New Item",class:"kanban-title-button btn btn-default",footer:!1},click:e=>{const t=e,a=t.getAttribute("data-eid")?t.querySelector(".kanban-text").textContent:t.textContent,n=t.getAttribute("data-due-date"),r=new Date,s=r.getFullYear(),c=n?`${n}, ${s}`:`${r.getDate()} ${r.toLocaleString("en",{month:"long"})}, ${s}`,u=t.getAttribute("data-badge-text"),d=t.getAttribute("data-assigned");B.show(),o.querySelector("#title").value=a,o.querySelector("#due-date").nextSibling.value=c,$(".kanban-update-item-sidebar").find(g).val(u).trigger("change"),o.querySelector(".assigned").innerHTML="",o.querySelector(".assigned").insertAdjacentHTML("afterbegin",`${L(d,!1,"sm","2",e.getAttribute("data-members"))}
        <div class="avatar avatar-sm ms-2">
            <span class="avatar-initial rounded-circle bg-label-secondary">
                <i class="icon-base ri ri-add-line"></i>
            </span>
        </div>`)},buttonClick:(e,t)=>{const a=document.createElement("form");a.setAttribute("class","new-item-form"),a.innerHTML=`
        <div class="mb-4">
            <textarea class="form-control add-new-item" rows="2" placeholder="Add Content" autofocus required></textarea>
        </div>
        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm me-3">Add</button>
            <button type="button" class="btn btn-label-secondary btn-sm cancel-add-item">Cancel</button>
        </div>
      `,i.addForm(t,a),a.addEventListener("submit",n=>{n.preventDefault();const r=Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-item`));i.addElement(t,{title:`<span class="kanban-text">${n.target[0].value}</span>`,id:`${t}-${r.length+1}`}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .kanban-text`)).forEach(d=>{d.insertAdjacentHTML("beforebegin",E())}),Array.from(document.querySelectorAll(".kanban-item .kanban-tasks-item-dropdown")).forEach(d=>{d.addEventListener("click",l=>l.stopPropagation())}),Array.from(document.querySelectorAll(`.kanban-board[data-id="${t}"] .delete-task`)).forEach(d=>{d.addEventListener("click",()=>{const l=d.closest(".kanban-item").getAttribute("data-eid");i.removeElement(l)})}),a.remove()}),a.querySelector(".cancel-add-item").addEventListener("click",()=>a.remove())}});w&&new PerfectScrollbar(w);const m=document.querySelector(".kanban-container"),q=Array.from(document.querySelectorAll(".kanban-title-board")),x=Array.from(document.querySelectorAll(".kanban-item"));x.length&&x.forEach(e=>{const t=`<span class="kanban-text">${e.textContent}</span>`;let a="";e.getAttribute("data-image")&&(a=`
              <img class="img-fluid rounded-4 mb-2"
                   src="${f}img/elements/${e.getAttribute("data-image")}">
          `),e.textContent="",e.getAttribute("data-badge")&&e.getAttribute("data-badge-text")&&e.insertAdjacentHTML("afterbegin",`${C(e.getAttribute("data-badge"),e.getAttribute("data-badge-text"))}${a}${t}`),(e.getAttribute("data-comments")||e.getAttribute("data-due-date")||e.getAttribute("data-assigned"))&&e.insertAdjacentHTML("beforeend",D(e.getAttribute("data-attachments")||0,e.getAttribute("data-comments")||0,e.getAttribute("data-assigned")||"",e.getAttribute("data-members")||""))}),Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(e=>{new bootstrap.Tooltip(e)});const T=Array.from(document.querySelectorAll(".kanban-tasks-item-dropdown"));T.length&&T.forEach(e=>{e.addEventListener("click",t=>{t.stopPropagation()})}),y&&y.addEventListener("click",()=>{p.forEach(e=>{e.value="",e.classList.toggle("d-none")})}),m&&m.append(b),q&&q.forEach(e=>{e.addEventListener("mouseenter",()=>{e.contentEditable="true"}),e.insertAdjacentHTML("afterend",S())}),Array.from(document.querySelectorAll(".delete-board")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-board").getAttribute("data-id");i.removeBoard(t)})}),Array.from(document.querySelectorAll(".delete-task")).forEach(e=>{e.addEventListener("click",()=>{const t=e.closest(".kanban-item").getAttribute("data-eid");i.removeElement(t)})});const j=document.querySelector(".kanban-add-board-cancel-btn");j&&j.addEventListener("click",()=>{p.forEach(e=>{e.classList.toggle("d-none")})}),b&&b.addEventListener("submit",e=>{e.preventDefault();const t=e.target.querySelector(".form-control").value.trim(),a=t.replace(/\s+/g,"-").toLowerCase();i.addBoards([{id:a,title:t}]);const n=document.querySelector(".kanban-board:last-child");if(n){const r=n.querySelector(".kanban-title-board");r.insertAdjacentHTML("afterend",S()),r.addEventListener("mouseenter",()=>{r.contentEditable="true"});const s=n.querySelector(".delete-board");s&&s.addEventListener("click",()=>{const c=s.closest(".kanban-board").getAttribute("data-id");i.removeBoard(c)})}p.forEach(r=>{r.classList.add("d-none")}),m&&m.append(b)}),o.addEventListener("hidden.bs.offcanvas",()=>{const e=o.querySelector(".ql-editor").firstElementChild;e&&(e.innerHTML="")}),o&&o.addEventListener("shown.bs.offcanvas",()=>{Array.from(o.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(t=>{new bootstrap.Tooltip(t)})})})();
