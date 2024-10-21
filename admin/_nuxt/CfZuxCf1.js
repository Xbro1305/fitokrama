var x=Object.defineProperty,P=(a,t,e)=>t in a?x(a,t,{enumerable:!0,configurable:!0,writable:!0,value:e}):a[t]=e,A=(a,t,e)=>P(a,typeof t!="symbol"?t+"":t,e);const w={"#":{pattern:/[0-9]/},"@":{pattern:/[a-zA-Z]/},"*":{pattern:/[a-zA-Z0-9]/}},I=(a,t,e)=>a.replaceAll(t,"").replace(e,".").replace("..",".").replace(/[^.\d]/g,""),N=(a,t,e)=>{var s;return new Intl.NumberFormat(((s=e.number)==null?void 0:s.locale)??"en",{minimumFractionDigits:a,maximumFractionDigits:t,roundingMode:"trunc"})},W=(a,t=!0,e)=>{var s,n,i,l;const u=((s=e.number)==null?void 0:s.unsigned)==null&&a.startsWith("-")?"-":"",p=((n=e.number)==null?void 0:n.fraction)??0;let o=N(0,p,e);const k=o.formatToParts(1000.12),h=((i=k.find(r=>r.type==="group"))==null?void 0:i.value)??" ",y=((l=k.find(r=>r.type==="decimal"))==null?void 0:l.value)??".",f=I(a,h,y);if(Number.isNaN(parseFloat(f)))return u;const g=f.split(".");if(g[1]!=null&&g[1].length>=1){const r=g[1].length<=p?g[1].length:p;o=N(r,p,e)}let m=o.format(parseFloat(f));return t?p>0&&f.endsWith(".")&&!f.slice(0,-1).includes(".")&&(m+=y):m=I(m,h,y),u+m};class F{constructor(t={}){A(this,"opts",{}),A(this,"memo",new Map);const e={...t};if(e.tokens!=null){e.tokens=e.tokensReplace?{...e.tokens}:{...w,...e.tokens};for(const s of Object.values(e.tokens))typeof s.pattern=="string"&&(s.pattern=new RegExp(s.pattern))}else e.tokens=w;Array.isArray(e.mask)&&(e.mask.length>1?e.mask=[...e.mask].sort((s,n)=>s.length-n.length):e.mask=e.mask[0]??""),e.mask===""&&(e.mask=null),this.opts=e}masked(t){return this.process(t,this.findMask(t))}unmasked(t){return this.process(t,this.findMask(t),!1)}isEager(){return this.opts.eager===!0}isReversed(){return this.opts.reversed===!0}completed(t){const e=this.findMask(t);if(this.opts.mask==null||e==null)return!1;const s=this.process(t,e).length;return typeof this.opts.mask=="string"?s>=this.opts.mask.length:s>=e.length}findMask(t){const e=this.opts.mask;if(e==null)return null;if(typeof e=="string")return e;if(typeof e=="function")return e(t);const s=this.process(t,e.slice(-1).pop()??"",!1);return e.find(n=>this.process(t,n,!1).length>=s.length)??""}escapeMask(t){const e=[],s=[];return t.split("").forEach((n,i)=>{n==="!"&&t[i-1]!=="!"?s.push(i-s.length):e.push(n)}),{mask:e.join(""),escaped:s}}process(t,e,s=!0){if(this.opts.number!=null)return W(t,s,this.opts);if(e==null)return t;const n=`v=${t},mr=${e},m=${s?1:0}`;if(this.memo.has(n))return this.memo.get(n);const{mask:i,escaped:l}=this.escapeMask(e),u=[],p=this.opts.tokens!=null?this.opts.tokens:{},o=this.isReversed()?-1:1,k=this.isReversed()?"unshift":"push",h=this.isReversed()?0:i.length-1,y=this.isReversed()?()=>r>-1&&c>-1:()=>r<i.length&&c<t.length,f=v=>!this.isReversed()&&v<=h||this.isReversed()&&v>=h;let g,m=-1,r=this.isReversed()?i.length-1:0,c=this.isReversed()?t.length-1:0,b=!1;for(;y();){const v=i.charAt(r),d=p[v],M=(d==null?void 0:d.transform)!=null?d.transform(t.charAt(c)):t.charAt(c);if(!l.includes(r)&&d!=null?(M.match(d.pattern)!=null?(u[k](M),d.repeated?(m===-1?m=r:r===h&&r!==m&&(r=m-o),h===m&&(r-=o)):d.multiple&&(b=!0,r-=o),r+=o):d.multiple?b&&(r+=o,c-=o,b=!1):M===g?g=void 0:d.optional&&(r+=o,c-=o),c+=o):(s&&!this.isEager()&&u[k](v),M===v&&!this.isEager()?c+=o:g=v,this.isEager()||(r+=o)),this.isEager())for(;f(r)&&(p[i.charAt(r)]==null||l.includes(r));){if(s){if(u[k](i.charAt(r)),t.charAt(c)===i.charAt(r)){r+=o,c+=o;continue}}else i.charAt(r)===t.charAt(c)&&(c+=o);r+=o}}return this.memo.set(n,u.join("")),this.memo.get(n)}}const T=a=>JSON.parse(a.replaceAll("'",'"')),O=(a,t={})=>{const e={...t};a.dataset.maska!=null&&a.dataset.maska!==""&&(e.mask=S(a.dataset.maska)),a.dataset.maskaEager!=null&&(e.eager=E(a.dataset.maskaEager)),a.dataset.maskaReversed!=null&&(e.reversed=E(a.dataset.maskaReversed)),a.dataset.maskaTokensReplace!=null&&(e.tokensReplace=E(a.dataset.maskaTokensReplace)),a.dataset.maskaTokens!=null&&(e.tokens=C(a.dataset.maskaTokens));const s={};return a.dataset.maskaNumberLocale!=null&&(s.locale=a.dataset.maskaNumberLocale),a.dataset.maskaNumberFraction!=null&&(s.fraction=parseInt(a.dataset.maskaNumberFraction)),a.dataset.maskaNumberUnsigned!=null&&(s.unsigned=E(a.dataset.maskaNumberUnsigned)),(a.dataset.maskaNumber!=null||Object.values(s).length>0)&&(e.number=s),e},E=a=>a!==""?!!JSON.parse(a):!0,S=a=>a.startsWith("[")&&a.endsWith("]")?T(a):a,C=a=>{if(a.startsWith("{")&&a.endsWith("}"))return T(a);const t={};return a.split("|").forEach(e=>{const s=e.split(":");t[s[0]]={pattern:new RegExp(s[1]),optional:s[2]==="optional",multiple:s[2]==="multiple",repeated:s[2]==="repeated"}}),t};class L{constructor(t,e={}){A(this,"items",new Map),A(this,"onInput",s=>{if(s instanceof CustomEvent&&s.type==="input"&&!s.isTrusted)return;const n=s.target,i=this.items.get(n),l="inputType"in s&&s.inputType.startsWith("delete"),u=i.isEager(),p=l&&u&&i.unmasked(n.value)===""?"":n.value;this.fixCursor(n,l,()=>this.setValue(n,p))}),this.options=e,this.init(this.getInputs(t))}update(t={}){this.options={...t},this.init(Array.from(this.items.keys()))}updateValue(t){t.value!==""&&t.value!==this.processInput(t).masked&&this.setValue(t,t.value)}destroy(){for(const t of this.items.keys())t.removeEventListener("input",this.onInput);this.items.clear()}init(t){const e=this.getOptions(this.options);for(const s of t){this.items.has(s)||s.addEventListener("input",this.onInput,{capture:!0});const n=new F(O(s,e));this.items.set(s,n),queueMicrotask(()=>this.updateValue(s)),s.selectionStart===null&&n.isEager()&&console.warn("Maska: input of `%s` type is not supported",s.type)}}getInputs(t){return typeof t=="string"?Array.from(document.querySelectorAll(t)):"length"in t?Array.from(t):[t]}getOptions(t){const{onMaska:e,preProcess:s,postProcess:n,...i}=t;return i}fixCursor(t,e,s){const n=t.selectionStart,i=t.value;if(s(),n===null||n===i.length&&!e)return;const l=t.value,u=i.slice(0,n),p=l.slice(0,n),o=this.processInput(t,u).unmasked,k=this.processInput(t,p).unmasked;let h=n;u!==p&&(h+=e?l.length-i.length:o.length-k.length),t.setSelectionRange(h,h)}setValue(t,e){const s=this.processInput(t,e);t.value=s.masked,this.options.onMaska!=null&&(Array.isArray(this.options.onMaska)?this.options.onMaska.forEach(n=>n(s)):this.options.onMaska(s)),t.dispatchEvent(new CustomEvent("maska",{detail:s})),t.dispatchEvent(new CustomEvent("input",{detail:s.masked}))}processInput(t,e){const s=this.items.get(t);let n=e??t.value;this.options.preProcess!=null&&(n=this.options.preProcess(n));let i=s.masked(n);return this.options.postProcess!=null&&(i=this.options.postProcess(i)),{masked:i,unmasked:s.unmasked(n),completed:s.completed(n)}}}const R=new WeakMap,j=(a,t)=>{if(a.arg==null||a.instance==null)return;const e="setup"in a.instance.$.type;a.arg in a.instance?a.instance[a.arg]=t:e&&console.warn("Maska: please expose `%s` using defineExpose",a.arg)},V=(a,t)=>{var e;const s=a instanceof HTMLInputElement?a:a.querySelector("input");if(s==null||(s==null?void 0:s.type)==="file")return;let n={};if(t.value!=null&&(n=typeof t.value=="string"?{mask:t.value}:{...t.value}),t.arg!=null){const i=l=>{const u=t.modifiers.unmasked?l.unmasked:t.modifiers.completed?l.completed:l.masked;j(t,u)};n.onMaska=n.onMaska==null?i:Array.isArray(n.onMaska)?[...n.onMaska,i]:[n.onMaska,i]}R.has(s)?(e=R.get(s))==null||e.update(n):R.set(s,new L(s,n))};export{V as k};
