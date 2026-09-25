// One-time source migration. Only CSS color values and legacy theme definitions
// are changed. JS data, patient records and component geometry are untouched.
import fs from 'node:fs';
import path from 'node:path';
function walk(p) { return fs.readdirSync(p,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(p,e.name)):[path.join(p,e.name)]); }
const aliases=new Set([...fs.readFileSync('resources/css/design-tokens.css','utf8').matchAll(/(--[\w-]+):/g)].map(m=>m[1]));
const explicit={};
function group(token,colors){for(const color of colors.split(' '))explicit[color.toLowerCase()]=token;}
group('border','eae5f4 f0dde6 eadfe7 ede7ec e5e7eb ddd6fe e2dbee edeeF2 dee2e6 e2e8f0 d1d5db 3a3b3c 2c2d2e 372f50 3c3455');
group('bg','fbf7fa f6f4fb fafafc f8fafc f9fafb fbfBfe 13101c 18191a 08040f');
group('surface','ffffff fff 242526 1e1a2b');
group('surface-soft','f4f0f3 f9f8fc f3f4f6 f3eff9 f8f9fa f1f5f9 262038 252035');
group('text','111827 1f2937 1e182d 161320 3c344e 374151 241d30 0f172a 292334 4a414d 51445c e4e6eb f5f3ff');
group('text-muted','756a88 a88b99 765a68 6b7280 9ca3af 64748b 9e94b0 756574 b0b3b8 a69bbf 7c7194 938a9c 756a80');
group('primary','7c3aed 9333ea 6c5ce7 8b5cf6 6366f1');
group('primary-text','6d28d9 5b21b6 4c1d95');
group('primary-soft','ede9fe f3effd e7e3fa f5f3ff');
group('secondary','f273ac f472b6 e75480');
group('secondary-text','b23a68 8f2f55 db2777 be185d 9d174d');
group('secondary-soft','fbe3ec fdf2f8 fce7f3 fff1f5 fff0f5 fad1dc');
group('success-soft','dff3ec ecfdf5 d1fae5 dcfce7 e5f6ee');
group('success-text','047857 16695e 2e8b7a 15803d 059669');
group('success','10b981 22c55e 22a06b');
group('warning-soft','fffbeb fef3c7 fef9c3 fffbf2');
group('warning-text','92400e b45309 d97706 b47719');
group('warning','f59e0b fbbf24 f4b740');
group('danger-soft','fef2f2 fde8e8 fee2e2 fff4f5 fff5f5');
group('danger-text','991b1b b91c1c dc2626 b43b52 c0392b a72d47');
group('danger','ef4444 dc4c64');
group('info-soft','e2eeff dbeafe e0f2fe');
group('info-text','1d4ed8 2563eb 4f8f86');
group('info','0ea5e9 06b6d4 2a9d8f');
function tokenFor(hex,property='',context='') {
    let raw=hex.replace('#','').toLowerCase();
    if(raw.length===3||raw.length===4)raw=[...raw].map(x=>x+x).join('');
    const rgb=raw.slice(0,6), alpha=raw.length===8?parseInt(raw.slice(6),16)/255:1;
    const r=parseInt(rgb.slice(0,2),16)/255,g=parseInt(rgb.slice(2,4),16)/255,b=parseInt(rgb.slice(4,6),16)/255;
    const max=Math.max(r,g,b),min=Math.min(r,g,b),delta=max-min,l=(max+min)/2;
    const s=delta===0?0:delta/(1-Math.abs(2*l-1));
    let h=delta===0?0:max===r?60*(((g-b)/delta)%6):max===g?60*((b-r)/delta+2):60*((r-g)/delta+4);if(h<0)h+=360;
    const fg=/(^color$|fill|stroke|text)/.test(property), border=property.includes('border');
    const ctx=context.toLowerCase();
    let token=explicit[rgb]||explicit[raw];
    const clinical=/critical|danger|risk.high|high.risk|emergency|\.high\b|\.red\b/.test(ctx)?'danger':/warning|risk.medium|medium.risk|\.medium\b/.test(ctx)?'warning':/success|risk.low|low.risk|\.low\b/.test(ctx)?'success':null;
    if(clinical && s>.15)token=clinical+(l>.8?'-soft':fg||l<.35?'-text':'');
    if(!token) {
        if(s<.18 || (l<.2 && s<.55)) token=border?'border':fg?(l>.7?'text':'text-muted'):(l>.9?'surface-soft':l<.17?'surface-strong':'surface-soft');
        else {
            const family=h<15||h>=355?'danger':h<48?'peach':h<72?'warning':h<166?'success':h<225?'info':h<290?'primary':'secondary';
            token=family+(l>.78?'-soft':fg||l<.36?'-text':'');
        }
    }
    if(rgb==='ffffff'&&fg)token=ctx.includes('data-theme')?'text':'on-solid';
    if(rgb==='000000')token=fg?'text':'surface-strong';
    if(token==='text'&&!fg&&!border)token=ctx.includes('data-theme')?'surface':'surface-strong';
    if(border&&s<.2)token='border';
    const value=`var(--color-${token})`;
    return alpha<1?`color-mix(in srgb, ${value} ${+(alpha*100).toFixed(2)}%, transparent)`:value;
}
function convertValue(value,property,context) {
    // Keep data-URI illustrations and encoded URLs intact.
    const urls=[];
    value=value.replace(/url\([^)]*\)/g,m=>`URL_TOKEN_${urls.push(m)-1}`);
    value=value.replace(/rgba?\(\s*(\d+)\s*[, ]\s*(\d+)\s*[, ]\s*(\d+)(?:\s*[,/]\s*([\d.]+%?))?\s*\)/gi,(m,r,g,b,a)=>{
        const hex='#'+[r,g,b].map(n=>(+n).toString(16).padStart(2,'0')).join('');
        const isShadow=property.includes('shadow');
        const base=isShadow?'rgb(var(--color-shadow-rgb))':tokenFor(hex,property,context);
        if(a===undefined)return base;
        const pct=a.endsWith('%')?parseFloat(a):parseFloat(a)*100;
        return `color-mix(in srgb, ${base} ${+pct.toFixed(2)}%, transparent)`;
    });
    value=value.replace(/#[\da-f]{3,8}\b/gi,h=>tokenFor(h,property,context));
    if(/color|background|border|shadow|fill|stroke/.test(property))value=value.replace(/\b(white|black)\b/g,m=>tokenFor(m==='white'?'#ffffff':'#000000',property,context));
    return value.replace(/URL_TOKEN_(\d+)/g,(_,i)=>urls[i]);
}
function declarations(body,context,removeAliases=false) {
    return body.replace(/(^|[;\n\r])([\t ]*)([-\w]+)\s*:\s*([^;]+)(?=;|$)/g,(full,start,space,property,value)=>{
        if(removeAliases&&aliases.has(property)&&!property.startsWith('--bs-'))return start;
        return `${start}${space}${property}:${convertValue(value,property,context)}`;
    });
}
function stylesheet(css){
    // These patches matched literal inline colors. Tokenized declarations now
    // adapt in both modes directly, so selector-string remapping is obsolete.
    css=css.replace(/[^{}]*\[style\*=[^{}]+\{[^{}]*\}/g,'');
    return css.replace(/([^{}]+)\{([^{}]*)\}/g,(m,selector,body)=>`${selector}{${declarations(body,selector,true)}}`);
}
const files=[...walk('resources/views').filter(p=>p.endsWith('.blade.php')&&!p.endsWith('welcome.blade.php')),...walk('public/css').filter(p=>p.endsWith('.css')&&!p.endsWith('bootstrap-icons.css')),'resources/css/app-components.css'];
let changed=0;
for(const file of files.filter(file => !process.argv[2] || file.replaceAll('\\', '/').includes(process.argv[2]))){
    let text=fs.readFileSync(file,'utf8'), before=text;
    if(file.endsWith('portal-theme.blade.php')&&text.includes("@push('scripts')"))text=text.slice(0,text.indexOf("@push('scripts')"));
    if(file.endsWith('.css'))text=stylesheet(text);
    else {
        text=text.replace(/<style[^>]*>([\s\S]*?)<\/style>/g,(m,css)=>m.replace(css,stylesheet(css)));
        text=text.replace(/style="([^"\n]*)"/g,(m,css)=>m.replace(css,declarations(css,'inline')));
        text=text.replace(/\b(fill|stroke)="(#[\da-f]{3,8})"/gi,(m,p,h)=>`${p}="${tokenFor(h,p,'svg')}"`);
    }
    if(before!==text){fs.writeFileSync(file,text);changed++;}
}
console.log('Tokenized color declarations in',changed,'files.');
