import{u as c}from"./C8wjtVdh.js";import{d as f,C as V,r as l,D as _,E as C,A as a,q as b,t as h,z as e,B as k,F as u,H as x,I as T}from"./DD7AHv1g.js";import{V as w,a as y,b as g}from"./MCwiuoYB.js";import{a as v,V as U}from"./Bv0GgKH3.js";import{V as q}from"./DNOu9Y9V.js";import{V as B}from"./ChHQ47om.js";import"./hYNdz_3a.js";import"./BQXSZ5PM.js";/* empty css        */import"./D-Pd036u.js";import"./DWGaNmQL.js";import"./DI6sJGf3.js";import"./9V3CzVYM.js";import"./Dgn9K1u6.js";import"./JcpH5EyD.js";const K=f({__name:"products",setup(F){c({title:"Товары"});const{email:m,password:i}=V(),n=b().public.backendUrl,d=[{title:"Название",key:"name"},{title:"Артикул",key:"art"}],s=l([]),t=l("");return _(t,async()=>{const{data:r}=await T(`${n}/search.php`,{method:"POST",body:{email:m,password:i},query:{search:t.value}},"$cOWNPPgU8q");s.value=r.value}),(r,o)=>(h(),C(w,null,{default:a(()=>[e(y,null,{default:a(()=>o[1]||(o[1]=[k(" Товары ")])),_:1}),e(g,null,{default:a(()=>[e(v,null,{default:a(()=>[e(U,{cols:"12",md:"6"},{default:a(()=>[e(q,{modelValue:u(t),"onUpdate:modelValue":o[0]||(o[0]=p=>x(t)?t.value=p:null),label:"Наименование, артикул"},null,8,["modelValue"])]),_:1})]),_:1}),e(B,{headers:d,items:u(s)},null,8,["items"])]),_:1})]),_:1}))}});export{K as default};