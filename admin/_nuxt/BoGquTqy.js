/* empty css        */import{u as Oe,a as E,k as de,d as _,n as ve,e as H,m as Ue,b as De,c as Ge,g as Ke,h as Fe,i as Ye,j as We,o as Xe,V as Z}from"./dM_3ga0O.js";import{F as T,a3 as J,i,r as Q,am as O,ak as fe,O as L,o as me,Y as ye,b9 as Ze,X as qe,ar as Je,aS as Qe,e as ge,ag as $,H as B,L as ee,z as f,ba as K,ay as te,h as he,G as be,M as Se,R as q,P as et,ah as tt,aa as D,J as at,ae as lt,S as pe,ai as nt}from"./CfqLg0YU.js";import{c as ot}from"./BTv1UrFX.js";import{u as Ve}from"./COEvdUSt.js";const G=Symbol.for("vuetify:layout"),xe=Symbol.for("vuetify:layout-item"),ue=1e3,st=T({overlaps:{type:Array,default:()=>[]},fullHeight:Boolean},"layout"),ut=T({name:{type:String},order:{type:[Number,String],default:0},absolute:Boolean},"layout-item");function it(){const e=J(G);if(!e)throw new Error("[Vuetify] Could not find injected layout");return{getLayoutItem:e.getLayoutItem,mainRect:e.mainRect,mainStyles:e.mainStyles}}function rt(e){const n=J(G);if(!n)throw new Error("[Vuetify] Could not find injected layout");const t=e.id??`layout-item-${qe()}`,o=fe("useLayoutItem");ye(xe,{id:t});const a=L(!1);Je(()=>a.value=!0),Qe(()=>a.value=!1);const{layoutItemStyles:l,layoutItemScrimStyles:s}=n.register(o,{...e,active:i(()=>a.value?!1:e.active.value),id:t});return ge(()=>n.unregister(t)),{layoutItemStyles:l,layoutRect:n.layoutRect,layoutItemScrimStyles:s}}const ct=(e,n,t,o)=>{let a={top:0,left:0,right:0,bottom:0};const l=[{id:"",layer:{...a}}];for(const s of e){const u=n.get(s),c=t.get(s),g=o.get(s);if(!u||!c||!g)continue;const y={...a,[u.value]:parseInt(a[u.value],10)+(g.value?parseInt(c.value,10):0)};l.push({id:s,layer:y}),a=y}return l};function dt(e){const n=J(G,null),t=i(()=>n?n.rootZIndex.value-100:ue),o=Q([]),a=O(new Map),l=O(new Map),s=O(new Map),u=O(new Map),c=O(new Map),{resizeRef:g,contentRect:y}=Oe(),S=i(()=>{const m=new Map,I=e.overlaps??[];for(const v of I.filter(b=>b.includes(":"))){const[b,h]=v.split(":");if(!o.value.includes(b)||!o.value.includes(h))continue;const w=a.get(b),N=a.get(h),j=l.get(b),M=l.get(h);!w||!N||!j||!M||(m.set(h,{position:w.value,amount:parseInt(j.value,10)}),m.set(b,{position:N.value,amount:-parseInt(M.value,10)}))}return m}),p=i(()=>{const m=[...new Set([...s.values()].map(v=>v.value))].sort((v,b)=>v-b),I=[];for(const v of m){const b=o.value.filter(h=>{var w;return((w=s.get(h))==null?void 0:w.value)===v});I.push(...b)}return ct(I,a,l,u)}),x=i(()=>!Array.from(c.values()).some(m=>m.value)),d=i(()=>p.value[p.value.length-1].layer),C=i(()=>({"--v-layout-left":$(d.value.left),"--v-layout-right":$(d.value.right),"--v-layout-top":$(d.value.top),"--v-layout-bottom":$(d.value.bottom),...x.value?void 0:{transition:"none"}})),V=i(()=>p.value.slice(1).map((m,I)=>{let{id:v}=m;const{layer:b}=p.value[I],h=l.get(v),w=a.get(v);return{id:v,...b,size:Number(h.value),position:w.value}})),A=m=>V.value.find(I=>I.id===m),r=fe("createLayout"),k=L(!1);me(()=>{k.value=!0}),ye(G,{register:(m,I)=>{let{id:v,order:b,position:h,layoutSize:w,elementSize:N,active:j,disableTransitions:M,absolute:He}=I;s.set(v,b),a.set(v,h),l.set(v,w),u.set(v,j),M&&c.set(v,M);const ne=Ze(xe,r==null?void 0:r.vnode).indexOf(m);ne>-1?o.value.splice(ne,0,v):o.value.push(v);const oe=i(()=>V.value.findIndex(z=>z.id===v)),F=i(()=>t.value+p.value.length*2-oe.value*2),_e=i(()=>{const z=h.value==="left"||h.value==="right",Y=h.value==="right",Me=h.value==="bottom",W=N.value??w.value,ze=W===0?"%":"px",se={[h.value]:0,zIndex:F.value,transform:`translate${z?"X":"Y"}(${(j.value?0:-(W===0?100:W))*(Y||Me?-1:1)}${ze})`,position:He.value||t.value!==ue?"absolute":"fixed",...x.value?void 0:{transition:"none"}};if(!k.value)return se;const P=V.value[oe.value];if(!P)throw new Error(`[Vuetify] Could not find layout item "${v}"`);const X=S.value.get(v);return X&&(P[X.position]+=X.amount),{...se,height:z?`calc(100% - ${P.top}px - ${P.bottom}px)`:N.value?`${N.value}px`:void 0,left:Y?void 0:`${P.left}px`,right:Y?`${P.right}px`:void 0,top:h.value!=="bottom"?`${P.top}px`:void 0,bottom:h.value!=="top"?`${P.bottom}px`:void 0,width:z?N.value?`${N.value}px`:void 0:`calc(100% - ${P.left}px - ${P.right}px)`}}),je=i(()=>({zIndex:F.value-1}));return{layoutItemStyles:_e,layoutItemScrimStyles:je,zIndex:F}},unregister:m=>{s.delete(m),a.delete(m),l.delete(m),u.delete(m),c.delete(m),o.value=o.value.filter(I=>I!==m)},mainRect:d,mainStyles:C,getLayoutItem:A,items:V,layoutRect:y,rootZIndex:t});const R=i(()=>["v-layout",{"v-layout--full-height":e.fullHeight}]),U=i(()=>({zIndex:n?t.value:void 0,position:n?"relative":void 0,overflow:n?"hidden":void 0}));return{layoutClasses:R,layoutStyles:U,getLayoutItem:A,items:V,layoutRect:y,layoutRef:g}}const vt=T({fluid:{type:Boolean,default:!1},...E(),...de(),..._()},"VContainer"),Rt=B()({name:"VContainer",props:vt(),setup(e,n){let{slots:t}=n;const{rtlClasses:o}=ee(),{dimensionStyles:a}=ve(e);return H(()=>f(e.tag,{class:["v-container",{"v-container--fluid":e.fluid},o.value,e.class],style:[a.value,e.style]},t)),{}}}),Ce=K.reduce((e,n)=>(e[n]={type:[Boolean,String,Number],default:!1},e),{}),Ie=K.reduce((e,n)=>{const t="offset"+te(n);return e[t]={type:[String,Number],default:null},e},{}),ke=K.reduce((e,n)=>{const t="order"+te(n);return e[t]={type:[String,Number],default:null},e},{}),ie={col:Object.keys(Ce),offset:Object.keys(Ie),order:Object.keys(ke)};function ft(e,n,t){let o=e;if(!(t==null||t===!1)){if(n){const a=n.replace(e,"");o+=`-${a}`}return e==="col"&&(o="v-"+o),e==="col"&&(t===""||t===!0)||(o+=`-${t}`),o.toLowerCase()}}const mt=["auto","start","end","center","baseline","stretch"],yt=T({cols:{type:[Boolean,String,Number],default:!1},...Ce,offset:{type:[String,Number],default:null},...Ie,order:{type:[String,Number],default:null},...ke,alignSelf:{type:String,default:null,validator:e=>mt.includes(e)},...E(),..._()},"VCol"),Et=B()({name:"VCol",props:yt(),setup(e,n){let{slots:t}=n;const o=i(()=>{const a=[];let l;for(l in ie)ie[l].forEach(u=>{const c=e[u],g=ft(l,u,c);g&&a.push(g)});const s=a.some(u=>u.startsWith("v-col-"));return a.push({"v-col":!s||!e.cols,[`v-col-${e.cols}`]:e.cols,[`offset-${e.offset}`]:e.offset,[`order-${e.order}`]:e.order,[`align-self-${e.alignSelf}`]:e.alignSelf}),a});return()=>{var a;return he(e.tag,{class:[o.value,e.class],style:e.style},(a=t.default)==null?void 0:a.call(t))}}}),ae=["start","end","center"],Te=["space-between","space-around","space-evenly"];function le(e,n){return K.reduce((t,o)=>{const a=e+te(o);return t[a]=n(),t},{})}const gt=[...ae,"baseline","stretch"],we=e=>gt.includes(e),Pe=le("align",()=>({type:String,default:null,validator:we})),ht=[...ae,...Te],Le=e=>ht.includes(e),Be=le("justify",()=>({type:String,default:null,validator:Le})),bt=[...ae,...Te,"stretch"],Ne=e=>bt.includes(e),$e=le("alignContent",()=>({type:String,default:null,validator:Ne})),re={align:Object.keys(Pe),justify:Object.keys(Be),alignContent:Object.keys($e)},St={align:"align",justify:"justify",alignContent:"align-content"};function pt(e,n,t){let o=St[e];if(t!=null){if(n){const a=n.replace(e,"");o+=`-${a}`}return o+=`-${t}`,o.toLowerCase()}}const Vt=T({dense:Boolean,noGutters:Boolean,align:{type:String,default:null,validator:we},...Pe,justify:{type:String,default:null,validator:Le},...Be,alignContent:{type:String,default:null,validator:Ne},...$e,...E(),..._()},"VRow"),Ht=B()({name:"VRow",props:Vt(),setup(e,n){let{slots:t}=n;const o=i(()=>{const a=[];let l;for(l in re)re[l].forEach(s=>{const u=e[s],c=pt(l,s,u);c&&a.push(c)});return a.push({"v-row--no-gutters":e.noGutters,"v-row--dense":e.dense,[`align-${e.align}`]:e.align,[`justify-${e.justify}`]:e.justify,[`align-content-${e.alignContent}`]:e.alignContent}),a});return()=>{var a;return he(e.tag,{class:["v-row",o.value,e.class],style:e.style},(a=t.default)==null?void 0:a.call(t))}}}),xt=T({...E(),...st({fullHeight:!0}),...be()},"VApp"),_t=B()({name:"VApp",props:xt(),setup(e,n){let{slots:t}=n;const o=Se(e),{layoutClasses:a,getLayoutItem:l,items:s,layoutRef:u}=dt(e),{rtlClasses:c}=ee();return H(()=>{var g;return f("div",{ref:u,class:["v-application",o.themeClasses.value,a.value,c.value,e.class],style:[e.style]},[f("div",{class:"v-application__wrap"},[(g=t.default)==null?void 0:g.call(t)])])}),{getLayoutItem:l,items:s,theme:o}}}),Ae=T({text:String,...E(),..._()},"VToolbarTitle"),Re=B()({name:"VToolbarTitle",props:Ae(),setup(e,n){let{slots:t}=n;return H(()=>{const o=!!(t.default||t.text||e.text);return f(e.tag,{class:["v-toolbar-title",e.class],style:e.style},{default:()=>{var a;return[o&&f("div",{class:"v-toolbar-title__placeholder"},[t.text?t.text():e.text,(a=t.default)==null?void 0:a.call(t)])]}})}),{}}}),Ct=[null,"prominent","default","comfortable","compact"],Ee=T({absolute:Boolean,collapse:Boolean,color:String,density:{type:String,default:"default",validator:e=>Ct.includes(e)},extended:Boolean,extensionHeight:{type:[Number,String],default:48},flat:Boolean,floating:Boolean,height:{type:[Number,String],default:64},image:String,title:String,...Ue(),...E(),...De(),...Ge(),..._({tag:"header"}),...be()},"VToolbar"),ce=B()({name:"VToolbar",props:Ee(),setup(e,n){var x;let{slots:t}=n;const{backgroundColorClasses:o,backgroundColorStyles:a}=Ke(q(e,"color")),{borderClasses:l}=Fe(e),{elevationClasses:s}=Ye(e),{roundedClasses:u}=We(e),{themeClasses:c}=Se(e),{rtlClasses:g}=ee(),y=L(!!(e.extended||(x=t.extension)!=null&&x.call(t))),S=i(()=>parseInt(Number(e.height)+(e.density==="prominent"?Number(e.height):0)-(e.density==="comfortable"?8:0)-(e.density==="compact"?16:0),10)),p=i(()=>y.value?parseInt(Number(e.extensionHeight)+(e.density==="prominent"?Number(e.extensionHeight):0)-(e.density==="comfortable"?4:0)-(e.density==="compact"?8:0),10):0);return et({VBtn:{variant:"text"}}),H(()=>{var A;const d=!!(e.title||t.title),C=!!(t.image||e.image),V=(A=t.extension)==null?void 0:A.call(t);return y.value=!!(e.extended||V),f(e.tag,{class:["v-toolbar",{"v-toolbar--absolute":e.absolute,"v-toolbar--collapse":e.collapse,"v-toolbar--flat":e.flat,"v-toolbar--floating":e.floating,[`v-toolbar--density-${e.density}`]:!0},o.value,l.value,s.value,u.value,c.value,g.value,e.class],style:[a.value,e.style]},{default:()=>[C&&f("div",{key:"image",class:"v-toolbar__image"},[t.image?f(Z,{key:"image-defaults",disabled:!e.image,defaults:{VImg:{cover:!0,src:e.image}}},t.image):f(Xe,{key:"image-img",cover:!0,src:e.image},null)]),f(Z,{defaults:{VTabs:{height:$(S.value)}}},{default:()=>{var r,k,R;return[f("div",{class:"v-toolbar__content",style:{height:$(S.value)}},[t.prepend&&f("div",{class:"v-toolbar__prepend"},[(r=t.prepend)==null?void 0:r.call(t)]),d&&f(Re,{key:"title",text:e.title},{text:t.title}),(k=t.default)==null?void 0:k.call(t),t.append&&f("div",{class:"v-toolbar__append"},[(R=t.append)==null?void 0:R.call(t)])])]}}),f(Z,{defaults:{VTabs:{height:$(p.value)}}},{default:()=>[f(ot,null,{default:()=>[y.value&&f("div",{class:"v-toolbar__extension",style:{height:$(p.value)}},[V])]})]})]})}),{contentHeight:S,extensionHeight:p}}}),It=T({scrollTarget:{type:String},scrollThreshold:{type:[String,Number],default:300}},"scroll");function kt(e){let n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{};const{canScroll:t}=n;let o=0,a=0;const l=Q(null),s=L(0),u=L(0),c=L(0),g=L(!1),y=L(!1),S=i(()=>Number(e.scrollThreshold)),p=i(()=>tt((S.value-s.value)/S.value||0)),x=()=>{const d=l.value;if(!d||t&&!t.value)return;o=s.value,s.value="window"in d?d.pageYOffset:d.scrollTop;const C=d instanceof Window?document.documentElement.scrollHeight:d.scrollHeight;if(a!==C){a=C;return}y.value=s.value<o,c.value=Math.abs(s.value-S.value)};return D(y,()=>{u.value=u.value||s.value}),D(g,()=>{u.value=0}),me(()=>{D(()=>e.scrollTarget,d=>{var V;const C=d?document.querySelector(d):window;C&&C!==l.value&&((V=l.value)==null||V.removeEventListener("scroll",x),l.value=C,l.value.addEventListener("scroll",x,{passive:!0}))},{immediate:!0})}),ge(()=>{var d;(d=l.value)==null||d.removeEventListener("scroll",x)}),t&&D(t,x,{immediate:!0}),{scrollThreshold:S,currentScroll:s,currentThreshold:c,isScrollActive:g,scrollRatio:p,isScrollingUp:y,savedScroll:u}}const Tt=T({scrollBehavior:String,modelValue:{type:Boolean,default:!0},location:{type:String,default:"top",validator:e=>["top","bottom"].includes(e)},...Ee(),...ut(),...It(),height:{type:[Number,String],default:64}},"VAppBar"),jt=B()({name:"VAppBar",props:Tt(),emits:{"update:modelValue":e=>!0},setup(e,n){let{slots:t}=n;const o=Q(),a=at(e,"modelValue"),l=i(()=>{var k;const r=new Set(((k=e.scrollBehavior)==null?void 0:k.split(" "))??[]);return{hide:r.has("hide"),fullyHide:r.has("fully-hide"),inverted:r.has("inverted"),collapse:r.has("collapse"),elevate:r.has("elevate"),fadeImage:r.has("fade-image")}}),s=i(()=>{const r=l.value;return r.hide||r.fullyHide||r.inverted||r.collapse||r.elevate||r.fadeImage||!a.value}),{currentScroll:u,scrollThreshold:c,isScrollingUp:g,scrollRatio:y}=kt(e,{canScroll:s}),S=i(()=>l.value.hide||l.value.fullyHide),p=i(()=>e.collapse||l.value.collapse&&(l.value.inverted?y.value>0:y.value===0)),x=i(()=>e.flat||l.value.fullyHide&&!a.value||l.value.elevate&&(l.value.inverted?u.value>0:u.value===0)),d=i(()=>l.value.fadeImage?l.value.inverted?1-y.value:y.value:void 0),C=i(()=>{var R,U;if(l.value.hide&&l.value.inverted)return 0;const r=((R=o.value)==null?void 0:R.contentHeight)??0,k=((U=o.value)==null?void 0:U.extensionHeight)??0;return S.value?u.value<c.value||l.value.fullyHide?r+k:r:r+k});lt(i(()=>!!e.scrollBehavior),()=>{nt(()=>{S.value?l.value.inverted?a.value=u.value>c.value:a.value=g.value||u.value<c.value:a.value=!0})});const{ssrBootStyles:V}=Ve(),{layoutItemStyles:A}=rt({id:e.name,order:i(()=>parseInt(e.order,10)),position:q(e,"location"),layoutSize:C,elementSize:L(void 0),active:a,absolute:q(e,"absolute")});return H(()=>{const r=ce.filterProps(e);return f(ce,pe({ref:o,class:["v-app-bar",{"v-app-bar--bottom":e.location==="bottom"},e.class],style:[{...A.value,"--v-toolbar-image-opacity":d.value,height:void 0,...V.value},e.style]},r,{collapse:p.value,flat:x.value}),t)}),{}}}),Mt=B()({name:"VAppBarTitle",props:Ae(),setup(e,n){let{slots:t}=n;return H(()=>f(Re,pe(e,{class:"v-app-bar-title"}),t)),{}}}),wt=T({scrollable:Boolean,...E(),...de(),..._({tag:"main"})},"VMain"),zt=B()({name:"VMain",props:wt(),setup(e,n){let{slots:t}=n;const{dimensionStyles:o}=ve(e),{mainStyles:a}=it(),{ssrBootStyles:l}=Ve();return H(()=>f(e.tag,{class:["v-main",{"v-main--scrollable":e.scrollable},e.class],style:[a.value,l.value,o.value,e.style]},{default:()=>{var s,u;return[e.scrollable?f("div",{class:"v-main__scroller"},[(s=t.default)==null?void 0:s.call(t)]):(u=t.default)==null?void 0:u.call(t)]}})),{}}});export{_t as V,Mt as a,jt as b,Rt as c,Ht as d,Et as e,zt as f,ut as m,rt as u};