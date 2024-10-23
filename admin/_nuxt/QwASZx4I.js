import{u as de}from"./B83tnpwP.js";import{O as ce,ar as me,as as fe,R as ve,at as Ve,a9 as pe,au as xe,i as E,r as p,av as ye,aw as ge,o as we,E as I,e as be,T as Ce,ax as ke,ay as Z,az as Pe,z as o,aA as Fe,ao as q,aB as W,J as C,aC as Ue,aD as Se,aE as he,U as Ie,aF as j,aG as Re,aH as Te,aI as ze,d as Be,W as Ne,D as $e,C as ee,F as Ae,A as m,q as He,t as Ee,B as J,V as s,G as l,I as te,K,X as Me,L as M}from"./CD-ciyMC.js";import{k as z}from"./CfZuxCf1.js";import{V as ae,a as Oe,b as le,c as De}from"./S1xGE2Sl.js";import{a as Ge,V as _e}from"./V7KghpKn.js";import{V as qe}from"./D2o8XtCw.js";import{V as We}from"./Dy0dx93s.js";import"./D2qL_eWx.js";import"./tHn_lS1N.js";import"./BJOK9706.js";import"./BT2HD4FG.js";const je=ce({autoGrow:Boolean,autofocus:Boolean,counter:[Boolean,Number,String],counterValue:Function,prefix:String,placeholder:String,persistentPlaceholder:Boolean,persistentCounter:Boolean,noResize:Boolean,rows:{type:[Number,String],default:5,validator:e=>!isNaN(parseFloat(e))},maxRows:{type:[Number,String],validator:e=>!isNaN(parseFloat(e))},suffix:String,modelModifiers:Object,...me(),...fe()},"VTextarea"),Je=ve()({name:"VTextarea",directives:{Intersect:Ve},inheritAttrs:!1,props:je(),emits:{"click:control":e=>!0,"mousedown:control":e=>!0,"update:focused":e=>!0,"update:modelValue":e=>!0},setup(e,R){let{attrs:x,emit:b,slots:i}=R;const c=pe(e,"modelValue"),{isFocused:k,focus:P,blur:O}=xe(e),T=E(()=>typeof e.counterValue=="function"?e.counterValue(c.value):(c.value||"").toString().length),n=E(()=>{if(x.maxlength)return x.maxlength;if(!(!e.counter||typeof e.counter!="number"&&typeof e.counter!="string"))return e.counter});function y(u,f){var d,V;!e.autofocus||!u||(V=(d=f[0].target)==null?void 0:d.focus)==null||V.call(d)}const v=p(),F=p(),B=ye(""),U=p(),r=E(()=>e.persistentPlaceholder||k.value||e.active);function t(){var u;U.value!==document.activeElement&&((u=U.value)==null||u.focus()),k.value||P()}function a(u){t(),b("click:control",u)}function N(u){b("mousedown:control",u)}function oe(u){u.stopPropagation(),t(),j(()=>{c.value="",Re(e["onClick:clear"],u)})}function ne(u){var d;const f=u.target;if(c.value=f.value,(d=e.modelModifiers)!=null&&d.trim){const V=[f.selectionStart,f.selectionEnd];j(()=>{f.selectionStart=V[0],f.selectionEnd=V[1]})}}const S=p(),$=p(+e.rows),D=E(()=>["plain","underlined"].includes(e.variant));ge(()=>{e.autoGrow||($.value=+e.rows)});function h(){e.autoGrow&&j(()=>{if(!S.value||!F.value)return;const u=getComputedStyle(S.value),f=getComputedStyle(F.value.$el),d=parseFloat(u.getPropertyValue("--v-field-padding-top"))+parseFloat(u.getPropertyValue("--v-input-padding-top"))+parseFloat(u.getPropertyValue("--v-field-padding-bottom")),V=S.value.scrollHeight,A=parseFloat(u.lineHeight),G=Math.max(parseFloat(e.rows)*A+d,parseFloat(f.getPropertyValue("--v-input-control-height"))),_=parseFloat(e.maxRows)*A+d||1/0,w=ze(V??0,G,_);$.value=Math.floor((w-d)/A),B.value=Te(w)})}we(h),I(c,h),I(()=>e.rows,h),I(()=>e.maxRows,h),I(()=>e.density,h);let g;return I(S,u=>{u?(g=new ResizeObserver(h),g.observe(S.value)):g==null||g.disconnect()}),be(()=>{g==null||g.disconnect()}),Ce(()=>{const u=!!(i.counter||e.counter||e.counterValue),f=!!(u||i.details),[d,V]=ke(x),{modelValue:A,...G}=Z.filterProps(e),_=Pe(e);return o(Z,q({ref:v,modelValue:c.value,"onUpdate:modelValue":w=>c.value=w,class:["v-textarea v-text-field",{"v-textarea--prefixed":e.prefix,"v-textarea--suffixed":e.suffix,"v-text-field--prefixed":e.prefix,"v-text-field--suffixed":e.suffix,"v-textarea--auto-grow":e.autoGrow,"v-textarea--no-resize":e.noResize||e.autoGrow,"v-input--plain-underlined":D.value},e.class],style:e.style},d,G,{centerAffix:$.value===1&&!D.value,focused:k.value}),{...i,default:w=>{let{id:H,isDisabled:L,isDirty:X,isReadonly:ue,isValid:re}=w;return o(Fe,q({ref:F,style:{"--v-textarea-control-height":B.value},onClick:a,onMousedown:N,"onClick:clear":oe,"onClick:prependInner":e["onClick:prependInner"],"onClick:appendInner":e["onClick:appendInner"]},_,{id:H.value,active:r.value||X.value,centerAffix:$.value===1&&!D.value,dirty:X.value||e.dirty,disabled:L.value,focused:k.value,error:re.value===!1}),{...i,default:se=>{let{props:{class:Q,...Y}}=se;return o(W,null,[e.prefix&&o("span",{class:"v-text-field__prefix"},[e.prefix]),C(o("textarea",q({ref:U,class:Q,value:c.value,onInput:ne,autofocus:e.autofocus,readonly:ue.value,disabled:L.value,placeholder:e.placeholder,rows:e.rows,name:e.name,onFocus:t,onBlur:O},Y,V),null),[[Ue("intersect"),{handler:y},null,{once:!0}]]),e.autoGrow&&C(o("textarea",{class:[Q,"v-textarea__sizer"],id:`${Y.id}-sizer`,"onUpdate:modelValue":ie=>c.value=ie,ref:S,readonly:!0,"aria-hidden":"true"},null),[[Se,c.value]]),e.suffix&&o("span",{class:"v-text-field__suffix"},[e.suffix])])}})},details:f?w=>{var H;return o(W,null,[(H=i.details)==null?void 0:H.call(i,w),u&&o(W,null,[o("span",null,null),o(he,{active:e.persistentCounter||k.value,value:T.value,max:n.value,disabled:e.disabled},i.counter)])])}:void 0})}),Ie({},v,F,U)}}),nt=Be({__name:"products",setup(e){const R=Ne({mask:"0.99",eager:!0,tokens:{0:{pattern:/\d/,multiple:!0},9:{pattern:/\d/,optional:!0}}});de({title:"Товары"});const{email:x,password:b}=$e(),{showError:i,showSuccess:c}=ee(),P=He().public.backendUrl,O=[{title:"Название",key:"name"},{title:"Артикул",key:"art"},{title:"",key:"actions"}],T=p([]),n=p(null),y=p(""),v=p(!1);I(y,async()=>{const{data:r,error:t}=await M(`${P}/search.php`,{method:"POST",body:{email:x,password:b},query:{search:y.value}},"$cOWNPPgU8q");if(r&&r.value)T.value=r.value;else if(t){const{showError:a}=ee();a("Ошибка соединения с сервером")}});const F=async r=>{const t=r.art.split("=")[1],{data:a,error:N}=await M(`${P}/good_details.php`,{method:"POST",body:{staff_login:x,staff_password:b,art:t}},"$qN6PabdkWd");a.value.good?(n.value=a.value.good,v.value=!0):a.value.error?i(a.value.error):N&&i("Ошибка соединения с сервером")},B=async()=>{const{data:r,error:t}=await M(`${P}/good_update.php`,{method:"POST",body:{staff_login:x,staff_password:b,product:n.value}},"$Le0FIJ3WzT");r.value.message?(c(r.value.message),v.value=!1,await U()):r.value.error?i(r.value.error):t&&i("Ошибка соединения с сервером")},U=async()=>{const{data:r}=await M(`${P}/search.php`,{method:"POST",body:{email:x,password:b},query:{search:y.value}},"$GcwM0HKSOj");T.value=r.value};return(r,t)=>(Ee(),Ae(ae,null,{default:m(()=>[o(Oe,null,{default:m(()=>t[17]||(t[17]=[J(" Товары ")])),_:1}),o(le,null,{default:m(()=>[o(Ge,null,{default:m(()=>[o(_e,{cols:"12",md:"6"},{default:m(()=>[o(s,{modelValue:l(y),"onUpdate:modelValue":t[0]||(t[0]=a=>te(y)?y.value=a:null),label:"Наименование, артикул"},null,8,["modelValue"])]),_:1})]),_:1}),o(qe,{headers:O,items:l(T)},{"item.actions":m(({item:a})=>[o(K,{color:"warning",icon:"mdi-pencil",density:"compact",onClick:N=>F(a)},null,8,["onClick"])]),_:2},1032,["items"])]),_:1}),o(We,{modelValue:l(v),"onUpdate:modelValue":t[16]||(t[16]=a=>te(v)?v.value=a:null),"max-width":"600"},{default:m(()=>[o(ae,null,{default:m(()=>[o(le,null,{default:m(()=>[o(s,{modelValue:l(n).art,"onUpdate:modelValue":t[1]||(t[1]=a=>l(n).art=a),label:"Артикул",disabled:""},null,8,["modelValue"]),o(s,{modelValue:l(n).name,"onUpdate:modelValue":t[2]||(t[2]=a=>l(n).name=a),label:"Название",density:"compact"},null,8,["modelValue"]),o(s,{modelValue:l(n).description_short,"onUpdate:modelValue":t[3]||(t[3]=a=>l(n).description_short=a),label:"Короткое описание",density:"compact"},null,8,["modelValue"]),o(Je,{modelValue:l(n).description_full,"onUpdate:modelValue":t[4]||(t[4]=a=>l(n).description_full=a),label:"Полное описание",density:"compact"},null,8,["modelValue"]),C(o(s,{modelValue:l(n).price,"onUpdate:modelValue":t[5]||(t[5]=a=>l(n).price=a),label:"Цена",density:"compact"},null,8,["modelValue"]),[[l(z),l(R)]]),C(o(s,{modelValue:l(n).price_old,"onUpdate:modelValue":t[6]||(t[6]=a=>l(n).price_old=a),label:"Цена старая",density:"compact"},null,8,["modelValue"]),[[l(z),l(R)]]),C(o(s,{modelValue:l(n).qty,"onUpdate:modelValue":t[7]||(t[7]=a=>l(n).qty=a),label:"Количество",density:"compact"},null,8,["modelValue"]),[[l(z),"#############"]]),C(o(s,{modelValue:l(n).barcode,"onUpdate:modelValue":t[8]||(t[8]=a=>l(n).barcode=a),label:"Barcode",density:"compact"},null,8,["modelValue"]),[[l(z),"#############"]]),o(s,{modelValue:l(n).producer,"onUpdate:modelValue":t[9]||(t[9]=a=>l(n).producer=a),label:"Производитель",density:"compact"},null,8,["modelValue"]),o(s,{modelValue:l(n).producer_country,"onUpdate:modelValue":t[10]||(t[10]=a=>l(n).producer_country=a),label:"Страна",density:"compact"},null,8,["modelValue"]),o(s,{modelValue:l(n).cat,"onUpdate:modelValue":t[11]||(t[11]=a=>l(n).cat=a),label:"Категория",density:"compact"},null,8,["modelValue"]),o(s,{modelValue:l(n).subcat,"onUpdate:modelValue":t[12]||(t[12]=a=>l(n).subcat=a),label:"Подкатегория",density:"compact"},null,8,["modelValue"]),C(o(s,{modelValue:l(n).koef_ed_izm,"onUpdate:modelValue":t[13]||(t[13]=a=>l(n).koef_ed_izm=a),label:"Коэффициент ед. изм.",density:"compact"},null,8,["modelValue"]),[[l(z),l(R)]]),o(s,{modelValue:l(n).ed_izm_name,"onUpdate:modelValue":t[14]||(t[14]=a=>l(n).ed_izm_name=a),label:"Название ед. изм.",density:"compact"},null,8,["modelValue"])]),_:1}),o(De,null,{default:m(()=>[o(Me),o(K,{onClick:t[15]||(t[15]=a=>v.value=!1)},{default:m(()=>t[18]||(t[18]=[J(" Отмена ")])),_:1}),o(K,{onClick:B},{default:m(()=>t[19]||(t[19]=[J(" Сохранить ")])),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue"])]),_:1}))}});export{nt as default};