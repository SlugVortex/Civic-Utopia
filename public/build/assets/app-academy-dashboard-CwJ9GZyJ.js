document.addEventListener("DOMContentLoaded",function(z){let p,d,n,g,u;isDarkStyle?u="dark":u="light",config.colors.cardColor,p=config.colors.headingColor,d=config.colors.textMuted,g=config.colors.borderColor,config.colors.bodyColor,n=config.fontFamily;const l={donut:{series1:config.colors.primary},donut2:{series1:"#49AC00",series2:"#4DB600",series3:config.colors.success,series4:"#78D533",series5:"#9ADF66",series6:"#BBEA99"},line:{series1:config.colors.warning,series2:config.colors.primary}},f=document.querySelector("#leadsReportChart"),v={chart:{height:140,width:130,parentHeightOffset:0,type:"donut",opacity:1},labels:["36h","56h","16h","32h","56h","16h"],series:[23,35,10,20,35,23],colors:[l.donut2.series1,l.donut2.series2,l.donut2.series3,l.donut2.series4,l.donut2.series5,l.donut2.series6],stroke:{width:0},dataLabels:{enabled:!1,formatter:function(t,r){return parseInt(t)+"%"}},legend:{show:!1},tooltip:{theme:u},grid:{padding:{top:0}},plotOptions:{pie:{donut:{size:"75%",labels:{show:!0,value:{fontSize:"1.125rem",fontFamily:n,color:p,fontWeight:500,offsetY:-15,formatter:function(t){return parseInt(t)+"%"}},name:{offsetY:20,fontFamily:n},total:{show:!0,fontSize:".9375rem",label:"Total",color:d,formatter:function(t){return"231h"}}}}}}};typeof f!==void 0&&f!==null&&new ApexCharts(f,v).render();const m=document.querySelector("#horizontalBarChart"),h={chart:{height:300,type:"bar",toolbar:{show:!1}},plotOptions:{bar:{horizontal:!0,barHeight:"60%",distributed:!0,startingShape:"rounded",borderRadiusApplication:"end",borderRadius:7}},grid:{strokeDashArray:10,borderColor:g,xaxis:{lines:{show:!0}},yaxis:{lines:{show:!1}},padding:{top:-35,bottom:-12}},colors:[config.colors.primary,config.colors.info,config.colors.success,config.colors.secondary,config.colors.danger,config.colors.warning],fill:{opacity:[1,1,1,1,1,1]},dataLabels:{enabled:!0,style:{colors:[config.colors.white],fontWeight:400,fontSize:"13px",fontFamily:n},formatter:function(t,r){return h.labels[r.dataPointIndex]},offsetX:0,dropShadow:{enabled:!1}},labels:["UI Design","UX Design","Music","Animation","React","SEO"],series:[{data:[35,20,14,12,10,9]}],xaxis:{categories:["6","5","4","3","2","1"],axisBorder:{show:!1},axisTicks:{show:!1},labels:{style:{colors:d,fontFamily:n,fontSize:"13px"},formatter:function(t){return`${t}%`}}},yaxis:{max:35,labels:{style:{colors:[d],fontFamily:n,fontSize:"13px"}}},tooltip:{enabled:!0,style:{fontSize:"12px"},onDatasetHover:{highlightDataSeries:!1},custom:function({series:t,seriesIndex:r,dataPointIndex:e,w:s}){return'<div class="px-3 py-2"><span>'+t[r][e]+"%</span></div>"}},legend:{show:!1}};typeof m!==void 0&&m!==null&&new ApexCharts(m,h).render();function w(t,r,e){return{chart:{height:e=="true"?58:55,width:e=="true"?58:45,type:"radialBar"},plotOptions:{radialBar:{hollow:{size:e=="true"?"45%":"25%"},dataLabels:{show:e=="true",value:{offsetY:-10,fontSize:"15px",fontWeight:500,fontFamily:n,color:p}},track:{background:config.colors_label.secondary}}},stroke:{lineCap:"round"},colors:[t],grid:{padding:{top:e=="true"?-12:-15,bottom:e=="true"?-17:-15,left:e=="true"?-17:-5,right:-15}},series:[r],labels:e=="true"?[""]:["Progress"]}}const b=document.querySelectorAll(".chart-progress");b&&b.forEach(function(t){const r=config.colors[t.dataset.color],e=t.dataset.series,s=t.dataset.progress_variant,o=w(r,e,s);new ApexCharts(t,o).render()});const x=document.querySelector(".datatables-academy-course"),C={angular:'<span class="badge bg-label-danger rounded p-1_5"><i class="icon-base ri ri-angularjs-line icon-28px"></i></span>',figma:'<span class="badge bg-label-warning rounded p-1_5"><i class="icon-base ri ri-pencil-line icon-28px"></i></span>',react:'<span class="badge bg-label-info rounded p-1_5"><i class="icon-base ri ri-reactjs-line icon-28px"></i></span>',art:'<span class="badge bg-label-success rounded p-1_5"><i class="icon-base ri ri-palette-line icon-28px"></i></span>',fundamentals:'<span class="badge bg-label-primary rounded p-1_5"><i class="icon-base ri ri-star-smile-line icon-28px"></i></span>'};if(x){let t=document.createElement("h5");t.classList.add("card-title","mb-0","text-nowrap","text-md-start","text-center"),t.innerHTML="Course you are taking",new DataTable(x,{ajax:assetsPath+"json/app-academy-dashboard.json",columns:[{data:"id"},{data:"id",orderable:!1,render:DataTable.render.select()},{data:"course name"},{data:"time"},{data:"progress"},{data:"status"}],columnDefs:[{className:"control",searchable:!1,orderable:!1,responsivePriority:2,targets:0,render:function(r,e,s,o){return""}},{targets:1,orderable:!1,searchable:!1,responsivePriority:3,checkboxes:!0,render:function(){return'<input type="checkbox" class="dt-checkboxes form-check-input">'},checkboxes:{selectAllRender:'<input type="checkbox" class="form-check-input">'}},{targets:2,responsivePriority:2,render:(r,e,s)=>{const{logo:o,course:a,user:i,image:c}=s,$=c?`<img src="${assetsPath}img/avatars/${c}" alt="Avatar" class="rounded-circle">`:(()=>{const y=["success","danger","warning","info","dark","primary","secondary"],k=y[Math.floor(Math.random()*y.length)],S=(i.match(/\b\w/g)||[]).reduce((T,_)=>T+_.toUpperCase(),"");return`<span class="avatar-initial rounded-circle bg-label-${k}">${S}</span>`})();return`
                  <div class="d-flex align-items-center">
                      <span class="me-4">${C[o]}</span>
                      <div>
                          <a class="text-heading text-truncate fw-medium mb-2 text-wrap" href="${baseUrl}app/academy/course-details">
                              ${a}
                          </a>
                          <div class="d-flex align-items-center mt-1">
                              <div class="avatar-wrapper me-2">
                                  <div class="avatar avatar-xs">
                                      ${$}
                                  </div>
                              </div>
                              <small class="text-nowrap text-heading">${i}</small>
                          </div>
                      </div>
                  </div>
              `}},{targets:3,responsivePriority:3,render:r=>{const e=moment.duration(r),s=Math.floor(e.asHours()),o=Math.floor(e.asMinutes())-s*60;return`<span class="fw-medium text-nowrap text-heading">${`${s}h ${o}m`}</span>`}},{targets:4,render:(r,e,s)=>{const{status:o,number:a}=s;return`
                  <div class="d-flex align-items-center gap-3">
                      <p class="fw-medium mb-0 text-heading">${o}</p>
                      <div class="progress bg-label-primary w-100" style="height: 8px;">
                          <div
                              class="progress-bar"
                              style="width: ${o}"
                              aria-valuenow="${o}"
                              aria-valuemin="0"
                              aria-valuemax="100">
                          </div>
                      </div>
                      <small>${a}</small>
                  </div>
              `}},{targets:5,render:(r,e,s)=>{const{user_number:o,note:a,view:i}=s;return`
                  <div class="d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center w-px-75">
                          <i class="icon-base ri ri-group-line icon-24px me-1_5 text-primary"></i>
                          <span>${o}</span>
                      </div>
                      <div class="d-flex align-items-center w-px-75">
                          <i class="icon-base ri ri-computer-line icon-24px me-1_5 text-info"></i>
                          <span>${a}</span>
                      </div>
                      <div class="d-flex align-items-center w-px-75">
                          <i class="icon-base ri ri-video-upload-line icon-24px me-1_5 text-danger scaleX-n1-rtl"></i>
                          <span>${i}</span>
                      </div>
                  </div>
              `}}],select:{style:"multi",selector:"td:nth-child(2)"},order:[[2,"desc"]],layout:{topStart:{rowClass:"row card-header mx-0 px-5 py-md-0",features:[t]},topEnd:{search:{placeholder:"Search Course",text:"_INPUT_"}},bottomStart:{rowClass:"row mx-3 justify-content-between",features:["info"]},bottomEnd:"paging"},lengthMenu:[5],language:{paginate:{next:'<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',previous:'<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',first:'<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',last:'<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'}},responsive:{details:{display:DataTable.Responsive.display.modal({header:function(r){return"Details of "+r.data().order}}),type:"column",renderer:function(r,e,s){const o=s.map(function(a){return a.title!==""?`<tr data-dt-row="${a.rowIndex}" data-dt-column="${a.columnIndex}">
                      <td>${a.title}:</td>
                      <td>${a.data}</td>
                    </tr>`:""}).join("");if(o){const a=document.createElement("div");a.classList.add("table-responsive");const i=document.createElement("table");a.appendChild(i),i.classList.add("table"),i.classList.add("datatables-basic");const c=document.createElement("tbody");return c.innerHTML=o,i.appendChild(c),a}return!1}}}})}setTimeout(()=>{[{selector:".dt-layout-table",classToRemove:"row mt-2"},{selector:".dt-layout-start",classToAdd:"px-0"},{selector:".dt-layout-end",classToAdd:"px-0"},{selector:".dt-layout-full",classToRemove:"col-md col-12",classToAdd:"table-responsive"}].forEach(({selector:r,classToRemove:e,classToAdd:s})=>{document.querySelectorAll(r).forEach(o=>{e&&e.split(" ").forEach(a=>o.classList.remove(a)),s&&s.split(" ").forEach(a=>o.classList.add(a))})})},100)});
