// Global Top Page Loader Bar & Navigation Management
(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isBackend = document.body.hasAttribute('data-no-reveal');

    if (!isBackend) {
        const motionCandidates = document.querySelectorAll('main > *, main article, main section, main form, main table, main [role="alert"]');

        motionCandidates.forEach((element, index) => {
            if (!element.hasAttribute('data-reveal')) {
                element.setAttribute('data-reveal', '');
                element.setAttribute('data-reveal-delay', String(index % 4));
            }
        });

        const revealElements = document.querySelectorAll('[data-reveal]');

        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealElements.forEach((element) => element.classList.add('is-visible'));
        } else {
            revealElements.forEach((element) => element.classList.add('hidden-by-reveal'));

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        entry.target.classList.remove('hidden-by-reveal');
                    } else {
                        // Keep elements visible once they've been revealed for better UX
                        // entry.target.classList.remove('is-visible');
                        // entry.target.classList.add('hidden-by-reveal');
                    }
                });
            }, { threshold: 0.05, rootMargin: '0px 0px -50px' });

            revealElements.forEach((element) => revealObserver.observe(element));

            const forceReveal = () => {
                revealElements.forEach((element) => {
                    if (element.classList.contains('is-visible')) return;
                    const rect = element.getBoundingClientRect();
                    if (rect.top < window.innerHeight && rect.bottom > 0) {
                        element.classList.add('is-visible');
                        element.classList.remove('hidden-by-reveal');
                    }
                });
            };

            window.addEventListener('scroll', forceReveal, { passive: true });
            window.addEventListener('resize', forceReveal, { passive: true });
            window.setTimeout(forceReveal, 500);
            window.setTimeout(() => {
                revealElements.forEach((element) => {
                    element.classList.add('is-visible');
                    element.classList.remove('hidden-by-reveal');
                });
            }, 5000);
        }
    }

    document.querySelectorAll('a, button').forEach((element) => element.classList.add('tap-feedback'));

    document.addEventListener('pointerdown', (event) => {
        const target = event.target.closest('a, button');
        if (!target || reduceMotion) return;

        const bounds = target.getBoundingClientRect();
        target.style.setProperty('--tap-x', `${event.clientX - bounds.left}px`);
        target.style.setProperty('--tap-y', `${event.clientY - bounds.top}px`);
        target.classList.add('is-tapped');
        window.setTimeout(() => target.classList.remove('is-tapped'), 480);
    });

    let previousScrollY = window.scrollY;
    let scrollTicking = false;

    window.addEventListener('scroll', () => {
        if (scrollTicking) return;
        scrollTicking = true;

        window.requestAnimationFrame(() => {
            const currentScrollY = window.scrollY;
            if (Math.abs(currentScrollY - previousScrollY) > 2) {
                document.documentElement.dataset.scrollDirection = currentScrollY > previousScrollY ? 'down' : 'up';
                previousScrollY = currentScrollY;
            }
            scrollTicking = false;
        });
    }, { passive: true });

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
        }, 700);
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
            link.origin === window.location.origin &&
            !link.hasAttribute('download')
        ) {
            if (document.documentElement.classList.contains('is-page-leaving')) return;

            e.preventDefault();
            sessionStorage.setItem('is-page-transition', 'true');
            loaderBar.classList.remove('page-loader--done');
            loaderBar.classList.add('page-loader--loading');

            document.documentElement.classList.add('is-page-leaving');
            const navigate = () => window.location.assign(link.href);

            if (reduceMotion) {
                navigate();
            } else {
                window.setTimeout(navigate, 250);
            }
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

    // 4. Handle login transition specifically
    const isLoginPage = document.body.classList.contains('auth-sky') || 
                       window.location.pathname.includes('/login') || 
                       window.location.pathname.includes('/register');
    
    if (isLoginPage && !sessionStorage.getItem('login-transition')) {
        sessionStorage.setItem('login-transition', 'true');
        document.body.style.opacity = '0';
        document.body.style.transform = 'translateY(20px)';
        
        window.addEventListener('load', () => {
            document.body.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            document.body.style.opacity = '1';
            document.body.style.transform = 'translateY(0)';
        });
    }
})();
