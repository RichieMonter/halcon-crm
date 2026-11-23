(() => {
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (motion.matches || !('IntersectionObserver' in window)) return;

    const targets = new Map();
    for (const scope of document.querySelectorAll('[data-scroll-reveal]')) {
        for (const section of scope.children) {
            if (section.matches('[data-reveal-group]') || section.querySelector('[data-reveal-group]')) continue;
            const panels = section.querySelector('.fixed') ? section.children : [section];
            for (const panel of panels) {
                if (!panel.matches('.fixed') && !panel.querySelector('.fixed')) targets.set(panel, 0);
            }
        }

        for (const group of scope.querySelectorAll('[data-reveal-group]')) {
            Array.from(group.children).forEach((card, index) => {
                if (!card.matches('template, .fixed') && !card.querySelector('.fixed')) {
                    targets.set(card, (index % 4) * 70);
                }
            });
        }
    }

    const height = document.documentElement.clientHeight;
    const pending = new Map(Array.from(targets).filter(([element]) => {
        const rect = element.getBoundingClientRect();
        return rect.height > 0 && rect.top >= height;
    }));
    if (!pending.size) return;

    const observer = new IntersectionObserver(entries => {
        for (const entry of entries) {
            if (entry.isIntersecting) show(entry.target);
        }
    });

    function show(element) {
        element.classList.remove('kopion-reveal-waiting');
        observer.unobserve(element);
        pending.delete(element);
    }

    for (const [element, delay] of pending) {
        element.style.setProperty('--kopion-reveal-delay', delay + 'ms');
        element.classList.add('kopion-reveal', 'kopion-reveal-waiting');
        observer.observe(element);
    }

    document.addEventListener('focusin', event => {
        const element = event.target.closest('.kopion-reveal-waiting');
        if (element) show(element);
    });

    motion.addEventListener('change', event => {
        if (event.matches) {
            for (const element of pending.keys()) show(element);
        }
    });
})();
