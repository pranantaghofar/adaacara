(function () {
    'use strict';

    if (window.AdaAcaraPublicMotion) return;

    var doc = document.documentElement;
    var gsapRef = null;
    var scrollTriggerRef = null;
    var booted = false;

    var api = {
        disabled: false,
        ready: false,
        refresh: function () {},
        refreshTemplateCards: function () {}
    };

    window.AdaAcaraPublicMotion = api;

    function isAllowedPage() {
        var body = document.body;
        return Boolean(body && (
            body.classList.contains('aa-home') ||
            body.classList.contains('aa-about-page') ||
            body.classList.contains('aa-template-page')
        ));
    }

    function shouldDisableMotion() {
        var connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection || null;
        return !isAllowedPage() ||
            window.AdaAcaraLiteMode === true ||
            (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) ||
            (connection && connection.saveData === true);
    }

    function dependenciesReady() {
        return Boolean(window.gsap && window.ScrollTrigger);
    }

    function getLenisInstance() {
        return window.aaHomeLenis ||
            window.aaTemplateLenis ||
            window.aaPublicLenis ||
            window.__aaPublicMotionLenis ||
            null;
    }

    function initLenisIfNeeded() {
        var existing = getLenisInstance();
        if (existing || !window.Lenis) return existing;

        var lenis = new window.Lenis({
            duration: 1.05,
            easing: function (t) {
                return Math.min(1, 1.001 - Math.pow(2, -10 * t));
            },
            smoothWheel: true,
            wheelMultiplier: 0.9,
            touchMultiplier: 1.1
        });

        window.__aaPublicMotionLenis = lenis;

        function raf(time) {
            if (window.__aaPublicMotionLenis) window.__aaPublicMotionLenis.raf(time);
            window.requestAnimationFrame(raf);
        }

        window.requestAnimationFrame(raf);
        return lenis;
    }

    function wireLenisToScrollTrigger() {
        var lenis = initLenisIfNeeded();
        if (!lenis || typeof lenis.on !== 'function') return;
        if (lenis.__aaScrollTriggerBound) return;
        lenis.__aaScrollTriggerBound = true;
        lenis.on('scroll', scrollTriggerRef.update);
    }

    function visibleElements(selector, root) {
        return Array.from((root || document).querySelectorAll(selector)).filter(function (element) {
            if (!element || element.hidden || element.classList.contains('hidden')) return false;
            return element.offsetParent !== null || element.getClientRects().length > 0;
        });
    }

    function splitHeading(element) {
        if (!element || element.dataset.aaMotionSplit === '1' || element.dataset.aaLetterReady === '1') return [];
        var label = (element.textContent || '').replace(/\s+/g, ' ').trim();
        if (!label || label.length > 170) return [];

        element.dataset.aaMotionSplit = '1';
        element.setAttribute('aria-label', label);

        var fragment = document.createDocumentFragment();
        var chars = [];

        label.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) {
                fragment.appendChild(document.createTextNode(part));
                return;
            }

            var word = document.createElement('span');
            word.className = 'aa-motion-word';
            word.setAttribute('aria-hidden', 'true');
            word.style.display = 'inline-block';
            word.style.overflow = 'hidden';
            word.style.verticalAlign = 'baseline';

            Array.from(part).forEach(function (letter) {
                var char = document.createElement('span');
                char.className = 'aa-motion-char';
                char.textContent = letter;
                char.style.display = 'inline-block';
                char.style.willChange = 'transform, opacity';
                word.appendChild(char);
                chars.push(char);
            });

            fragment.appendChild(word);
        });

        element.textContent = '';
        element.appendChild(fragment);
        return chars;
    }

    function animateSplitHeadings() {
        var selectors = [
            '.aa-about-hero h1',
            '.aa-about-section-title',
            '.aa-template-shell h1',
            '.aa-home-hero h1',
            '.aa-home-section-head h2'
        ].join(',');

        visibleElements(selectors).forEach(function (heading) {
            var chars = splitHeading(heading);
            if (!chars.length || heading.dataset.aaMotionSplitAnimated === '1') return;
            heading.dataset.aaMotionSplitAnimated = '1';

            gsapRef.from(chars, {
                yPercent: 112,
                opacity: 0,
                rotateX: -42,
                transformOrigin: '50% 100%',
                duration: 0.82,
                ease: 'power3.out',
                stagger: 0.014,
                scrollTrigger: {
                    trigger: heading,
                    start: 'top 88%',
                    once: true
                }
            });
        });
    }

    function revealElements() {
        var selectors = [
            '.aa-home-eyebrow',
            '.aa-home-hero p',
            '.aa-home-hero-actions',
            '.aa-home-radiant-card',
            '.aa-home-section-head',
            '.aa-home-feature-detail',
            '.aa-home-path-card',
            '.aa-home-card',
            '.aa-about-hero > div',
            '.aa-about-visual',
            '.aa-about-panel',
            '.aa-about-mini-card',
            '.aa-about-doc-card',
            '.aa-about-trust-card',
            '.aa-template-shell > section:first-child',
            '.aa-template-filter',
            '.aa-template-pagination'
        ].join(',');

        visibleElements(selectors).forEach(function (element) {
            if (element.dataset.aaMotionReveal === '1') return;
            element.dataset.aaMotionReveal = '1';

            gsapRef.from(element, {
                y: 48,
                opacity: 0,
                scale: 0.985,
                duration: 0.86,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 90%',
                    once: true
                }
            });
        });
    }

    function animateTemplateCards(root) {
        var cards = visibleElements([
            '[data-template-card]',
            '[data-home-template-card]',
            '.aa-template-card'
        ].join(','), root);

        cards.forEach(function (card, index) {
            if (card.dataset.aaMotionCard === '1') return;
            card.dataset.aaMotionCard = '1';

            gsapRef.from(card, {
                y: 44,
                opacity: 0,
                scale: 0.975,
                duration: 0.68,
                delay: Math.min(index, 10) * 0.04,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: card,
                    start: 'top 94%',
                    once: true
                }
            });
        });
    }

    function refreshTemplateCards(root) {
        visibleElements('[data-template-card], [data-home-template-card], .aa-template-card', root).forEach(function (card) {
            card.dataset.aaMotionCard = '';
        });
        animateTemplateCards(root || document);
        scrollTriggerRef.refresh();
    }

    function initMotion() {
        if (booted) return;
        booted = true;

        gsapRef = window.gsap;
        scrollTriggerRef = window.ScrollTrigger;
        gsapRef.registerPlugin(scrollTriggerRef);

        api.disabled = false;
        api.ready = true;
        doc.classList.remove('aa-motion-off', 'aa-motion-deps-missing');
        doc.classList.add('aa-motion-ready');

        api.refresh = function () {
            animateSplitHeadings();
            revealElements();
            animateTemplateCards(document);
            scrollTriggerRef.refresh();
        };

        api.refreshTemplateCards = refreshTemplateCards;

        wireLenisToScrollTrigger();
        api.refresh();

        window.addEventListener('load', function () {
            window.setTimeout(function () {
                wireLenisToScrollTrigger();
                api.refresh();
            }, 120);
        }, { once: true });
    }

    function boot(attempt) {
        attempt = attempt || 0;

        if (shouldDisableMotion()) {
            api.disabled = true;
            doc.classList.add('aa-motion-off');
            return;
        }

        if (!dependenciesReady()) {
            if (attempt < 60) {
                window.setTimeout(function () {
                    boot(attempt + 1);
                }, 50);
                return;
            }
            doc.classList.add('aa-motion-deps-missing');
            return;
        }

        initMotion();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            boot(0);
        }, { once: true });
    } else {
        boot(0);
    }
})();
