// Maintainer utility: generate the shared tokens and their legacy aliases.
// Layout, spacing and behavior remain in the existing component stylesheets.
import fs from 'node:fs';
const target = 'resources/css/design-tokens.css';
const original = fs.readFileSync(target, 'utf8');
if (!fs.existsSync('resources/css/app-components.css')) {
    fs.writeFileSync('resources/css/app-components.css', original.slice(original.indexOf('/* ------------------------------------------------------------\n   Base Styles') >= 0 ? original.indexOf('/* ------------------------------------------------------------\n   Base Styles') : original.indexOf('body {')));
}
const neutral = {
    'bg':'#FAFAFC', 'surface':'#FFFFFF', 'surface-soft':'#F4F5F8', 'surface-strong':'#1F2937',
    'border':'#E5E7EB', 'input-border':'#8791A2', 'text':'#1F2937', 'text-muted':'#5D677C',
    'white':'#FFFFFF', 'ink':'#101828', 'shadow-rgb':'16 24 40',
};
const fixed = {
    pink:['#E75480','#FCE7EF','#AD2851','#FFADC6','#3C2534'],
    purple:['#7C3AED','#F1EAFE','#6D28D9','#C8ADFF','#302741'],
    green:['#22A06B','#E5F6EE','#14744B','#69D3A3','#1F3934'],
    teal:['#2A9D8F','#E6F5F2','#176C63','#6BD3C5','#213A3C'],
    peach:['#F59E7A','#FFF0E9','#964B2D','#FFC6AE','#40332F'],
    amber:['#F4B740','#FFF5DB','#835600','#F4CB73','#3C3528'],
    red:['#DC4C64','#FDEBF0','#B52D46','#FFADBB','#402A36'],
};
let light = Object.entries(neutral).map(([k,v])=>`--color-${k}: ${v};`);
let dark = Object.entries({bg:'#111827',surface:'#1F2937','surface-soft':'#283446',border:'#465469','input-border':'#7A899E',text:'#F3F4F6','text-muted':'#B7C0CE'}).map(([k,v])=>`--color-${k}: ${v};`);
for(const [name,[base,soft,text,darkText,darkSoft]] of Object.entries(fixed)) {
    light.push(`--color-${name}: ${base};`,`--color-${name}-soft: ${soft};`,`--color-${name}-text: ${text};`);
    dark.push(`--color-${name}-soft: ${darkSoft};`,`--color-${name}-text: ${darkText};`);
}
for (const [slot,base,hover,on,onHover,text,textDark,soft,softDark] of [
    ['primary','#7C3AED','#6D28D9','#FFFFFF','#FFFFFF','#6D28D9','#C8ADFF','#F1EAFE','#302741'],
    ['secondary','#E75480','#C34770','#101828','#FFFFFF','#AD2851','#FFADC6','#FCE7EF','#3C2534'],
    ['accent','#22A06B','#1D8A5C','#101828','#FFFFFF','#14744B','#69D3A3','#E5F6EE','#1F3934'],
]) {
    for(const [variant,fallback] of [['',base],['-hover',hover],['-on',on],['-on-hover',onHover],['-text',text],['-soft',soft]]) {
        const source=variant==='-text' ? '-text-light' : variant;
        light.push(`--color-${slot}${variant}: var(--theme-${slot}${source}, ${fallback});`);
    }
    dark.push(`--color-${slot}-text: var(--theme-${slot}-text-dark, ${textDark});`,`--color-${slot}-soft: var(--theme-${slot}-soft-dark, ${softDark});`);
}
const aliases={};
const alias=(name,to)=>{aliases[name]=`var(--${to})`;};
for(const [semantic,base] of Object.entries({success:'green',warning:'amber',danger:'red',info:'teal'})) {
    for(const variant of ['', '-soft','-text']) alias(`color-${semantic}${variant}`,`color-${base}${variant}`);
    alias(semantic,`color-${semantic}`);
    aliases[`color-${semantic}-hover`]=`color-mix(in srgb, var(--color-${base}) 84%, var(--color-ink))`;
    aliases[`color-${semantic}-border`]=`color-mix(in srgb, var(--color-${base}) 45%, var(--color-border))`;
    aliases[`color-${semantic}-on`]=semantic==='danger'?'#000000':'var(--color-ink)';
    for(const v of ['bg','text'])alias(`badge-${semantic==='danger'?'critical':semantic}-${v}`,`color-${semantic}-${v==='bg'?'soft':'text'}`);
}
for(const slot of ['primary','secondary']) {
    for(const prefix of ['', 'mw-', 'wp-']) {
        alias(`${prefix}${slot}`,`color-${slot}`);
        for(const suffix of ['dark','deep','dk','light','lt'])alias(`${prefix}${slot}-${suffix}`,`color-${slot}-text`);
        for(const suffix of ['subtle','border'])alias(`${prefix}${slot}-${suffix}`,`color-${slot}-soft`);
        aliases[`${prefix}${slot}-glow`]=`color-mix(in srgb, var(--color-${slot}) 20%, transparent)`;
    }
    for(const suffix of ['light','dark'])alias(`color-${slot}-${suffix}`,`color-${slot}-text`);
    alias(`color-${slot}-subtle`,`color-${slot}-soft`);
}
for(const [name,to] of Object.entries({
    'bg-main':'bg','bg-card':'surface','bg-card2':'surface-soft','bg-input':'surface','bg-sidebar':'surface',
    'bg-glass':'surface','nav-bg':'surface','sidebar-bg':'surface','border':'border','border-glass':'border',
    'input-border':'input-border','text':'text','text-muted':'text-muted','text-dim':'text-muted','placeholder':'text-muted',
    'skeleton-from':'surface-soft','skeleton-to':'border','row-alt':'surface-soft','row-hover':'primary-soft',
    'deep':'text','deep-light':'text','deep-dark':'text','color-deep':'text','color-deep-dark':'text','color-deep-light':'text',
    'color-text-dim':'text-muted','color-surface-subtle':'surface-soft',
    'dark-contrast':'primary','dark-contrast-hover':'primary-hover','color-dark-contrast':'primary','color-dark-contrast-hover':'primary-hover',
    'lavender':'purple-soft','lavender-light':'purple-soft','lavender-banner':'secondary-soft',
    'color-lavender':'purple-soft','color-lavender-soft':'purple-soft','color-lavender-light':'purple-soft','color-lavender-banner':'secondary-soft',
    'accent-violet':'purple-text',
    'mw-heading':'text','mw-body':'text-muted','mw-muted':'text-muted','mw-bg':'bg','mw-surface':'surface','mw-border':'border','mw-border-soft':'border',
    'wp-lavender':'secondary-soft','wp-lavender-subtle':'secondary-soft','wp-dark':'text','wp-deep':'text',
    'wp-teal':'info-text','wp-teal-light':'info-soft','wp-teal-dim':'info-soft','wp-rose':'pink','wp-rose-dark':'pink-text','wp-rose-light':'pink-soft',
    'wp-surface':'surface','wp-bg':'bg','wp-cream':'bg','wp-white':'surface','wp-text':'text','wp-text-mid':'text','wp-text-soft':'text-muted','wp-text-faint':'text-muted','wp-border':'border',
    'wp-success':'success-text','wp-success-bg':'success-soft','wp-warning':'warning-text','wp-warning-bg':'warning-soft','wp-danger':'danger-text','wp-danger-bg':'danger-soft',
    'public-rose':'primary-text','public-rose-dark':'primary-hover','public-ink':'text','public-muted':'text-muted','public-line':'border','public-bg':'bg','public-lilac':'secondary-soft',
}))alias(name,`color-${to}`);
for(const [shade,to] of Object.entries({50:'bg',100:'surface-soft',200:'border',300:'border',400:'input-border',500:'text-muted',600:'text-muted',700:'text',800:'text',900:'text'}))alias(`color-gray-${shade}`,`color-${to}`);
for(const [name,base]of Object.entries({peach:'peach',blue:'info',lavender:'purple',mint:'green'})) {
    alias(`pastel-${name}-bg`,`color-${base}-soft`);alias(`pastel-${name}-border`,`color-border`);alias(`pastel-${name}-text`,`color-${base}-text`);
}
aliases['focus-ring']='color-mix(in srgb, var(--color-primary-text) 25%, transparent)';
aliases['color-primary-glow']='var(--focus-ring)';
for(const [size,value] of Object.entries({sm:'0 2px 8px rgb(var(--color-shadow-rgb) / .05)',md:'0 10px 30px -4px rgb(var(--color-shadow-rgb) / .08)',lg:'0 20px 48px -8px rgb(var(--color-shadow-rgb) / .12)',glow:'0 6px 18px rgb(var(--color-shadow-rgb) / .12)'})) aliases[`shadow-${size}`]=value;
for(const prefix of ['mw','wp'])for(const size of ['xs','sm','md','lg','pink'])alias(`${prefix}-shadow-${size}`,`shadow-${size==='xs'?'sm':size==='pink'?'glow':size}`);
aliases['color-on-solid']='var(--color-white)';
const structural=`--font-sans:'Inter',sans-serif; --font-heading:'Plus Jakarta Sans',sans-serif; --font-size-base:.9375rem; --font-size-sm:.8125rem; --font-size-xs:.75rem;
--spacing-0:0; --spacing-1:.25rem; --spacing-2:.5rem; --spacing-3:.75rem; --spacing-4:1rem; --spacing-5:1.25rem; --spacing-6:1.5rem;
--rounded-xs:6px; --rounded-sm:10px; --rounded:18px; --rounded-lg:24px; --rounded-xl:30px; --rounded-pill:9999px; --rounded-full:9999px;
--transition-fast:.15s ease; --transition-base:.25s ease;`;
const scope=':root, [data-theme="light"], .rc-appearance-preview';
const css=`/* ReproCare color source of truth. Generated by scripts/build-color-tokens.mjs.
   Semantic status colors are fixed; --theme-* only customizes brand interactions. */
${scope} {\n    ${light.join('\n    ')}\n    ${structural}\n}
[data-theme="dark"] {\n    ${dark.join('\n    ')}\n    color-scheme:dark;\n}
${scope}, [data-theme="dark"] {\n    ${Object.entries(aliases).map(([k,v])=>`--${k}: ${v};`).join('\n    ')}\n}
`;
fs.writeFileSync(target,css);
console.log('Generated canonical tokens and',Object.keys(aliases).length,'compatibility aliases.');
