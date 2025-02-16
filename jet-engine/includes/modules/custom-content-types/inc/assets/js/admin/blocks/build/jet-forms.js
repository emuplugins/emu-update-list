(()=>{"use strict";const e=window.React,{BaseComputedField:t=Object}=JetFBComponents,{sprintf:n,__:l}=wp.i18n,o=e=>{const{insert_custom_content_type:t={}}=e?.settings;return t.type};function s(){t.call(this),this.getSupportedActions=function(){return["insert_custom_content_type"]},this.isSupported=function(e){return t.prototype.isSupported.call(this,e)&&o(e)},this.getName=function(){const e=this.hasInList?`_${this.action.id}`:"";return`inserted_cct_${o(this.action)}`+e},this.getHelp=function(){return n(l("A computed field from the <b>Insert/Update Custom Content Type Item (%s)</b> action.","jet-form-builder"),this.action.id)}}s.prototype=Object.create(t.prototype);const i=s;var a;const{TextControl:c,SelectControl:r}=wp.components,{useState:d,useEffect:p}=wp.element,{addAction:u,getFormFieldsBlocks:f,Tools:{withPlaceholder:m},addComputedField:h=()=>{},convertListToFieldsMap:_=()=>[]}=JetFBActions,{ActionFieldsMap:y,WrapperRequiredControl:b}=JetFBComponents,{useFields:g=()=>!1}=null!==(a=window?.JetFBHooks)&&void 0!==a?a:{},{addFilter:F}=wp.hooks;h(i),F("jet.fb.preset.editor.custom.condition","jet-form-builder",(function(e,t,n){return"cct_query_var"===t?"custom_content_type"===n.from&&"query_var"===n.post_from:e})),u("insert_custom_content_type",(function({settings:t,label:n,help:l,source:o,onChangeSetting:s,getMapField:i,setMapField:a}){const[u,h]=d([]),[F,v]=d([]),[C,E]=d(!1);let w=g();const[j]=d((()=>_(!1===w?f():w)),[]),I=function(e){e&&(E(!0),wp.apiFetch({method:"get",path:o.fetch_path+"?type="+e}).then((e=>{if(e.success&&e.fields){const n=[];for(var t=0;t<e.fields.length;t++)"_ID"===e.fields[t].value&&(e.fields[t].label+=" (will update the item)"),n.push({...e.fields[t]});h(n)}else alert(e.notices[t].join("; ")+";");E(!1)})).catch((e=>{E(!1),alert(e),console.log(e)})))};return p((()=>{I(t.type)}),[]),p((()=>{t.type||h([])}),[t.type]),p((()=>{const e={};u.forEach((t=>{"_ID"!==t.value&&(e[t.value]={label:t.label})})),v(Object.entries(e))}),[u]),(0,e.createElement)(e.Fragment,null,(0,e.createElement)(r,{label:n("type"),labelPosition:"side",value:t.type,onChange:e=>{s(e,"type"),I(e)},options:m(o.types)}),(0,e.createElement)(r,{label:n("status"),labelPosition:"side",value:t.status,onChange:e=>{s(e,"status")},options:m(o.statuses)}),(0,e.createElement)("div",{style:{opacity:C?"0.5":"1"},className:"jet-control-full"},(0,e.createElement)(y,{label:n("fields_map"),fields:j,plainHelp:l("fields_map")},(({fieldId:t,fieldData:n,index:l})=>(0,e.createElement)(b,{field:[t,n]},(0,e.createElement)(r,{key:t+l,value:i({name:t}),onChange:e=>a({nameField:t,value:e}),options:m(u)})))),0<F.length&&(0,e.createElement)(y,{label:n("default_fields"),fields:F,plainHelp:l("default_fields")},(({fieldId:t,fieldData:n,index:l})=>(0,e.createElement)(b,{field:[t,n]},(0,e.createElement)(c,{key:t+l,value:i({source:"default_fields",name:t}),onChange:e=>a({source:"default_fields",nameField:t,value:e})}))))))}))})();
//# sourceMappingURL=jet-forms.js.map