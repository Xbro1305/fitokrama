import{m as o,w as f,a as y,f as V,x as k,g as P,h as C,E as S,i as h,j as z,p as B,F as I,u as x,O as A,s as D,t as F,r as R}from"./BrzFJEKX.js";import{N as i,aB as T,aY as N,h as O,M as _,P as b,Q as j,S as w,z as l}from"./B1_Hl8uH.js";function Y(e){let r=arguments.length>1&&arguments[1]!==void 0?arguments[1]:"div",s=arguments.length>2?arguments[2]:void 0;return i()({name:s??T(N(e.replace(/__/g,"-"))),props:{tag:{type:String,default:r},...o()},setup(a,u){let{slots:t}=u;return()=>{var n;return O(a.tag,{class:[e,a.class],style:a.style},(n=t.default)==null?void 0:n.call(t))}}})}const E=_({start:Boolean,end:Boolean,icon:b,image:String,text:String,...f(),...o(),...y(),...V(),...k(),...P(),...j(),...C({variant:"flat"})},"VAvatar"),q=i()({name:"VAvatar",props:E(),setup(e,r){let{slots:s}=r;const{themeClasses:a}=w(e),{borderClasses:u}=S(e),{colorClasses:t,colorStyles:n,variantClasses:c}=h(e),{densityClasses:m}=z(e),{roundedClasses:d}=B(e),{sizeClasses:v,sizeStyles:g}=I(e);return x(()=>l(e.tag,{class:["v-avatar",{"v-avatar--start":e.start,"v-avatar--end":e.end},a.value,u.value,t.value,m.value,d.value,v.value,c.value,e.class],style:[n.value,g.value,e.style]},{default:()=>[s.default?l(F,{key:"content-defaults",defaults:{VImg:{cover:!0,src:e.image},VIcon:{icon:e.icon}}},{default:()=>[s.default()]}):e.image?l(A,{key:"image",src:e.image,alt:"",cover:!0},null):e.icon?l(D,{key:"icon",icon:e.icon},null):e.text,R(!1,"v-avatar")]})),{}}});export{q as V,Y as c};