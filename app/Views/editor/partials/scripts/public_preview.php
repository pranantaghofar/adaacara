        function getCanvasData() {
            storeCurrentPage();
            const activePage = state.pages[state.activePageIndex] || getCurrentPageData();
            return {
                renderer: 'fabric',
                mode: 'website-pages',
                projectIntent: state.projectIntent || '',
                editMode: state.editMode === 'opening' ? 'opening' : (state.editMode === 'photobooth' ? 'photobooth' : 'pages'),
                activePageIndex: state.activePageIndex,
                pages: state.pages,
                opening: normalizeOpeningConfig(state.opening),
                photoboothFrames: normalizePhotoboothFrames(state.photoboothFrames || []),
                activePhotoboothFrameIndex: Math.max(0, Math.min(Number(state.activePhotoboothFrameIndex) || 0, Math.max(0, (state.photoboothFrames || []).length - 1))),
                objects: activePage.objects || [],
                artboard: activePage.artboard || {
                    width: state.canvas.getWidth(),
                    height: state.canvas.getHeight()
                },
                background: activePage.background || activePage.backgroundColor || '#ffffff',
                guestbook: normalizeGuestbookConfig(state.guestbook),
                guestbookEndpoint: config.publicBaseUrl + normalizeSlug(config.initialSlug || config.initialTitle) +
                    '/guestbook',
            };
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function previewCssColorValue(value) {
            value = String(value || '').trim();
            if (/^#[0-9a-f]{3,8}$/i.test(value)) return value;
            if (/^(rgba?|hsla?)\([0-9.,%\s-]+\)$/i.test(value)) return value;
            return '#ffffff';
        }

        function previewLoaderTextColor(color) {
            color = previewCssColorValue(color);
            var match = color.match(/^#([0-9a-f]{3}|[0-9a-f]{6})$/i);
            if (!match) return '#0f172a';
            var hex = match[1];
            if (hex.length === 3) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }
            var red = parseInt(hex.slice(0, 2), 16);
            var green = parseInt(hex.slice(2, 4), 16);
            var blue = parseInt(hex.slice(4, 6), 16);
            var luminance = ((red * 299) + (green * 587) + (blue * 114)) / 1000;
            return luminance < 145 ? '#ffffff' : '#0f172a';
        }

        function publicHtml() {
            const data = getCanvasData();
            const visiblePages = data.pages.filter(pageData => pageData.hidden !== true);
            const pages = visiblePages.length ? visiblePages : [data.pages[data.activePageIndex] || data.pages[0]];
            const pageHtml = pages.map((pageData, index) => {
                const width = pageData.artboard?.width || 1080;
                const height = pageData.artboard?.height || 1920;
                const ratio = height > 0 ? width / height : 0.5625;
                const maxWidth = ratio >= 1 ? 860 : 520;
                const title = escapeHtml(pageData.title || `Halaman ${index + 1}`);
                const pageBg = previewCssColorValue(pageData.background || pageData.backgroundColor || '#ffffff');
                const loaderText = previewLoaderTextColor(pageBg);
                return `<section class="aa-fabric-page-section" data-page-index="${index}"><div class="aa-fabric-artboard" style="--aa-artboard-ratio:${ratio};--aa-artboard-max-width:${maxWidth}px;--aa-page-bg:${pageBg};--aa-loader-text:${loaderText};aspect-ratio:${width}/${height};"><canvas id="aaFabricPublicCanvas${index}" aria-label="${title}"></canvas></div></section>`;
            }).join('');

            return `<main class="aa-fabric-page">${pageHtml}</main><footer class="aa-fabric-watermark"><img src="https://adaacara.com/assets/img/adaacara-logo.png" alt="AdaAcara.com"></footer>`;
        }

        function previewOpeningPayload(data) {
            const opening = normalizeOpeningConfig(data.opening);
            const artboard = opening.artboard || {};
            return {
                enabled: opening.enabled !== false,
                mode: opening.mode || 'default',
                exitAnimation: opening.exitAnimation || 'fade',
                background: opening.background || '#0f766e',
                backgroundColor: opening.background || '#0f766e',
                artboard: {
                    width: Math.max(1, Number(artboard.width) || 1080),
                    height: Math.max(1, Number(artboard.height) || 1920),
                },
                objects: Array.isArray(opening.objects) ? opening.objects : [],
            };
        }

        function previewOpeningHtml(data) {
            const opening = previewOpeningPayload(data);
            if (opening.enabled === false) return '';
            const hasCustom = opening.mode === 'custom' && opening.objects.length > 0;
            const hasButton = hasCustom && opening.objects.some(object => object?.customType === 'opening-button');
            if (!hasButton) return '';
            const ratio = `${opening.artboard.width}/${opening.artboard.height}`;
            return `<div id="aaOpeningModal" class="aa-opening-modal aa-opening-exit-${escapeHtml(opening.exitAnimation)}" role="dialog" aria-modal="true" aria-label="Opening undangan"><section class="aa-opening-card is-custom" style="aspect-ratio:${ratio}"><div class="aa-opening-custom-stage"><canvas id="aaOpeningFabricCanvas" aria-label="Opening undangan"></canvas></div><button id="aaOpeningButton" class="aa-opening-custom-hotspot" type="button" aria-label="Buka Undangan"></button></section></div>`;
        }

        function previewOpeningCss() {
            return `.aa-opening-modal{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:22px;background:rgba(0,0,0,.34);backdrop-filter:blur(1px);opacity:0;pointer-events:none;transition:opacity 1.2s ease}.aa-opening-modal.is-visible{opacity:1;pointer-events:auto}.aa-opening-modal.is-leaving{opacity:0;pointer-events:none}.aa-opening-card{position:relative;width:min(88vw,430px);min-height:360px;overflow:hidden;border:1px solid rgba(255,255,255,.34);border-radius:30px;background:linear-gradient(145deg,rgba(255,255,255,.24),rgba(255,255,255,.08)),rgba(17,24,39,.18);box-shadow:0 30px 90px rgba(0,0,0,.34);color:#fff;transform:translateY(18px) scale(.96);transition:opacity 1.2s ease,transform 1.2s cubic-bezier(.2,.8,.2,1)}.aa-opening-modal.is-visible .aa-opening-card{transform:translateY(0) scale(1)}.aa-opening-modal.is-leaving .aa-opening-card{opacity:0;transform:translateY(10px) scale(.97)}.aa-opening-modal.is-leaving.aa-opening-exit-slide-up .aa-opening-card,.aa-opening-modal.is-leaving.aa-opening-exit-elegant-lift .aa-opening-card{transform:translateY(-42px) scale(.98)}.aa-opening-modal.is-leaving.aa-opening-exit-zoom-out .aa-opening-card{transform:scale(.86)}.aa-opening-modal.is-leaving.aa-opening-exit-blur-fade .aa-opening-card{filter:blur(10px);transform:scale(.98)}.aa-opening-card.is-custom{display:grid;place-items:stretch;background:rgba(17,24,39,.16)}.aa-opening-custom-stage{position:absolute;inset:0;z-index:0;overflow:hidden}.aa-opening-custom-stage canvas{display:block;width:100%!important;height:100%!important}.aa-opening-main{position:absolute;inset:0;z-index:1;display:grid;place-items:center}.aa-opening-button{display:inline-flex;min-height:35px;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.48);border-radius:999px;background:rgba(255,255,255,.22);color:#fff;padding:0 20px;font:inherit;font-size:11px;font-weight:950;letter-spacing:.12em;cursor:pointer}.aa-opening-button.is-custom-overlay{position:absolute;left:50%;bottom:28px;z-index:3;transform:translateX(-50%);background:rgba(15,118,110,.92)}.aa-opening-custom-hotspot{position:absolute;z-index:4;border:0;border-radius:999px;background:transparent;padding:0;cursor:pointer}.aa-opening-guest{position:absolute;left:28px;bottom:28px;z-index:1}.aa-opening-label{margin:0 0 8px;color:rgba(255,255,255,.72);font-size:11px;font-weight:900;letter-spacing:.16em;text-transform:uppercase}.aa-opening-name{display:block;color:#fff;font-size:clamp(18px,3vw,55px);font-weight:900;line-height:.98}`;
        }

        function previewOpeningJs(data) {
            const opening = previewOpeningPayload(data);
            if (opening.enabled === false) return '';
            const openingJson = JSON.stringify(opening).replace(/</g, '\\u003c');
            return `(function(){var modal=document.getElementById('aaOpeningModal');var button=document.getElementById('aaOpeningButton');var openingCanvasEl=document.getElementById('aaOpeningFabricCanvas');var openingData=${openingJson};var pending=[];var gated=!!(modal&&button);window.AdaAcaraPublicInvitationOpened=!gated;window.AdaAcaraRunWhenInvitationOpened=function(cb){if(typeof cb!=='function')return;if(window.AdaAcaraPublicInvitationOpened){cb();return;}pending.push(cb);};function release(){var list=pending.slice();pending=[];list.forEach(function(cb){try{cb();}catch(e){console.error(e);}});}if(!gated){release();return;}function aaOpeningImageEffectFilter(image){var preset=String(image&&image.aaImageEffectPreset||'none');if(!preset||preset==='none'||preset==='opacity'||preset==='shadow')return '';if(Array.isArray(image.filters)&&image.filters.length)return '';if(preset==='brightness')return 'brightness(1.16)';if(preset==='contrast')return 'contrast(1.22)';if(preset==='saturation')return 'saturate(1.38)';if(preset==='grayscale')return 'grayscale(1)';if(preset==='sepia')return 'sepia(1)';if(preset==='blur')return 'blur(2px)';if(preset==='sharpen')return 'contrast(1.28) saturate(1.12)';if(preset==='vintage')return 'sepia(.55) contrast(1.08) saturate(.82)';if(preset==='soft-wedding')return 'brightness(1.08) contrast(.96) saturate(1.18) sepia(.08)';if(preset==='clean-bright')return 'brightness(1.14) contrast(1.08) saturate(1.08)';if(preset==='warm-editorial')return 'sepia(.18) brightness(1.06) contrast(1.12) saturate(1.14)';if(preset==='film-matte')return 'sepia(.2) contrast(.92) saturate(.78) brightness(1.04)';if(preset==='pastel-bloom')return 'brightness(1.1) contrast(.94) saturate(1.32) hue-rotate(-6deg)';if(preset==='moody-luxe')return 'brightness(.88) contrast(1.22) saturate(.9) sepia(.08)';if(preset==='classic-bw')return 'grayscale(1) contrast(1.18) brightness(1.04)';if(preset==='dreamy-soft')return 'brightness(1.12) contrast(.9) saturate(1.12) blur(.75px)';if(preset==='recolor-white')return 'grayscale(.35) brightness(1.34) contrast(.86) saturate(.68)';if(preset==='recolor-black')return 'grayscale(1) brightness(.72) contrast(1.28)';if(preset==='recolor-gold')return 'sepia(.55) saturate(1.45) hue-rotate(4deg) brightness(1.08) contrast(1.04)';if(preset==='recolor-teal')return 'sepia(.18) saturate(1.35) hue-rotate(135deg) brightness(.96) contrast(1.06)';if(preset==='recolor-rose')return 'sepia(.22) saturate(1.35) hue-rotate(300deg) brightness(1.04) contrast(.98)';if(preset==='recolor-slate')return 'grayscale(.65) sepia(.12) saturate(.7) hue-rotate(170deg) brightness(.92) contrast(1.08)';if(preset==='remove-color')return 'saturate(.2) contrast(1.12)';return '';}function installOpeningRoundedImageRenderer(){if(!window.fabric||fabric.Image.prototype.__aaRoundedRendererInstalled)return;var originalRender=fabric.Image.prototype._render;function drawPath(ctx,width,height,radius){var r=Math.min(Math.max(0,Number(radius)||0),width/2,height/2);var x=-width/2;var y=-height/2;ctx.beginPath();if(!r){ctx.rect(x,y,width,height);return;}ctx.moveTo(x+r,y);ctx.lineTo(x+width-r,y);ctx.quadraticCurveTo(x+width,y,x+width,y+r);ctx.lineTo(x+width,y+height-r);ctx.quadraticCurveTo(x+width,y+height,x+width-r,y+height);ctx.lineTo(x+r,y+height);ctx.quadraticCurveTo(x,y+height,x,y+height-r);ctx.lineTo(x,y+r);ctx.quadraticCurveTo(x,y,x+r,y);ctx.closePath();}function drawStroke(ctx,image,width,height,radius){var strokeWidth=Math.max(0,Number(image.strokeWidth)||0);if(!strokeWidth||!image.stroke||image.stroke==='transparent')return;ctx.save();drawPath(ctx,width,height,radius);ctx.lineWidth=strokeWidth;ctx.strokeStyle=image.stroke;ctx.lineJoin='round';ctx.lineCap=image.imageStrokeStyle==='dotted'?'round':'butt';if(Array.isArray(image.strokeDashArray))ctx.setLineDash(image.strokeDashArray);ctx.stroke();ctx.restore();}function renderWithEffect(image,ctx){var filter=aaOpeningImageEffectFilter(image);if(!filter){originalRender.call(image,ctx);return;}var previous=ctx.filter;ctx.filter=filter;originalRender.call(image,ctx);ctx.filter=previous;}fabric.Image.prototype._render=function(ctx){var radius=Math.max(0,Number(this.borderRadius)||0);var width=Math.max(1,this.width||1);var height=Math.max(1,this.height||1);if(radius){ctx.save();drawPath(ctx,width,height,radius);ctx.clip();renderWithEffect(this,ctx);ctx.restore();}else{renderWithEffect(this,ctx);}drawStroke(ctx,this,width,height,radius);};fabric.Image.prototype.__aaRoundedRendererInstalled=true;}function refreshOpeningImageStyles(c){if(!c||!c.getObjects)return;c.getObjects().forEach(function(o){var list=o&&o.type==='group'&&o.getObjects?o.getObjects():[o];list.forEach(function(item){if(!item||item.type!=='image')return;if(item.borderRadius&&item.clipPath&&(item.clipPath.rx||item.clipPath.ry)){item.clipPath=null;}if(item.aaImageEffectPreset&&Array.isArray(item.filters)){item.filters=[];}item.dirty=true;item.setCoords();});if(o&&o.type==='group'){o.dirty=true;o.setCoords();}});}function getOpeningGuestName(){var params=new URLSearchParams(window.location.search||'');var value=params.get('to')||params.get('tamu')||params.get('invite')||params.get('guest')||'';value=String(value||'').replace(/\\+/g,' ').trim();return value||'Tamu Undangan';}function isOpeningTextObject(o){return o&&['i-text','textbox','text'].indexOf(o.type)!==-1;}function walkOpeningObjects(objects,cb){(objects||[]).forEach(function(o){cb(o);if(o&&typeof o.getObjects==='function')walkOpeningObjects(o.getObjects(),cb);});}function renderOpeningGuestNameTemplate(template,guestName){template=String(template||'Kepada Yth.\\n{{guest_name}}');if(!/\\{\\{\\s*guest_name\\s*\\}\\}/i.test(template)){template=template.replace(/Nama Tamu|Tamu Undangan/gi,'{{guest_name}}');}return template.replace(/\\{\\{\\s*guest_name\\s*\\}\\}/gi,guestName||'Tamu Undangan');}function applyOpeningGuestName(c){if(!c||!c.getObjects)return;var guestName=getOpeningGuestName();walkOpeningObjects(c.getObjects(),function(o){var isGuestName=o&&(o.isGuestName===true||o.customType==='guest_name'||o.dynamicKey==='guest_name');if(!isGuestName)return;var currentText=o.templateText||o.text||'';if(!currentText&&o&&typeof o.getObjects==='function'){var children=o.getObjects();var child=children.find(function(item){return item.name==='guest-name-text';})||children.find(isOpeningTextObject);currentText=child?child.text:'';}var nextText=renderOpeningGuestNameTemplate(currentText,guestName);var target=o;if(o&&typeof o.getObjects==='function'){var objectChildren=o.getObjects();target=objectChildren.find(function(item){return item.name==='guest-name-text';})||objectChildren.find(isOpeningTextObject)||o;}if(target&&isOpeningTextObject(target)&&typeof target.set==='function'){target.set('text',nextText);target.dirty=true;if(typeof target.initDimensions==='function')target.initDimensions();target.setCoords();}if(o&&typeof o.setCoords==='function')o.setCoords();});}function normalizeOpeningFontFamily(family){family=String(family||'Inter').trim().replace(/^['"]|['"]$/g,'');return family||'Inter';}function collectOpeningFontVariants(raw,variants){if(!raw||typeof raw!=='object')return;if(isOpeningTextObject(raw)){addOpeningFontVariant(variants,raw.fontFamily,raw.fontWeight,raw.fontStyle);}var children=Array.isArray(raw.objects)?raw.objects:(Array.isArray(raw._objects)?raw._objects:[]);children.forEach(function(child){collectOpeningFontVariants(child,variants);});}function addOpeningFontVariant(variants,family,weight,style){family=normalizeOpeningFontFamily(family);weight=String(weight||'400').toLowerCase()==='bold'?'700':String(weight||'400');if(!/^[1-9]00$/.test(weight))weight=Number(weight)>=600?'700':'400';style=String(style||'normal').toLowerCase()==='italic'?'italic':'normal';var key=family+'|'+weight+'|'+style;if(!variants.some(function(item){return item.key===key;})){variants.push({family:family,weight:weight,style:style,key:key});}}function waitOpeningFonts(raw){if(!document.fonts||!document.fonts.load)return Promise.resolve();var variants=[];collectOpeningFontVariants(raw,variants);addOpeningFontVariant(variants,'Inter','400','normal');var loads=variants.map(function(v){return document.fonts.load(v.style+' '+v.weight+' 32px "'+v.family+'"').catch(function(){return null;});});var ready=Promise.all(loads).then(function(){return document.fonts.ready||null;}).catch(function(){return null;});return new Promise(function(resolve){var done=false;function finish(){if(done)return;done=true;resolve();}ready.then(function(){setTimeout(finish,120);}).catch(finish);setTimeout(finish,1400);});}function refreshOpeningTextMetrics(c){if(!c||!c.getObjects)return;walkOpeningObjects(c.getObjects(),function(o){if(!isOpeningTextObject(o))return;o.dirty=true;if(typeof o.initDimensions==='function')o.initDimensions();if(typeof o.setCoords==='function')o.setCoords();});}function renderOpening(){if(!openingCanvasEl||!openingData||!window.fabric)return;var art=openingData.artboard||{};var w=Math.max(1,Number(art.width)||1080);var h=Math.max(1,Number(art.height)||1920);installOpeningRoundedImageRenderer();var c=new fabric.StaticCanvas(openingCanvasEl,{width:w,height:h,renderOnAddRemove:false,enableRetinaScaling:true});c.backgroundColor=openingData.background||openingData.backgroundColor||'#0f766e';waitOpeningFonts(openingData).then(function(){c.loadFromJSON(openingData,function(){c.getObjects().forEach(function(o){o.selectable=false;o.evented=false;});refreshOpeningImageStyles(c);applyOpeningGuestName(c);refreshOpeningTextMetrics(c);var target=null;c.getObjects().forEach(function(o){if(!target&&o&&o.customType==='opening-button')target=o;});if(target&&button.classList.contains('aa-opening-custom-hotspot')){var r=target.getBoundingRect(true,true);button.style.left=(r.left/w*100)+'%';button.style.top=(r.top/h*100)+'%';button.style.width=(r.width/w*100)+'%';button.style.height=(r.height/h*100)+'%';}c.requestRenderAll();setTimeout(function(){refreshOpeningTextMetrics(c);c.requestRenderAll();},220);setTimeout(function(){refreshOpeningTextMetrics(c);c.requestRenderAll();},700);});}).catch(function(){c.loadFromJSON(openingData,function(){c.getObjects().forEach(function(o){o.selectable=false;o.evented=false;});refreshOpeningImageStyles(c);applyOpeningGuestName(c);refreshOpeningTextMetrics(c);c.requestRenderAll();});});}if(openingCanvasEl){if(window.fabric)renderOpening();else{var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js';s.onload=renderOpening;document.head.appendChild(s);}}var closed=false;button.addEventListener('click',function(){if(closed)return;closed=true;modal.classList.add('is-leaving');modal.classList.remove('is-visible');window.AdaAcaraPublicInvitationOpened=true;release();setTimeout(function(){modal.remove();},1200);});setTimeout(function(){modal.classList.add('is-visible');},220);})();`;
        }

        function publicCss() {
            return `
.aa-fabric-page {
    min-height: 100vh;
    display: grid;
    margin: 0;
    background: #f1f5f9;
    box-sizing: border-box;
    justify-items: center;
    gap: 0;
}
.aa-fabric-page-section {
    display: grid;
    place-items: center;
    width: 100%;
    min-height: 0;
    box-sizing: border-box;
    padding: 0;
    margin: 0;
}
.aa-fabric-artboard {
    position: relative;
    width: min(100%, var(--aa-artboard-max-width, 520px));
    margin: 0 auto;
    background: var(--aa-page-bg, #ffffff);
    overflow: hidden;
    box-shadow: 0 20px 70px rgba(15, 23, 42, .14);
}
.aa-fabric-artboard.is-rendering::after {
    content: "Memuat undangan...";
    position: absolute;
    inset: 0;
    z-index: 6;
    display: grid;
    place-items: center;
    background: var(--aa-page-bg, #ffffff);
    color: var(--aa-loader-text, #0f172a);
    font: 900 14px Inter, Arial, sans-serif;
    transition: opacity .24s ease, visibility .24s ease;
}
.aa-fabric-artboard .canvas-container,
.aa-fabric-artboard canvas,
.aa-fabric-artboard .upper-canvas,
.aa-fabric-artboard .lower-canvas {
    position: relative;
    z-index: 1;
    display: block;
    width: 100% !important;
    height: 100% !important;
    touch-action: pan-y pinch-zoom !important;
}
.aa-fabric-bg-gif-layer {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}
.aa-fabric-bg-gif-layer img {
    position: absolute;
    display: block;
    object-fit: fill;
    transform-origin: center center;
    pointer-events: none;
}
.aa-fabric-gif-under-layer {
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}
.aa-fabric-gif-under-layer img {
    position: absolute;
    display: block;
    object-fit: fill;
    transform-origin: center center;
    pointer-events: none;
}
.aa-fabric-click-layer {
    position: absolute;
    inset: 0;
    z-index: 3;
    pointer-events: none;
}
.aa-fabric-gif-layer {
    position: absolute;
    inset: 0;
    z-index: 2;
    pointer-events: none;
    overflow: hidden;
}
.aa-fabric-gif-layer img {
    position: absolute;
    display: block;
    object-fit: fill;
    transform-origin: center center;
    pointer-events: none;
}
.aa-fabric-gif-crop-frame {
    position: absolute;
    display: block;
    overflow: hidden;
    transform-origin: center center;
    pointer-events: none;
}
.aa-fabric-hotspot {
    position: absolute;
    display: block;
    border: 0;
    background: transparent;
    color: transparent;
    font-size: 0;
    line-height: 0;
    padding: 0;
    cursor: pointer;
    pointer-events: auto;
    touch-action: manipulation pan-y;
}
.aa-fabric-guestbook-layer {
    position: absolute;
    inset: 0;
    z-index: 4;
    pointer-events: none;
}
.aa-fabric-guestbook-control {
    position: absolute;
    box-sizing: border-box;
    pointer-events: auto;
    font-family: Inter, Arial, sans-serif;
    line-height: 1.15;
}
    .aa-fabric-guestbook-control input,
    .aa-fabric-guestbook-control select,
    .aa-fabric-guestbook-control textarea,
    .aa-fabric-guestbook-control button {
        width: 100%;
        height: 100%;
        min-height: 0;
        box-sizing: border-box;
        border: var(--aa-field-border-width, 1px) solid var(--aa-field-border-color, #cbd5e1);
        border-radius: inherit;
        background: inherit;
        color: inherit;
        font: inherit;
        text-align: inherit;
        line-height: var(--aa-field-line-height, 1.25);
        padding: var(--aa-field-padding-y, 10px) var(--aa-field-padding-x, 14px);
        margin: 0;
        outline: none;
        box-shadow: none;
        appearance: none;
        -webkit-appearance: none;
    }
.aa-fabric-guestbook-control input::placeholder,
.aa-fabric-guestbook-control textarea::placeholder {
    color: currentColor;
    opacity: .72;
}
.aa-fabric-guestbook-control textarea {
    resize: none;
}
.aa-fabric-guestbook-control button {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: 800;
}
.aa-fabric-guestbook-control[data-guestbook-role="guest-name-input"] input,
.aa-fabric-guestbook-control[data-guestbook-role="guest-attendance-select"] select,
.aa-fabric-guestbook-control[data-guestbook-role="guest-sticker-picker"] button,
.aa-fabric-guestbook-control[data-guestbook-role="guest-submit-button"] button {
    overflow: hidden;
    white-space: nowrap;
}
.aa-fabric-sticker-popover {
    position: absolute;
    left: 0;
    bottom: calc(100% + 8px);
    z-index: 5;
    display: none;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: 8px;
    width: min(380px, 88vw);
    max-width: calc(100vw - 28px);
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: rgba(255, 255, 255, .96);
    padding: 10px;
    box-shadow: 0 18px 48px rgba(15, 23, 42, .18);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.aa-fabric-sticker-popover.is-open {
    display: grid;
}
.aa-fabric-sticker-track {
    display: flex;
    min-width: 0;
    gap: 8px;
    overflow-x: auto;
    overscroll-behavior-x: contain;
    scroll-behavior: smooth;
    scroll-snap-type: x proximity;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
}
.aa-fabric-sticker-track::-webkit-scrollbar {
    display: none;
}
.aa-fabric-sticker-choice {
    display: inline-grid;
    width: 68px;
    height: 68px;
    flex: 0 0 68px;
    place-items: center;
    border: 1px solid rgba(226, 232, 240, .88);
    border-radius: 16px;
    background: rgba(255, 255, 255, .48);
    padding: 6px;
    cursor: pointer;
    scroll-snap-align: center;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .72);
}
.aa-fabric-sticker-choice.is-selected {
    border-color: #0f766e;
    background: rgba(204, 251, 241, .72);
    box-shadow: inset 0 0 0 1px rgba(15, 118, 110, .24), 0 10px 20px rgba(15, 118, 110, .12);
}
.aa-fabric-sticker-choice img {
    display: block;
    width: 54px;
    height: 54px;
    object-fit: contain;
}
.aa-fabric-sticker-nav {
    display: inline-grid;
    width: 34px;
    height: 52px;
    flex: 0 0 34px;
    place-items: center;
    border: 1px solid rgba(226, 232, 240, .86);
    border-radius: 999px;
    background: rgba(255, 255, 255, .72);
    color: #0f766e;
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
}
.aa-fabric-sticker-nav svg {
    width: 17px;
    height: 17px;
}
.aa-fabric-selected-sticker {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    display: none;
    align-items: center;
    gap: 7px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .96);
    padding: 5px 8px;
    color: inherit;
    font: 700 11px Inter, Arial, sans-serif;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .16);
}
.aa-fabric-selected-sticker.is-visible {
    display: inline-flex;
}
.aa-fabric-selected-sticker img {
    width: 28px;
    height: 28px;
    object-fit: contain;
}
.aa-fabric-selected-sticker button {
    width: auto;
    height: auto;
    min-height: 0;
    border: 0;
    border-radius: 999px;
    background: #fee2e2;
    color: #be123c;
    padding: 5px 8px;
    font: 800 10px Inter, Arial, sans-serif;
}
.aa-fabric-comment-list {
    width: 100%;
    height: 100%;
    overflow-y: auto;
    display: grid;
    gap: 8px;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: inherit;
    background: inherit;
    color: inherit;
}
.aa-fabric-comment-card,
.aa-fabric-comment-empty {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: rgba(255, 255, 255, .82);
    color: inherit;
    padding: 10px;
}
.aa-fabric-comment-card strong,
.aa-fabric-comment-card p,
.aa-fabric-comment-empty {
    color: inherit;
}
.aa-fabric-interactive-layer {
    position: absolute;
    inset: 0;
    z-index: 5;
    pointer-events: none;
}
.aa-fabric-interactive-control {
    position: absolute;
    box-sizing: border-box;
    pointer-events: auto;
    font-family: Inter, Arial, sans-serif;
    --aa-countdown-gap: 8px;
}
.aa-fabric-overlay-animated {
    --aa-overlay-base-transform: rotate(0deg);
    --aa-overlay-animation-duration: 900ms;
    --aa-overlay-animation-delay: 0ms;
    --aa-overlay-final-opacity: 1;
    animation-duration: var(--aa-overlay-animation-duration);
    animation-delay: var(--aa-overlay-animation-delay);
    animation-fill-mode: both;
    animation-timing-function: cubic-bezier(.22, 1, .36, 1);
    transform-origin: center center;
}
.aa-fabric-overlay-animation-waiting {
    animation-play-state: paused;
}
.aa-overlay-animation-fade-in { animation-name: aaOverlayFadeIn; }
.aa-overlay-animation-rise,
.aa-overlay-animation-fade-up { animation-name: aaOverlayFadeUp; }
.aa-overlay-animation-fade-down { animation-name: aaOverlayFadeDown; }
.aa-overlay-animation-fade-left { animation-name: aaOverlayFadeLeft; }
.aa-overlay-animation-fade-right { animation-name: aaOverlayFadeRight; }
.aa-overlay-animation-slide-up { animation-name: aaOverlaySlideUp; }
.aa-overlay-animation-slide-down { animation-name: aaOverlaySlideDown; }
.aa-overlay-animation-slide-left { animation-name: aaOverlaySlideLeft; }
.aa-overlay-animation-slide-right { animation-name: aaOverlaySlideRight; }
.aa-overlay-animation-zoom-in { animation-name: aaOverlayZoomIn; }
.aa-overlay-animation-zoom-out { animation-name: aaOverlayZoomOut; }
.aa-overlay-animation-flip-in { animation-name: aaOverlayFlipIn; }
.aa-overlay-animation-bounce { animation-name: aaOverlayBounce; }
.aa-overlay-animation-pulse { animation-name: aaOverlayPulse; }
.aa-overlay-animation-swing { animation-name: aaOverlaySwing; }
.aa-overlay-animation-spin { animation-name: aaOverlaySpin; }
.aa-overlay-animation-float-loop,
.aa-overlay-animation-sway-loop,
.aa-overlay-animation-pulse-loop,
.aa-overlay-animation-spin-loop,
.aa-overlay-animation-heartbeat-loop,
.aa-overlay-animation-drift-loop {
    animation-duration: var(--aa-overlay-loop-duration, var(--aa-overlay-animation-duration));
    animation-iteration-count: infinite;
    animation-timing-function: ease-in-out;
}
.aa-overlay-animation-float-loop { --aa-overlay-loop-duration: 2600ms; animation-name: aaOverlayFloatLoop; }
.aa-overlay-animation-sway-loop { --aa-overlay-loop-duration: 1900ms; animation-name: aaOverlaySwayLoop; }
.aa-overlay-animation-pulse-loop { --aa-overlay-loop-duration: 1560ms; animation-name: aaOverlayPulseLoop; }
.aa-overlay-animation-spin-loop { --aa-overlay-loop-duration: 4200ms; animation-name: aaOverlaySpinLoop; }
.aa-overlay-animation-heartbeat-loop { --aa-overlay-loop-duration: 720ms; animation-name: aaOverlayHeartbeatLoop; }
.aa-overlay-animation-drift-loop { --aa-overlay-loop-duration: 2400ms; animation-name: aaOverlayDriftLoop; }
@keyframes aaOverlayFadeIn { from { opacity: 0; transform: var(--aa-overlay-base-transform); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform); } }
@keyframes aaOverlayFadeUp { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateY(36px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlayFadeDown { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateY(-36px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlayFadeLeft { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateX(42px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlayFadeRight { from { opacity: 0; transform: var(--aa-overlay-base-transform) translateX(-42px); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlaySlideUp { from { transform: var(--aa-overlay-base-transform) translateY(58px); } to { transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlaySlideDown { from { transform: var(--aa-overlay-base-transform) translateY(-58px); } to { transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlaySlideLeft { from { transform: var(--aa-overlay-base-transform) translateX(64px); } to { transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlaySlideRight { from { transform: var(--aa-overlay-base-transform) translateX(-64px); } to { transform: var(--aa-overlay-base-transform) translateX(0); } }
@keyframes aaOverlayZoomIn { from { opacity: 0; transform: var(--aa-overlay-base-transform) scale(.72); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) scale(1); } }
@keyframes aaOverlayZoomOut { from { opacity: 0; transform: var(--aa-overlay-base-transform) scale(1.28); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) scale(1); } }
@keyframes aaOverlayFlipIn { from { opacity: 0; transform: var(--aa-overlay-base-transform) rotateY(82deg); } to { opacity: var(--aa-overlay-final-opacity, 1); transform: var(--aa-overlay-base-transform) rotateY(0); } }
@keyframes aaOverlayBounce { 0% { transform: var(--aa-overlay-base-transform) translateY(-42px); } 65% { transform: var(--aa-overlay-base-transform) translateY(8px); } 100% { transform: var(--aa-overlay-base-transform) translateY(0); } }
@keyframes aaOverlayPulse { 0%, 100% { transform: var(--aa-overlay-base-transform) scale(1); } 50% { transform: var(--aa-overlay-base-transform) scale(1.12); } }
@keyframes aaOverlaySwing { 0% { transform: var(--aa-overlay-base-transform) rotate(-10deg); } 50% { transform: var(--aa-overlay-base-transform) rotate(10deg); } 100% { transform: var(--aa-overlay-base-transform) rotate(0); } }
@keyframes aaOverlaySpin { from { transform: var(--aa-overlay-base-transform) rotate(0); } to { transform: var(--aa-overlay-base-transform) rotate(360deg); } }
@keyframes aaOverlayFloatLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) translateY(0); } 50% { transform: var(--aa-overlay-base-transform) translateY(-18px); } }
@keyframes aaOverlaySwayLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) rotate(-6deg); } 50% { transform: var(--aa-overlay-base-transform) rotate(6deg); } }
@keyframes aaOverlayPulseLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) scale(1); } 50% { transform: var(--aa-overlay-base-transform) scale(1.08); } }
@keyframes aaOverlaySpinLoop { from { transform: var(--aa-overlay-base-transform) rotate(0); } to { transform: var(--aa-overlay-base-transform) rotate(360deg); } }
@keyframes aaOverlayHeartbeatLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) scale(1); } 35% { transform: var(--aa-overlay-base-transform) scale(1.16); } 60% { transform: var(--aa-overlay-base-transform) scale(.98); } }
@keyframes aaOverlayDriftLoop { 0%, 100% { transform: var(--aa-overlay-base-transform) translateX(0); } 50% { transform: var(--aa-overlay-base-transform) translateX(18px); } }
.aa-fabric-music-button,
.aa-fabric-scroll-button {
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: var(--aa-control-border-width, 1px) solid #0f766e00;
    border-radius: inherit;
    background: var(--aa-control-bg, #ffffff);
    color: var(--aa-control-color, #0f172a);
    font: inherit;
    font-weight: 900;
    cursor: pointer;
    line-height: 1;
    overflow: hidden;
    -webkit-tap-highlight-color: transparent;
}
.aa-fabric-music-button {
    flex-direction: column;
}
.aa-fabric-music-icon {
    display: block;
    font-size: 25px;
    line-height: 1;
    top: -1px;
    position: relative;
    left: -1px;
}
.aa-fabric-music-icon svg {
    display: block;
    width: 1em;
    height: 1em;
}
.aa-fabric-music-label {
    display: block;
    font-size: .62em;
    line-height: 1.1;
}
.aa-fabric-social-box,
.aa-fabric-story-box {
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    overflow: auto;
    border: 1px solid rgba(15, 118, 110, .16);
    border-radius: inherit;
    background: var(--aa-control-bg, rgba(255, 255, 255, .92));
    color: var(--aa-control-color, #0f172a);
    padding: clamp(10px, 3vw, 18px);
    font-family: Inter, ui-sans-serif, system-ui, sans-serif;
}
.aa-fabric-social-box strong,
.aa-fabric-story-box > strong {
    display: block;
    margin-bottom: 10px;
    font-size: clamp(14px, 4vw, 22px);
    font-weight: 950;
    line-height: 1.15;
    text-align: center;
}
.aa-fabric-social-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}
.aa-fabric-social-link {
    display: inline-grid;
    min-width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 999px;
    background: #0f766e;
    color: #ffffff;
    padding: 0 10px;
    font-size: 12px;
    font-weight: 950;
    text-decoration: none;
}
.aa-social-instagram { background: #e1306c; }
.aa-social-tiktok { background: #111827; }
.aa-social-threads { background: #000000; }
.aa-social-x { background: #0f172a; }
.aa-social-facebook { background: #1877f2; }
.aa-social-youtube { background: #ff0000; }
.aa-fabric-social-empty,
.aa-fabric-story-empty {
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    text-align: center;
}
.aa-fabric-story-list {
    display: grid;
    gap: 10px;
}
.aa-fabric-story-item {
    display: grid;
    gap: 4px;
    border-left: 3px solid #0f766e;
    border-radius: 12px;
    background: rgba(255, 255, 255, .72);
    padding: 9px 10px;
}
.aa-fabric-story-item small {
    color: #0f766e;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.aa-fabric-story-item b {
    font-size: 13px;
    font-weight: 950;
}
.aa-fabric-story-item p {
    margin: 0;
    color: #475569;
    font-size: 11px;
    font-weight: 650;
    line-height: 1.45;
}
.aa-fabric-countdown {
    display: grid;
    width: 100%;
    height: 100%;
    grid-template-columns: repeat(var(--aa-countdown-columns, 4), 1fr);
    gap: var(--aa-countdown-gap, 8px);
    align-items: center;
    border: 0;
    border-radius: inherit;
    background: transparent;
    color: var(--aa-control-color, #0f172a);
    padding: 0;
}
.aa-fabric-countdown span {
    position: relative;
    display: grid;
    min-width: 0;
    min-height: 0;
    height: 100%;
    border: var(--aa-control-border-width, 1px) solid #0f766e00;
    border-radius: var(--aa-countdown-card-radius, inherit);
    background: var(--aa-control-bg, #ffffff);
    padding: 0;
    text-align: center;
    font-weight: 900;
}
.aa-fabric-countdown strong,
.aa-fabric-countdown small {
    position: absolute;
    left: 50%;
    width: 100%;
    transform: translate(-50%, -50%);
    line-height: 1;
}
.aa-fabric-countdown strong {
    top: 38%;
}
.aa-fabric-countdown small {
    top: 76%;
    font-size: .36em;
    opacity: .72;
    text-transform: uppercase;
}
.aa-scroll-step-page {
    min-height: 100svh;
    scroll-snap-align: start;
}
.aa-fabric-gallery {
    display: grid;
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.aa-fabric-gallery button {
    overflow: hidden;
    border: 0;
    background: #e2e8f0;
    padding: 0;
    cursor: pointer;
}
.aa-fabric-gallery img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.aa-fabric-youtube-frame,
.aa-fabric-youtube-placeholder {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
    border-radius: inherit;
    overflow: hidden;
    background: #111827;
}
.aa-fabric-youtube-placeholder {
    display: grid;
    place-items: center;
    color: #ffffff;
    font: 900 14px Inter, Arial, sans-serif;
    text-align: center;
    padding: 14px;
}
.aa-fabric-lightbox {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    place-items: center;
    background: rgba(15, 23, 42, .82);
    padding: 18px;
}
.aa-fabric-lightbox.is-open {
    display: grid;
}
.aa-fabric-lightbox img {
    display: block;
    max-width: min(94vw, 980px);
    max-height: 88vh;
    border-radius: 18px;
    object-fit: contain;
    background: #ffffff;
}
.aa-fabric-lightbox button {
    position: fixed;
    top: 18px;
    right: 18px;
    border: 0;
    border-radius: 999px;
    background: #ffffff;
    color: #0f172a;
    padding: 10px 14px;
    font: 900 14px Inter, Arial, sans-serif;
    cursor: pointer;
}
.aa-fabric-watermark {
    display: flex;
    align-items: center;
    justify-content: center;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
    padding: 18px 16px;
    font: 800 12px Inter, Arial, sans-serif;
}
.aa-fabric-watermark img {
    width: 168px;
    max-height: 42px;
    object-fit: cover;
    filter: invert(0.5);
}
@media (max-width: 420px) {
    .aa-fabric-watermark {
        padding: 16px 14px;
    }
}
`;
        }

        function guestbookPreviewCss() {
            return `
.aa-guestbook {
    padding: 72px 20px;
    background: var(--aa-gb-bg, #f8fafc);
    color: var(--aa-gb-text, #101828);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}
.aa-guestbook-wrap {
    max-width: 920px;
    margin: 0 auto;
}
.aa-guestbook-head {
    margin-bottom: 32px;
    text-align: center;
}
.aa-guestbook-head p {
    margin: 0 0 10px;
    color: var(--aa-gb-accent, #0f766e);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
}
.aa-guestbook-head h2 {
    margin: 0;
    font-size: clamp(30px, 5vw, 46px);
    line-height: 1.1;
}
.aa-guestbook-head .aa-guestbook-subtitle {
    max-width: 580px;
    margin: 14px auto 0;
    color: var(--aa-gb-muted, #667085);
    line-height: 1.7;
    text-transform: none;
    letter-spacing: 0;
    font-size: 16px;
    font-weight: 500;
}
.aa-guestbook-form {
    display: grid;
    gap: 14px;
    border: 1px solid #e4e7ec;
    border-radius: var(--aa-gb-radius, 22px);
    background: var(--aa-gb-card, #ffffff);
    padding: 22px;
    box-shadow: 0 18px 50px rgba(16, 24, 40, .06);
}
.aa-guestbook-label {
    display: grid;
    gap: 8px;
    font-weight: 700;
}
.aa-guestbook-input,
.aa-guestbook-select,
.aa-guestbook-textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
    padding: 12px 14px;
    background: #ffffff;
    color: #101828;
    font: inherit;
}
.aa-guestbook-textarea {
    min-height: 116px;
    resize: vertical;
}
.aa-sticker-button,
.aa-guestbook-submit {
    border: 0;
    border-radius: 14px;
    font: inherit;
    font-weight: 800;
}
.aa-sticker-button {
    background: #ecfdf5;
    color: var(--aa-gb-accent, #0f766e);
    padding: 10px 14px;
}
.aa-guestbook-submit {
    background: var(--aa-gb-accent, #0f766e);
    color: #ffffff;
    padding: 14px 18px;
}
.aa-comment-list {
    display: grid;
    gap: 12px;
    max-height: var(--aa-gb-max-height, 380px);
    overflow-y: auto;
    margin-top: 24px;
    padding-right: 6px;
}
.aa-comment-card,
.aa-comment-empty {
    border: 1px solid #e4e7ec;
    border-radius: 18px;
    background: var(--aa-gb-card, #ffffff);
    padding: 18px;
}
.aa-comment-empty {
    border-style: dashed;
    color: var(--aa-gb-muted, #667085);
    text-align: center;
}
`;
        }

        function guestbookPreviewHtml() {
            const guestbook = normalizeGuestbookConfig(state.guestbook);
            if (!guestbook.enabled) return '';
            const style = [
                `--aa-gb-bg:${guestbook.backgroundColor}`,
                `--aa-gb-card:${guestbook.cardColor}`,
                `--aa-gb-text:${guestbook.textColor}`,
                `--aa-gb-muted:${guestbook.mutedColor}`,
                `--aa-gb-accent:${guestbook.accentColor}`,
                `--aa-gb-radius:${guestbook.borderRadius}px`,
                `--aa-gb-max-height:${guestbook.maxHeight}px`,
            ].join(';');
            const attendanceField = guestbook.showAttendance ? `
                <label class="aa-guestbook-label">Kehadiran
                    <select class="aa-guestbook-select" disabled>
                        <option>Hadir</option>
                    </select>
                </label>` : '';
            const stickerField = guestbook.showSticker ? `
                <div class="aa-sticker-row">
                    <button class="aa-sticker-button" type="button">Pilih stiker GIF</button>
                </div>` : '';
            return `<section id="guestbook" class="aa-guestbook" style="${style}">
                <div class="aa-guestbook-wrap">
                    <div class="aa-guestbook-head">
                        <p>${escapeHtml(guestbook.eyebrow)}</p>
                        <h2>${escapeHtml(guestbook.title)}</h2>
                        <p class="aa-guestbook-subtitle">${escapeHtml(guestbook.subtitle)}</p>
                    </div>
                    <form class="aa-guestbook-form">
                        <label class="aa-guestbook-label">Nama
                            <input class="aa-guestbook-input" type="text" value="Tamu Undangan" disabled>
                        </label>
                        ${attendanceField}
                        <label class="aa-guestbook-label">Komentar / ucapan
                            <textarea class="aa-guestbook-textarea" disabled>Selamat dan sukses untuk acaranya.</textarea>
                        </label>
                        ${stickerField}
                        <button class="aa-guestbook-submit" type="button">${escapeHtml(guestbook.buttonText)}</button>
                    </form>
                    <div class="aa-comment-list">
                        <article class="aa-comment-card">
                            <div class="aa-comment-meta">
                                <div><h3>Tamu Undangan</h3><p>Hadir</p></div>
                                <time>Preview</time>
                            </div>
                            <div class="aa-comment-body"><p>Ini contoh tampilan ucapan di guestbook.</p></div>
                        </article>
                    </div>
                </div>
            </section>`;
        }

        function publicJs() {
            const data = getCanvasData();
            const safeDataJson = JSON.stringify(data)
                .replace(/<\//g, '<\\/')
                .replace(/\u2028/g, '\\u2028')
                .replace(/\u2029/g, '\\u2029');
            return `(function () {
    var fabricData = ${safeDataJson};
    function loadFabric(callback) {
        if (window.fabric) {
            callback();
            return;
        }
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js';
        script.onload = callback;
        document.head.appendChild(script);
    }
    function renderFabric() {
    var sourcePages = Array.isArray(fabricData.pages) && fabricData.pages.length ? fabricData.pages : [fabricData];
    var pages = sourcePages.filter(function (pageData) {
        return pageData && pageData.hidden !== true;
    });

    if (!pages.length && sourcePages.length) {
        pages = [sourcePages[fabricData.activePageIndex || 0] || sourcePages[0]];
    }

    function injectPreviewGoogleFonts() {
        try {
            var href = previewGoogleFontUrl(fabricData);

            if (!href || href.indexOf('family=') === -1) {
                console.warn('[AA PUBLIC PREVIEW FONT] Google font URL kosong:', href);
                return Promise.resolve();
            }

            var old = document.getElementById('aa-public-preview-google-fonts');
            if (old) old.remove();

            var preconnect1 = document.createElement('link');
            preconnect1.rel = 'preconnect';
            preconnect1.href = 'https://fonts.googleapis.com';

            var preconnect2 = document.createElement('link');
            preconnect2.rel = 'preconnect';
            preconnect2.href = 'https://fonts.gstatic.com';
            preconnect2.crossOrigin = 'anonymous';

            var link = document.createElement('link');
            link.id = 'aa-public-preview-google-fonts';
            link.rel = 'stylesheet';
            link.href = href;

            document.head.appendChild(preconnect1);
            document.head.appendChild(preconnect2);
            document.head.appendChild(link);

            console.log('[AA PUBLIC PREVIEW FONT] inject:', href);

            return new Promise(function (resolve) {
                link.onload = function () {
                    if (document.fonts && document.fonts.ready) {
                        document.fonts.ready.then(function () {
                            setTimeout(resolve, 150);
                        }).catch(resolve);
                    } else {
                        setTimeout(resolve, 700);
                    }
                };

                link.onerror = function () {
                    console.warn('[AA PUBLIC PREVIEW FONT] gagal load:', href);
                    resolve();
                };

                setTimeout(resolve, 1600);
            });
        } catch (error) {
            console.warn('[AA PUBLIC PREVIEW FONT] inject error:', error);
            return Promise.resolve();
        }
    }

    var startRender = function () {
        lazyRenderPages(pages);
    };

    injectPreviewGoogleFonts().then(function () {
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(startRender).catch(startRender);
            return;
        }

        startRender();
    });
}
    function sanitizeFabricObject(object) {
        if (!object || typeof object !== 'object') return object;

        if (['i-text', 'textbox', 'text'].indexOf(object.type) !== -1) {
            delete object.clipPath;
        }

        Object.keys(object).forEach(function (key) {
            if (key === 'textBaseline' && object[key] === 'alphabetical') {
                object[key] = 'alphabetic';
                return;
            }

            if (object[key] && typeof object[key] === 'object') {
                sanitizeFabricObject(object[key]);
            }
        });

        if (Array.isArray(object)) {
            object.forEach(sanitizeFabricObject);
        }

        return object;
    }

    function aaPublicMaterialSpec(preset) {
        var specs = {
            gold: { type: 'foil', fallback: '#d4af37', colors: ['#fff7bf', '#d4af37', '#8f6b12', '#f8e18a'] },
            copper: { type: 'foil', fallback: '#c8753f', colors: ['#ffd7b0', '#c8753f', '#7c341c', '#f5a66d'] },
            blue: { type: 'foil', fallback: '#4da3c7', colors: ['#c8f2ff', '#4da3c7', '#1e5571', '#9bd8ef'] },
            pearl: { type: 'foil', fallback: '#dbeafe', colors: ['#f8fafc', '#dbeafe', '#f5d0fe', '#ccfbf1'] },
            red: { type: 'foil', fallback: '#dc2626', colors: ['#fecaca', '#dc2626', '#7f1d1d', '#f87171'] },
            rose: { type: 'foil', fallback: '#be6b75', colors: ['#ffe4e6', '#be6b75', '#7f3540', '#f3a6ad'] },
            silver: { type: 'foil', fallback: '#cbd5e1', colors: ['#ffffff', '#cbd5e1', '#64748b', '#e2e8f0'] },
            'gold-glitter': { type: 'glitter', fallback: '#d4af37', colors: ['#facc15', '#8f6b12', '#fff7ad'] },
            'silver-glitter': { type: 'glitter', fallback: '#cbd5e1', colors: ['#f8fafc', '#64748b', '#ffffff'] },
            'black-glitter': { type: 'glitter', fallback: '#111827', colors: ['#334155', '#020617', '#f8fafc'] },
            'aqua-glitter': { type: 'glitter', fallback: '#14b8a6', colors: ['#22d3ee', '#0f766e', '#cffafe'] },
            'emerald-glitter': { type: 'glitter', fallback: '#047857', colors: ['#10b981', '#064e3b', '#bbf7d0'] },
            'rose-glitter': { type: 'glitter', fallback: '#be185d', colors: ['#f9a8d4', '#be185d', '#fff1f2'] },
            'pink-glitter': { type: 'glitter', fallback: '#db2777', colors: ['#f472b6', '#db2777', '#fce7f3'] },
            'purple-glitter': { type: 'glitter', fallback: '#7e22ce', colors: ['#c084fc', '#7e22ce', '#f3e8ff'] }
        };
        return specs[preset] || null;
    }

    function aaPublicFoilFill(object, spec) {
        var width = Math.max(1, Number(object && object.width) || 1);
        var height = Math.max(1, Number(object && object.height) || 1);
        return new fabric.Gradient({
            type: 'linear',
            gradientUnits: 'pixels',
            coords: { x1: -width / 2, y1: height / 2, x2: width / 2, y2: -height / 2 },
            colorStops: [
                { offset: 0, color: spec.colors[2] },
                { offset: .12, color: spec.colors[1] },
                { offset: .22, color: spec.colors[0] },
                { offset: .34, color: spec.colors[3] || spec.colors[0] },
                { offset: .46, color: spec.colors[1] },
                { offset: .56, color: spec.colors[2] },
                { offset: .68, color: spec.colors[0] },
                { offset: .82, color: spec.colors[1] },
                { offset: 1, color: spec.colors[3] || spec.colors[0] }
            ]
        });
    }

    function aaPublicIsMaterialTextObject(object) {
        return object && ['i-text', 'textbox', 'text'].indexOf(String(object.type || '')) !== -1;
    }

    function aaPublicMaterialPatternSize(object) {
        var isText = aaPublicIsMaterialTextObject(object);
        var padding = isText ? Math.max(24, Math.round((Number(object && object.fontSize) || 32) * 0.55)) : 0;
        var width = Math.abs(Number(object && object.width) || 0) || 144;
        var height = Math.abs(Number(object && object.height) || 0) || 144;
        return {
            width: Math.max(144, Math.min(760, Math.ceil(width + padding * 2))),
            height: Math.max(144, Math.min(760, Math.ceil(height + padding * 2))),
            padding: padding
        };
    }

    function aaPublicGlitterFill(spec, object) {
        var patternSize = aaPublicMaterialPatternSize(object);
        var sourceWidth = patternSize.width;
        var sourceHeight = patternSize.height;
        var canvas = document.createElement('canvas');
        canvas.width = sourceWidth;
        canvas.height = sourceHeight;
        var ctx = canvas.getContext('2d');
        if (!ctx) return spec.fallback;
        var gradient = ctx.createLinearGradient(0, 0, sourceWidth, sourceHeight);
        gradient.addColorStop(0, spec.colors[0]);
        gradient.addColorStop(.52, spec.fallback || spec.colors[0]);
        gradient.addColorStop(1, spec.colors[1]);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, sourceWidth, sourceHeight);
        var areaScale = Math.max(1, (sourceWidth * sourceHeight) / (144 * 144));
        var particleCount = Math.min(9000, Math.round(1150 * areaScale));
        for (var j = 0; j < particleCount; j += 1) {
            var gx = (j * 29 + (j % 7) * 17 + (j % 11) * 5) % sourceWidth;
            var gy = (j * 47 + (j % 5) * 19 + (j % 13) * 7) % sourceHeight;
            var gr = .35 + ((j * 19) % 18) / 18;
            ctx.beginPath();
            ctx.fillStyle = j % 9 === 0 ? spec.colors[2] : (j % 4 === 0 ? 'rgba(255,255,255,.9)' : (j % 3 === 0 ? 'rgba(255,255,255,.58)' : 'rgba(0,0,0,.18)'));
            ctx.arc(gx, gy, gr, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalCompositeOperation = 'screen';
        var sparkleCount = Math.min(1600, Math.round(210 * areaScale));
        for (var k = 0; k < sparkleCount; k += 1) {
            var sx = (k * 41 + (k % 9) * 13) % sourceWidth;
            var sy = (k * 23 + (k % 7) * 11) % sourceHeight;
            ctx.fillStyle = k % 2 === 0 ? 'rgba(255,255,255,.86)' : 'rgba(255,255,255,.52)';
            ctx.fillRect(sx, sy, 1.35, 1.35);
        }
        ctx.globalCompositeOperation = 'source-over';
        return new fabric.Pattern({
            source: canvas,
            repeat: 'no-repeat',
            offsetX: -patternSize.padding,
            offsetY: -patternSize.padding
        });
    }

    function aaRestorePublicMaterials(canvas) {
        if (!canvas || !canvas.getObjects || !window.fabric) return;
        function restore(object) {
            if (!object) return;
            if (typeof object.getObjects === 'function') {
                object.getObjects().forEach(restore);
            }
            var preset = String(object.aaMaterialPreset || '');
            if (!preset || object.type === 'image') return;
            var spec = aaPublicMaterialSpec(preset);
            if (!spec) return;
            object.set('fill', spec.type === 'glitter' ? aaPublicGlitterFill(spec, object) : aaPublicFoilFill(object, spec));
            object.aaMaterialType = spec.type;
            object.aaMaterialFallback = spec.fallback;
            if (aaPublicIsMaterialTextObject(object)) {
                object.objectCaching = false;
                object.noScaleCache = true;
            }
            object.dirty = true;
            if (!aaPublicIsMaterialTextObject(object) && typeof object.initDimensions === 'function') object.initDimensions();
            if (typeof object.setCoords === 'function') object.setCoords();
        }
        canvas.getObjects().forEach(restore);
        canvas.requestRenderAll();
    }

    function flattenActiveSelectionObject(object) {
        var children = Array.isArray(object && object.objects) ? object.objects : [];
        var left = Number(object.left) || 0;
        var top = Number(object.top) || 0;
        var width = Number(object.width) || 0;
        var height = Number(object.height) || 0;
        var centerX = left + (object.originX === 'center' ? 0 : width / 2);
        var centerY = top + (object.originY === 'center' ? 0 : height / 2);
        var scaleX = Number(object.scaleX) || 1;
        var scaleY = Number(object.scaleY) || 1;
        var angle = Number(object.angle) || 0;

        return children.map(function (child) {
            if (!child || typeof child !== 'object') return child;
            child.left = centerX + (Number(child.left) || 0) * scaleX;
            child.top = centerY + (Number(child.top) || 0) * scaleY;
            child.scaleX = (Number(child.scaleX) || 1) * scaleX;
            child.scaleY = (Number(child.scaleY) || 1) * scaleY;
            child.angle = (Number(child.angle) || 0) + angle;
            return child;
        });
    }
    function flattenFabricObjects(objects) {
        var flattened = [];
        (objects || []).forEach(function (object) {
            if (!object || typeof object !== 'object') return;
            if (object.type === 'activeSelection') {
                flattened = flattened.concat(flattenFabricObjects(flattenActiveSelectionObject(object)));
                return;
            }
            if (Array.isArray(object.objects)) {
                object.objects = flattenFabricObjects(object.objects);
            }
            flattened.push(object);
        });
        return flattened;
    }
    function sanitizePageData(pageData) {
        if (!pageData || typeof pageData !== 'object') return pageData;
        if (Array.isArray(pageData.objects)) {
            pageData.objects = flattenFabricObjects(pageData.objects);
            pageData.objects = pageData.objects.filter(function (object) {
                return object && object.customType !== 'crop-helper' && object.excludeFromExport !== true;
            });
            pageData.objects.forEach(sanitizeFabricObject);
        }
        return pageData;
    }
    function isTextObject(object) {
        return object && ['i-text', 'textbox', 'text'].indexOf(object.type) !== -1;
    }
    function isAnimatedTextLayerBlocker(object) {
        return isTextObject(object) &&
            object.visible !== false &&
            object.__aaSkipObject !== true &&
            aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled;
    }
    function isAnimatedLayerBlocker(object) {
        if (!object || object.visible === false || object.__aaSkipObject === true) return false;
        if (object.customType === 'background' || object.excludeFromAnimation === true) return false;
        if (isAnimatedTextLayerBlocker(object)) return true;
        var animationName = getObjectAnimationName(object);
        if (animationName && animationName !== 'none') return true;
        var children = typeof object.getObjects === 'function'
            ? object.getObjects()
            : (Array.isArray(object.objects) ? object.objects : []);
        for (var i = 0; i < children.length; i += 1) {
            if (isAnimatedLayerBlocker(children[i])) return true;
        }
        return false;
    }
    function walkObjects(objects, callback) {
        (objects || []).forEach(function (object) {
            callback(object);
            if (object && typeof object.getObjects === 'function') {
                walkObjects(object.getObjects(), callback);
            }
        });
    }
    function normalizeFontFamily(fontFamily) {
    return String(fontFamily || 'Inter')
        .replace(/['"]/g, '') 
        .trim() || 'Inter';
}
    var previewGoogleFontWeights = {
        'Aboreto': '400',
        'Abril Fatface': '400',
        'Adamina': '400',
        'Alex Brush': '400',
        'Alfa Slab One': '400',
        'Allura': '400',
        'Amarante': '400',
        'Amiri': '400;700',
        'Anton': '400',
        'Archivo': '400;500;700',
        'Archivo Black': '400',
        'Arizonia': '400',
        'Assistant': '200;300;400;500;600;700;800',
        'Barlow': '100;200;300;400;500;600;700;800;900',
        'Bebas Neue': '400',
        'Bellefair': '400',
        'Bitter': '100;200;300;400;500;600;700;800;900',
        'Black Ops One': '400',
        'Bodoni Moda': '400;500;600;700;800;900',
        'Bonheur Royale': '400',
        'Cabin': '400;500;600;700',
        'Caudex': '400;700',
        'Caveat': '400;700',
        'Changa One': '400',
        'Cinzel': '400;500;600;700;800;900',
        'Cookie': '400',
        'Cormorant Garamond': '300;400;500;600;700',
        'Cormorant Infant': '300;400;500;600;700',
        'Cormorant Upright': '300;400;500;600;700',
        'Courgette': '400',
        'Crimson Text': '400;600;700',
        'DM Sans': '100;200;300;400;500;600;700;800;900;1000',
        'DM Serif Display': '400',
        'Dancing Script': '400;500;600;700',
        'Dosis': '200;300;400;500;600;700;800',
        'EB Garamond': '400;500;600;700;800',
        'Elsie': '400;900',
        'Ephesis': '400',
        'Figtree': '400;500;700',
        'Fira Sans': '100;200;300;400;500;600;700;800;900',
        'Fleur De Leah': '400',
        'Forum': '400',
        'Fraunces': '100;200;300;400;500;600;700;800;900',
        'Google Sans': '400;500;600;700',
        'Great Vibes': '400',
        'Heebo': '100;200;300;400;500;600;700;800;900',
        'IBM Plex Sans': '100;200;300;400;500;600;700',
        'Imperial Script': '400',
        'Inconsolata': '200;300;400;500;600;700;800;900',
        'Instrument Serif': '400',
        'Inter': '100;200;300;400;500;600;700;800;900',
        'Inter Tight': '100;200;300;400;500;600;700;800;900',
        'Italiana': '400',
        'Italianno': '400',
        'JetBrains Mono': '100;200;300;400;500;600;700;800',
        'Josefin Sans': '400;500;700',
        'Jost': '100;200;300;400;500;600;700;800;900',
        'Kanit': '100;200;300;400;500;600;700;800;900',
        'Karla': '400;700',
        'Lavishly Yours': '400',
        'Libre Baskerville': '400;500;600;700',
        'Libre Franklin': '100;200;300;400;500;600;700;800;900',
        'Lobster Two': '400;700',
        'Lora': '400;500;600;700',
        'Manrope': '400;500;700',
        'Marcellus': '400',
        'Mea Culpa': '400',
        'Merriweather': '400;700',
        'Monsieur La Doulaise': '400',
        'Montserrat': '100;200;300;400;500;600;700;800;900',
        'Mulish': '400;500;700',
        'Noto Naskh Arabic': '400;500;600;700',
        'Noto Sans': '400;500;700',
        'Noto Serif': '400;700',
        'Nunito': '400;600;700',
        'Nunito Sans': '200;300;400;500;600;700;800;900;1000',
        'Open Sans': '400;500;700',
        'Oswald': '400;500;700',
        'Outfit': '100;200;300;400;500;600;700;800;900',
        'Oxygen': '400;700',
        'PT Serif': '400;700',
        'Pacifico': '400',
        'Parisienne': '400',
        'Petit Formal Script': '400',
        'Philosopher': '400;700',
        'Playfair Display': '400;500;600;700;800;900',
        'Plus Jakarta Sans': '400;500;700',
        'Poiret One': '400',
        'Poppins': '100;200;300;400;500;600;700;800;900',
        'Prata': '400',
        'Prompt': '100;200;300;400;500;600;700;800;900',
        'Public Sans': '100;200;300;400;500;600;700;800;900',
        'Questrial': '400',
        'Quicksand': '400;500;700',
        'Quintessential': '400',
        'Raleway': '400;500;700',
        'Red Hat Display': '300;400;500;600;700;800;900',
        'Roboto': '400;500;700',
        'Roboto Mono': '100;200;300;400;500;600;700',
        'Roboto Slab': '100;200;300;400;500;600;700;800;900',
        'Rubik': '400;500;700',
        'Sacramento': '400',
        'Satisfy': '400',
        'Sora': '100;200;300;400;500;600;700;800',
        'Sorts Mill Goudy': '400',
        'Source Code Pro': '200;300;400;500;600;700;800;900',
        'Source Sans 3': '200;300;400;500;600;700;800;900',
        'Space Grotesk': '400;500;700',
        'Tangerine': '400;700',
        'The Nautigal': '400;700',
        'Titillium Web': '200;300;400;600;700;900',
        'Ubuntu': '300;400;500;700',
        'Unna': '400;700',
        'Urbanist': '400;500;700',
        'Viaoda Libre': '400',
        'WindSong': '400;500',
        'Work Sans': '400;500;700',
        'Yeseva One': '400'
    };
    function collectPreviewFontFamiliesFromObject(object, fonts) {
    if (!object || typeof object !== 'object') return;
    
    if (object.fontFamily) {
        fonts.add(normalizeFontFamily(object.fontFamily));
    }
    
    if (object.styles && typeof object.styles === 'object') {
        Object.keys(object.styles).forEach(function (lineKey) {
            var line = object.styles[lineKey];
            if (!line || typeof line !== 'object') return;
            Object.keys(line).forEach(function (charKey) {
                var style = line[charKey];
                if (style && style.fontFamily) {
                    fonts.add(normalizeFontFamily(style.fontFamily));
                }
            });
        });
    }
    
    var subObjects = object.objects || (object._objects) || [];
    if (Array.isArray(subObjects)) {
        subObjects.forEach(function (child) {
            collectPreviewFontFamiliesFromObject(child, fonts);
        });
    }
}
   function collectPreviewFontFamilies(data) {
    var fonts = new Set(['Inter']);
    collectPreviewFontFamiliesFromObject(data && data.opening, fonts);
    
    (data && data.objects || []).forEach(function (object) {
        collectPreviewFontFamiliesFromObject(object, fonts);
    });
    (data && data.pages || []).forEach(function (page) {
        collectPreviewFontFamiliesFromObject(page, fonts);
        (page.objects || []).forEach(function (object) {
            collectPreviewFontFamiliesFromObject(object, fonts);
        });
    });
    
    return Array.from(fonts);
}
    function previewGoogleFontUrl(data) {
    var families = collectPreviewFontFamilies(data);

    var googleFamilies = families.filter(function (family) {
        return Object.prototype.hasOwnProperty.call(previewGoogleFontWeights, normalizeFontFamily(family));
    });

    if (googleFamilies.indexOf('Inter') === -1) {
        googleFamilies.unshift('Inter');
    }

    var parts = googleFamilies.map(function (family) {
        family = normalizeFontFamily(family);

        var weights = previewGoogleFontWeights[family];

        var encodedFamily = encodeURIComponent(family)
            .replace(/%20/g, '+');

        return 'family=' + encodedFamily +
            (weights && weights !== '400' ? ':wght@' + weights : '');
    });

    if (!parts.length) {
        return '';
    }

    return 'https://fonts.googleapis.com/css2?' + parts.join('&') + '&display=swap';
}
    function getPublicGuestName() {
        var params = new URLSearchParams(window.location.search || '');
        var value = params.get('to') || params.get('tamu') || params.get('invite') || params.get('guest') || '';
        value = String(value || '').replace(/\\+/g, ' ').trim();
        return value || 'Tamu Undangan';
    }
    function renderGuestNameTemplate(template, guestName) {
        template = String(template || 'Kepada Yth.\\n{{guest_name}}');
        if (!/\\{\\{\\s*guest_name\\s*\\}\\}/i.test(template)) {
            template = template.replace(/Nama Tamu|Tamu Undangan/gi, '{{guest_name}}');
        }
        return template.replace(/\\{\\{\\s*guest_name\\s*\\}\\}/gi, guestName || 'Tamu Undangan');
    }
    function isGuestNamePlaceholderText(value) {
        var text = String(value || '').trim();
        var normalized = text.replace(/\\s+/g, ' ');
        return /\\{\\{\\s*guest_name\\s*\\}\\}/i.test(text) ||
            /\\bNama\\s+Tamu\\b/i.test(text) ||
            /^(Kepada\\s+(Yth\\.?|Yang\\s+Terhormat)\\s*)?Tamu\\s+Undangan$/i.test(normalized);
    }
    function applyGuestNameObjects(canvas) {
        if (!canvas || !canvas.getObjects) return;
        var guestName = getPublicGuestName();
        walkObjects(canvas.getObjects(), function (object) {
            var isGuestNameCandidate = object && (object.isGuestName === true || object.customType === 'guest_name' ||
                object.dynamicKey === 'guest_name' || (isTextObject(object) && isGuestNamePlaceholderText(object.text)));
            if (!isGuestNameCandidate) return;
            var currentText = object.templateText || object.text || '';
            if (!currentText && object && typeof object.getObjects === 'function') {
                var currentChildren = object.getObjects();
                var currentTarget = currentChildren.find(function (child) { return child.name === 'guest-name-text'; }) ||
                    currentChildren.find(isTextObject);
                currentText = currentTarget ? currentTarget.text : '';
            }
            var nextText = renderGuestNameTemplate(currentText, guestName);
            var target = object;
            if (object && typeof object.getObjects === 'function') {
                var children = object.getObjects();
                target = children.find(function (child) { return child.name === 'guest-name-text'; }) ||
                    children.find(isTextObject) || object;
            }
            if (typeof target.set === 'function') {
                target.set('text', nextText);
            } else {
                target.text = nextText;
            }
            target.dirty = true;
            if (typeof target.initDimensions === 'function') target.initDimensions();
            object.dirty = true;
            if (typeof object.setCoords === 'function') object.setCoords();
        });
    }
    function loadFontsForCanvas(canvas) {
    if (!document.fonts || !document.fonts.load || !canvas || !canvas.getObjects) {
        return Promise.resolve();
    }
    
    var variants = [];
    var addVariant = function (family, weight, style) {
        family = normalizeFontFamily(family);
        weight = String(weight || '400').toLowerCase() === 'bold' ? '700' : String(weight || '400');
        if (!/^[1-9]00$/.test(weight)) weight = Number(weight) >= 600 ? '700' : '400';
        style = String(style || 'normal').toLowerCase() === 'italic' ? 'italic' : 'normal';
        
        var key = family + '|' + weight + '|' + style;
        if (!variants.some(function (item) { return item.key === key; })) {
            variants.push({ family: family, weight: weight, style: style, key: key });
        }
    };

    walkObjects(canvas.getObjects(), function (object) {
        if (!isTextObject(object)) return;
        addVariant(object.fontFamily, object.fontWeight, object.fontStyle);
    });
    
    addVariant('Inter', '400', 'normal');

    var fontPromises = variants.map(function (v) {
        // Format: "normal 400 32px 'Nama Font'"
        var fontString = v.style + ' ' + v.weight + ' 32px "' + v.family + '"';
        
        return document.fonts.load(fontString).then(function (loaded) {
            console.log('[AA FONT TRACKER] Loaded:', fontString, loaded.length > 0 ? 'Success' : 'Fallback Used');
            return null;
        }).catch(function (err) {
            console.warn('[AA FONT TRACKER] Failed to load:', fontString, err);
            return null;
        });
    });

    return Promise.all(fontPromises).then(function () {
        if (document.fonts && document.fonts.ready) {
            return document.fonts.ready;
        }
        return null;
    }).then(function () {
        return new Promise(function (resolve) {
            setTimeout(resolve, 250);
        });
    });
}
    function recalculateTextObjects(canvas) {
        if (!canvas || !canvas.getObjects) return;
        walkObjects(canvas.getObjects(), function (object) {
            if (object.type === 'image' && object.borderRadius && object.clipPath && (object.clipPath.rx || object.clipPath.ry)) {
                object.clipPath = null;
                object.dirty = true;
                object.setCoords();
            }
            if (object.type === 'image' && object.aaImageEffectPreset && Array.isArray(object.filters) && object.filters.length) {
                object.filters = [];
                object.dirty = true;
                object.setCoords();
            }
            if (!isTextObject(object)) return;
            if (object.clipPath) {
                object.clipPath = null;
            }
            object.dirty = true;
            if (typeof object.initDimensions === 'function') object.initDimensions();
            object.setCoords();
        });
    }
    function installRoundedImageRenderer() {
        if (!window.fabric || fabric.Image.prototype.__aaRoundedRendererInstalled) return;
        var originalRender = fabric.Image.prototype._render;
        var drawImagePath = function (ctx, width, height, radius) {
            var r = Math.min(Math.max(0, Number(radius) || 0), width / 2, height / 2);
            var x = -width / 2;
            var y = -height / 2;
            ctx.beginPath();
            if (!r) {
                ctx.rect(x, y, width, height);
                return;
            }
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + width - r, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + r);
            ctx.lineTo(x + width, y + height - r);
            ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
            ctx.lineTo(x + r, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - r);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.closePath();
        };
        var drawImageStroke = function (ctx, image, width, height, radius) {
            var strokeWidth = Math.max(0, Number(image.strokeWidth) || 0);
            if (!strokeWidth || !image.stroke || image.stroke === 'transparent') return;
            ctx.save();
            drawImagePath(ctx, width, height, radius);
            ctx.lineWidth = strokeWidth;
            ctx.strokeStyle = image.stroke;
            ctx.lineJoin = 'round';
            ctx.lineCap = image.imageStrokeStyle === 'dotted' ? 'round' : 'butt';
            if (Array.isArray(image.strokeDashArray)) {
                ctx.setLineDash(image.strokeDashArray);
            }
            ctx.stroke();
            ctx.restore();
        };
        var imageEffectCanvasFilter = function (image) {
            var preset = String((image && image.aaImageEffectPreset) || 'none');
            if (!preset || preset === 'none' || preset === 'opacity' || preset === 'shadow') return '';
            if (Array.isArray(image.filters) && image.filters.length) return '';
            if (preset === 'brightness') return 'brightness(1.16)';
            if (preset === 'contrast') return 'contrast(1.22)';
            if (preset === 'saturation') return 'saturate(1.38)';
            if (preset === 'grayscale') return 'grayscale(1)';
            if (preset === 'sepia') return 'sepia(1)';
            if (preset === 'blur') return 'blur(2px)';
            if (preset === 'sharpen') return 'contrast(1.28) saturate(1.12)';
            if (preset === 'vintage') return 'sepia(.55) contrast(1.08) saturate(.82)';
            if (preset === 'soft-wedding') return 'brightness(1.08) contrast(.96) saturate(1.18) sepia(.08)';
            if (preset === 'clean-bright') return 'brightness(1.14) contrast(1.08) saturate(1.08)';
            if (preset === 'warm-editorial') return 'sepia(.18) brightness(1.06) contrast(1.12) saturate(1.14)';
            if (preset === 'film-matte') return 'sepia(.2) contrast(.92) saturate(.78) brightness(1.04)';
            if (preset === 'pastel-bloom') return 'brightness(1.1) contrast(.94) saturate(1.32) hue-rotate(-6deg)';
            if (preset === 'moody-luxe') return 'brightness(.88) contrast(1.22) saturate(.9) sepia(.08)';
            if (preset === 'classic-bw') return 'grayscale(1) contrast(1.18) brightness(1.04)';
            if (preset === 'dreamy-soft') return 'brightness(1.12) contrast(.9) saturate(1.12) blur(.75px)';
            if (preset === 'recolor-white') return 'grayscale(.35) brightness(1.34) contrast(.86) saturate(.68)';
            if (preset === 'recolor-black') return 'grayscale(1) brightness(.72) contrast(1.28)';
            if (preset === 'recolor-gold') return 'sepia(.55) saturate(1.45) hue-rotate(4deg) brightness(1.08) contrast(1.04)';
            if (preset === 'recolor-teal') return 'sepia(.18) saturate(1.35) hue-rotate(135deg) brightness(.96) contrast(1.06)';
            if (preset === 'recolor-rose') return 'sepia(.22) saturate(1.35) hue-rotate(300deg) brightness(1.04) contrast(.98)';
            if (preset === 'recolor-slate') return 'grayscale(.65) sepia(.12) saturate(.7) hue-rotate(170deg) brightness(.92) contrast(1.08)';
            if (preset === 'remove-color') return 'saturate(.2) contrast(1.12)';
            return '';
        };
        var renderImageWithCanvasEffect = function (image, ctx) {
            var filter = imageEffectCanvasFilter(image);
            if (!filter) {
                originalRender.call(image, ctx);
                return;
            }
            var previousFilter = ctx.filter;
            ctx.filter = filter;
            originalRender.call(image, ctx);
            ctx.filter = previousFilter;
        };
        fabric.Image.prototype._render = function (ctx) {
            var radius = Math.max(0, Number(this.borderRadius) || 0);
            var width = Math.max(1, this.width || 1);
            var height = Math.max(1, this.height || 1);
            if (radius) {
                ctx.save();
                drawImagePath(ctx, width, height, radius);
                ctx.clip();
                renderImageWithCanvasEffect(this, ctx);
                ctx.restore();
            } else {
                renderImageWithCanvasEffect(this, ctx);
            }
            drawImageStroke(ctx, this, width, height, radius);
        };
        fabric.Image.prototype.__aaRoundedRendererInstalled = true;
    }
    function renderPage(pageData, index) {
        if (!pageData || pageData.__aaRendered) return;
        pageData.__aaRendered = true;
        pageData = sanitizePageData(pageData);
        var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
        if (!canvasEl || !window.fabric) return;
        installRoundedImageRenderer();
        var artboardEl = canvasEl.closest('.aa-fabric-artboard');
        if (artboardEl) artboardEl.classList.add('is-rendering');
        var width = pageData.artboard && pageData.artboard.width ? pageData.artboard.width : 1080;
        var height = pageData.artboard && pageData.artboard.height ? pageData.artboard.height : 1920;
        var resizeCanvas = function (canvas) {
            canvas.setDimensions({
                width: width,
                height: height
            });
            canvasEl.style.width = '100%';
            canvasEl.style.height = '100%';
            if (canvas.wrapperEl) {
                canvas.wrapperEl.style.width = '100%';
                canvas.wrapperEl.style.height = '100%';
            }
            canvas.calcOffset();
            canvas.requestRenderAll();
        };
        var canvas = new fabric.StaticCanvas(canvasEl, {
            width: width,
            height: height,
            renderOnAddRemove: false
        });

        window.__AA_PREVIEW_CANVASES__ = window.__AA_PREVIEW_CANVASES__ || [];
        window.__AA_PREVIEW_CANVASES__.push(canvas);

        canvasEl.__aaFabricCanvas = canvas;
        canvasEl.__aaFabricCanvas = canvas;
        canvas.loadFromJSON(pageData, function () {
            canvas.backgroundColor = pageData.background || pageData.backgroundColor || '#ffffff';
            aaRestorePublicMaterials(canvas);
            canvas.getObjects().forEach(function (object) {
                object.selectable = false;
                object.evented = false;
                if (isGuestbookObject(object) || isInteractiveObject(object)) {
                    object.visible = false;
                } else if (isAnimatedGifObject(object) && object.customType !== 'background') {
                    object.visible = true;
                }
            });
            prepareScrollAnimatedObjects(canvas);
            var finalize = function () {
                applyGuestNameObjects(canvas);
                recalculateTextObjects(canvas);
                resizeCanvas(canvas);
                prepareTextAnimatedObjects(canvas);
                setupAnimatedGifBackground(canvasEl, canvas);
                canvas.renderAll();
                setupAnimatedGifOverlay(canvasEl, canvas);
                setupActionHotspots(canvasEl, canvas);
                setupGuestbookOverlay(canvasEl, canvas);
                setupInteractiveOverlay(canvasEl, canvas);
                setupScrollAnimations(canvasEl, canvas);
                if (artboardEl) artboardEl.classList.remove('is-rendering');
            };
           loadFontsForCanvas(canvas).then(function () {
    function forceTextRerender() {
        applyGuestNameObjects(canvas);
        recalculateTextObjects(canvas);

        canvas.getObjects().forEach(function (object) {
            if (!object) return;
            
            // Lakukan pengecekan hingga ke dalam grup
            var subObjects = typeof object.getObjects === 'function' ? object.getObjects() : [object];
            subObjects.forEach(function (target) {
                if (target && ['text', 'i-text', 'textbox'].indexOf(target.type) !== -1) {
                    target.dirty = true;
                    if (typeof target.initDimensions === 'function') {
                        target.initDimensions(); // Hitung ulang width/height text box setelah font asli masuk
                    }
                    if (typeof target.setCoords === 'function') {
                        target.setCoords();
                    }
                }
            });
        });

        resizeCanvas(canvas);
        canvas.requestRenderAll();
    }

    function runFinalRender() {
        finalize();
        // Trigger berkala untuk memastikan font yang telat dimuat tetap ter-render presisi
        forceTextRerender();
        setTimeout(forceTextRerender, 300);
        setTimeout(forceTextRerender, 800);
        setTimeout(forceTextRerender, 1500);
    }

    if (window.requestAnimationFrame) {
        requestAnimationFrame(function () {
            requestAnimationFrame(runFinalRender);
        });
    } else {
        window.setTimeout(runFinalRender, 50);
    }
});
            window.addEventListener('resize', function () {
                resizeCanvas(canvas);
            });
        });
    }
    function lazyRenderPages(pages) {
        pages = (pages || []).filter(function (pageData) {
            return pageData && pageData.hidden !== true;
        });
        if (!pages.length) return;
        var rendered = {};
        var renderAt = function (index) {
            if (rendered[index]) return;
            rendered[index] = true;
            renderPage(pages[index], index);
        };
        renderAt(0);
        if (!('IntersectionObserver' in window)) {
            pages.forEach(function (_pageData, index) {
                if (index === 0) return;
                window.setTimeout(function () {
                    renderAt(index);
                }, index * 260);
            });
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var index = Number(entry.target.getAttribute('data-aa-page-index') || 0);
                renderAt(index);
                observer.unobserve(entry.target);
            });
        }, {
            rootMargin: '320px 0px 420px 0px',
            threshold: 0.01
        });
        pages.forEach(function (_pageData, index) {
            if (index === 0) return;
            var canvasEl = document.getElementById('aaFabricPublicCanvas' + index);
            var section = canvasEl ? canvasEl.closest('.aa-fabric-page-section') : null;
            if (!section) {
                window.setTimeout(function () {
                    renderAt(index);
                }, index * 260);
                return;
            }
            section.setAttribute('data-aa-page-index', String(index));
            observer.observe(section);
        });
    }
    function normalizeUrl(url) {
        url = String(url || '').trim();
        if (!url) return '';
        if (/^(https?:|mailto:|tel:|sms:|whatsapp:)/i.test(url)) return url;
        return 'https://' + url.replace(/^\\/+/, '');
    }
    function showCopyToast(message) {
        var toast = document.createElement('div');
        toast.textContent = message || 'Tersalin';
        toast.style.cssText = 'position:fixed;left:50%;bottom:24px;z-index:99999;transform:translateX(-50%);border-radius:999px;background:rgba(17,24,39,.94);color:#fff;padding:10px 16px;font:700 13px Inter,Arial,sans-serif;box-shadow:0 14px 36px rgba(15,23,42,.24);pointer-events:none;';
        document.body.appendChild(toast);
        window.setTimeout(function () {
            toast.style.transition = 'opacity .22s ease, transform .22s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(8px)';
        }, 1100);
        window.setTimeout(function () {
            toast.remove();
        }, 1400);
    }
    function copyToClipboard(value, message) {
        value = String(value || '');
        if (!value) return;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(function () {
                showCopyToast(message);
            }).catch(function () {
                fallbackCopy(value, message);
            });
            return;
        }
        fallbackCopy(value, message);
    }
    function fallbackCopy(value, message) {
        var input = document.createElement('textarea');
        input.value = value;
        input.setAttribute('readonly', 'readonly');
        input.style.cssText = 'position:fixed;left:-9999px;top:0;';
        document.body.appendChild(input);
        input.select();
        try {
            document.execCommand('copy');
            showCopyToast(message);
        } catch (error) {
            showCopyToast('Tidak bisa copy otomatis');
        }
        input.remove();
    }
    function isAnimatedGifObject(object) {
        if (!object || object.type !== 'image') return false;
        var src = String(object.aaAnimatedSrc || object.src || (object._element && object._element.src) || '');
        var cleanSrc = src.split('?')[0].toLowerCase();
        return object.aaMediaKind === 'gif' || cleanSrc.endsWith('.gif');
    }
    function animatedGifObjectOpacity(object) {
        var original = object && object.__aaAnimationOriginal;
        var value = original && original.opacity != null
            ? original.opacity
            : (object && object.opacity != null ? object.opacity : 1);
        value = Number(value);
        return Math.max(0, Math.min(1, isFinite(value) ? value : 1));
    }
    function applyAnimatedGifOverlayAnimation(node, object, baseTransform) {
        if (!node || !object) return false;
        var animationName = getObjectAnimationName(object);
        if (!animationName || animationName === 'none') return false;
        var finalOpacity = animatedGifObjectOpacity(object);
        node.style.opacity = String(finalOpacity);
        node.style.setProperty('--aa-overlay-base-transform', baseTransform || node.style.transform || 'rotate(0deg)');
        node.style.setProperty('--aa-overlay-final-opacity', String(finalOpacity));
        applyOverlayAnimation(node, object);
        return node.classList.contains('aa-fabric-overlay-animated');
    }
    function clampRectToCanvas(rect, canvasWidth, canvasHeight) {
        if (!rect) return null;
        var left = Math.max(0, Number(rect.left) || 0);
        var top = Math.max(0, Number(rect.top) || 0);
        var right = Math.min(canvasWidth, (Number(rect.left) || 0) + Math.max(0, Number(rect.width) || 0));
        var bottom = Math.min(canvasHeight, (Number(rect.top) || 0) + Math.max(0, Number(rect.height) || 0));
        if (right <= left || bottom <= top) return null;
        return { left: left, top: top, width: right - left, height: bottom - top };
    }
    function canUseAnimatedGifOverlay(object, allObjects, canvasWidth, canvasHeight) {
        if (!object || object.customType === 'background') return false;
        var rect = clampRectToCanvas(object.getBoundingRect(true, true), canvasWidth, canvasHeight);
        if (!rect) return false;
        var index = allObjects.indexOf(object);
        if (index < 0) return false;
        for (var i = index + 1; i < allObjects.length; i += 1) {
            var above = allObjects[i];
            if (!above || above.visible === false || (above.opacity === 0 && !isAnimatedLayerBlocker(above)) || isGuestbookObject(above) || isInteractiveObject(above)) continue;
            if (isAnimatedGifObject(above)) continue;
            var aboveRect = clampRectToCanvas(above.getBoundingRect(true, true), canvasWidth, canvasHeight);
            if (!aboveRect) continue;
            if (rect.left < aboveRect.left + aboveRect.width && rect.left + rect.width > aboveRect.left && rect.top < aboveRect.top + aboveRect.height && rect.top + rect.height > aboveRect.top) {
                return false;
            }
        }
        return true;
    }
    function prepareAnimatedGifOverlayObjects(canvas) {
        if (!canvas || !canvas.getObjects) return;
        var allObjects = canvas.getObjects();
        var canvasWidth = canvas.getWidth() || 1;
        var canvasHeight = canvas.getHeight() || 1;
        allObjects.forEach(function (object) {
            if (!isAnimatedGifObject(object) || object.customType === 'background') return;
            if (!canUseAnimatedGifOverlay(object, allObjects, canvasWidth, canvasHeight)) return;
            object.visible = false;
            object.evented = false;
            object.selectable = false;
        });
    }
    function animatedGifObjectGeometry(object, canvasWidth, canvasHeight, imageWidth, imageHeight) {
        var isCoverBackground = object && object.customType === 'background' && (
            object.name === 'Background Image' || object.aaBgOffsetX != null || object.aaBgOffsetY != null
        );
        var sourceWidth = Math.max(1, Number(imageWidth) || 0);
        var sourceHeight = Math.max(1, Number(imageHeight) || 0);
        var center = object.getCenterPoint ? object.getCenterPoint() : {
            x: (Number(object.left) || canvasWidth / 2),
            y: (Number(object.top) || canvasHeight / 2)
        };
        var storedWidth = Math.abs((Number(object.width) || 0) * (Number(object.scaleX) || 1));
        var storedHeight = Math.abs((Number(object.height) || 0) * (Number(object.scaleY) || 1));
        var width = storedWidth > 1 ? storedWidth : canvasWidth;
        var height = storedHeight > 1 ? storedHeight : canvasHeight;
        if (isCoverBackground && sourceWidth > 1 && sourceHeight > 1 && (storedWidth <= 1 || storedHeight <= 1)) {
            var offsetX = Number(object.aaBgOffsetX || 0);
            var offsetY = Number(object.aaBgOffsetY || 0);
            var coverScale = Math.max(canvasWidth / sourceWidth, canvasHeight / sourceHeight);
            return {
                center: {
                    x: (canvasWidth / 2) + ((canvasWidth * offsetX) / 100),
                    y: (canvasHeight / 2) + ((canvasHeight * offsetY) / 100)
                },
                width: Math.max(1, sourceWidth * coverScale),
                height: Math.max(1, sourceHeight * coverScale),
                angle: Number(object.angle) || 0,
                opacity: Math.max(0, Math.min(1, Number(object.opacity == null ? 1 : object.opacity))),
                flipX: object.flipX ? ' scaleX(-1)' : '',
                flipY: object.flipY ? ' scaleY(-1)' : ''
            };
        }
        return {
            center: center,
            width: width,
            height: height,
            angle: Number(object.angle) || 0,
            opacity: Math.max(0, Math.min(1, Number(object.opacity == null ? 1 : object.opacity))),
            flipX: object.flipX ? ' scaleX(-1)' : '',
            flipY: object.flipY ? ' scaleY(-1)' : ''
        };
    }
    function setupAnimatedGifBackground(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard || !canvas || !canvas.getObjects) return;

        var oldLayer = artboard.querySelector('.aa-fabric-bg-gif-layer');
        if (oldLayer) oldLayer.remove();

        var background = canvas.getObjects().find(function (object) {
            return object && object.customType === 'background' && isAnimatedGifObject(object);
        });
        if (!background) return;

        background.visible = false;
        background.evented = false;
        background.selectable = false;
        canvas.backgroundColor = '';

        var canvasWidth = canvas.getWidth() || 1;
        var canvasHeight = canvas.getHeight() || 1;
        var src = background.aaAnimatedSrc || background.src || (background._element && background._element.src) || '';
        if (!src) return;

        var layer = document.createElement('div');
        layer.className = 'aa-fabric-bg-gif-layer';
        var img = document.createElement('img');
        img.alt = '';
        img.loading = 'eager';
        img.decoding = 'async';
        var applyGeometry = function () {
            var geometry = animatedGifObjectGeometry(background, canvasWidth, canvasHeight, img.naturalWidth, img.naturalHeight);
            img.style.left = (geometry.center.x / canvasWidth * 100) + '%';
            img.style.top = (geometry.center.y / canvasHeight * 100) + '%';
            img.style.width = (geometry.width / canvasWidth * 100) + '%';
            img.style.height = (geometry.height / canvasHeight * 100) + '%';
            img.style.opacity = String(geometry.opacity);
            img.style.transform = 'translate(-50%, -50%) rotate(' + geometry.angle + 'deg)' + geometry.flipX + geometry.flipY;
        };
        img.addEventListener('load', applyGeometry, { once: true });
        img.src = src;
        applyGeometry();
        layer.appendChild(img);
        artboard.insertBefore(layer, artboard.firstChild);
    }
    function setupAnimatedGifOverlay(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard || !canvas || !canvas.getObjects) return;

        var oldLayer = artboard.querySelector('.aa-fabric-gif-layer');
        if (oldLayer) oldLayer.remove();
        var oldUnderLayer = artboard.querySelector('.aa-fabric-gif-under-layer');
        if (oldUnderLayer) oldUnderLayer.remove();

        var allObjects = canvas.getObjects();
        var objects = allObjects.filter(isAnimatedGifObject);
        if (!objects.length) return;

        var layer = null;
        var underLayer = null;
        var ensureUpperLayer = function () {
            if (layer) return layer;
            layer = document.createElement('div');
            layer.className = 'aa-fabric-gif-layer';
            artboard.appendChild(layer);
            return layer;
        };
        var ensureUnderLayer = function () {
            if (underLayer) return underLayer;
            underLayer = document.createElement('div');
            underLayer.className = 'aa-fabric-gif-under-layer';
            artboard.insertBefore(underLayer, artboard.firstChild);
            canvas.backgroundColor = '';
            return underLayer;
        };

        var canvasWidth = canvas.getWidth() || 1;
        var canvasHeight = canvas.getHeight() || 1;
        var rectsOverlap = function (a, b) {
            if (!a || !b) return false;
            return a.left < b.left + b.width && a.left + a.width > b.left && a.top < b.top + b.height && a.top + a.height > b.top;
        };
        var hasVisibleObjectAbove = function (object, rect) {
            var index = allObjects.indexOf(object);
            if (index < 0) return false;
            for (var i = index + 1; i < allObjects.length; i += 1) {
                var above = allObjects[i];
                if (!above || above.visible === false || (above.opacity === 0 && !isAnimatedLayerBlocker(above)) || isGuestbookObject(above) || isInteractiveObject(above)) continue;
                if (isAnimatedGifObject(above)) continue;
                var aboveRect = clampRectToCanvas(above.getBoundingRect(true, true), canvasWidth, canvasHeight);
                if (aboveRect && rectsOverlap(rect, aboveRect)) return true;
            }
            return false;
        };
        var gifCropGeometry = function (object) {
            var element = object && (object._element || (typeof object.getElement === 'function' ? object.getElement() : null));
            var sourceWidth = Math.max(1, Number(element && (element.naturalWidth || element.width)) || Number(object.width) || 1);
            var sourceHeight = Math.max(1, Number(element && (element.naturalHeight || element.height)) || Number(object.height) || 1);
            var cropX = Math.max(0, Math.min(Number(object.cropX) || 0, sourceWidth - 1));
            var cropY = Math.max(0, Math.min(Number(object.cropY) || 0, sourceHeight - 1));
            var cropWidth = Math.max(1, Math.min(Number(object.width) || sourceWidth, sourceWidth - cropX));
            var cropHeight = Math.max(1, Math.min(Number(object.height) || sourceHeight, sourceHeight - cropY));
            var cropped = cropX > 0.5 || cropY > 0.5 || cropWidth < sourceWidth - cropX - 0.5 || cropHeight < sourceHeight - cropY - 0.5;
            return {
                cropped: cropped,
                sourceWidth: sourceWidth,
                sourceHeight: sourceHeight,
                cropX: cropX,
                cropY: cropY,
                cropWidth: cropWidth,
                cropHeight: cropHeight
            };
        };
        var restoreFabricGifObject = function (object, node) {
            if (node && node.parentNode) node.parentNode.removeChild(node);
            object.visible = true;
            object.evented = false;
            object.selectable = false;
            canvas.requestRenderAll();
        };
        var hideFabricGifObject = function (object, image) {
            if (!image.naturalWidth || !image.naturalHeight) return;
            requestAnimationFrame(function () {
                object.visible = false;
                object.evented = false;
                object.selectable = false;
                canvas.requestRenderAll();
            });
        };

        objects.forEach(function (object) {
            if (object.customType === 'background') return;
            var src = object.aaAnimatedSrc || object.src || (object._element && object._element.src) || '';
            if (!src) return;
            object.visible = true;
            object.evented = false;
            object.selectable = false;
            var objectRect = clampRectToCanvas(object.getBoundingRect(true, true), canvasWidth, canvasHeight);
            if (!objectRect) return;
            var targetLayer = hasVisibleObjectAbove(object, objectRect) ? ensureUnderLayer() : ensureUpperLayer();

            var center = object.getCenterPoint ? object.getCenterPoint() : {
                x: (Number(object.left) || 0),
                y: (Number(object.top) || 0)
            };
            var width = Math.max(1, Math.abs((Number(object.width) || 1) * (Number(object.scaleX) || 1)));
            var height = Math.max(1, Math.abs((Number(object.height) || 1) * (Number(object.scaleY) || 1)));
            var radius = Math.max(0, Number(object.borderRadius) || 0) * Math.max(Math.abs(Number(object.scaleX) || 1), Math.abs(Number(object.scaleY) || 1));
            var crop = gifCropGeometry(object);
            var img = document.createElement('img');
            img.alt = '';
            img.loading = 'eager';
            img.decoding = 'async';
            if (crop.cropped) {
                var frame = document.createElement('span');
                frame.className = 'aa-fabric-gif-crop-frame';
                frame.style.left = (center.x / canvasWidth * 100) + '%';
                frame.style.top = (center.y / canvasHeight * 100) + '%';
                frame.style.width = (width / canvasWidth * 100) + '%';
                frame.style.height = (height / canvasHeight * 100) + '%';
                frame.style.zIndex = String(Math.max(1, allObjects.indexOf(object) + 1));
                frame.style.opacity = String(animatedGifObjectOpacity(object));
                frame.style.borderRadius = radius ? (radius / Math.max(width, height) * 100) + '%' : '0';
                var frameTransform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');
                frame.style.transform = frameTransform;
                if (applyAnimatedGifOverlayAnimation(frame, object, frameTransform)) {
                    canvas.__aaHasOverlayAnimations = true;
                }
                img.style.left = (-crop.cropX / crop.cropWidth * 100) + '%';
                img.style.top = (-crop.cropY / crop.cropHeight * 100) + '%';
                img.style.width = (crop.sourceWidth / crop.cropWidth * 100) + '%';
                img.style.height = (crop.sourceHeight / crop.cropHeight * 100) + '%';
                img.style.maxWidth = 'none';
                img.style.maxHeight = 'none';
                img.addEventListener('load', function () {
                    hideFabricGifObject(object, img);
                }, { once: true });
                img.addEventListener('error', function () {
                    restoreFabricGifObject(object, frame);
                }, { once: true });
                img.src = src;
                frame.appendChild(img);
                targetLayer.appendChild(frame);
                return;
            }
            img.style.left = (center.x / canvasWidth * 100) + '%';
            img.style.top = (center.y / canvasHeight * 100) + '%';
            img.style.width = (width / canvasWidth * 100) + '%';
            img.style.height = (height / canvasHeight * 100) + '%';
            img.style.zIndex = String(Math.max(1, allObjects.indexOf(object) + 1));
            img.style.opacity = String(animatedGifObjectOpacity(object));
            img.style.borderRadius = radius ? (radius / Math.max(width, height) * 100) + '%' : '0';
            var imgTransform = 'translate(-50%, -50%) rotate(' + (Number(object.angle) || 0) + 'deg)' + (object.flipX ? ' scaleX(-1)' : '') + (object.flipY ? ' scaleY(-1)' : '');
            img.style.transform = imgTransform;
            if (applyAnimatedGifOverlayAnimation(img, object, imgTransform)) {
                canvas.__aaHasOverlayAnimations = true;
            }
            img.addEventListener('load', function () {
                hideFabricGifObject(object, img);
            }, { once: true });
            img.addEventListener('error', function () {
                restoreFabricGifObject(object, img);
            }, { once: true });
            img.src = src;
            targetLayer.appendChild(img);
        });
    }
    function setupActionHotspots(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard) return;

        var oldLayer = artboard.querySelector('.aa-fabric-click-layer');
        if (oldLayer) oldLayer.remove();

        var layer = document.createElement('div');
        layer.className = 'aa-fabric-click-layer';
        artboard.appendChild(layer);

        var canvasWidth = canvas.getWidth() || 1;
        var canvasHeight = canvas.getHeight() || 1;

        canvas.getObjects().forEach(function (object) {
            if (object && (object.customType === 'gallery-photo' || object.isGalleryPhoto === true || object.galleryZoom === true)) {
                var galleryRect = object.getBoundingRect(true, true);
                var galleryHotspot = document.createElement('button');
                galleryHotspot.type = 'button';
                galleryHotspot.className = 'aa-fabric-hotspot';
                galleryHotspot.style.left = (galleryRect.left / canvasWidth * 100) + '%';
                galleryHotspot.style.top = (galleryRect.top / canvasHeight * 100) + '%';
                galleryHotspot.style.width = (galleryRect.width / canvasWidth * 100) + '%';
                galleryHotspot.style.height = (galleryRect.height / canvasHeight * 100) + '%';
                galleryHotspot.setAttribute('aria-label', 'Zoom foto gallery');
                galleryHotspot.addEventListener('click', function () {
                    openGalleryLightbox(object.galleryImageSrc || object.src || (object._element && object._element.src) || '');
                });
                layer.appendChild(galleryHotspot);
            }

            if (!object.link && !object.copyText) return;

            var rect = object.getBoundingRect(true, true);
            var hotspot = object.link ? document.createElement('a') : document.createElement('button');
            hotspot.className = 'aa-fabric-hotspot';
            hotspot.style.left = (rect.left / canvasWidth * 100) + '%';
            hotspot.style.top = (rect.top / canvasHeight * 100) + '%';
            hotspot.style.width = (rect.width / canvasWidth * 100) + '%';
            hotspot.style.height = (rect.height / canvasHeight * 100) + '%';
            hotspot.setAttribute('aria-label', object.link ? 'Buka link' : 'Copy text');

            if (object.link) {
                hotspot.href = normalizeUrl(object.link);
                hotspot.target = '_blank';
                hotspot.rel = 'noopener';
            } else {
                hotspot.type = 'button';
                hotspot.addEventListener('click', function () {
                    copyToClipboard(object.copyText, object.copyFeedback || 'Tersalin');
                });
            }

            layer.appendChild(hotspot);
        });
    }
    function isGuestbookObject(object) {
        return object && [
            'guest-name-input',
            'guest-attendance-select',
            'guest-message-textarea',
            'guest-sticker-picker',
            'guest-submit-button',
            'guest-comment-list'
        ].indexOf(object.customType) !== -1;
    }
    function guestbookParts(object) {
        var children = object && object.getObjects ? object.getObjects() : [];
        var box = null;
        var text = null;
        children.forEach(function (child) {
            if (!box && (child.name === 'guestbook-box' || child.type === 'rect')) box = child;
            if (!text && (child.name === 'guestbook-text' || isTextObject(child))) text = child;
        });
        return { box: box, text: text };
    }
    function getGuestbookEndpoint() {
        if (window.AdaAcaraGuestbookEndpoint) return window.AdaAcaraGuestbookEndpoint;
        if (fabricData.guestbookEndpoint) return fabricData.guestbookEndpoint;
        var match = window.location.pathname.match(/\\/u\\/([^\\/]+)/);
        return match ? '/u/' + match[1] + '/guestbook' : '';
    }
    function addGuestbookCsrf(formData) {
        var csrf = window.AdaAcaraGuestbookCsrf || {};
        if (csrf.name && csrf.hash) formData.append(csrf.name, csrf.hash);
    }
    function updateGuestbookCsrf(hash) {
        if (!hash || !window.AdaAcaraGuestbookCsrf) return;
        window.AdaAcaraGuestbookCsrf.hash = hash;
    }
    function stickerUrl(file) {
        file = String(file || '').replace(/[^a-z0-9.]/gi, '');
        if (!file) return '';
        return (window.AdaAcaraStickerBase || '/assets/stiker/') + file;
    }
    function commentCard(comment) {
        var card = document.createElement('article');
        card.className = 'aa-fabric-comment-card';
        var name = document.createElement('strong');
        name.textContent = comment.guest_name || '';
        var meta = document.createElement('div');
        meta.style.cssText = 'font-size:12px;opacity:.72;margin:3px 0 8px;';
        meta.textContent = comment.attendance === 'tidak_hadir' ? 'Tidak hadir' : comment.attendance === 'hadir' ? 'Hadir' : 'Ragu';
        var body = document.createElement('div');
        body.style.cssText = 'display:grid;gap:8px;white-space:pre-wrap;';
        if (comment.sticker_url) {
            var img = document.createElement('img');
            img.src = comment.sticker_url;
            img.alt = 'Sticker';
            img.loading = 'lazy';
            img.style.cssText = 'width:48px;height:48px;object-fit:contain;';
            body.appendChild(img);
        }
        var message = document.createElement('p');
        message.textContent = comment.message || '';
        message.style.margin = '0';
        body.appendChild(message);
        card.append(name, meta, body);
        return card;
    }
    function populateCommentLists(layer) {
        var comments = Array.isArray(window.AdaAcaraGuestbookEntries) ? window.AdaAcaraGuestbookEntries : [];
        layer.querySelectorAll('[data-aa-comment-list]').forEach(function (list) {
            list.innerHTML = '';
            if (!comments.length) {
                var empty = document.createElement('div');
                empty.className = 'aa-fabric-comment-empty';
                empty.textContent = 'Belum ada ucapan. Jadilah yang pertama mengisi guestbook.';
                list.appendChild(empty);
                return;
            }
            comments.forEach(function (comment) {
                list.appendChild(commentCard(comment));
            });
        });
    }
    function setupStickerPicker(wrapper, hiddenInput, preview) {
        var popover = document.createElement('div');
        popover.className = 'aa-fabric-sticker-popover';
        var prevButton = document.createElement('button');
        prevButton.type = 'button';
        prevButton.className = 'aa-fabric-sticker-nav';
        prevButton.setAttribute('aria-label', 'Stiker sebelumnya');
        prevButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>';

        var track = document.createElement('div');
        track.className = 'aa-fabric-sticker-track';

        var nextButton = document.createElement('button');
        nextButton.type = 'button';
        nextButton.className = 'aa-fabric-sticker-nav';
        nextButton.setAttribute('aria-label', 'Stiker berikutnya');
        nextButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';

        function scrollStickerTrack(direction) {
            var amount = Math.max(86, Math.round(track.clientWidth * .75));
            track.scrollBy({
                left: direction * amount,
                behavior: 'smooth'
            });
        }
        prevButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            scrollStickerTrack(-1);
        });
        nextButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            scrollStickerTrack(1);
        });

        function setSelected(file, src) {
            hiddenInput.value = file || '';
            track.querySelectorAll('[data-sticker]').forEach(function (button) {
                button.classList.toggle('is-selected', button.dataset.sticker === file);
            });
            if (!preview) return;
            var img = preview.querySelector('img');
            if (img) img.src = src || '';
            preview.classList.toggle('is-visible', Boolean(file));
        }
        for (var i = 1; i <= 34; i += 1) {
            var file = 'sticker' + String(i).padStart(3, '0') + '.gif';
            var choice = document.createElement('button');
            choice.type = 'button';
            choice.className = 'aa-fabric-sticker-choice';
            choice.dataset.sticker = file;
            var img = document.createElement('img');
            img.src = stickerUrl(file);
            img.alt = 'Sticker';
            choice.appendChild(img);
            choice.addEventListener('click', function (event) {
                var selected = event.currentTarget.querySelector('img');
                var selectedFile = event.currentTarget.dataset.sticker || '';
                setSelected(selectedFile, selected ? selected.src : '');
                popover.classList.remove('is-open');
            });
            track.appendChild(choice);
        }
        popover.append(prevButton, track, nextButton);
        popover.__aaSetSelected = setSelected;
        wrapper.appendChild(popover);
        return popover;
    }
    function guestbookControlStyle(object, canvas) {
        var parts = guestbookParts(object);
        var rect = object.getBoundingRect(true, true);
        var box = parts.box || {};
        var text = parts.text || {};
        var scaleX = Math.abs(object.scaleX || 1);
        var scaleY = Math.abs(object.scaleY || 1);
        var canvasWidth = Math.max(1, canvas.getWidth() || 1080);
        var boxLeft = Number(box.left || 0);
        var boxTop = Number(box.top || 0);
        var textLeft = Number(text.left || 0);
        var textTop = Number(text.top || 0);
        var isCenteredButton = object.customType === 'guest-submit-button';
        var fontSize = Math.max(6, Number(text.fontSize || 32) * scaleY);
        var paddingX = isCenteredButton ? 0 : Math.max(0, (textLeft - boxLeft) * scaleX);
        var paddingY = isCenteredButton ? 0 : Math.max(0, (textTop - boxTop) * scaleY);
        var fontViewport = Math.max(2, fontSize / canvasWidth * 100);
        var radius = Math.max(0, Number(box.rx || box.ry || 0) * Math.max(scaleX, scaleY));
        var radiusViewport = Math.max(0, radius / canvasWidth * 100);
        var borderWidth = Math.max(0, Number(box.strokeWidth || 1) * Math.max(scaleX, scaleY));
        var borderViewport = Math.max(0, borderWidth / canvasWidth * 100);
        if (!paddingX) paddingX = isCenteredButton ? 0 : 26 * scaleX;
        if (!paddingY) paddingY = isCenteredButton ? 0 : 18 * scaleY;
        var paddingXViewport = Math.max(0, paddingX / canvasWidth * 100);
        var paddingYViewport = Math.max(0, paddingY / canvasWidth * 100);
        paddingX = isCenteredButton ? '0px' : 'clamp(8px, ' + paddingXViewport + 'vw, 16px)';
        paddingY = isCenteredButton ? '0px' : 'clamp(5px, ' + paddingYViewport + 'vw, 12px)';
        return {
            left: (rect.left / canvas.getWidth() * 100) + '%',
            top: (rect.top / canvas.getHeight() * 100) + '%',
            width: (rect.width / canvas.getWidth() * 100) + '%',
            height: (rect.height / canvas.getHeight() * 100) + '%',
            borderRadius: 'clamp(4px, ' + radiusViewport + 'vw, ' + Math.max(4, radius) + 'px)',
            background: box.fill || '#ffffff',
            borderColor: box.stroke || '#cbd5e1',
            borderWidth: 'clamp(1px, ' + borderViewport + 'vw, ' + Math.max(1, Math.min(8, borderWidth)) + 'px)',
            color: text.fill || '#334155',
            fontFamily: normalizeFontFamily(text.fontFamily),
            fontSize: 'clamp(10px, ' + fontViewport + 'vw, 18px)',
            fontWeight: text.fontWeight || 'normal',
            textAlign: text.textAlign || 'left',
            lineHeight: Number(text.lineHeight || 1.14),
            paddingX: paddingX,
            paddingY: paddingY,
            angle: object.angle || 0,
        };
    }
    function applyControlStyle(el, style) {
        el.style.left = style.left;
        el.style.top = style.top;
        el.style.width = style.width;
        el.style.height = style.height;
        el.style.borderRadius = style.borderRadius;
        el.style.background = style.background;
        el.style.color = style.color;
        el.style.fontFamily = style.fontFamily;
        el.style.fontSize = style.fontSize;
        el.style.fontWeight = style.fontWeight;
        el.style.textAlign = style.textAlign;
        el.style.setProperty('--aa-field-line-height', style.lineHeight);
        el.style.setProperty('--aa-field-padding-x', style.paddingX);
        el.style.setProperty('--aa-field-padding-y', style.paddingY);
        el.style.setProperty('--aa-field-border-color', style.borderColor);
        el.style.setProperty('--aa-field-border-width', style.borderWidth);
        var baseTransform = style.angle ? 'rotate(' + style.angle + 'deg)' : 'rotate(0deg)';
        el.style.setProperty('--aa-overlay-base-transform', baseTransform);
        el.style.transform = baseTransform;
        el.style.transformOrigin = 'center center';
    }
    function applyOverlayAnimation(el, object) {
        var animationName = getObjectAnimationName(object);
        if (!animationName || animationName === 'none') return;
        var safeName = String(animationName).toLowerCase().replace(/[^a-z0-9-]/g, '');
        var duration = Number(object.aaAnimationDuration != null ? object.aaAnimationDuration : object.animationDuration);
        var delay = Number(object.aaAnimationDelay != null ? object.aaAnimationDelay : object.animationDelay);
        el.classList.add('aa-fabric-overlay-animated', 'aa-fabric-overlay-animation-waiting', 'aa-overlay-animation-' + safeName);
        el.dataset.aaOverlayAnimation = safeName;
        el.style.setProperty('--aa-overlay-animation-duration', (isFinite(duration) && duration > 0 ? duration : 900) + 'ms');
        el.style.setProperty('--aa-overlay-animation-delay', (isFinite(delay) && delay > 0 ? delay : 0) + 'ms');
    }
    function setupGuestbookOverlay(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard) return;
        var old = artboard.querySelector('.aa-fabric-guestbook-layer');
        if (old) old.remove();
        var guestObjects = canvas.getObjects().filter(isGuestbookObject);
        if (!guestObjects.length) return;

        var layer = document.createElement('form');
        layer.className = 'aa-fabric-guestbook-layer';
        layer.action = getGuestbookEndpoint() || '#';
        layer.method = 'post';
        layer.noValidate = true;
        var stickerInput = document.createElement('input');
        stickerInput.type = 'hidden';
        stickerInput.name = 'sticker';
        layer.appendChild(stickerInput);

        guestObjects.forEach(function (object) {
            var control = document.createElement('div');
            control.className = 'aa-fabric-guestbook-control';
            control.dataset.guestbookRole = object.customType || '';
            applyControlStyle(control, guestbookControlStyle(object, canvas));
            applyOverlayAnimation(control, object);
            var placeholder = object.placeholder || object.label || '';

            if (object.customType === 'guest-name-input') {
                var input = document.createElement('input');
                input.name = object.fieldName || 'guest_name';
                input.placeholder = placeholder || 'Nama';
                input.maxLength = Number(object.maxLength) || 120;
                input.required = object.required !== false;
                control.appendChild(input);
            } else if (object.customType === 'guest-attendance-select') {
                var select = document.createElement('select');
                select.name = object.fieldName || 'attendance';
                select.required = object.required !== false;
                var empty = document.createElement('option');
                empty.value = '';
                empty.textContent = placeholder || 'Pilih Kehadiran';
                select.appendChild(empty);
                (Array.isArray(object.options) && object.options.length ? object.options : ['hadir:Hadir', 'tidak_hadir:Tidak hadir', 'ragu:Ragu']).forEach(function (item) {
                    var parts = String(item).split(':');
                    var option = document.createElement('option');
                    option.value = parts[0] || item;
                    option.textContent = parts[1] || item;
                    select.appendChild(option);
                });
                control.appendChild(select);
            } else if (object.customType === 'guest-message-textarea') {
                var textarea = document.createElement('textarea');
                textarea.name = object.fieldName || 'message';
                textarea.placeholder = placeholder || 'Tulis ucapan...';
                textarea.maxLength = Number(object.maxLength) || 800;
                textarea.required = object.required !== false;
                control.appendChild(textarea);
            } else if (object.customType === 'guest-sticker-picker') {
                var stickerButton = document.createElement('button');
                stickerButton.type = 'button';
                stickerButton.textContent = placeholder || 'Stiker';
                var selectedPreview = document.createElement('span');
                selectedPreview.className = 'aa-fabric-selected-sticker';
                selectedPreview.innerHTML = '<img src="" alt="Stiker terpilih"><span>Stiker dipilih</span>';
                var clearSticker = document.createElement('button');
                clearSticker.type = 'button';
                clearSticker.textContent = 'X';
                selectedPreview.appendChild(clearSticker);
                control.appendChild(selectedPreview);
                var popover = setupStickerPicker(control, stickerInput, selectedPreview);
                stickerButton.addEventListener('click', function () {
                    popover.classList.toggle('is-open');
                });
                clearSticker.addEventListener('click', function () {
                    if (popover.__aaSetSelected) popover.__aaSetSelected('', '');
                    popover.classList.remove('is-open');
                });
                control.appendChild(stickerButton);
            } else if (object.customType === 'guest-submit-button') {
                var submit = document.createElement('button');
                submit.type = 'submit';
                submit.textContent = object.buttonText || placeholder || 'Kirim Ucapan';
                control.appendChild(submit);
            } else if (object.customType === 'guest-comment-list') {
                var list = document.createElement('div');
                list.className = 'aa-fabric-comment-list';
                list.dataset.aaCommentList = 'true';
                control.appendChild(list);
            }
            layer.appendChild(control);
        });

        layer.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!layer.action || layer.action === '#') {
                showCopyToast('Preview form aktif setelah halaman dipublish.');
                return;
            }
            var formData = new FormData(layer);
            if (!formData.get('attendance')) formData.set('attendance', 'ragu');
            if (!String(formData.get('guest_name') || '').trim()) {
                showCopyToast('Nama wajib diisi.');
                return;
            }
            if (!String(formData.get('message') || '').trim()) {
                showCopyToast('Ucapan wajib diisi.');
                return;
            }
            addGuestbookCsrf(formData);
            var submit = layer.querySelector('button[type="submit"]');
            var originalText = submit ? submit.textContent : '';
            if (submit) {
                submit.disabled = true;
                submit.textContent = 'Mengirim...';
            }
            fetch(layer.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || data.success === false) throw new Error(data.message || 'Ucapan gagal dikirim.');
                    return data;
                });
            }).then(function (data) {
                updateGuestbookCsrf(data.csrf_hash);
                var comment = data.comment || {};
                window.AdaAcaraGuestbookEntries = window.AdaAcaraGuestbookEntries || [];
                window.AdaAcaraGuestbookEntries.unshift(comment);
                layer.reset();
                stickerInput.value = '';
                layer.querySelectorAll('.aa-fabric-selected-sticker').forEach(function (preview) {
                    preview.classList.remove('is-visible');
                });
                layer.querySelectorAll('.aa-fabric-sticker-popover button').forEach(function (button) {
                    button.classList.remove('is-selected');
                });
                populateCommentLists(layer);
                showCopyToast(data.message || 'Ucapan berhasil dikirim.');
            }).catch(function (error) {
                showCopyToast(error.message || 'Ucapan gagal dikirim.');
            }).finally(function () {
                if (submit) {
                    submit.disabled = false;
                    submit.textContent = originalText;
                }
            });
        });

        artboard.appendChild(layer);
        if (layer.querySelector('.aa-fabric-overlay-animated')) {
            canvas.__aaHasOverlayAnimations = true;
        }
        populateCommentLists(layer);
    }
    function isInteractiveObject(object) {
        return object && ['music-player', 'scroll-next-button', 'countdown-timer', 'photo-gallery', 'youtube-video', 'opening-button'].indexOf(object.customType) !== -1;
    }
    function interactiveParts(object) {
        var children = object && object.getObjects ? object.getObjects() : [];
        var box = null;
        var text = null;
        children.forEach(function (child) {
            if (!box && (child.name === 'interactive-box' || child.type === 'rect')) box = child;
            if (!text && (child.name === 'interactive-text' || isTextObject(child))) text = child;
        });
        return { box: box, text: text };
    }
    function countdownPreviewColumns(object) {
        object = object || {};
        var width = Math.max(80, Number(object.width) || 620);
        var gap = Math.max(0, Number(object.countdownGap) || 0);
        var fontSize = Math.max(8, Number(object.countdownFontSize || object.fontSize) || 36);
        var minCardWidth = Math.max(64, fontSize * 1.8);
        var fourColumnWidth = (minCardWidth * 4) + (gap * 3);
        var twoColumnWidth = (minCardWidth * 2) + gap;

        if (width < twoColumnWidth * 1.05) return 1;
        if (width < fourColumnWidth * 0.96) return 2;
        return 4;
    }
    function interactiveStyle(object, canvas) {
        var parts = interactiveParts(object);
        var rect = object.getBoundingRect(true, true);
        var box = parts.box || {};
        var text = parts.text || {};
        var isCountdown = object.customType === 'countdown-timer';
        var canvasWidth = Math.max(1, canvas.getWidth() || 1080);
        var canvasEl = canvas.lowerCanvasEl || (typeof canvas.getElement === 'function' ? canvas.getElement() : null);
        var artboardEl = canvasEl && typeof canvasEl.closest === 'function' ? canvasEl.closest('.aa-fabric-artboard') : null;
        var renderedWidth = artboardEl ? artboardEl.clientWidth : 0;
        if (!renderedWidth && canvasEl && typeof canvasEl.getBoundingClientRect === 'function') {
            renderedWidth = canvasEl.getBoundingClientRect().width;
        }
        var artboardScale = renderedWidth > 0 ? Math.max(0.05, Math.min(1, renderedWidth / canvasWidth)) : 1;
        var countdownFontSize = Math.max(8, Number(object.countdownFontSize || text.fontSize || 36));
        var radiusBase = Number(object.controlRadius != null ? object.controlRadius : (box.rx || box.ry || 0));
        var radius = Math.max(0, isCountdown ? radiusBase : radiusBase * Math.max(Math.abs(object.scaleX || 1), Math.abs(object.scaleY || 1)));
        var countdownGap = Math.max(0, Number(object.countdownGap) || 0);
        var countdownScaledFontSize = Math.max(8, Math.min(50, countdownFontSize * artboardScale));
        var countdownScaledRadius = Math.max(0, radius * artboardScale);
        var countdownScaledGap = Math.max(0, Math.min(10, countdownGap * artboardScale));
        return {
            left: (rect.left / canvas.getWidth() * 100) + '%',
            top: (rect.top / canvas.getHeight() * 100) + '%',
            width: (rect.width / canvas.getWidth() * 100) + '%',
            height: (rect.height / canvas.getHeight() * 100) + '%',
            radius: radius + 'px',
            bg: object.controlBackground || box.fill || '#ffffff',
            border: box.stroke || '#cbd5e1',
            borderWidth: Math.max(0, Number(box.strokeWidth || 1)) + 'px',
            color: object.countdownTextColor || text.fill || '#0f172a',
            fontFamily: normalizeFontFamily(object.countdownFontFamily || text.fontFamily),
            fontSize: isCountdown
                ? countdownScaledFontSize + 'px'
                : Math.max(10, Math.min(24, Number(text.fontSize || 34) * Math.abs(object.scaleY || 1))) + 'px',
            fontWeight: text.fontWeight || 'bold',
            countdownGap: isCountdown
                ? countdownScaledGap + 'px'
                : countdownGap + 'px',
            countdownColumns: countdownPreviewColumns(object),
            countdownCardRadius: isCountdown
                ? countdownScaledRadius + 'px'
                : radius + 'px',
        };
    }
    function applyInteractiveStyle(el, style) {
        el.style.left = style.left;
        el.style.top = style.top;
        el.style.width = style.width;
        el.style.height = style.height;
        el.style.borderRadius = style.radius;
        el.style.fontFamily = style.fontFamily;
        el.style.fontSize = style.fontSize;
        el.style.fontWeight = style.fontWeight;
        el.style.setProperty('--aa-control-bg', style.bg);
        el.style.setProperty('--aa-control-color', style.color);
        el.style.setProperty('--aa-control-border-color', style.border);
        el.style.setProperty('--aa-control-border-width', style.borderWidth);
        el.style.setProperty('--aa-countdown-gap', style.countdownGap || '8px');
        el.style.setProperty('--aa-countdown-columns', String(style.countdownColumns || 4));
        el.style.setProperty('--aa-countdown-card-radius', style.countdownCardRadius || style.radius);
        el.style.setProperty('--aa-overlay-base-transform', 'rotate(0deg)');
    }
function setupMusicControl(wrapper, object) {
    var url = String(object.audioUrl || '').trim();

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'aa-fabric-music-button';
    button.setAttribute('aria-label', 'Putar musik');
    button.setAttribute('title', 'Putar musik');

    var icon = document.createElement('span');
    icon.className = 'aa-fabric-music-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.6 5.42v13.16c0 .66.72 1.06 1.27.7l10.02-6.58a.84.84 0 0 0 0-1.4L9.87 4.72a.83.83 0 0 0-1.27.7Z"/></svg>';

    button.innerHTML = '';
    button.appendChild(icon);

    if (object.showPlayerButton === false || object.showPlayerButton === 'false') {
        button.style.opacity = '0';
    }

    var audio = document.createElement('audio');
    audio.preload = 'auto';
    audio.setAttribute('playsinline', 'playsinline');
    audio.setAttribute('webkit-playsinline', 'webkit-playsinline');

    var shouldLoop = object.loopAudio !== false && object.loopAudio !== 'false';
    audio.loop = shouldLoop;

    if (shouldLoop) {
        audio.setAttribute('loop', 'loop');
    }

    if (url) {
        audio.src = url;
    }

    function setPlaying(playing) {
        icon.innerHTML = playing
            ? '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.25 5.5A1.25 1.25 0 0 1 8.5 4.25h1.75A1.25 1.25 0 0 1 11.5 5.5v13a1.25 1.25 0 0 1-1.25 1.25H8.5a1.25 1.25 0 0 1-1.25-1.25v-13Zm5.25 0a1.25 1.25 0 0 1 1.25-1.25h1.75a1.25 1.25 0 0 1 1.25 1.25v13a1.25 1.25 0 0 1-1.25 1.25h-1.75a1.25 1.25 0 0 1-1.25-1.25v-13Z"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.6 5.42v13.16c0 .66.72 1.06 1.27.7l10.02-6.58a.84.84 0 0 0 0-1.4L9.87 4.72a.83.83 0 0 0-1.27.7Z"/></svg>';
        button.setAttribute('aria-label', playing ? 'Jeda musik' : 'Putar musik');
        button.setAttribute('title', playing ? 'Jeda musik' : 'Putar musik');
    }

    function playAudio() {
        if (!url) {
            return Promise.reject(new Error('Audio URL kosong.'));
        }

        audio.loop = shouldLoop;

        if (shouldLoop) {
            audio.setAttribute('loop', 'loop');
        }

        return audio.play().then(function () {
            setPlaying(true);
        });
    }

    function pauseAudio() {
        audio.pause();
        setPlaying(false);
    }

    button.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if (!audio.paused) {
            pauseAudio();
            return;
        }

        playAudio().catch(function (error) {
            console.warn('Audio play gagal:', error);

            if (typeof showCopyToast === 'function') {
                showCopyToast('Musik belum bisa diputar. Pastikan file MP3 bisa diakses publik.');
            }
        });
    });

    audio.addEventListener('ended', function () {
        if (shouldLoop) {
            audio.currentTime = 0;
            playAudio().catch(function () {});
            return;
        }

        setPlaying(false);
    });

    if (object.autoplayAfterInteraction !== false && object.autoplayAfterInteraction !== 'false' && url) {
        var hasTriedAutoplay = false;

        function autoplayAfterUserInteraction() {
            if (hasTriedAutoplay) return;
            hasTriedAutoplay = true;

            playAudio().catch(function (error) {
                console.warn('Autoplay setelah interaksi gagal:', error);
                setPlaying(false);
            });

            document.removeEventListener('click', autoplayAfterUserInteraction);
            document.removeEventListener('touchstart', autoplayAfterUserInteraction);
            window.removeEventListener('adaacara:invitation-opened', autoplayAfterUserInteraction);
        }

        document.addEventListener('click', autoplayAfterUserInteraction, { once: true });
        document.addEventListener('touchstart', autoplayAfterUserInteraction, { once: true });
        window.addEventListener('adaacara:invitation-opened', autoplayAfterUserInteraction, { once: true });

        if (window.AdaAcaraPublicInvitationOpened) {
            window.setTimeout(autoplayAfterUserInteraction, 150);
        }
    }

    wrapper.appendChild(audio);
    wrapper.appendChild(button);
}
    function sanitizeYoutubeVideoId(value) {
        var match = String(value || '').match(/[A-Za-z0-9_-]{6,20}/);
        return match ? match[0] : '';
    }
    function extractYoutubeIdFromText(value) {
        var source = String(value || '').trim();
        var markers = ['youtu.be/', 'watch?v=', 'embed/', 'shorts/', 'live/'];
        for (var i = 0; i < markers.length; i++) {
            var marker = markers[i];
            var index = source.indexOf(marker);
            if (index === -1) continue;
            return sanitizeYoutubeVideoId(source.slice(index + marker.length));
        }
        return '';
    }
    function parseYoutubeVideoId(value) {
        var source = String(value || '').trim();
        if (!source) return '';
        if (/^[A-Za-z0-9_-]{6,20}$/.test(source)) return source;
        try {
            var url = new URL(source);
            var host = url.hostname.replace(/^www\./, '');
            if (host === 'youtu.be') return sanitizeYoutubeVideoId(url.pathname.split('/').filter(Boolean)[0] || '');
            if (host.indexOf('youtube.com') !== -1 || host.indexOf('youtube-nocookie.com') !== -1) {
                var watchId = url.searchParams.get('v');
                if (watchId) return sanitizeYoutubeVideoId(watchId);
                var parts = url.pathname.split('/').filter(Boolean);
                for (var i = 0; i < parts.length - 1; i++) {
                    if (['embed', 'shorts', 'live'].indexOf(parts[i]) !== -1) return sanitizeYoutubeVideoId(parts[i + 1]);
                }
            }
        } catch (error) {
            return extractYoutubeIdFromText(source);
        }
        return '';
    }
    function youtubeEmbedUrl(id, options) {
        var params = [
            'controls=1',
            'modestbranding=1',
            'rel=0',
            'playsinline=1',
            'iv_load_policy=3'
        ];
        if (options && options.autoplay) {
            params.push('autoplay=1');
            params.push('mute=1');
        }
        if (options && options.loop) {
            params.push('loop=1');
            params.push('playlist=' + encodeURIComponent(id));
        }
        return 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?' + params.join('&');
    }
    function setupYoutubeAutoplayOnView(wrapper, iframe, id, object) {
        var shouldAutoplay = object.youtubeAutoplayOnView !== false && object.youtubeAutoplayOnView !== 'false';
        if (!shouldAutoplay || !('IntersectionObserver' in window)) return;
        var autoplaySrc = youtubeEmbedUrl(id, {
            autoplay: true,
            loop: object.youtubeLoop !== false && object.youtubeLoop !== 'false'
        });
        var started = false;
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (started || !entry.isIntersecting) return;
                started = true;
                iframe.src = autoplaySrc;
                observer.unobserve(wrapper);
                observer.disconnect();
            });
        }, { threshold: 0.35 });
        observer.observe(wrapper);
    }
    function setupYoutubeControl(wrapper, object) {
        var id = sanitizeYoutubeVideoId(object.youtubeVideoId) || parseYoutubeVideoId(object.youtubeUrl);
        if (!id) {
            var placeholder = document.createElement('div');
            placeholder.className = 'aa-fabric-youtube-placeholder';
            placeholder.textContent = 'Video Youtube belum diatur';
            wrapper.appendChild(placeholder);
            return;
        }
        var iframe = document.createElement('iframe');
        iframe.className = 'aa-fabric-youtube-frame';
        iframe.src = youtubeEmbedUrl(id, {
            autoplay: false,
            loop: object.youtubeLoop !== false && object.youtubeLoop !== 'false'
        });
        iframe.title = object.label || 'Youtube Video';
        iframe.loading = 'lazy';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        wrapper.appendChild(iframe);
        setupYoutubeAutoplayOnView(wrapper, iframe, id, object);
    }
    function safeUrl(value) {
        var source = String(value || '').trim();
        if (!source) return '';
        try {
            var url = new URL(source, window.location.origin);
            if (['http:', 'https:', 'mailto:', 'tel:'].indexOf(url.protocol) === -1) return '';
            return url.href;
        } catch (error) {
            return '';
        }
    }
    function setupSocialMediaControl(wrapper, object) {
        var links = object.socialLinks || {};
        var icons = {
            instagram: ['Instagram', 'IG'],
            tiktok: ['TikTok', 'TT'],
            threads: ['Threads', 'TH'],
            x: ['X', 'X'],
            facebook: ['Facebook', 'FB'],
            youtube: ['YouTube', 'YT']
        };
        var box = document.createElement('div');
        box.className = 'aa-fabric-social-box';
        var title = document.createElement('strong');
        var titleObject = object.getObjects ? object.getObjects().find(function (child) {
            return child && (child.name === 'interactive-title' || child.name === 'social-title' || child.name === 'title');
        }) : null;
        title.textContent = object.socialTitle || 'Ikuti Kami';
        if (titleObject) {
            if (titleObject.fontFamily) title.style.fontFamily = '"' + String(titleObject.fontFamily).replace(/"/g, '') + '", Inter, sans-serif';
            if (titleObject.fontWeight) title.style.fontWeight = titleObject.fontWeight;
            if (titleObject.fontStyle) title.style.fontStyle = titleObject.fontStyle;
            if (titleObject.fill) title.style.color = titleObject.fill;
            if (titleObject.underline) title.style.textDecoration = 'underline';
        }
        var row = document.createElement('div');
        row.className = 'aa-fabric-social-row';
        Object.keys(icons).forEach(function (key) {
            var url = safeUrl(links[key]);
            if (!url) return;
            var link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.className = 'aa-fabric-social-link aa-social-' + key;
            link.setAttribute('aria-label', icons[key][0]);
            link.textContent = icons[key][1];
            row.appendChild(link);
        });
        if (!row.children.length) {
            var empty = document.createElement('span');
            empty.className = 'aa-fabric-social-empty';
            empty.textContent = 'Social media belum diatur';
            row.appendChild(empty);
        }
        box.append(title, row);
        wrapper.appendChild(box);
    }
    function setupStoryMakerControl(wrapper, object) {
        var box = document.createElement('div');
        box.className = 'aa-fabric-story-box';
        var title = document.createElement('strong');
        title.textContent = object.storyTitle || 'Our Story';
        var list = document.createElement('div');
        list.className = 'aa-fabric-story-list';
        var items = Array.isArray(object.storyItems) ? object.storyItems : [];
        items.forEach(function (item) {
            var card = document.createElement('article');
            card.className = 'aa-fabric-story-item';
            var date = document.createElement('small');
            date.textContent = item.date || '';
            var heading = document.createElement('b');
            heading.textContent = item.title || 'Cerita';
            var text = document.createElement('p');
            text.textContent = item.description || '';
            card.append(date, heading, text);
            list.appendChild(card);
        });
        if (!items.length) {
            var empty = document.createElement('p');
            empty.className = 'aa-fabric-story-empty';
            empty.textContent = 'Cerita belum diatur';
            list.appendChild(empty);
        }
        box.append(title, list);
        wrapper.appendChild(box);
    }
    function setupScrollNextControl(wrapper, canvasEl, object) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'aa-fabric-scroll-button';
        button.textContent = object.buttonText || object.label || 'Scroll Down';
        if (object.lockPageScroll !== false) {
            var currentSection = canvasEl.closest('.aa-fabric-page-section');
            if (currentSection) currentSection.classList.add('aa-scroll-step-page');
        }
        var smoothScrollToElement = function (target) {
            if (!target) return;
            var start = window.pageYOffset || document.documentElement.scrollTop || 0;
            var end = target.getBoundingClientRect().top + start;
            var distance = end - start;
            var duration = 920;
            var startTime = null;
            var ease = function (t) {
                return t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
            };
            var step = function (time) {
                if (startTime === null) startTime = time;
                var progress = Math.min(1, (time - startTime) / duration);
                window.scrollTo(0, start + distance * ease(progress));
                if (progress < 1) window.requestAnimationFrame(step);
            };
            window.requestAnimationFrame(step);
        };
        button.addEventListener('click', function () {
            var section = canvasEl.closest('.aa-fabric-page-section');
            var next = section ? section.nextElementSibling : null;
            if (next) smoothScrollToElement(next);
        });
        wrapper.appendChild(button);
    }
    function setupCountdownControl(wrapper, object) {
        var box = document.createElement('div');
        box.className = 'aa-fabric-countdown';
        var labels = ['Hari', 'Jam', 'Menit', 'Detik'];
        labels.forEach(function (label) {
            var item = document.createElement('span');
            item.innerHTML = '<strong>00</strong><small>' + label + '</small>';
            box.appendChild(item);
        });
        var target = new Date(object.countdownTarget || ((object.countdownDate || '') + 'T' + (object.countdownTime || '00:00') + ':00')).getTime();
        if (!Number.isFinite(target)) target = Date.now();
        var tick = function () {
            var diff = Math.max(0, target - Date.now());
            var values = [
                Math.floor(diff / 86400000),
                Math.floor((diff % 86400000) / 3600000),
                Math.floor((diff % 3600000) / 60000),
                Math.floor((diff % 60000) / 1000),
            ];
            box.querySelectorAll('strong').forEach(function (node, index) {
                node.textContent = String(values[index] || 0).padStart(2, '0');
            });
        };
        tick();
        window.setInterval(tick, 1000);
        wrapper.appendChild(box);
    }
    function setupGalleryControl(wrapper, object) {
        var items = Array.isArray(object.galleryItems) && object.galleryItems.length ? object.galleryItems : (Array.isArray(object.galleryImages) ? object.galleryImages : []).map(function (src) { return { src: src }; });
        var gallery = document.createElement('div');
        gallery.className = 'aa-fabric-gallery';
        gallery.style.gridTemplateColumns = 'repeat(' + Math.max(1, Math.min(6, Number(object.galleryColumns) || 2)) + ', 1fr)';
        gallery.style.gap = Math.max(0, Number(object.galleryGap) || 0) + 'px';
        var itemRadius = Math.max(0, Number(object.galleryRadius) || 0) * Math.max(Math.abs(object.scaleX || 1), Math.abs(object.scaleY || 1));
        items.filter(function (item) { return item && item.src; }).forEach(function (item) {
            var button = document.createElement('button');
            button.type = 'button';
            button.style.borderRadius = itemRadius + 'px';
            if (item.aspectRatio) button.style.aspectRatio = String(item.aspectRatio);
            var img = document.createElement('img');
            img.src = item.src;
            img.alt = item.name || 'Gallery';
            img.loading = 'lazy';
            button.appendChild(img);
            button.addEventListener('click', function () { openGalleryLightbox(item.src); });
            gallery.appendChild(button);
        });
        wrapper.appendChild(gallery);
    }
    function openGalleryLightbox(url) {
        var lightbox = document.querySelector('.aa-fabric-lightbox');
        if (!lightbox) {
            lightbox = document.createElement('div');
            lightbox.className = 'aa-fabric-lightbox';
            lightbox.innerHTML = '<button type="button">Close</button><img src="" alt="Preview gallery">';
            document.body.appendChild(lightbox);
            lightbox.addEventListener('click', function (event) {
                if (event.target === lightbox || event.target.tagName === 'BUTTON') lightbox.classList.remove('is-open');
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') lightbox.classList.remove('is-open');
            });
        }
        lightbox.querySelector('img').src = url;
        lightbox.classList.add('is-open');
    }
    function setupInteractiveOverlay(canvasEl, canvas) {
        var artboard = canvasEl.closest('.aa-fabric-artboard');
        if (!artboard) return;
        var old = artboard.querySelector('.aa-fabric-interactive-layer');
        if (old) old.remove();
        var objects = canvas.getObjects().filter(isInteractiveObject);
        if (!objects.length) return;
        var layer = document.createElement('div');
        layer.className = 'aa-fabric-interactive-layer';
        objects.forEach(function (object) {
            var control = document.createElement('div');
            control.className = 'aa-fabric-interactive-control';
            control.dataset.interactiveRole = object.customType || '';
            applyInteractiveStyle(control, interactiveStyle(object, canvas));
            applyOverlayAnimation(control, object);
            if (object.customType === 'music-player') setupMusicControl(control, object);
            if (object.customType === 'youtube-video') setupYoutubeControl(control, object);
            if (object.customType === 'social-media') setupSocialMediaControl(control, object);
            if (object.customType === 'scroll-next-button') setupScrollNextControl(control, canvasEl, object);
            if (object.customType === 'countdown-timer') setupCountdownControl(control, object);
            if (object.customType === 'photo-gallery') setupGalleryControl(control, object);
            layer.appendChild(control);
        });
        artboard.appendChild(layer);
        if (layer.querySelector('.aa-fabric-overlay-animated')) {
            canvas.__aaHasOverlayAnimations = true;
        }
    }
    function setupScrollAnimations(canvasEl, canvas) {
        var section = canvasEl.closest('.aa-fabric-page-section') || canvasEl;
        var clickLayer = canvasEl.closest('.aa-fabric-artboard') ? canvasEl.closest('.aa-fabric-artboard').querySelector('.aa-fabric-click-layer') : null;
        if ((canvas.__aaHasScrollAnimations || canvas.__aaHasTextAnimations || canvas.__aaHasOverlayAnimations) && clickLayer) {
            clickLayer.style.display = 'none';
        }
        var runOnce = function () {
            if (canvas.__aaAnimationsStarted) return;
            canvas.__aaAnimationsStarted = true;
            section.querySelectorAll('.aa-fabric-overlay-animation-waiting').forEach(function (node) {
                node.classList.remove('aa-fabric-overlay-animation-waiting');
            });
            runObjectAnimations(canvas);
            runTextAnimations(canvas);
            ensureTextAnimationsVisibleFallback(canvas);
            if (clickLayer) clickLayer.style.display = '';
        };
        var runWhenOpeningReady = function () {
            if (window.AdaAcaraRunWhenInvitationOpened) {
                window.AdaAcaraRunWhenInvitationOpened(runOnce);
                return;
            }
            runOnce();
        };
        if (!(canvas.__aaHasScrollAnimations || canvas.__aaHasTextAnimations || canvas.__aaHasOverlayAnimations) || !('IntersectionObserver' in window)) {
            runWhenOpeningReady();
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                runWhenOpeningReady();
                observer.disconnect();
            });
        }, {
            threshold: 0.28,
            rootMargin: '0px 0px -8% 0px'
        });
        observer.observe(section);
    }
    function getObjectAnimationName(object) {
        return String(
            (object && (object.aaAnimation || object.customAnimation || object.animationPreset || object.animation || object.animationName)) ||
            'none'
        );
    }
    function isAnimationObject(object) {
        if (!object || object.visible === false) return false;
        if (object.__aaSkipObject === true) return false;
        if (object.customType === 'background') return false;
        if (object.excludeFromAnimation === true) return false;
        if (isGuestbookObject(object) || isInteractiveObject(object)) return false;
        if (isAnimatedGifObject(object)) return false;
        if (isTextObject(object) && aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled) return false;
        var animationName = getObjectAnimationName(object);
        return Boolean(animationName && animationName !== 'none');
    }
    function getAnimationSortedObjects(canvas) {
        if (!canvas || !canvas.getObjects) return [];
        return canvas.getObjects()
            .filter(isAnimationObject)
            .sort(function (a, b) {
                var rectA = a.getBoundingRect(true, true);
                var rectB = b.getBoundingRect(true, true);

                if (Math.abs(rectA.top - rectB.top) > 12) {
                    return rectA.top - rectB.top;
                }

                return rectA.left - rectB.left;
            });
    }
    function getAnimationObjects(canvas) {
        return getAnimationSortedObjects(canvas);
    }
    function prepareScrollAnimatedObjects(canvas) {
        canvas.__aaHasScrollAnimations = false;
        getAnimationObjects(canvas).forEach(function (object) {
            canvas.__aaHasScrollAnimations = true;
            if (!object.__aaAnimationOriginal) {
                object.__aaAnimationOriginal = objectSnapshot(object);
            }
            object.set({ opacity: 0 });
            object.dirty = true;
        });
        canvas.requestRenderAll();
    }

    function aaNormalizeTextAnimationConfig(value) {
        var source = value && typeof value === 'object' ? value : {};
        var allowed = ['typewriter', 'letter-fade-up', 'letter-wave', 'word-reveal', 'text-glow', 'shine-text'];
        var type = allowed.indexOf(source.type) !== -1 ? source.type : 'none';
        var enabled = source.enabled === true && type !== 'none';
        var clamp = function (number, min, max, fallback) {
            number = Number(number);
            return isFinite(number) ? Math.max(min, Math.min(max, Math.round(number))) : fallback;
        };
        return {
            enabled: enabled,
            type: enabled ? type : 'none',
            delay: clamp(source.delay, 0, 5000, 0),
            duration: clamp(source.duration, 200, 8000, 1200),
            stagger: clamp(source.stagger, 0, 300, 40),
            loop: source.loop === true || type === 'text-glow' || type === 'shine-text'
        };
    }

    function isTextAnimationObject(object) {
        if (!isTextObject(object) || object.visible === false || object.__aaSkipObject === true) return false;
        return aaNormalizeTextAnimationConfig(object.aaTextAnimation).enabled;
    }

    function getTextAnimationObjects(canvas) {
        if (!canvas || !canvas.getObjects) return [];
        return canvas.getObjects().filter(isTextAnimationObject);
    }

    function aaTextAnimationOriginal(object) {
        if (!object.__aaTextAnimationOriginal) {
            object.__aaTextAnimationOriginal = {
                text: object.text || '',
                top: object.top,
                opacity: object.opacity == null ? 1 : object.opacity,
                fill: object.fill,
                shadow: object.shadow || null,
                charSpacing: object.charSpacing || 0
            };
        }
        return object.__aaTextAnimationOriginal;
    }

    function prepareTextAnimatedObjects(canvas) {
        canvas.__aaHasTextAnimations = false;
        getTextAnimationObjects(canvas).forEach(function (object) {
            var config = aaNormalizeTextAnimationConfig(object.aaTextAnimation);
            var original = aaTextAnimationOriginal(object);
            canvas.__aaHasTextAnimations = true;
            if (['typewriter', 'letter-fade-up', 'word-reveal'].indexOf(config.type) !== -1) {
                object.set({ opacity: 0, text: original.text });
            }
            object.dirty = true;
        });
        canvas.requestRenderAll();
    }

    function aaSetTextAnimationText(object, text) {
        object.set('text', text);
        if (typeof object.initDimensions === 'function') object.initDimensions();
        object.setCoords();
        object.dirty = true;
    }

    function runTextRevealAnimation(canvas, object, config, byWord) {
        var original = aaTextAnimationOriginal(object);
        var text = String(original.text || '');
        var units = byWord ? text.split(/(\s+)/) : Array.from(text);
        if (!byWord && units.length > 180) {
            runTextRevealAnimation(canvas, object, Object.assign({}, config, { type: 'word-reveal' }), true);
            return;
        }
        var duration = Math.max(200, config.duration + Math.min(units.length, 80) * config.stagger);
        var start = null;
        var render = function () { canvas.requestRenderAll(); };
        object.set({ opacity: config.type === 'letter-fade-up' ? 0 : original.opacity, top: config.type === 'letter-fade-up' ? original.top + 24 : original.top });
        aaSetTextAnimationText(object, '');
        var step = function (time) {
            if (start === null) start = time;
            var progress = Math.min(1, (time - start) / duration);
            var count = Math.min(units.length, Math.ceil(progress * units.length));
            aaSetTextAnimationText(object, units.slice(0, count).join(''));
            if (config.type === 'letter-fade-up') {
                object.set({ opacity: original.opacity * progress, top: original.top + (24 * (1 - progress)) });
            }
            render();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                object.set({ opacity: original.opacity, top: original.top });
                aaSetTextAnimationText(object, text);
                render();
            }
        };
        requestAnimationFrame(step);
    }

    function runTextMotionAnimation(canvas, object, config) {
        var original = aaTextAnimationOriginal(object);
        var start = null;
        var render = function () { canvas.requestRenderAll(); };
        object.set({ opacity: original.opacity });
        var step = function (time) {
            if (start === null) start = time;
            var progress = Math.min(1, (time - start) / Math.max(200, config.duration));
            var wave = Math.sin(progress * Math.PI * 4);
            object.set({
                top: original.top + wave * 7,
                charSpacing: original.charSpacing + Math.round(wave * 18)
            });
            render();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                object.set({ top: original.top, charSpacing: original.charSpacing });
                render();
            }
        };
        requestAnimationFrame(step);
    }

    function runTextGlowAnimation(canvas, object, config, shine) {
        var original = aaTextAnimationOriginal(object);
        var start = null;
        var baseFill = original.fill || object.fill || '#111827';
        var glowColor = shine ? '#ffffff' : baseFill;
        var render = function () { canvas.requestRenderAll(); };
        object.set({ opacity: original.opacity, text: original.text });
        var step = function (time) {
            if (start === null) start = time;
            var duration = Math.max(400, config.duration);
            var progress = ((time - start) % duration) / duration;
            var pulse = (Math.sin(progress * Math.PI * 2 - Math.PI / 2) + 1) / 2;
            object.set({
                fill: shine && pulse > .52 ? glowColor : baseFill,
                shadow: new fabric.Shadow({
                    color: shine ? 'rgba(255,255,255,.72)' : String(glowColor),
                    blur: Math.round(4 + pulse * (shine ? 18 : 16)),
                    offsetX: 0,
                    offsetY: 0
                })
            });
            render();
            if (config.loop || time - start < duration) {
                requestAnimationFrame(step);
            } else {
                object.set({ fill: baseFill, shadow: original.shadow });
                render();
            }
        };
        requestAnimationFrame(step);
    }

    function runTextAnimations(canvas) {
        getTextAnimationObjects(canvas).forEach(function (object) {
            var config = aaNormalizeTextAnimationConfig(object.aaTextAnimation);
            window.setTimeout(function () {
                if (config.type === 'word-reveal') runTextRevealAnimation(canvas, object, config, true);
                else if (config.type === 'typewriter' || config.type === 'letter-fade-up') runTextRevealAnimation(canvas, object, config, false);
                else if (config.type === 'letter-wave') runTextMotionAnimation(canvas, object, config);
                else if (config.type === 'text-glow') runTextGlowAnimation(canvas, object, config, false);
                else if (config.type === 'shine-text') runTextGlowAnimation(canvas, object, config, true);
            }, config.delay);
        });
    }

    function ensureTextAnimationsVisibleFallback(canvas) {
        var objects = getTextAnimationObjects(canvas);
        if (!objects.length) return;
        objects.forEach(function (object) {
            var config = aaNormalizeTextAnimationConfig(object.aaTextAnimation);
            var original = aaTextAnimationOriginal(object);
            var timeout = Math.max(1800, config.delay + config.duration + Math.min(String(original.text || '').length, 80) * config.stagger + 450);
            window.setTimeout(function () {
                if (!object || object.visible === false || object.__aaSkipObject === true) return;
                var currentText = String(object.text || '');
                var originalText = String(original.text || '');
                var invisible = Number(object.opacity == null ? 1 : object.opacity) <= 0.02;
                var incompleteReveal = originalText && currentText.length < originalText.length && ['typewriter', 'letter-fade-up', 'word-reveal'].indexOf(config.type) !== -1;
                if (!invisible && !incompleteReveal) return;
                object.set({
                    opacity: original.opacity == null ? 1 : original.opacity,
                    top: original.top,
                    text: originalText,
                    fill: object.fill || original.fill
                });
                if (typeof object.initDimensions === 'function') object.initDimensions();
                if (typeof object.setCoords === 'function') object.setCoords();
                object.dirty = true;
                canvas.requestRenderAll();
            }, timeout);
        });
    }

    function objectSnapshot(object) {
        if (object.__aaAnimationOriginal) {
            return {
                left: object.__aaAnimationOriginal.left,
                top: object.__aaAnimationOriginal.top,
                originX: object.__aaAnimationOriginal.originX || object.originX || 'left',
                originY: object.__aaAnimationOriginal.originY || object.originY || 'top',
                opacity: object.__aaAnimationOriginal.opacity == null ? 1 : object.__aaAnimationOriginal.opacity,
                scaleX: object.__aaAnimationOriginal.scaleX || 1,
                scaleY: object.__aaAnimationOriginal.scaleY || 1,
                angle: object.__aaAnimationOriginal.angle || 0,
                shadow: object.__aaAnimationOriginal.shadow || null,
                clipPath: object.__aaAnimationOriginal.clipPath || null
            };
        }
        return {
            left: object.left,
            top: object.top,
            originX: object.originX || 'left',
            originY: object.originY || 'top',
            opacity: object.opacity == null ? 1 : object.opacity,
            scaleX: object.scaleX || 1,
            scaleY: object.scaleY || 1,
            angle: object.angle || 0,
            shadow: object.shadow || null,
            clipPath: object.clipPath || null
        };
    }
    function aaGetAnimationDuration(object, fallback) {
        var value = object && (object.aaAnimationDuration != null ? object.aaAnimationDuration : object.animationDuration);
        value = Number(value);
        return isFinite(value) && value > 0 ? value : fallback;
    }
    function shouldCenterGroupLoopAnimationOrigin(animationName, object) {
        return object && object.type === 'group' && [
            'sway-loop',
            'pulse-loop',
            'heartbeat-loop',
            'spin-loop'
        ].indexOf(animationName) !== -1;
    }
    function centerGroupAnimationOrigin(object) {
        if (!object || typeof object.getCenterPoint !== 'function' || typeof object.setPositionByOrigin !== 'function') {
            return;
        }
        var center = object.getCenterPoint();
        object.set({
            originX: 'center',
            originY: 'center'
        });
        object.setPositionByOrigin(center, 'center', 'center');
        if (typeof object.setCoords === 'function') object.setCoords();
    }
    function runObjectAnimations(canvas) {
        var animatedObjects = getAnimationObjects(canvas);
        animatedObjects.forEach(function (object, index) {
            var animationName = getObjectAnimationName(object);
            var manualDelay = object.animationDelay != null ? object.animationDelay : object.aaAnimationDelay;
            var delay = Number(manualDelay);
            if (object.animationOrderMode !== 'manual' || !isFinite(delay)) {
                object.aaAnimationOrder = index;
                object.aaAnimationDelay = index * 120;
                delay = object.aaAnimationDelay;
            }
            window.setTimeout(function () {
                runSingleAnimation(canvas, object, animationName);
            }, Math.max(0, delay));
        });
    }
    function runSingleAnimation(canvas, object, animationName) {
        if (shouldCenterGroupLoopAnimationOrigin(animationName, object)) {
            centerGroupAnimationOrigin(object);
        }
        var original = objectSnapshot(object);
        var durationFor = function (fallback) { return aaGetAnimationDuration(object, fallback); };
        var render = function () { canvas.requestRenderAll(); };
        var finish = function () {
            object.set(original);
            canvas.requestRenderAll();
        };
        if (animationName === 'fade-in') {
            object.set({ opacity: 0 });
            object.animate('opacity', original.opacity, {
                duration: durationFor(650),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (animationName === 'rise') {
            object.set({ top: original.top + 70, opacity: 0 });
            object.animate('top', original.top, {
                duration: durationFor(720),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render
            });
            object.animate('opacity', original.opacity, {
                duration: durationFor(650),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (['fade-up', 'fade-down', 'fade-left', 'fade-right'].indexOf(animationName) !== -1) {
            var fadeOffset = 86;
            var fadeFrom = { opacity: 0, top: original.top, left: original.left };
            if (animationName === 'fade-up') fadeFrom.top = original.top + fadeOffset;
            if (animationName === 'fade-down') fadeFrom.top = original.top - fadeOffset;
            if (animationName === 'fade-left') fadeFrom.left = original.left + fadeOffset;
            if (animationName === 'fade-right') fadeFrom.left = original.left - fadeOffset;
            object.set(fadeFrom);
            object.animate('left', original.left, {
                duration: durationFor(720),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render
            });
            object.animate('top', original.top, {
                duration: durationFor(720),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render
            });
            object.animate('opacity', original.opacity, {
                duration: durationFor(650),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (['slide-up', 'slide-down', 'slide-left', 'slide-right'].indexOf(animationName) !== -1) {
            var slideOffset = 130;
            var slideFrom = { top: original.top, left: original.left, opacity: original.opacity };
            if (animationName === 'slide-up') slideFrom.top = original.top + slideOffset;
            if (animationName === 'slide-down') slideFrom.top = original.top - slideOffset;
            if (animationName === 'slide-left') slideFrom.left = original.left + slideOffset;
            if (animationName === 'slide-right') slideFrom.left = original.left - slideOffset;
            object.set(slideFrom);
            object.animate('left', original.left, {
                duration: durationFor(760),
                easing: fabric.util.ease.easeOutBack,
                onChange: render
            });
            object.animate('top', original.top, {
                duration: durationFor(760),
                easing: fabric.util.ease.easeOutBack,
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (animationName === 'zoom-in') {
            object.set({ scaleX: original.scaleX * .72, scaleY: original.scaleY * .72, opacity: 0 });
            object.animate('scaleX', original.scaleX, {
                duration: durationFor(680),
                easing: fabric.util.ease.easeOutBack,
                onChange: render
            });
            object.animate('scaleY', original.scaleY, {
                duration: durationFor(680),
                easing: fabric.util.ease.easeOutBack,
                onChange: render
            });
            object.animate('opacity', original.opacity, {
                duration: durationFor(520),
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (animationName === 'zoom-out') {
            object.set({ scaleX: original.scaleX * 1.36, scaleY: original.scaleY * 1.36, opacity: 0 });
            object.animate('scaleX', original.scaleX, {
                duration: durationFor(700),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render
            });
            object.animate('scaleY', original.scaleY, {
                duration: durationFor(700),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render
            });
            object.animate('opacity', original.opacity, {
                duration: durationFor(540),
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (animationName === 'flip-in') {
            object.set({ scaleX: Math.max(.01, original.scaleX * .08), opacity: 0 });
            object.animate('scaleX', original.scaleX, {
                duration: durationFor(720),
                easing: fabric.util.ease.easeOutBack,
                onChange: render
            });
            object.animate('opacity', original.opacity, {
                duration: durationFor(520),
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (animationName === 'bounce') {
            object.set({ top: original.top - 50, opacity: original.opacity });
            object.animate('top', original.top, {
                duration: durationFor(780),
                easing: fabric.util.ease.easeOutBounce,
                onChange: render,
                onComplete: finish
            });
            return;
        }
        if (animationName === 'float-loop') {
            object.set({ opacity: original.opacity, top: original.top });
            var floatLoop = function () {
                object.animate('top', original.top - 34, {
                    duration: durationFor(1300),
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('top', original.top + 18, {
                            duration: durationFor(1300),
                            easing: fabric.util.ease.easeInOutSine,
                            onChange: render,
                            onComplete: floatLoop
                        });
                    }
                });
            };
            floatLoop();
            return;
        }
        if (animationName === 'sway-loop') {
            object.set({ opacity: original.opacity, angle: original.angle });
            var swayLoop = function () {
                object.animate('angle', original.angle + 8, {
                    duration: durationFor(950),
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('angle', original.angle - 8, {
                            duration: durationFor(950),
                            easing: fabric.util.ease.easeInOutSine,
                            onChange: render,
                            onComplete: swayLoop
                        });
                    }
                });
            };
            swayLoop();
            return;
        }
        if (animationName === 'pulse-loop' || animationName === 'heartbeat-loop') {
            object.set({ opacity: original.opacity, scaleX: original.scaleX, scaleY: original.scaleY });
            var pulseAmount = animationName === 'heartbeat-loop' ? 1.18 : 1.1;
            var pulseDuration = durationFor(animationName === 'heartbeat-loop' ? 360 : 780);
            var pulseLoop = function () {
                object.animate('scaleX', original.scaleX * pulseAmount, {
                    duration: pulseDuration,
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('scaleX', original.scaleX, {
                            duration: pulseDuration,
                            easing: fabric.util.ease.easeInOutSine,
                            onChange: render
                        });
                    }
                });
                object.animate('scaleY', original.scaleY * pulseAmount, {
                    duration: pulseDuration,
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('scaleY', original.scaleY, {
                            duration: pulseDuration,
                            easing: fabric.util.ease.easeInOutSine,
                            onChange: render,
                            onComplete: pulseLoop
                        });
                    }
                });
            };
            pulseLoop();
            return;
        }
        if (animationName === 'drift-loop') {
            object.set({ opacity: original.opacity, left: original.left });
            var driftLoop = function () {
                object.animate('left', original.left + 28, {
                    duration: durationFor(1200),
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.animate('left', original.left - 18, {
                            duration: durationFor(1200),
                            easing: fabric.util.ease.easeInOutSine,
                            onChange: render,
                            onComplete: driftLoop
                        });
                    }
                });
            };
            driftLoop();
            return;
        }
        if (animationName === 'spin-loop') {
            object.set({ opacity: original.opacity, angle: original.angle });
            var spinLoop = function () {
                object.animate('angle', original.angle + 360, {
                    duration: durationFor(4200),
                    easing: fabric.util.ease.easeInOutSine,
                    onChange: render,
                    onComplete: function () {
                        object.set({ angle: original.angle });
                        spinLoop();
                    }
                });
            };
            spinLoop();
            return;
        }
        if (animationName === 'pulse') {
            object.set({ opacity: original.opacity, scaleX: original.scaleX, scaleY: original.scaleY });
            object.animate('scaleX', original.scaleX * 1.14, {
                duration: durationFor(270),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render,
                onComplete: function () {
                    object.animate('scaleX', original.scaleX, {
                        duration: durationFor(300),
                        easing: fabric.util.ease.easeOutCubic,
                        onChange: render
                    });
                }
            });
            object.animate('scaleY', original.scaleY * 1.14, {
                duration: durationFor(270),
                easing: fabric.util.ease.easeOutCubic,
                onChange: render,
                onComplete: function () {
                    object.animate('scaleY', original.scaleY, {
                        duration: durationFor(300),
                        easing: fabric.util.ease.easeOutCubic,
                        onChange: render,
                        onComplete: finish
                    });
                }
            });
            return;
        }
        if (animationName === 'swing') {
            object.set({ angle: original.angle - 10, opacity: original.opacity });
            object.animate('angle', original.angle + 10, {
                duration: durationFor(290),
                easing: fabric.util.ease.easeInOutSine,
                onChange: render,
                onComplete: function () {
                    object.animate('angle', original.angle, {
                        duration: durationFor(310),
                        easing: fabric.util.ease.easeInOutSine,
                        onChange: render,
                        onComplete: finish
                    });
                }
            });
            return;
        }
        if (animationName === 'spin') {
            object.set({ opacity: original.opacity, angle: original.angle });
            object.animate('angle', original.angle + 360, {
                duration: durationFor(900),
                easing: fabric.util.ease.easeInOutCubic,
                onChange: render,
                onComplete: finish
            });
            return;
        }
        object.set({ opacity: original.opacity });
        canvas.requestRenderAll();
    }
    loadFabric(renderFabric);
})();`;
        }

        function previewGoogleFontUrl(data) {
            const families = typeof collectPreviewFontFamilies === 'function'
                ? collectPreviewFontFamilies(data)
                : ['Inter'];
            const weightRegistry = typeof previewGoogleFontWeights !== 'undefined' ? previewGoogleFontWeights : {};
            const uniqueFamilies = Array.from(new Set((families || [])
                .map(font => String(font || '').replace(/^["']|["']$/g, '').trim())
                .filter(font => font && Object.prototype.hasOwnProperty.call(weightRegistry, font))));
            if (!uniqueFamilies.includes('Inter') && Object.prototype.hasOwnProperty.call(weightRegistry, 'Inter')) {
                uniqueFamilies.unshift('Inter');
            }
            const parts = uniqueFamilies.map(family => {
                const weights = weightRegistry[family];
                const encodedFamily = encodeURIComponent(family).replace(/%20/g, '+');
                return 'family=' + encodedFamily + (weights && weights !== '400' ? ':wght@' + weights : '');
            });

            return parts.length ? 'https://fonts.googleapis.com/css2?' + parts.join('&') + '&display=swap' : '';
        }

        function previewDocument() {
    const data = getCanvasData();
    const fontUrl = previewGoogleFontUrl(data);
    const customFontUrl = (typeof config !== 'undefined' && config.customFontCssUrl)
        ? String(config.customFontCssUrl).replace(/"/g, '&quot;')
        : '';
    
    return `<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <script src="https://unpkg.com/lenis@1.1.20/dist/lenis.min.js"><\/script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css">
    ${fontUrl ? `<link href="${fontUrl}" rel="stylesheet">` : ''}
    ${bunnyFontHeadLinks(data)}
    ${customFontUrl ? `<link href="${customFontUrl}" rel="stylesheet" data-aa-custom-font-css>` : ''}
    <style>
        html.aa-smooth-scroll-active {scroll-behavior: auto !important;}
        html,body{overscroll-behavior-y: none;}      
        @media (pointer: coarse), (max-width: 820px) {
            html,
            body {
                overflow-x: hidden;
                overscroll-behavior-y: auto !important;
                touch-action: auto !important;
                scroll-behavior: smooth;
            }

            .aa-fabric-page {
                scroll-snap-type: y proximity;
            }

            .aa-fabric-page-section {
                scroll-snap-align: start;
                scroll-snap-stop: normal;
            }

            .aa-fabric-artboard,
            .aa-fabric-artboard canvas,
            .aa-fabric-artboard .canvas-container {
                touch-action: pan-y pinch-zoom !important;
            }
        }  
        body{margin:0;font-family:Inter,Arial,sans-serif}
        ${previewOpeningCss()}
        ${publicCss()}
    </style>
</head>
<body>
    ${previewOpeningHtml(data)}
    ${publicHtml()}
    <script>${previewOpeningJs(data)}<\/script>
    <script>${publicJs()}<\/script>   
    <script>
    (function () {
        if (window.__aaSmoothScrollReady) return;
        window.__aaSmoothScrollReady = true;

        function initAaSmoothScroll() {
            if (!window.Lenis) {
                console.warn('[AA SMOOTH SCROLL] Lenis belum dimuat.');
                return;
            }

            const lenis = new Lenis({
                duration: 1.8,
                easing: function (t) {
                    return Math.min(1, 1.001 - Math.pow(2, -10 * t));
                },
                smoothWheel: true,
                wheelMultiplier: 0.72,
                touchMultiplier: 0.85,
                infinite: false,
                autoResize: true
            });

            window.aaLenis = lenis;

            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }

            requestAnimationFrame(raf);

            document.documentElement.classList.add('aa-smooth-scroll-active');

            // Support tombol/anchor scroll seperti #section
            document.addEventListener('click', function (event) {
                const link = event.target.closest('a[href^="#"]');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href === '#') return;

                const target = document.querySelector(href);
                if (!target) return;

                event.preventDefault();

                lenis.scrollTo(target, {
                    offset: 0,
                    duration: 2.2,
                    wheelMultiplier: 0.55,
                    touchMultiplier: 0.70,
                    easing: function (t) {
                        return 1 - Math.pow(1 - t, 4);
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAaSmoothScroll);
        } else {
            initAaSmoothScroll();
        }
    })();
    <\/script>     
</body>
</html>`;
}
