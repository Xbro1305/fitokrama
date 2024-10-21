import{m as l,u as s,g as V,a as x,V as p,s as C,t as K,b as Q,c as q,C as G,d as J,e as U,f as W,x as X,h as Y,R as Z,y as $,i as ee,j as ae,k as te,l as ne,D as de,n as ie,o as le,p as se,A as ce,E as re,L as ue,r as oe}from"./C7KYmi4a.js";import{L as c,a0 as ve,z as n,K as y,N as m,ac as h,O as me,Q as ye,i as P,aa as be,ab as ge}from"./bgoYWPAe.js";import{c as ke,V as S}from"./DAr8DC6_.js";const fe=c()({name:"VCardActions",props:l(),setup(e,d){let{slots:t}=d;return ve({VBtn:{slim:!0,variant:"text"}}),s(()=>{var a;return n("div",{class:["v-card-actions",e.class],style:e.style},[(a=t.default)==null?void 0:a.call(t)])}),{}}}),Ce=y({opacity:[Number,String],...l(),...V()},"VCardSubtitle"),Ve=c()({name:"VCardSubtitle",props:Ce(),setup(e,d){let{slots:t}=d;return s(()=>n(e.tag,{class:["v-card-subtitle",e.class],style:[{"--v-card-subtitle-opacity":e.opacity},e.style]},t)),{}}}),Ie=ke("v-card-title"),Ae=y({appendAvatar:String,appendIcon:m,prependAvatar:String,prependIcon:m,subtitle:[String,Number],title:[String,Number],...l(),...x()},"VCardItem"),pe=c()({name:"VCardItem",props:Ae(),setup(e,d){let{slots:t}=d;return s(()=>{var u;const a=!!(e.prependAvatar||e.prependIcon),b=!!(a||t.prepend),r=!!(e.appendAvatar||e.appendIcon),g=!!(r||t.append),k=!!(e.title!=null||t.title),f=!!(e.subtitle!=null||t.subtitle);return n("div",{class:["v-card-item",e.class],style:e.style},[b&&n("div",{key:"prepend",class:"v-card-item__prepend"},[t.prepend?n(C,{key:"prepend-defaults",disabled:!a,defaults:{VAvatar:{density:e.density,image:e.prependAvatar},VIcon:{density:e.density,icon:e.prependIcon}}},t.prepend):n(h,null,[e.prependAvatar&&n(S,{key:"prepend-avatar",density:e.density,image:e.prependAvatar},null),e.prependIcon&&n(p,{key:"prepend-icon",density:e.density,icon:e.prependIcon},null)])]),n("div",{class:"v-card-item__content"},[k&&n(Ie,{key:"title"},{default:()=>{var i;return[((i=t.title)==null?void 0:i.call(t))??e.title]}}),f&&n(Ve,{key:"subtitle"},{default:()=>{var i;return[((i=t.subtitle)==null?void 0:i.call(t))??e.subtitle]}}),(u=t.default)==null?void 0:u.call(t)]),g&&n("div",{key:"append",class:"v-card-item__append"},[t.append?n(C,{key:"append-defaults",disabled:!r,defaults:{VAvatar:{density:e.density,image:e.appendAvatar},VIcon:{density:e.density,icon:e.appendIcon}}},t.append):n(h,null,[e.appendIcon&&n(p,{key:"append-icon",density:e.density,icon:e.appendIcon},null),e.appendAvatar&&n(S,{key:"append-avatar",density:e.density,image:e.appendAvatar},null)])])])}),{}}}),he=y({opacity:[Number,String],...l(),...V()},"VCardText"),Pe=c()({name:"VCardText",props:he(),setup(e,d){let{slots:t}=d;return s(()=>n(e.tag,{class:["v-card-text",e.class],style:[{"--v-card-text-opacity":e.opacity},e.style]},t)),{}}}),Se=y({appendAvatar:String,appendIcon:m,disabled:Boolean,flat:Boolean,hover:Boolean,image:String,link:{type:Boolean,default:void 0},prependAvatar:String,prependIcon:m,ripple:{type:[Boolean,Object],default:!0},subtitle:[String,Number],text:[String,Number],title:[String,Number],...K(),...l(),...x(),...Q(),...q(),...G(),...J(),...U(),...W(),...X(),...V(),...me(),...Y({variant:"elevated"})},"VCard"),De=c()({name:"VCard",directives:{Ripple:Z},props:Se(),setup(e,d){let{attrs:t,slots:a}=d;const{themeClasses:b}=ye(e),{borderClasses:r}=$(e),{colorClasses:g,colorStyles:k,variantClasses:f}=ee(e),{densityClasses:u}=ae(e),{dimensionStyles:i}=te(e),{elevationClasses:T}=ne(e),{loaderClasses:L}=de(e),{locationStyles:D}=ie(e),{positionClasses:N}=le(e),{roundedClasses:B}=se(e),o=ce(e,t),_=P(()=>e.link!==!1&&o.isLink.value),v=P(()=>!e.disabled&&e.link!==!1&&(e.link||o.isClickable.value));return s(()=>{const R=_.value?"a":e.tag,E=!!(a.title||e.title!=null),F=!!(a.subtitle||e.subtitle!=null),O=E||F,j=!!(a.append||e.appendAvatar||e.appendIcon),M=!!(a.prepend||e.prependAvatar||e.prependIcon),w=!!(a.image||e.image),z=O||M||j,H=!!(a.text||e.text!=null);return be(n(R,{class:["v-card",{"v-card--disabled":e.disabled,"v-card--flat":e.flat,"v-card--hover":e.hover&&!(e.disabled||e.flat),"v-card--link":v.value},b.value,r.value,g.value,u.value,T.value,L.value,N.value,B.value,f.value,e.class],style:[k.value,i.value,D.value,e.style],href:o.href.value,onClick:v.value&&o.navigate,tabindex:e.disabled?-1:void 0},{default:()=>{var I;return[w&&n("div",{key:"image",class:"v-card__image"},[a.image?n(C,{key:"image-defaults",disabled:!e.image,defaults:{VImg:{cover:!0,src:e.image}}},a.image):n(re,{key:"image-img",cover:!0,src:e.image},null)]),n(ue,{name:"v-card",active:!!e.loading,color:typeof e.loading=="boolean"?void 0:e.loading},{default:a.loader}),z&&n(pe,{key:"item",prependAvatar:e.prependAvatar,prependIcon:e.prependIcon,title:e.title,subtitle:e.subtitle,appendAvatar:e.appendAvatar,appendIcon:e.appendIcon},{default:a.item,prepend:a.prepend,title:a.title,subtitle:a.subtitle,append:a.append}),H&&n(Pe,{key:"text"},{default:()=>{var A;return[((A=a.text)==null?void 0:A.call(a))??e.text]}}),(I=a.default)==null?void 0:I.call(a),a.actions&&n(fe,null,{default:a.actions}),oe(v.value,"v-card")]}}),[[ge("ripple"),v.value&&e.ripple]])}),{}}});export{De as V,Ie as a,Pe as b,fe as c};
