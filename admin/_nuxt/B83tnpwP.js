import{bH as o,r as u,aw as f,E as d,e as v,bI as l,bJ as i,bK as h,bL as b}from"./CD-ciyMC.js";function I(t,a={}){const e=a.head||o();if(e)return e.ssr?e.push(t,a):m(e,t,a)}function m(t,a,e={}){const s=u(!1),n=u({});f(()=>{n.value=s.value?{}:h(a)});const r=t.push(n.value,e);return d(n,c=>{r.patch(c)}),b()&&(v(()=>{r.dispose()}),l(()=>{s.value=!0}),i(()=>{s.value=!1})),r}export{I as u};
