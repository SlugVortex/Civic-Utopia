(function(){const l=document.querySelector("#TagifyBasic");l&&new Tagify(l);const r=document.querySelector("#TagifyReadonly");r&&new Tagify(r);const o=document.querySelector("#TagifyCustomInlineSuggestion"),c=document.querySelector("#TagifyCustomListSuggestion"),m=["A# .NET","A# (Axiom)","A-0 System","A+","A++","ABAP","ABC","ABC ALGOL","ABSET","ABSYS","ACC","Accent","Ace DASL","ACL2","Avicsoft","ACT-III","Action!","ActionScript","Ada","Adenine","Agda","Agilent VEE","Agora","AIMMS","Alef","ALF","ALGOL 58","ALGOL 60","ALGOL 68","ALGOL W","Alice","Alma-0","AmbientTalk","Amiga E","AMOS","AMPL","Apex (Salesforce.com)","APL","AppleScript","Arc","ARexx","Argus","AspectJ","Assembly language","ATS","Ateji PX","AutoHotkey","Autocoder","AutoIt","AutoLISP / Visual LISP","Averest","AWK","Axum","Active Server Pages","ASP.NET"];o&&new Tagify(o,{whitelist:m,maxTags:10,dropdown:{maxItems:20,classname:"tags-inline",enabled:0,closeOnSelect:!1}}),c&&new Tagify(c,{whitelist:m,maxTags:10,dropdown:{maxItems:20,classname:"",enabled:0,closeOnSelect:!1}});const d=document.querySelector("#TagifyUserList"),g=[{value:1,name:"Justinian Hattersley",avatar:"https://i.pravatar.cc/80?img=1",email:"jhattersley0@ucsd.edu"},{value:2,name:"Antons Esson",avatar:"https://i.pravatar.cc/80?img=2",email:"aesson1@ning.com"},{value:3,name:"Ardeen Batisse",avatar:"https://i.pravatar.cc/80?img=3",email:"abatisse2@nih.gov"},{value:4,name:"Graeme Yellowley",avatar:"https://i.pravatar.cc/80?img=4",email:"gyellowley3@behance.net"},{value:5,name:"Dido Wilford",avatar:"https://i.pravatar.cc/80?img=5",email:"dwilford4@jugem.jp"},{value:6,name:"Celesta Orwin",avatar:"https://i.pravatar.cc/80?img=6",email:"corwin5@meetup.com"},{value:7,name:"Sally Main",avatar:"https://i.pravatar.cc/80?img=7",email:"smain6@techcrunch.com"},{value:8,name:"Grethel Haysman",avatar:"https://i.pravatar.cc/80?img=8",email:"ghaysman7@mashable.com"},{value:9,name:"Marvin Mandrake",avatar:"https://i.pravatar.cc/80?img=9",email:"mmandrake8@sourceforge.net"},{value:10,name:"Corrie Tidey",avatar:"https://i.pravatar.cc/80?img=10",email:"ctidey9@youtube.com"}];function A(e){return`
    <tag title="${e.title||e.email}"
      contenteditable='false'
      spellcheck='false'
      tabIndex="-1"
      class="${this.settings.classNames.tag} ${e.class||""}"
      ${this.getAttributes(e)}
    >
      <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>
      <div>
        <div class='tagify__tag__avatar-wrap'>
          <img onerror="this.style.visibility='hidden'" src="${e.avatar}">
        </div>
        <span class='tagify__tag-text'>${e.name}</span>
      </div>
    </tag>
  `}function u(e){return`
    <div ${this.getAttributes(e)}
      class='tagify__dropdown__item align-items-center ${e.class||""}'
      tabindex="0"
      role="option"
    >
      ${e.avatar?`<div class='tagify__dropdown__item__avatar-wrap'>
        <img onerror="this.style.visibility='hidden'" src="${e.avatar}">
      </div>`:""}
      <div class="fw-medium">${e.name}</div>
      <span>${e.email}</span>
    </div>
  `}function v(e){return`
        <div class="${this.settings.classNames.dropdownItem} ${this.settings.classNames.dropdownItem}__addAll">
            <strong>${this.value.length?"Add remaining":"Add All"}</strong>
            <span>${e.length} members</span>
        </div>
    `}if(d){let t=function(a){a.detail.elm.classList.contains(`${e.settings.classNames.dropdownItem}__addAll`)&&e.dropdown.selectAll()},n=function({detail:{tag:a,data:i}}){e.setTagTextNode(a,`${i.name} <${i.email}>`)};var p=t,f=n;const e=new Tagify(d,{tagTextProp:"name",enforceWhitelist:!0,skipInvalid:!0,dropdown:{closeOnSelect:!1,enabled:0,classname:"users-list",searchKeys:["name","email"]},templates:{tag:A,dropdownItem:u,dropdownHeader:v},whitelist:g});e.on("dropdown:select",t).on("edit:start",n)}const s=document.querySelector("#TagifyEmailList");if(s){let a=function(i){console.log("invalid",i.detail)};var y=a;const e=Array.from({length:100},()=>Array.from({length:Math.floor(Math.random()*10+3)},()=>String.fromCharCode(Math.random()*26+97)).join("")+"@gmail.com"),t=new Tagify(s,{pattern:/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,whitelist:e,callbacks:{invalid:a},dropdown:{position:"text",enabled:1}});s.nextElementSibling.addEventListener("click",()=>t.addEmptyTag())}})();
