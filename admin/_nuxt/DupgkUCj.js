import{u as c}from"./YPrUqGfY.js";import{d as f,D as V,r,V as _,C,A as t,q as b,t as h,z as e,B as k,G as u,H as x,F as T}from"./bgoYWPAe.js";import{V as w,a as y,b as g}from"./Cr7eoFdf.js";import{V as v,a as U}from"./M4MG1mPs.js";import{V as q}from"./ChHoO5pa.js";import{V as B}from"./CmFHYveS.js";import"./C7KYmi4a.js";import"./DAr8DC6_.js";/* empty css        */import"./D0wQKDx6.js";import"./vZHN5tzn.js";import"./BlkDD05S.js";import"./QH28GrQP.js";const I=f({__name:"products",setup(F){c({title:"Товары"});const{email:n,password:m}=V(),i=b().public.backendUrl,d=[{title:"Название",key:"name"},{title:"Артикул",key:"art"}],s=r([]),a=r("");return _(a,async()=>{const{data:l}=await T(`${i}/search.php`,{method:"POST",body:{email:n,password:m},query:{search:a.value}},"$cOWNPPgU8q");s.value=l.value}),(l,o)=>(h(),C(w,null,{default:t(()=>[e(y,null,{default:t(()=>o[1]||(o[1]=[k(" Товары ")])),_:1}),e(g,null,{default:t(()=>[e(v,null,{default:t(()=>[e(U,{cols:"12",md:"6"},{default:t(()=>[e(q,{modelValue:u(a),"onUpdate:modelValue":o[0]||(o[0]=p=>x(a)?a.value=p:null),label:"Наименование, артикул"},null,8,["modelValue"])]),_:1})]),_:1}),e(B,{headers:d,items:u(s)},null,8,["items"])]),_:1})]),_:1}))}});export{I as default};
