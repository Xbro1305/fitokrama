/* empty css        */import{m as C,g as S}from"./C7KYmi4a.js";import{aZ as c,aA as u,K as b,L as k,i as N,h as j}from"./bgoYWPAe.js";const V=c.reduce((t,a)=>(t[a]={type:[Boolean,String,Number],default:!1},t),{}),L=c.reduce((t,a)=>{const e="offset"+u(a);return t[e]={type:[String,Number],default:null},t},{}),v=c.reduce((t,a)=>{const e="order"+u(a);return t[e]={type:[String,Number],default:null},t},{}),y={col:Object.keys(V),offset:Object.keys(L),order:Object.keys(v)};function G(t,a,e){let s=t;if(!(e==null||e===!1)){if(a){const n=a.replace(t,"");s+=`-${n}`}return t==="col"&&(s="v-"+s),t==="col"&&(e===""||e===!0)||(s+=`-${e}`),s.toLowerCase()}}const _=["auto","start","end","center","baseline","stretch"],I=b({cols:{type:[Boolean,String,Number],default:!1},...V,offset:{type:[String,Number],default:null},...L,order:{type:[String,Number],default:null},...v,alignSelf:{type:String,default:null,validator:t=>_.includes(t)},...C(),...S()},"VCol"),J=k()({name:"VCol",props:I(),setup(t,a){let{slots:e}=a;const s=N(()=>{const n=[];let l;for(l in y)y[l].forEach(o=>{const i=t[o],g=G(l,o,i);g&&n.push(g)});const r=n.some(o=>o.startsWith("v-col-"));return n.push({"v-col":!r||!t.cols,[`v-col-${t.cols}`]:t.cols,[`offset-${t.offset}`]:t.offset,[`order-${t.order}`]:t.order,[`align-self-${t.alignSelf}`]:t.alignSelf}),n});return()=>{var n;return j(t.tag,{class:[s.value,t.class],style:t.style},(n=e.default)==null?void 0:n.call(e))}}}),f=["start","end","center"],$=["space-between","space-around","space-evenly"];function d(t,a){return c.reduce((e,s)=>{const n=t+u(s);return e[n]=a(),e},{})}const R=[...f,"baseline","stretch"],h=t=>R.includes(t),w=d("align",()=>({type:String,default:null,validator:h})),T=[...f,...$],P=t=>T.includes(t),A=d("justify",()=>({type:String,default:null,validator:P})),U=[...f,...$,"stretch"],E=t=>U.includes(t),O=d("alignContent",()=>({type:String,default:null,validator:E})),m={align:Object.keys(w),justify:Object.keys(A),alignContent:Object.keys(O)},B={align:"align",justify:"justify",alignContent:"align-content"};function K(t,a,e){let s=B[t];if(e!=null){if(a){const n=a.replace(t,"");s+=`-${n}`}return s+=`-${e}`,s.toLowerCase()}}const M=b({dense:Boolean,noGutters:Boolean,align:{type:String,default:null,validator:h},...w,justify:{type:String,default:null,validator:P},...A,alignContent:{type:String,default:null,validator:E},...O,...C(),...S()},"VRow"),W=k()({name:"VRow",props:M(),setup(t,a){let{slots:e}=a;const s=N(()=>{const n=[];let l;for(l in m)m[l].forEach(r=>{const o=t[r],i=K(l,r,o);i&&n.push(i)});return n.push({"v-row--no-gutters":t.noGutters,"v-row--dense":t.dense,[`align-${t.align}`]:t.align,[`justify-${t.justify}`]:t.justify,[`align-content-${t.alignContent}`]:t.alignContent}),n});return()=>{var n;return j(t.tag,{class:["v-row",s.value,t.class],style:t.style},(n=e.default)==null?void 0:n.call(e))}}});export{W as V,J as a};