import{u as U}from"./C8wjtVdh.js";import{L as O,O as E,P as N,M as j,Q as q,i as g,R as K,S as M,T as G,z as l,U as H,d as Q,C as J,r as S,I as X,V as Y,E as d,A as r,q as Z,t as m,B as v,F as V,y as W,G as ee}from"./DD7AHv1g.js";import{V as te,a as ae,b as le}from"./MCwiuoYB.js";import{c as se}from"./BQXSZ5PM.js";import{m as oe,a as ne,b as re,c as ie,d as ce,e as ue,f as de,g as me,h as ve,i as fe,j as ye,k as ke,l as be,n as Ce,o as ge,p as Ve,q as _e,r as Pe,s as pe,t as x,V as T,v as Se}from"./hYNdz_3a.js";const xe=window.setInterval,he=se("v-alert-title"),Te=["success","info","warning","error"],Ie=O({border:{type:[Boolean,String],validator:e=>typeof e=="boolean"||["top","end","bottom","start"].includes(e)},borderColor:String,closable:Boolean,closeIcon:{type:E,default:"$close"},closeLabel:{type:String,default:"$vuetify.close"},icon:{type:[Boolean,String,Function,Object],default:null},modelValue:{type:Boolean,default:!0},prominent:Boolean,title:String,text:String,type:{type:String,validator:e=>Te.includes(e)},...oe(),...ne(),...re(),...ie(),...ce(),...ue(),...de(),...me(),...N(),...ve({variant:"flat"})},"VAlert"),h=j()({name:"VAlert",props:Ie(),emits:{"click:close":e=>!0,"update:modelValue":e=>!0},setup(e,f){let{emit:y,slots:a}=f;const c=q(e,"modelValue"),o=g(()=>{if(e.icon!==!1)return e.type?e.icon??`$${e.type}`:e.icon}),u=g(()=>({color:e.color??e.type,variant:e.variant})),{themeClasses:n}=K(e),{colorClasses:k,colorStyles:b,variantClasses:s}=fe(u),{densityClasses:t}=ye(e),{dimensionStyles:I}=ke(e),{elevationClasses:B}=be(e),{locationStyles:A}=Ce(e),{positionClasses:$}=ge(e),{roundedClasses:w}=Ve(e),{textColorClasses:D,textColorStyles:L}=_e(M(e,"borderColor")),{t:z}=G(),_=g(()=>({"aria-label":z(e.closeLabel),onClick(C){c.value=!1,y("click:close",C)}}));return()=>{const C=!!(a.prepend||o.value),F=!!(a.title||e.title),R=!!(a.close||e.closable);return c.value&&l(e.tag,{class:["v-alert",e.border&&{"v-alert--border":!!e.border,[`v-alert--border-${e.border===!0?"start":e.border}`]:!0},{"v-alert--prominent":e.prominent},n.value,k.value,t.value,B.value,$.value,w.value,s.value,e.class],style:[b.value,I.value,A.value,e.style],role:"alert"},{default:()=>{var P,p;return[Pe(!1,"v-alert"),e.border&&l("div",{key:"border",class:["v-alert__border",D.value],style:L.value},null),C&&l("div",{key:"prepend",class:"v-alert__prepend"},[a.prepend?l(x,{key:"prepend-defaults",disabled:!o.value,defaults:{VIcon:{density:e.density,icon:o.value,size:e.prominent?44:28}}},a.prepend):l(pe,{key:"prepend-icon",density:e.density,icon:o.value,size:e.prominent?44:28},null)]),l("div",{class:"v-alert__content"},[F&&l(he,{key:"title"},{default:()=>{var i;return[((i=a.title)==null?void 0:i.call(a))??e.title]}}),((P=a.text)==null?void 0:P.call(a))??e.text,(p=a.default)==null?void 0:p.call(a)]),a.append&&l("div",{key:"append",class:"v-alert__append"},[a.append()]),R&&l("div",{key:"close",class:"v-alert__close"},[a.close?l(x,{key:"close-defaults",defaults:{VBtn:{icon:e.closeIcon,size:"x-small",variant:"text"}}},{default:()=>{var i;return[(i=a.close)==null?void 0:i.call(a,{props:_.value})]}}):l(T,H({key:"close-btn",icon:e.closeIcon,size:"x-small",variant:"text"},_.value),null)])]}})}}}),Le=Q({__name:"print",setup(e){U({title:"Автопечать"});const{email:f,password:y}=J(),c=Z().public.backendUrl,o=S(!1);let u;const n=S([]);window.navigator.userAgent==="adminpage configuration"&&(o.value=!0,u=xe(()=>{const{data:s}=X(`${c}/order_print_for_assembly.php`,{method:"POST",body:{staff_login:f,staff_password:y}},"$7aIB8dKoKu");s.value&&s.value.html_print&&n.value.push(s.value.html_print)},1e3)),Y(()=>clearInterval(u));const k=s=>new Promise(t=>setTimeout(t,s)),b=()=>{const s=n.value.pop();if(s){const t=window.open("","_blank");t==null||t.document.write(s),k(200).then(()=>{t==null||t.document.close(),t==null||t.focus(),t==null||t.print()})}};return(s,t)=>(m(),d(te,null,{default:r(()=>[l(ae,null,{default:r(()=>t[0]||(t[0]=[v(" Автопечать заказов на сборку ")])),_:1}),l(le,null,{default:r(()=>[V(o)?(m(),d(h,{key:1,color:"success"},{default:r(()=>[t[2]||(t[2]=v(" Ожидание заказов для печати ")),l(Se,{color:"error",indeterminate:""})]),_:1})):(m(),d(h,{key:0,color:"error"},{default:r(()=>t[1]||(t[1]=[v(" Запустите Supermium с правильной настройкой для автопечати ")])),_:1})),V(n).length>0?(m(),d(T,{key:2,color:"primary",class:"mt-2",onClick:b},{default:r(()=>[v(" Печать ("+W(V(n).length)+") ",1)]),_:1})):ee("",!0)]),_:1})]),_:1}))}});export{Le as default};