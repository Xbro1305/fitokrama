import{u as p}from"./C8wjtVdh.js";import{d as u,C as c,J as f,K as _,E as k,A as a,q as b,I as C,t as V,z as r,B as h,F as x}from"./DD7AHv1g.js";import{V as y,a as T,b as v}from"./MCwiuoYB.js";import{V as w}from"./ChHQ47om.js";import"./hYNdz_3a.js";import"./BQXSZ5PM.js";import"./DNOu9Y9V.js";import"./D-Pd036u.js";import"./DWGaNmQL.js";import"./DI6sJGf3.js";import"./9V3CzVYM.js";import"./Dgn9K1u6.js";import"./JcpH5EyD.js";const K=u({__name:"index",async setup(g){let t,o;p({title:"Все заказы"});const{email:n,password:m}=c(),d=b().public.backendUrl,l=[{title:"ID",key:"id"},{title:"Номер",key:"number"},{title:"Дата",key:"datetime_create"},{title:"Сумма",key:"sum"}],{data:e}=([t,o]=f(()=>C(`${d}/admin/orders.php`,{method:"POST",body:{email:n,password:m,date_from:"2000-01-01",date_to:"2025-01-01"}},"$pqtWcjQkdb")),t=await t,o(),t),s=_([]);return e.value&&e.value.orders&&s.push(...e.value.orders),(B,i)=>(V(),k(y,null,{default:a(()=>[r(T,null,{default:a(()=>i[0]||(i[0]=[h(" Все заказы ")])),_:1}),r(v,null,{default:a(()=>[r(w,{headers:l,items:x(s)},null,8,["items"])]),_:1})]),_:1}))}});export{K as default};
