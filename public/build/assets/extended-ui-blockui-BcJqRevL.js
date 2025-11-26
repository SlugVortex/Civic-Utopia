document.addEventListener("DOMContentLoaded",function(se){const n=document.getElementById("section-block"),m=document.querySelector(".btn-section-block"),g=document.querySelector(".btn-section-block-overlay"),p=document.querySelector(".btn-section-block-spinner"),w=document.querySelector(".btn-section-block-custom"),f=document.querySelector(".btn-section-block-multiple"),o="#section-block",d=document.querySelector("#card-block"),L=document.querySelector(".btn-card-block"),q=document.querySelector(".btn-card-block-overlay"),H=document.querySelector(".btn-card-block-spinner"),T=document.querySelector(".btn-card-block-custom"),E=document.querySelector(".btn-card-block-multiple"),s="#card-block",v=document.querySelector(".form-block"),S=document.querySelector(".btn-form-block"),x=document.querySelector(".btn-form-block-overlay"),B=document.querySelector(".btn-form-block-spinner"),y=document.querySelector(".btn-form-block-custom"),C=document.querySelector(".btn-form-block-multiple"),c=".form-block",a=document.querySelector("#option-block"),M=document.querySelector(".btn-option-block"),h=document.querySelector(".btn-option-block-hourglass"),z=document.querySelector(".btn-option-block-circle"),V=document.querySelector(".btn-option-block-arrows"),P=document.querySelector(".btn-option-block-dots"),A=document.querySelector(".btn-option-block-pulse"),i="#option-block",F=document.querySelector(".btn-page-block"),I=document.querySelector(".btn-page-block-overlay"),D=document.querySelector(".btn-page-block-spinner"),O=document.querySelector(".btn-page-block-custom"),N=document.querySelector(".btn-page-block-multiple"),j=document.querySelector(".remove-btn"),k=document.querySelector(".remove-card-btn"),$=document.querySelector(".remove-form-btn"),b=document.querySelector(".remove-option-btn"),u=document.querySelector(".remove-page-btn");n&&m&&m.addEventListener("click",()=>{Block.circle(o,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),n&&g&&g.addEventListener("click",()=>{Block.standard(o,{backgroundColor:"rgba("+window.Helpers.getCssVar("white-rgb")+", 0.8)",svgSize:"0px"});let e=document.createElement("div");e.classList.add("spinner-border","text-primary"),e.setAttribute("role","status"),document.querySelector("#section-block .notiflix-block").appendChild(e)}),n&&p&&p.addEventListener("click",()=>{Block.standard(o,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `,t=document.querySelector("#section-block .notiflix-block");t.innerHTML=e}),n&&w&&w.addEventListener("click",()=>{Block.standard(o,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `,t=document.querySelector("#section-block .notiflix-block");t.innerHTML=e});let G,J,K;n&&f&&f.addEventListener("click",()=>{Block.standard(o,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
            <div class="d-flex justify-content-center">
                <p class="mb-0 text-white">Please wait...</p>
                <div class="sk-wave m-0">
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                    <div class="sk-rect sk-wave-rect"></div>
                </div>
            </div>
        `,t=document.querySelector("#section-block .notiflix-block");t&&(t.innerHTML=e),Block.remove(o,1e3),G=setTimeout(()=>{Block.standard(o,"Almost Done...",{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",messageFontSize:"15px",svgSize:"0px",messageColor:config.colors.white}),Block.remove(o,1e3),J=setTimeout(()=>{Block.standard(o,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)"});let l='<div class="px-12 py-3 bg-success text-white">Success</div>',r=document.querySelector("#section-block .notiflix-block");r&&(r.innerHTML=l),K=setTimeout(()=>{Block.remove(o),setTimeout(()=>{m.classList.remove("disabled"),g.classList.remove("disabled"),p.classList.remove("disabled"),w.classList.remove("disabled"),f.classList.remove("disabled")},500)},1810)},1810)},1610)});const Q=[".btn-section-block",".btn-section-block-overlay",".btn-section-block-spinner",".btn-section-block-custom",".btn-section-block-multiple"].map(e=>document.querySelector(e));Q.forEach(e=>{e&&e.addEventListener("click",()=>{Q.forEach(t=>{t&&t.classList.add("disabled")})})}),j&&j.addEventListener("click",()=>{setTimeout(()=>{document.querySelector(`${o} .notiflix-block`)?Block.remove(o):alert("No active block to remove.")},400),clearTimeout(G),clearTimeout(J),clearTimeout(K),setTimeout(()=>{m.classList.remove("disabled"),g.classList.remove("disabled"),p.classList.remove("disabled"),w.classList.remove("disabled"),f.classList.remove("disabled")},500)}),d&&L&&L.addEventListener("click",()=>{Block.circle(s,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),d&&q&&q.addEventListener("click",()=>{Block.standard(s,{backgroundColor:"rgba("+window.Helpers.getCssVar("white-rgb")+", 0.8)",svgSize:"0px"});const e=document.createElement("div");e.classList.add("spinner-border","text-primary"),e.setAttribute("role","status"),document.querySelector("#card-block .notiflix-block").appendChild(e)}),d&&H&&H.addEventListener("click",()=>{Block.standard(s,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `,t=document.querySelector("#card-block .notiflix-block");t.innerHTML=e}),d&&T&&T.addEventListener("click",()=>{Block.standard(s,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `,t=document.querySelector("#card-block .notiflix-block");t.innerHTML=e});let R,U,W;d&&E&&E.addEventListener("click",()=>{Block.standard(s,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `,t=document.querySelector("#card-block .notiflix-block");t&&(t.innerHTML=e),Block.remove(s,1e3),R=setTimeout(()=>{Block.standard(s,"Almost Done...",{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",messageFontSize:"15px",svgSize:"0px",messageColor:config.colors.white}),Block.remove(s,1e3),U=setTimeout(()=>{Block.standard(s,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)"});let l='<div class="px-12 py-3 bg-success text-white">Success</div>',r=document.querySelector("#card-block .notiflix-block");r&&(r.innerHTML=l),W=setTimeout(()=>{Block.remove(s)},1610)},1610)},1610)}),[".btn-card-block",".btn-card-block-overlay",".btn-card-block-spinner",".btn-card-block-custom",".btn-card-block-multiple"].map(e=>document.querySelector(e)).forEach(e=>{e&&e.addEventListener("click",()=>{k.style.position="relative",k.style.pointerEvents="auto",k.style.zIndex=1074})}),k&&k.addEventListener("click",()=>{setTimeout(()=>{document.querySelector(`${s} .notiflix-block`)?Block.remove(s):alert("No active block to remove.")},400),clearTimeout(R),clearTimeout(U),clearTimeout(W)}),a&&M&&M.addEventListener("click",()=>{Block.standard(i,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),a&&h&&h.addEventListener("click",()=>{Block.hourglass(i,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),a&&z&&z.addEventListener("click",()=>{Block.circle(i,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),a&&V&&V.addEventListener("click",()=>{Block.arrows(i,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),a&&P&&P.addEventListener("click",()=>{Block.dots(i,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),a&&A&&A.addEventListener("click",()=>{Block.pulse(i,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),[".btn-option-block",".btn-option-block-overlay",".btn-option-block-spinner",".btn-option-block-custom",".btn-option-block-multiple"].map(e=>document.querySelector(e)).forEach(e=>{e&&e.addEventListener("click",()=>{b.style.position="relative",b.style.pointerEvents="auto",b.style.zIndex=1074})}),b&&b.addEventListener("click",()=>{document.querySelector(`${i} .notiflix-block`)?Block.remove(i):alert("No active block to remove.")}),F&&F.addEventListener("click",()=>{Loading.circle({backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),I&&I.addEventListener("click",()=>{Loading.standard({backgroundColor:"rgba("+window.Helpers.getCssVar("white-rgb")+", 0.8)",svgSize:"0px"});const e=document.createElement("div");e.classList.add("spinner-border","text-primary"),e.setAttribute("role","status"),document.querySelector(".notiflix-loading").appendChild(e)}),D&&D.addEventListener("click",()=>{Loading.standard({backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `,t=document.querySelector(".notiflix-loading");t.innerHTML=e}),O&&O.addEventListener("click",()=>{Loading.standard({backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `,t=document.querySelector(".notiflix-loading");t.innerHTML=e});let X,Y,Z;N&&N.addEventListener("click",()=>{Loading.standard({backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `,t=document.querySelector(".notiflix-loading");t&&(t.innerHTML=e),Loading.remove(1e3),X=setTimeout(()=>{Loading.standard("Almost Done...",{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",messageFontSize:"15px",svgSize:"0px",messageColor:config.colors.white}),Loading.remove(1e3),Y=setTimeout(()=>{Loading.standard({backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)"});let l='<div class="px-12 py-3 bg-success text-white">Success</div>',r=document.querySelector(".notiflix-loading");r&&(r.innerHTML=l),Z=setTimeout(()=>{Loading.remove()},1610)},1610)},1610)}),[".btn-page-block",".btn-page-block-overlay",".btn-page-block-spinner",".btn-page-block-custom",".btn-page-block-multiple"].map(e=>document.querySelector(e)).forEach(e=>{e&&e.addEventListener("click",()=>{u.style.position="relative",u.style.pointerEvents="auto",u.style.zIndex=9999})}),u&&u.addEventListener("click",()=>{document.querySelector(".notiflix-loading")?Loading.remove():alert("No active loading to remove."),clearTimeout(X),clearTimeout(Y),clearTimeout(Z)}),v&&S&&S.addEventListener("click",()=>{Block.circle(c,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"40px",svgColor:config.colors.white})}),v&&x&&x.addEventListener("click",()=>{Block.standard(c,{backgroundColor:"rgba("+window.Helpers.getCssVar("white-rgb")+", 0.8)",svgSize:"0px"});let e=document.createElement("div");e.classList.add("spinner-border","text-primary"),e.setAttribute("role","status"),document.querySelector(".form-block .notiflix-block").appendChild(e)}),v&&B&&B.addEventListener("click",()=>{Block.standard(c,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="sk-wave mx-auto">
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
              <div class="sk-rect sk-wave-rect"></div>
          </div>
        `,t=document.querySelector(".form-block .notiflix-block");t.innerHTML=e}),v&&y&&y.addEventListener("click",()=>{Block.standard(c,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
          <div class="d-flex">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
          </div>
        `,t=document.querySelector(".form-block .notiflix-block");t.innerHTML=e});let _,ee,te;v&&C&&C.addEventListener("click",()=>{Block.standard(c,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",svgSize:"0px"});let e=`
            <div class="d-flex justify-content-center">
              <p class="mb-0 text-white">Please wait...</p>
              <div class="sk-wave m-0">
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
                  <div class="sk-rect sk-wave-rect"></div>
              </div>
            </div>
          `,t=document.querySelector(".form-block .notiflix-block");t&&(t.innerHTML=e),Block.remove(c,1e3),_=setTimeout(()=>{Block.standard(c,"Almost Done...",{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)",messageFontSize:"15px",svgSize:"0px",messageColor:config.colors.white}),Block.remove(c,1e3),ee=setTimeout(()=>{Block.standard(c,{backgroundColor:"rgba("+window.Helpers.getCssVar("black-rgb")+", 0.5)"});let l='<div class="px-12 py-3 bg-success text-white">Success</div>',r=document.querySelector(".form-block .notiflix-block");r&&(r.innerHTML=l),te=setTimeout(()=>{Block.remove(c),setTimeout(()=>{S.classList.remove("disabled"),x.classList.remove("disabled"),B.classList.remove("disabled"),y.classList.remove("disabled"),C.classList.remove("disabled")},500)},1810)},1810)},1610)});const oe=[".btn-form-block",".btn-form-block-overlay",".btn-form-block-spinner",".btn-form-block-custom",".btn-form-block-multiple"].map(e=>document.querySelector(e));oe.forEach(e=>{e&&e.addEventListener("click",()=>{oe.forEach(t=>{t&&t.classList.add("disabled")})})}),$&&$.addEventListener("click",()=>{setTimeout(()=>{document.querySelector(`${c} .notiflix-block`)?Block.remove(c):alert("No active block to remove.")},450),clearTimeout(_),clearTimeout(ee),clearTimeout(te),setTimeout(()=>{S.classList.remove("disabled"),x.classList.remove("disabled"),B.classList.remove("disabled"),y.classList.remove("disabled"),C.classList.remove("disabled")},500)})});
