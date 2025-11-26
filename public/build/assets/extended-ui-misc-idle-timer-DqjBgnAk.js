$(function(){var i=$("#document-Status"),o=$("#document-Pause"),m=$("#document-Resume"),u=$("#document-Elapsed"),c=$("#document-Destroy"),d=$("#document-Init");if(i.length){var s=5e3;$(document).on("idle.idleTimer",function(t,e,a){i.val(function(r,l){return l+"Idle @ "+moment().format()+` 
`}).removeClass("alert-success").addClass("alert-warning")}),$(document).on("active.idleTimer",function(t,e,a,r){i.val(function(l,T){return T+"Active ["+r.type+"] ["+r.target.nodeName+"] @ "+moment().format()+` 
`}).addClass("alert-success").removeClass("alert-warning")}),o.on("click",function(){return $(document).idleTimer("pause"),i.val(function(t,e){return e+"Paused @ "+moment().format()+` 
`}),$(this).blur(),!1}),m.on("click",function(){return $(document).idleTimer("resume"),i.val(function(t,e){return e+"Resumed @ "+moment().format()+` 
`}),$(this).blur(),!1}),u.on("click",function(){return i.val(function(t,e){return e+"Elapsed (since becoming active): "+$(document).idleTimer("getElapsedTime")+` 
`}),$(this).blur(),!1}),c.on("click",function(){return $(document).idleTimer("destroy"),i.val(function(t,e){return e+"Destroyed: @ "+moment().format()+` 
`}).removeClass("alert-success").removeClass("alert-warning"),$(this).blur(),!1}),d.on("click",function(){return $(document).idleTimer({timeout:s}),i.val(function(t,e){return e+"Init: @ "+moment().format()+` 
`}),$(document).idleTimer("isIdle")?i.removeClass("alert-success").addClass("alert-warning"):i.addClass("alert-success").removeClass("alert-warning"),$(this).blur(),!1}),i.val(""),$(document).idleTimer(s),$(document).idleTimer("isIdle")?i.val(function(t,e){return e+"Initial Idle State @ "+moment().format()+` 
`}).removeClass("alert-success").addClass("alert-warning"):i.val(function(t,e){return e+"Initial Active State @ "+moment().format()+` 
`}).addClass("alert-success").removeClass("alert-warning")}var n=$("#element-Status"),f=$("#element-Reset"),v=$("#element-Remaining"),g=$("#element-LastActive"),C=$("#element-State");if(n.length){var b=3e3;n.on("idle.idleTimer",function(t,e,a){t.stopPropagation(),n.val(function(r,l){return l+"Idle @ "+moment().format()+` 
`}).removeClass("alert-success").addClass("alert-warning")}),n.on("active.idleTimer",function(t){t.stopPropagation(),n.val(function(e,a){return a+"Active @ "+moment().format()+` 
`}).addClass("alert-success").removeClass("alert-warning")}),f.on("click",function(){return n.idleTimer("reset").val(function(t,e){return e+"Reset @ "+moment().format()+` 
`}),$("#element-Status").idleTimer("isIdle")?n.removeClass("alert-success").addClass("alert-warning"):n.addClass("alert-success").removeClass("alert-warning"),$(this).blur(),!1}),v.on("click",function(){return n.val(function(t,e){return e+"Remaining: "+n.idleTimer("getRemainingTime")+` 
`}),$(this).blur(),!1}),g.on("click",function(){return n.val(function(t,e){return e+"LastActive: "+n.idleTimer("getLastActiveTime")+` 
`}),$(this).blur(),!1}),C.on("click",function(){return n.val(function(t,e){return e+"State: "+($("#element-Status").idleTimer("isIdle")?"idle":"active")+` 
`}),$(this).blur(),!1}),n.val("").idleTimer(b),n.idleTimer("isIdle")?n.val(function(t,e){return e+"Initial Idle @ "+moment().format()+` 
`}).removeClass("alert-success").addClass("alert-warning"):n.val(function(t,e){return e+"Initial Active @ "+moment().format()+` 
`}).addClass("alert-success").removeClass("alert-warning")}});
