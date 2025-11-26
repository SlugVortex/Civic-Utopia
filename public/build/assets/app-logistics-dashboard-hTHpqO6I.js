document.addEventListener("DOMContentLoaded",function(y){let l,c,i,u,h,d;isDarkStyle?d="dark":d="light",config.colors.cardColor,l=config.colors.headingColor,c=config.colors.textMuted,u=config.colors.borderColor,h=config.colors.bodyColor,i=config.fontFamily;const n={donut:{series1:config.colors.primary},donut2:{series3:config.colors.success,series4:"#78D533",series5:"#9ADF66",series6:"#BBEA99"},line:{series1:config.colors.warning,series2:config.colors.primary}},p=document.querySelector("#shipmentStatisticsChart"),b={series:[{name:"Shipment",type:"column",data:[38,45,33,38,32,50,48,40,42,37]},{name:"Delivery",type:"line",data:[23,28,23,32,28,44,32,38,26,34]}],chart:{height:280,type:"line",stacked:!1,parentHeightOffset:0,toolbar:{show:!1},zoom:{enabled:!1}},markers:{size:5,colors:[config.colors.white],strokeColors:n.line.series2,hover:{size:6},borderRadius:4},stroke:{curve:"smooth",width:[0,3],lineCap:"round"},legend:{show:!0,position:"bottom",markers:{size:4,offsetX:-3,strokeWidth:0},height:40,itemMargin:{horizontal:8,vertical:0},fontSize:"15px",fontFamily:i,fontWeight:400,labels:{colors:l,useSeriesColors:!1},offsetY:5},grid:{strokeDashArray:8,borderColor:u},colors:[n.line.series1,n.line.series2],fill:{opacity:[1,1]},plotOptions:{bar:{columnWidth:"30%",startingShape:"rounded",endingShape:"rounded",borderRadius:4}},dataLabels:{enabled:!1},xaxis:{tickAmount:10,categories:["1 Jan","2 Jan","3 Jan","4 Jan","5 Jan","6 Jan","7 Jan","8 Jan","9 Jan","10 Jan"],labels:{style:{colors:c,fontSize:"13px",fontFamily:i,fontWeight:400}},axisBorder:{show:!1},axisTicks:{show:!1}},yaxis:{tickAmount:4,min:0,max:50,labels:{style:{colors:c,fontSize:"13px",fontFamily:i,fontWeight:400},formatter:function(e){return e+"%"}}},responsive:[{breakpoint:1400,options:{chart:{height:320},xaxis:{labels:{style:{fontSize:"10px"}}},legend:{fontSize:"13px"}}},{breakpoint:1025,options:{chart:{height:415},plotOptions:{bar:{columnWidth:"50%"}}}},{breakpoint:982,options:{plotOptions:{bar:{columnWidth:"30%"}}}},{breakpoint:480,options:{chart:{height:250},legend:{offsetY:7}}}]};typeof p!==void 0&&p!==null&&new ApexCharts(p,b).render();const f=document.querySelector("#deliveryExceptionsChart"),g={chart:{height:388,parentHeightOffset:0,type:"donut"},labels:["Incorrect address","Weather conditions","Federal Holidays","Damage during transit"],series:[13,25,22,40],colors:[n.donut2.series3,n.donut2.series4,n.donut2.series5,n.donut2.series6],stroke:{width:0},dataLabels:{enabled:!1,formatter:function(e,a){return parseInt(e)+"%"}},legend:{show:!0,position:"bottom",offsetY:10,markers:{size:5,width:8,height:8,strokeWidth:0},itemMargin:{horizontal:16,vertical:5},fontSize:"13px",fontFamily:i,fontWeight:400,labels:{colors:l,useSeriesColors:!1}},tooltip:{theme:d},grid:{padding:{top:15}},plotOptions:{pie:{donut:{size:"75%",labels:{show:!0,value:{fontSize:"26px",fontFamily:i,color:l,fontWeight:500,offsetY:-30,formatter:function(e){return parseInt(e)+"%"}},name:{offsetY:20,fontFamily:i},total:{show:!0,fontSize:"0.9375rem",label:"AVG. Exceptions",color:h,formatter:function(e){return"30%"}}}}}},responsive:[{breakpoint:420,options:{chart:{height:360}}}]};typeof f!==void 0&&f!==null&&new ApexCharts(f,g).render();const m=document.querySelector(".dt-route-vehicles");m&&new DataTable(m,{ajax:assetsPath+"json/logistics-dashboard.json",columns:[{data:"id"},{data:"id",orderable:!1,render:DataTable.render.select()},{data:"location"},{data:"start_city"},{data:"end_city"},{data:"warnings"},{data:"progress"}],columnDefs:[{className:"control",orderable:!1,searchable:!1,responsivePriority:2,targets:0,render:function(e,a,o,t){return""}},{targets:1,orderable:!1,searchable:!1,responsivePriority:3,checkboxes:!0,render:function(){return'<input type="checkbox" class="dt-checkboxes form-check-input">'},checkboxes:{selectAllRender:'<input type="checkbox" class="form-check-input">'}},{targets:2,responsivePriority:1,render:(e,a,o)=>{const t=o.location;return`
                  <div class="d-flex justify-content-start align-items-center user-name">
                      <div class="avatar-wrapper">
                          <div class="avatar me-3">
                              <span class="avatar-initial rounded-circle bg-label-secondary">
                                  <i class="icon-base ri ri-car-line icon-28px"></i>
                              </span>
                          </div>
                      </div>
                      <div class="d-flex flex-column">
                        <a class="text-heading text-nowrap fw-medium" href="${baseUrl}app/logistics/fleet">VOL-${t}</a>
                      </div>
                  </div>
              `}},{targets:3,render:(e,a,o)=>{const{start_city:t,start_country:s}=o;return`
                  <div class="text-body">
                      ${t}, ${s}
                  </div>
              `}},{targets:4,render:(e,a,o)=>{const{end_city:t,end_country:s}=o;return`
                  <div class="text-body">
                      ${t}, ${s}
                  </div>
              `}},{targets:-2,render:(e,a,o)=>{const t=o.warnings,r={1:{title:"No Warnings",class:"bg-label-success"},2:{title:"Temperature Not Optimal",class:"bg-label-warning"},3:{title:"Ecu Not Responding",class:"bg-label-danger"},4:{title:"Oil Leakage",class:"bg-label-info"},5:{title:"Fuel Problems",class:"bg-label-primary"}}[t];return r?`
                  <span class="badge rounded-pill ${r.class}">
                      ${r.title}
                  </span>
              `:e}},{targets:-1,render:(e,a,o)=>{const t=o.progress;return`
                  <div class="d-flex align-items-center">
                      <div class="progress bg-label-primary w-100" style="height: 8px;">
                          <div
                              class="progress-bar"
                              role="progressbar"
                              style="width: ${t}%"
                              aria-valuenow="${t}"
                              aria-valuemin="0"
                              aria-valuemax="100">
                          </div>
                      </div>
                      <div class="text-body ms-2">${t}%</div>
                  </div>
              `}}],select:{style:"multi",selector:"td:nth-child(2)"},order:[2,"asc"],layout:{topStart:{rowClass:"",features:[]},topEnd:{},bottomStart:{rowClass:"row mx-3 justify-content-between",features:["info"]},bottomEnd:"paging"},lengthMenu:[5],language:{paginate:{next:'<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',previous:'<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',first:'<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',last:'<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'}},responsive:{details:{display:DataTable.Responsive.display.modal({header:function(e){return"Details of "+e.data().location}}),type:"column",renderer:function(e,a,o){const t=o.map(function(s){return s.title!==""?`<tr data-dt-row="${s.rowIndex}" data-dt-column="${s.columnIndex}">
                      <td>${s.title}:</td>
                      <td>${s.data}</td>
                    </tr>`:""}).join("");if(t){const s=document.createElement("table");s.classList.add("table","datatables-basic","mb-2");const r=document.createElement("tbody");return r.innerHTML=t,s.appendChild(r),s}return!1}}}}),setTimeout(()=>{[{selector:".dt-layout-start",classToAdd:"my-0"},{selector:".dt-layout-end",classToAdd:"my-0"},{selector:".dt-layout-table",classToRemove:"row mt-2",classToAdd:"mt-n2"},{selector:".dt-layout-full",classToRemove:"col-md col-12",classToAdd:"table-responsive"}].forEach(({selector:a,classToRemove:o,classToAdd:t})=>{document.querySelectorAll(a).forEach(s=>{o&&o.split(" ").forEach(r=>s.classList.remove(r)),t&&t.split(" ").forEach(r=>s.classList.add(r))})})},100)});
