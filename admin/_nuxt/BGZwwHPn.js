import{O as L,aZ as W,i as r,r as R,W as U,aQ as Re,bj as me,av as P,o as ae,aV as fe,b_ as $e,aU as Ne,bI as Ee,bJ as ze,e as ye,aH as N,P as j,a1 as ge,a6 as Z,R as E,aN as le,ad as be,T as z,z as c,a3 as Me,a4 as Oe,a5 as he,a8 as De,a7 as ne,b1 as Ue,bE as Fe,a9 as pe,ag as je,bF as Ye,aa as oe,ab as Ke,ah as Se,bk as xe,E as O,b$ as We,bG as re,al as Ze,c0 as Ge,an as Y,ao as K,U as Xe,aW as qe,aw as Ve,aF as Je,d as Qe,C as et,t as tt,F as at,A as Q,K as lt,B as ce,y as nt,G as ee,I as ot,aL as st,a2 as it,a$ as ut,aj as te,bc as rt,ae as ct,aP as vt,bX as dt,aI as mt}from"./CD-ciyMC.js";import{V as ft}from"./tHn_lS1N.js";import{u as ke}from"./BT2HD4FG.js";const F=Symbol.for("vuetify:layout"),Te=Symbol.for("vuetify:layout-item"),ve=1e3,yt=L({overlaps:{type:Array,default:()=>[]},fullHeight:Boolean},"layout"),gt=L({name:{type:String},order:{type:[Number,String],default:0},absolute:Boolean},"layout-item");function Ie(){const e=W(F);if(!e)throw new Error("[Vuetify] Could not find injected layout");return{getLayoutItem:e.getLayoutItem,mainRect:e.mainRect,mainStyles:e.mainStyles}}function bt(e){const o=W(F);if(!o)throw new Error("[Vuetify] Could not find injected layout");const t=e.id??`layout-item-${Ne()}`,a=me("useLayoutItem");fe(Te,{id:t});const l=P(!1);Ee(()=>l.value=!0),ze(()=>l.value=!1);const{layoutItemStyles:n,layoutItemScrimStyles:s}=o.register(a,{...e,active:r(()=>l.value?!1:e.active.value),id:t});return ye(()=>o.unregister(t)),{layoutItemStyles:n,layoutRect:o.layoutRect,layoutItemScrimStyles:s}}const ht=(e,o,t,a)=>{let l={top:0,left:0,right:0,bottom:0};const n=[{id:"",layer:{...l}}];for(const s of e){const i=o.get(s),y=t.get(s),h=a.get(s);if(!i||!y||!h)continue;const g={...l,[i.value]:parseInt(l[i.value],10)+(h.value?parseInt(y.value,10):0)};n.push({id:s,layer:g}),l=g}return n};function pt(e){const o=W(F,null),t=r(()=>o?o.rootZIndex.value-100:ve),a=R([]),l=U(new Map),n=U(new Map),s=U(new Map),i=U(new Map),y=U(new Map),{resizeRef:h,contentRect:g}=Re(),p=r(()=>{const f=new Map,w=e.overlaps??[];for(const m of w.filter(k=>k.includes(":"))){const[k,b]=m.split(":");if(!a.value.includes(k)||!a.value.includes(b))continue;const d=l.get(k),C=l.get(b),B=n.get(k),$=n.get(b);!d||!C||!B||!$||(f.set(b,{position:d.value,amount:parseInt(B.value,10)}),f.set(k,{position:C.value,amount:-parseInt($.value,10)}))}return f}),S=r(()=>{const f=[...new Set([...s.values()].map(m=>m.value))].sort((m,k)=>m-k),w=[];for(const m of f){const k=a.value.filter(b=>{var d;return((d=s.get(b))==null?void 0:d.value)===m});w.push(...k)}return ht(w,l,n,i)}),T=r(()=>!Array.from(y.values()).some(f=>f.value)),v=r(()=>S.value[S.value.length-1].layer),I=r(()=>({"--v-layout-left":N(v.value.left),"--v-layout-right":N(v.value.right),"--v-layout-top":N(v.value.top),"--v-layout-bottom":N(v.value.bottom),...T.value?void 0:{transition:"none"}})),x=r(()=>S.value.slice(1).map((f,w)=>{let{id:m}=f;const{layer:k}=S.value[w],b=n.get(m),d=l.get(m);return{id:m,...k,size:Number(b.value),position:d.value}})),A=f=>x.value.find(w=>w.id===f),u=me("createLayout"),V=P(!1);ae(()=>{V.value=!0}),fe(F,{register:(f,w)=>{let{id:m,order:k,position:b,layoutSize:d,elementSize:C,active:B,disableTransitions:$,absolute:Be}=w;s.set(m,k),l.set(m,b),n.set(m,d),i.set(m,B),$&&y.set(m,$);const se=$e(Te,u==null?void 0:u.vnode).indexOf(f);se>-1?a.value.splice(se,0,m):a.value.push(m);const ie=r(()=>x.value.findIndex(D=>D.id===m)),G=r(()=>t.value+S.value.length*2-ie.value*2),_e=r(()=>{const D=b.value==="left"||b.value==="right",X=b.value==="right",Ae=b.value==="bottom",q=C.value??d.value,He=q===0?"%":"px",ue={[b.value]:0,zIndex:G.value,transform:`translate${D?"X":"Y"}(${(B.value?0:-(q===0?100:q))*(X||Ae?-1:1)}${He})`,position:Be.value||t.value!==ve?"absolute":"fixed",...T.value?void 0:{transition:"none"}};if(!V.value)return ue;const _=x.value[ie.value];if(!_)throw new Error(`[Vuetify] Could not find layout item "${m}"`);const J=p.value.get(m);return J&&(_[J.position]+=J.amount),{...ue,height:D?`calc(100% - ${_.top}px - ${_.bottom}px)`:C.value?`${C.value}px`:void 0,left:X?void 0:`${_.left}px`,right:X?`${_.right}px`:void 0,top:b.value!=="bottom"?`${_.top}px`:void 0,bottom:b.value!=="top"?`${_.bottom}px`:void 0,width:D?C.value?`${C.value}px`:void 0:`calc(100% - ${_.left}px - ${_.right}px)`}}),Le=r(()=>({zIndex:G.value-1}));return{layoutItemStyles:_e,layoutItemScrimStyles:Le,zIndex:G}},unregister:f=>{s.delete(f),l.delete(f),n.delete(f),i.delete(f),y.delete(f),a.value=a.value.filter(w=>w!==f)},mainRect:v,mainStyles:I,getLayoutItem:A,items:x,layoutRect:g,rootZIndex:t});const H=r(()=>["v-layout",{"v-layout--full-height":e.fullHeight}]),M=r(()=>({zIndex:o?t.value:void 0,position:o?"relative":void 0,overflow:o?"hidden":void 0}));return{layoutClasses:H,layoutStyles:M,getLayoutItem:A,items:x,layoutRect:g,layoutRef:h}}const St=L({fluid:{type:Boolean,default:!1},...j(),...ge(),...Z()},"VContainer"),Rt=E()({name:"VContainer",props:St(),setup(e,o){let{slots:t}=o;const{rtlClasses:a}=le(),{dimensionStyles:l}=be(e);return z(()=>c(e.tag,{class:["v-container",{"v-container--fluid":e.fluid},a.value,e.class],style:[l.value,e.style]},t)),{}}});function xt(e){const o=P(e());let t=-1;function a(){clearInterval(t)}function l(){a(),Je(()=>o.value=e())}function n(s){const i=s?getComputedStyle(s):{transitionDuration:.2},y=parseFloat(i.transitionDuration)*1e3||200;if(a(),o.value<=0)return;const h=performance.now();t=window.setInterval(()=>{const g=performance.now()-h+y;o.value=Math.max(e()-g,0),o.value<=0&&a()},y)}return qe(a),{clear:a,time:o,start:n,reset:l}}const Vt=L({multiLine:Boolean,text:String,timer:[Boolean,String],timeout:{type:[Number,String],default:5e3},vertical:Boolean,...Me({location:"bottom"}),...Oe(),...he(),...De(),...ne(),...Ue(Fe({transition:"v-snackbar-transition"}),["persistent","noClickAnimation","scrim","scrollStrategy"])},"VSnackbar"),kt=E()({name:"VSnackbar",props:Vt(),emits:{"update:modelValue":e=>!0},setup(e,o){let{slots:t}=o;const a=pe(e,"modelValue"),{positionClasses:l}=je(e),{scopeId:n}=Ye(),{themeClasses:s}=oe(e),{colorClasses:i,colorStyles:y,variantClasses:h}=Ke(e),{roundedClasses:g}=Se(e),p=xt(()=>Number(e.timeout)),S=R(),T=R(),v=P(!1),I=P(0),x=R(),A=W(F,void 0);xe(()=>!!A,()=>{const d=Ie();Ve(()=>{x.value=d.mainStyles.value})}),O(a,V),O(()=>e.timeout,V),ae(()=>{a.value&&V()});let u=-1;function V(){p.reset(),window.clearTimeout(u);const d=Number(e.timeout);if(!a.value||d===-1)return;const C=We(T.value);p.start(C),u=window.setTimeout(()=>{a.value=!1},d)}function H(){p.reset(),window.clearTimeout(u)}function M(){v.value=!0,H()}function f(){v.value=!1,V()}function w(d){I.value=d.touches[0].clientY}function m(d){Math.abs(I.value-d.changedTouches[0].clientY)>50&&(a.value=!1)}function k(){v.value&&f()}const b=r(()=>e.location.split(" ").reduce((d,C)=>(d[`v-snackbar--${C}`]=!0,d),{}));return z(()=>{const d=re.filterProps(e),C=!!(t.default||t.text||e.text);return c(re,K({ref:S,class:["v-snackbar",{"v-snackbar--active":a.value,"v-snackbar--multi-line":e.multiLine&&!e.vertical,"v-snackbar--timer":!!e.timer,"v-snackbar--vertical":e.vertical},b.value,l.value,e.class],style:[x.value,e.style]},d,{modelValue:a.value,"onUpdate:modelValue":B=>a.value=B,contentProps:K({class:["v-snackbar__wrapper",s.value,i.value,g.value,h.value],style:[y.value],onPointerenter:M,onPointerleave:f},d.contentProps),persistent:!0,noClickAnimation:!0,scrim:!1,scrollStrategy:"none",_disableGlobalStack:!0,onTouchstartPassive:w,onTouchend:m,onAfterLeave:k},n),{default:()=>{var B,$;return[Ze(!1,"v-snackbar"),e.timer&&!v.value&&c("div",{key:"timer",class:"v-snackbar__timer"},[c(Ge,{ref:T,color:typeof e.timer=="string"?e.timer:"info",max:e.timeout,"model-value":p.time.value},null)]),C&&c("div",{key:"content",class:"v-snackbar__content",role:"status","aria-live":"polite"},[((B=t.text)==null?void 0:B.call(t))??e.text,($=t.default)==null?void 0:$.call(t)]),t.actions&&c(Y,{defaults:{VBtn:{variant:"text",ripple:!1,slim:!0}}},{default:()=>[c("div",{class:"v-snackbar__actions"},[t.actions({isActive:a})])]})]},activator:t.activator})}),Xe({},S)}}),$t=Qe({__name:"NotificationComponent",setup(e){const o=et(),t=R(""),a=R(""),l=R(!1);return o.$onAction(({after:n})=>{n(()=>{o.text&&(t.value=o.color,a.value=o.text,l.value=!0)})}),(n,s)=>(tt(),at(kt,{modelValue:ee(l),"onUpdate:modelValue":s[1]||(s[1]=i=>ot(l)?l.value=i:null),color:ee(t)},{actions:Q(()=>[c(lt,{variant:"text",onClick:s[0]||(s[0]=i=>l.value=!1)},{default:Q(()=>s[2]||(s[2]=[ce(" Закрыть ")])),_:1})]),default:Q(()=>[ce(nt(ee(a))+" ",1)]),_:1},8,["modelValue","color"]))}}),Tt=L({...j(),...yt({fullHeight:!0}),...ne()},"VApp"),Nt=E()({name:"VApp",props:Tt(),setup(e,o){let{slots:t}=o;const a=oe(e),{layoutClasses:l,getLayoutItem:n,items:s,layoutRef:i}=pt(e),{rtlClasses:y}=le();return z(()=>{var h;return c("div",{ref:i,class:["v-application",a.themeClasses.value,l.value,y.value,e.class],style:[e.style]},[c("div",{class:"v-application__wrap"},[(h=t.default)==null?void 0:h.call(t)])])}),{getLayoutItem:n,items:s,theme:a}}}),we=L({text:String,...j(),...Z()},"VToolbarTitle"),Ce=E()({name:"VToolbarTitle",props:we(),setup(e,o){let{slots:t}=o;return z(()=>{const a=!!(t.default||t.text||e.text);return c(e.tag,{class:["v-toolbar-title",e.class],style:e.style},{default:()=>{var l;return[a&&c("div",{class:"v-toolbar-title__placeholder"},[t.text?t.text():e.text,(l=t.default)==null?void 0:l.call(t)])]}})}),{}}}),It=[null,"prominent","default","comfortable","compact"],Pe=L({absolute:Boolean,collapse:Boolean,color:String,density:{type:String,default:"default",validator:e=>It.includes(e)},extended:Boolean,extensionHeight:{type:[Number,String],default:48},flat:Boolean,floating:Boolean,height:{type:[Number,String],default:64},image:String,title:String,...st(),...j(),...it(),...he(),...Z({tag:"header"}),...ne()},"VToolbar"),de=E()({name:"VToolbar",props:Pe(),setup(e,o){var T;let{slots:t}=o;const{backgroundColorClasses:a,backgroundColorStyles:l}=ut(te(e,"color")),{borderClasses:n}=rt(e),{elevationClasses:s}=ct(e),{roundedClasses:i}=Se(e),{themeClasses:y}=oe(e),{rtlClasses:h}=le(),g=P(!!(e.extended||(T=t.extension)!=null&&T.call(t))),p=r(()=>parseInt(Number(e.height)+(e.density==="prominent"?Number(e.height):0)-(e.density==="comfortable"?8:0)-(e.density==="compact"?16:0),10)),S=r(()=>g.value?parseInt(Number(e.extensionHeight)+(e.density==="prominent"?Number(e.extensionHeight):0)-(e.density==="comfortable"?4:0)-(e.density==="compact"?8:0),10):0);return vt({VBtn:{variant:"text"}}),z(()=>{var A;const v=!!(e.title||t.title),I=!!(t.image||e.image),x=(A=t.extension)==null?void 0:A.call(t);return g.value=!!(e.extended||x),c(e.tag,{class:["v-toolbar",{"v-toolbar--absolute":e.absolute,"v-toolbar--collapse":e.collapse,"v-toolbar--flat":e.flat,"v-toolbar--floating":e.floating,[`v-toolbar--density-${e.density}`]:!0},a.value,n.value,s.value,i.value,y.value,h.value,e.class],style:[l.value,e.style]},{default:()=>[I&&c("div",{key:"image",class:"v-toolbar__image"},[t.image?c(Y,{key:"image-defaults",disabled:!e.image,defaults:{VImg:{cover:!0,src:e.image}}},t.image):c(ft,{key:"image-img",cover:!0,src:e.image},null)]),c(Y,{defaults:{VTabs:{height:N(p.value)}}},{default:()=>{var u,V,H;return[c("div",{class:"v-toolbar__content",style:{height:N(p.value)}},[t.prepend&&c("div",{class:"v-toolbar__prepend"},[(u=t.prepend)==null?void 0:u.call(t)]),v&&c(Ce,{key:"title",text:e.title},{text:t.title}),(V=t.default)==null?void 0:V.call(t),t.append&&c("div",{class:"v-toolbar__append"},[(H=t.append)==null?void 0:H.call(t)])])]}}),c(Y,{defaults:{VTabs:{height:N(S.value)}}},{default:()=>[c(dt,null,{default:()=>[g.value&&c("div",{class:"v-toolbar__extension",style:{height:N(S.value)}},[x])]})]})]})}),{contentHeight:p,extensionHeight:S}}}),wt=L({scrollTarget:{type:String},scrollThreshold:{type:[String,Number],default:300}},"scroll");function Ct(e){let o=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{};const{canScroll:t}=o;let a=0,l=0;const n=R(null),s=P(0),i=P(0),y=P(0),h=P(!1),g=P(!1),p=r(()=>Number(e.scrollThreshold)),S=r(()=>mt((p.value-s.value)/p.value||0)),T=()=>{const v=n.value;if(!v||t&&!t.value)return;a=s.value,s.value="window"in v?v.pageYOffset:v.scrollTop;const I=v instanceof Window?document.documentElement.scrollHeight:v.scrollHeight;if(l!==I){l=I;return}g.value=s.value<a,y.value=Math.abs(s.value-p.value)};return O(g,()=>{i.value=i.value||s.value}),O(h,()=>{i.value=0}),ae(()=>{O(()=>e.scrollTarget,v=>{var x;const I=v?document.querySelector(v):window;I&&I!==n.value&&((x=n.value)==null||x.removeEventListener("scroll",T),n.value=I,n.value.addEventListener("scroll",T,{passive:!0}))},{immediate:!0})}),ye(()=>{var v;(v=n.value)==null||v.removeEventListener("scroll",T)}),t&&O(t,T,{immediate:!0}),{scrollThreshold:p,currentScroll:s,currentThreshold:y,isScrollActive:h,scrollRatio:S,isScrollingUp:g,savedScroll:i}}const Pt=L({scrollBehavior:String,modelValue:{type:Boolean,default:!0},location:{type:String,default:"top",validator:e=>["top","bottom"].includes(e)},...Pe(),...gt(),...wt(),height:{type:[Number,String],default:64}},"VAppBar"),Et=E()({name:"VAppBar",props:Pt(),emits:{"update:modelValue":e=>!0},setup(e,o){let{slots:t}=o;const a=R(),l=pe(e,"modelValue"),n=r(()=>{var V;const u=new Set(((V=e.scrollBehavior)==null?void 0:V.split(" "))??[]);return{hide:u.has("hide"),fullyHide:u.has("fully-hide"),inverted:u.has("inverted"),collapse:u.has("collapse"),elevate:u.has("elevate"),fadeImage:u.has("fade-image")}}),s=r(()=>{const u=n.value;return u.hide||u.fullyHide||u.inverted||u.collapse||u.elevate||u.fadeImage||!l.value}),{currentScroll:i,scrollThreshold:y,isScrollingUp:h,scrollRatio:g}=Ct(e,{canScroll:s}),p=r(()=>n.value.hide||n.value.fullyHide),S=r(()=>e.collapse||n.value.collapse&&(n.value.inverted?g.value>0:g.value===0)),T=r(()=>e.flat||n.value.fullyHide&&!l.value||n.value.elevate&&(n.value.inverted?i.value>0:i.value===0)),v=r(()=>n.value.fadeImage?n.value.inverted?1-g.value:g.value:void 0),I=r(()=>{var H,M;if(n.value.hide&&n.value.inverted)return 0;const u=((H=a.value)==null?void 0:H.contentHeight)??0,V=((M=a.value)==null?void 0:M.extensionHeight)??0;return p.value?i.value<y.value||n.value.fullyHide?u+V:u:u+V});xe(r(()=>!!e.scrollBehavior),()=>{Ve(()=>{p.value?n.value.inverted?l.value=i.value>y.value:l.value=h.value||i.value<y.value:l.value=!0})});const{ssrBootStyles:x}=ke(),{layoutItemStyles:A}=bt({id:e.name,order:r(()=>parseInt(e.order,10)),position:te(e,"location"),layoutSize:I,elementSize:P(void 0),active:l,absolute:te(e,"absolute")});return z(()=>{const u=de.filterProps(e);return c(de,K({ref:a,class:["v-app-bar",{"v-app-bar--bottom":e.location==="bottom"},e.class],style:[{...A.value,"--v-toolbar-image-opacity":v.value,height:void 0,...x.value},e.style]},u,{collapse:S.value,flat:T.value}),t)}),{}}}),zt=E()({name:"VAppBarTitle",props:we(),setup(e,o){let{slots:t}=o;return z(()=>c(Ce,K(e,{class:"v-app-bar-title"}),t)),{}}}),Bt=L({scrollable:Boolean,...j(),...ge(),...Z({tag:"main"})},"VMain"),Mt=E()({name:"VMain",props:Bt(),setup(e,o){let{slots:t}=o;const{dimensionStyles:a}=be(e),{mainStyles:l}=Ie(),{ssrBootStyles:n}=ke();return z(()=>c(e.tag,{class:["v-main",{"v-main--scrollable":e.scrollable},e.class],style:[l.value,n.value,a.value,e.style]},{default:()=>{var s,i;return[e.scrollable?c("div",{class:"v-main__scroller"},[(s=t.default)==null?void 0:s.call(t)]):(i=t.default)==null?void 0:i.call(t)]}})),{}}});export{Nt as V,$t as _,zt as a,Et as b,Rt as c,Mt as d,gt as m,bt as u};
