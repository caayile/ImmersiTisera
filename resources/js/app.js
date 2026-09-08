// Global Top Page Loader Bar & Navigation Management
(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealElements = document.querySelectorAll('[data-reveal]');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        revealElements.forEach((element) => element.classList.add('is-visible'));
    } else {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px' });

        revealElements.forEach((element) => revealObserver.observe(element));
    }

    document.addEventListener('pointerdown', (event) => {
        const target = event.target.closest('a, button');
        if (!target || reduceMotion) return;

        const bounds = target.getBoundingClientRect();
        target.style.setProperty('--tap-x', `${event.clientX - bounds.left}px`);
        target.style.setProperty('--tap-y', `${event.clientY - bounds.top}px`);
        target.classList.add('is-tapped');
        window.setTimeout(() => target.classList.remove('is-tapped'), 420);
    });

    // 1. Create top page loader element if not present
    let loaderBar = document.getElementById('page-loader-bar');
    if (!loaderBar) {
        loaderBar = document.createElement('div');
        loaderBar.id = 'page-loader-bar';
        loaderBar.className = 'page-loader-bar';
        document.body.prepend(loaderBar);
    }

    // 2. Finish loading bar on page ready
    window.addEventListener('DOMContentLoaded', () => {
        loaderBar.classList.add('page-loader--done');
        setTimeout(() => {
            loaderBar.classList.remove('page-loader--loading', 'page-loader--done');
        }, 500);
    });

    // 3. Listen to link clicks for internal page navigation
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        const target = link.getAttribute('target');

        // Check if internal navigation link
        if (
            href &&
            !href.startsWith('#') &&
            !href.startsWith('javascript:') &&
            !href.startsWith('mailto:') &&
            !href.startsWith('tel:') &&
            (!target || target === '_self') &&
            link.origin === window.location.origin
        ) {
            sessionStorage.setItem('is-page-transition', 'true');
            loaderBar.classList.remove('page-loader--done');
            loaderBar.classList.add('page-loader--loading');
        }
    });

    // Also on GET form submit
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.method && form.method.toLowerCase() === 'get') {
            sessionStorage.setItem('is-page-transition', 'true');
            loaderBar.classList.remove('page-loader--done');
            loaderBar.classList.add('page-loader--loading');
        }
    });
})();
