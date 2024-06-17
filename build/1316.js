"use strict";(self.webpackChunkwoo_laqirapay=self.webpackChunkwoo_laqirapay||[]).push([[1316],{1316:(e,a,t)=>{t.d(a,{offchainLookup:()=>g,offchainLookupSignature:()=>y});var r=t(6782),s=t(8463),n=t(6329),o=t(1526);class c extends n.C{constructor({callbackSelector:e,cause:a,data:t,extraData:r,sender:s,urls:n}){super(a.shortMessage||"An error occurred while fetching for an offchain result.",{cause:a,metaMessages:[...a.metaMessages||[],a.metaMessages?.length?"":[],"Offchain Gateway Call:",n&&["  Gateway URL(s):",...n.map((e=>`    ${(0,o.ID)(e)}`))],`  Sender: ${s}`,`  Data: ${t}`,`  Callback selector: ${e}`,`  Extra data: ${r}`].flat()}),Object.defineProperty(this,"name",{enumerable:!0,configurable:!0,writable:!0,value:"OffchainLookupError"})}}class d extends n.C{constructor({result:e,url:a}){super("Offchain gateway response is malformed. Response data must be a hex value.",{metaMessages:[`Gateway URL: ${(0,o.ID)(a)}`,`Response: ${(0,s.A)(e)}`]}),Object.defineProperty(this,"name",{enumerable:!0,configurable:!0,writable:!0,value:"OffchainLookupResponseMalformedError"})}}class l extends n.C{constructor({sender:e,to:a}){super("Reverted sender address does not match target contract address (`to`).",{metaMessages:[`Contract address: ${a}`,`OffchainLookup sender address: ${e}`]}),Object.defineProperty(this,"name",{enumerable:!0,configurable:!0,writable:!0,value:"OffchainLookupSenderMismatchError"})}}var u=t(6595),i=t(5462),f=t(4531),p=t(4306),h=t(1657),b=t(5419),w=t(6394);const y="0x556f1830",m={name:"OffchainLookup",type:"error",inputs:[{name:"sender",type:"address"},{name:"urls",type:"string[]"},{name:"callData",type:"bytes"},{name:"callbackFunction",type:"bytes4"},{name:"extraData",type:"bytes"}]};async function g(e,{blockNumber:a,blockTag:t,data:s,to:n}){const{args:o}=(0,i.W)({data:s,abi:[m]}),[d,u,w,y,g]=o,{ccipRead:C}=e,O=C&&"function"==typeof C?.request?C.request:k;try{if(!function(e,a){if(!(0,h.P)(e,{strict:!1}))throw new p.M({address:e});if(!(0,h.P)(a,{strict:!1}))throw new p.M({address:a});return e.toLowerCase()===a.toLowerCase()}(n,d))throw new l({sender:d,to:n});const s=await O({data:w,sender:d,urls:u}),{data:o}=await(0,r.T1)(e,{blockNumber:a,blockTag:t,data:(0,b.xW)([y,(0,f.h)([{type:"bytes"},{type:"bytes"}],[s,g])]),to:n});return o}catch(e){throw new c({callbackSelector:y,cause:e,data:s,extraData:g,sender:d,urls:u})}}async function k({data:e,sender:a,urls:t}){let r=new Error("An unknown error occurred.");for(let n=0;n<t.length;n++){const o=t[n],c=o.includes("{data}")?"GET":"POST",l="POST"===c?{data:e,sender:a}:void 0;try{const t=await fetch(o.replace("{sender}",a).replace("{data}",e),{body:JSON.stringify(l),method:c});let n;if(n=t.headers.get("Content-Type")?.startsWith("application/json")?(await t.json()).data:await t.text(),!t.ok){r=new u.Ci({body:l,details:n?.error?(0,s.A)(n.error):t.statusText,headers:t.headers,status:t.status,url:o});continue}if(!(0,w.q)(n)){r=new d({result:n,url:o});continue}return n}catch(e){r=new u.Ci({body:l,details:e.message,url:o})}}throw r}}}]);