import{u as w}from"./YPrUqGfY.js";import{d as T,D as x,r as c,E as S,C as f,A as a,q as B,F as p,t as _,z as l,B as h,G as r,H as D,y as N,I as P}from"./bgoYWPAe.js";import{u as $}from"./DDvaEP9s.js";import{V as A,a as O,b as U}from"./Cr7eoFdf.js";import{V as z}from"./CmFHYveS.js";import{V as E}from"./vZHN5tzn.js";import"./C7KYmi4a.js";import"./DAr8DC6_.js";import"./ChHoO5pa.js";import"./D0wQKDx6.js";import"./BlkDD05S.js";import"./QH28GrQP.js";const Z=T({__name:"assembly_post",async setup(F){let o,n;w({title:"Сборка на почту"});const{email:u,password:m}=x(),{showError:V,showSuccess:y}=$(),i=B().public.backendUrl,d=c([]),s=c([]),{data:v}=([o,n]=S(()=>p(`${i}/orders_to_send.php`,{method:"POST",body:{staff_login:u,staff_password:m}},"$KCa4DdInez")),o=await o,n(),o);d.value=v.value;const b=[{title:"Название",key:"name"},{title:"Заказов",key:"orders_count",value:t=>t.orders.length},{title:"Заказы",key:"orders",value:t=>t.orders.map(e=>e.number).join(", ")}],g=t=>new Promise(e=>setTimeout(e,t)),C=async()=>{const{data:t}=await p(`${i}/orders_list_to_send.php`,{method:"POST",body:{staff_login:u,staff_password:m,methods:s.value}},"$tiMBmtbOP6");if(t.value.message?y(t.value.message):t.value.error&&V(t.value.error),t.value.for_print){const e=window.open("","_blank");e==null||e.document.write(t.value.for_print),g(200).then(()=>{e==null||e.document.close(),e==null||e.focus(),e==null||e.print()})}};return(t,e)=>(_(),f(A,null,{default:a(()=>[l(O,null,{default:a(()=>e[1]||(e[1]=[h(" Сборка на почту ")])),_:1}),l(U,null,{default:a(()=>[l(z,{modelValue:r(s),"onUpdate:modelValue":e[0]||(e[0]=k=>D(s)?s.value=k:null),headers:b,items:r(d),"show-select":""},null,8,["modelValue","items"]),r(s).length>0?(_(),f(E,{key:0,color:"primary",onClick:C},{default:a(()=>[h(" Распечатать список на отправку ("+N(r(s).length)+") ",1)]),_:1})):P("",!0)]),_:1})]),_:1}))}});export{Z as default};
