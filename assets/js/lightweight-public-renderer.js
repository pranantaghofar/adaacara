(function () {
  'use strict';

  if (window.AdaAcaraPublicRenderer) return;

  function canUseWebGL() {
    try {
      var canvas = document.createElement('canvas');
      var gl = canvas.getContext('webgl', {
        alpha: true,
        antialias: false,
        preserveDrawingBuffer: false
      }) || canvas.getContext('experimental-webgl');
      if (!gl) return false;
      canvas.addEventListener('webglcontextlost', function (event) {
        event.preventDefault();
        document.documentElement.classList.add('aa-webgl-lost');
      });
      return true;
    } catch (error) {
      return false;
    }
  }

  function getDeviceProfile() {
    var memory = Number(navigator.deviceMemory || 4);
    var cores = Number(navigator.hardwareConcurrency || 4);
    var width = Math.min(window.innerWidth || 1024, screen.width || 1024);
    var dpr = Number(window.devicePixelRatio || 1);
    var reducedMotion = false;
    try {
      reducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch (error) {}

    var webgl = canUseWebGL();
    var lowMemory = memory > 0 && memory <= 2;
    var weakCpu = cores > 0 && cores <= 4;
    var lowEnd = window.AdaAcaraLiteMode === true || lowMemory || (weakCpu && width <= 820);
    var mode = reducedMotion || memory <= 1 ? 'safe' : (lowEnd ? 'balanced' : (webgl ? 'high' : 'balanced'));

    return {
      memory: memory,
      cores: cores,
      width: width,
      webgl: webgl,
      lowEnd: lowEnd,
      reducedMotion: reducedMotion,
      mode: mode,
      dpr: mode === 'safe' ? Math.min(dpr, 1.15) : (mode === 'balanced' ? Math.min(dpr, 1.5) : Math.min(dpr, 2)),
      rootMargin: mode === 'safe' ? '160px 0px 220px 0px' : (mode === 'balanced' ? '280px 0px 420px 0px' : '420px 0px 620px 0px')
    };
  }

  var profile = getDeviceProfile();
  document.documentElement.classList.add('aa-light-renderer-ready', 'aa-render-mode-' + profile.mode);
  document.documentElement.dataset.aaRenderMode = profile.mode;
  window.AdaAcaraLiteMode = window.AdaAcaraLiteMode === true || profile.mode === 'safe';
  window.AdaAcaraDisableSmoothScroll = window.AdaAcaraDisableSmoothScroll === true ||
    profile.mode === 'safe' ||
    profile.reducedMotion === true;

  function requestIdle(callback, timeout) {
    if (typeof callback !== 'function') return;
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(callback, { timeout: timeout || 900 });
      return;
    }
    window.setTimeout(callback, 60);
  }

  function getSections() {
    return Array.prototype.slice.call(document.querySelectorAll('.aa-fabric-page-section'));
  }

  function tuneImage(img) {
    if (!img || img.__aaLightImageTuned) return;
    img.__aaLightImageTuned = true;
    if (!img.hasAttribute('loading')) img.setAttribute('loading', 'lazy');
    if (!img.hasAttribute('decoding')) img.setAttribute('decoding', 'async');
    if (!img.hasAttribute('fetchpriority')) img.setAttribute('fetchpriority', 'low');
  }

  function tuneImages(root) {
    Array.prototype.slice.call((root || document).querySelectorAll('img')).forEach(tuneImage);
  }

  function setSectionActive(section, active) {
    if (!section) return;
    section.classList.toggle('aa-section-visible', active);
    section.classList.toggle('aa-section-paused', !active);
    section.querySelectorAll('[data-aa-animating="1"], .aa-animate, .aa-looping').forEach(function (el) {
      el.style.animationPlayState = active ? 'running' : 'paused';
    });
    var canvas = section.querySelector('canvas');
    var instance = canvas && (canvas.__aaFabricCanvas || canvas.fabric || canvas.__fabric);
    if (instance) {
      instance.__aaPublicPaused = !active;
      if (active && typeof instance.requestRenderAll === 'function') {
        requestIdle(function () {
          if (!instance.__aaPublicPaused) instance.requestRenderAll();
        }, 400);
      }
    }
  }

  function preloadSectionAssets(section) {
    if (!section || section.__aaAssetsPreloaded) return;
    section.__aaAssetsPreloaded = true;
    tuneImages(section);
    Array.prototype.slice.call(section.querySelectorAll('img')).slice(0, profile.mode === 'safe' ? 2 : 6).forEach(function (img) {
      if (img.decode) img.decode().catch(function () {});
    });
  }

  function observeSections() {
    var sections = getSections();
    if (!sections.length) return;

    if (!('IntersectionObserver' in window)) {
      sections.forEach(function (section) {
        section.classList.add('aa-section-visible');
        tuneImages(section);
      });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var section = entry.target;
        var index = Number(section.getAttribute('data-page-index') || section.getAttribute('data-aa-page-index') || 0);
        if (entry.isIntersecting) {
          setSectionActive(section, true);
          preloadSectionAssets(section);
          var next = sections[index + 1];
          if (next) requestIdle(function () { preloadSectionAssets(next); }, 900);
          return;
        }
        setSectionActive(section, false);
      });
    }, {
      rootMargin: profile.rootMargin,
      threshold: 0.01
    });

    sections.forEach(function (section, index) {
      section.setAttribute('data-aa-page-index', String(index));
      observer.observe(section);
    });
  }

  function installBaseStyles() {
    if (document.getElementById('aa-light-public-renderer-style')) return;
    var style = document.createElement('style');
    style.id = 'aa-light-public-renderer-style';
    style.textContent = [
      '.aa-fabric-page-section{content-visibility:auto;contain-intrinsic-size:1px 900px;}',
      '.aa-section-paused *{animation-play-state:paused!important;}',
      '.aa-render-mode-safe .aa-fabric-artboard{box-shadow:none!important;}',
      '.aa-render-mode-safe .aa-fabric-artboard canvas{image-rendering:auto;}'
    ].join('');
    document.head.appendChild(style);
  }

  function init() {
    installBaseStyles();
    tuneImages(document);
    observeSections();
    try {
      document.dispatchEvent(new CustomEvent('adaacara:light-renderer-ready', {
        detail: { profile: profile }
      }));
    } catch (error) {
      var event = document.createEvent('CustomEvent');
      event.initCustomEvent('adaacara:light-renderer-ready', false, false, { profile: profile });
      document.dispatchEvent(event);
    }
  }

  window.AdaAcaraPublicRenderer = {
    profile: profile,
    requestIdle: requestIdle,
    tuneImages: tuneImages,
    preloadSectionAssets: preloadSectionAssets,
    setSectionActive: setSectionActive,
    init: init
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
