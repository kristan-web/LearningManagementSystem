// Landing page motion (resources/views/landing.blade.php).
// The hero plays an intro on load, then keeps looping (photo slideshow, underline
// redraw, face wave, drifting decorations, pointer parallax).
// Every other section has its own entrance that
// replays whenever it scrolls into view, in either direction:
//   headings    lines rise, amber underline draws
//   strands     cards swing open like doors, icons spin in
//   about       photo wipes in, inset tilts up, chip pops; checklist slides in
//   how         cards converge from the sides, number badges flip
//   requirements dark panel slides from the left, rows from the right
//   voices      portraits wipe upward, quotes slide in from both sides, stars pop
//   stats       panel tips forward in 3D, numbers count up
//   team        portraits are dealt like cards
//   band        banner unfolds from below
//   bulletin    posts rise while their photos zoom out
//   footer      columns fade up
// Blocks tagged data-hero / data-anim are hidden by CSS (.js-anim) until GSAP takes over.
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(ScrollTrigger, SplitText);

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => gsap.utils.toArray(sel, root);

// Plays on the way in (down or up), resets once it has left the top of the
// screen, and rewinds if you scroll back up past where it started.
const replay = (trigger, start = 'top 85%') => ({
    trigger,
    start,
    end: 'bottom top',
    toggleActions: 'play reset play reverse',
});
const scene = (trigger, start) => gsap.timeline({ scrollTrigger: replay(trigger, start) });

const mm = gsap.matchMedia();

mm.add('(prefers-reduced-motion: reduce)', () => {
    gsap.set('[data-hero], [data-hero-deco], [data-anim]', { autoAlpha: 1 });
    gsap.set('[data-progress]', { autoAlpha: 0 });
});

mm.add('(prefers-reduced-motion: no-preference)', () => {
    gsap.defaults({ ease: 'power3.out', duration: 0.9 });
    gsap.set('[data-hero], [data-hero-deco], [data-anim]', { autoAlpha: 1 });

    // ---------------------------------------------------------------- hero intro
    const title = $('[data-hero="title"]');
    const split = SplitText.create(title, { type: 'words', mask: 'words' });
    const marks = $$('.hl', title);
    const faces = $$('[data-hero="trust"] img');
    const floats = $$('[data-float]');
    const panel = $('[data-hero="panel"]');
    const photo = $('[data-hero="photo"]');
    const ring = $('[data-hero-deco="ring"]');
    const dots = $('[data-hero-deco="dots"]');
    const sparks = $$('[data-hero-deco="spark"]');

    gsap.timeline({ delay: 0.1, onComplete: startHeroLoops })
        .from('[data-hero="bar"]', { y: -24, autoAlpha: 0, duration: 0.7 })
        .from('[data-hero="eyebrow"]', { y: 16, scale: 0.9, autoAlpha: 0 }, '-=0.35')
        .from(split.words, {
            yPercent: 120,
            rotationX: -70,
            transformPerspective: 600,
            transformOrigin: '50% 100%',
            stagger: 0.07,
            duration: 0.9,
        }, '-=0.6')
        .fromTo(marks, { '--hl': 0 }, { '--hl': 1, duration: 0.7, ease: 'power2.inOut' }, '-=0.3')
        .from('[data-hero="lede"]', { y: 20, autoAlpha: 0 }, '<-0.2')
        .from(faces, { scale: 0, rotate: -30, stagger: 0.08, duration: 0.5, ease: 'back.out(2)' }, '-=0.5')
        .from('[data-hero="trust"] > span:last-child', { x: -12, autoAlpha: 0, duration: 0.6 }, '<0.1')
        .from(panel, { x: 60, y: 40, rotate: 6, autoAlpha: 0, duration: 1.2 }, 0.25)
        .fromTo(photo,
            { clipPath: 'inset(100% 0% 0% 0% round 30px)', scale: 1.08 },
            { clipPath: 'inset(0% 0% 0% 0% round 30px)', scale: 1, duration: 1.3, ease: 'expo.out' }, 0.35)
        .from(ring, { scale: 0, rotation: -180, autoAlpha: 0, duration: 1.1, ease: 'back.out(1.4)' }, 0.8)
        .from(dots, { scale: 0.4, autoAlpha: 0, duration: 0.9 }, 0.9)
        .from(sparks, { scale: 0, rotation: -90, autoAlpha: 0, stagger: 0.15, duration: 0.6, ease: 'back.out(3)' }, 1)
        .from(floats, { y: 24, scale: 0.6, autoAlpha: 0, stagger: 0.15, duration: 0.7, ease: 'back.out(1.8)' }, '-=0.7');

    // ---------------------------------------------------------------- hero loops
    function startHeroLoops() {
        // Photo slideshow: the hidden layer loads the next shot, fades in over the
        // visible one with a slow Ken Burns zoom, then the two swap roles.
        const slides = $$('[data-slide]', photo);
        const base = slides[0].src.replace(/hero-\d+\.jpg.*$/, '');
        const files = [1, 2, 3, 4, 5].map((n) => `${base}hero-${n}.jpg`);
        files.forEach((src) => { new Image().src = src; });
        let index = Math.max(0, files.indexOf(slides[0].src));
        let [front, back] = slides;
        gsap.set(back, { autoAlpha: 0 });
        gsap.fromTo(front, { scale: 1.12 }, { scale: 1, duration: 6, ease: 'none' });

        const nextSlide = async () => {
            index = (index + 1) % files.length;
            back.src = files[index];
            try { await back.decode(); } catch { /* show it anyway */ }
            gsap.set(back, { zIndex: 2 });
            gsap.set(front, { zIndex: 1 });
            gsap.fromTo(back, { autoAlpha: 0 }, { autoAlpha: 1, duration: 1.2, ease: 'power2.inOut' });
            gsap.fromTo(back, { scale: 1.15 }, { scale: 1, duration: 6.5, ease: 'none' });
            gsap.to(front, { autoAlpha: 0, duration: 1.2, delay: 0.2, ease: 'power2.inOut' });
            [front, back] = [back, front];
            gsap.delayedCall(5, nextSlide);
        };
        gsap.delayedCall(4.5, nextSlide);

        // Underline redraws every few seconds
        gsap.timeline({ repeat: -1, repeatDelay: 4.5, delay: 3 })
            .to(marks, { '--hl': 0, duration: 0.45, ease: 'power2.in' })
            .to(marks, { '--hl': 1, duration: 0.7, ease: 'power2.inOut' });

        // Student faces do a little wave, left to right
        gsap.timeline({ repeat: -1, repeatDelay: 2.5, delay: 1.5 })
            .to(faces, { y: -7, duration: 0.28, stagger: 0.1, ease: 'power2.out' })
            .to(faces, { y: 0, duration: 0.45, stagger: 0.1, ease: 'bounce.out' }, 0.28);

        // Ambient motion around the photo
        gsap.to(panel, { rotate: 2.5, scale: 1.03, duration: 5, ease: 'sine.inOut', yoyo: true, repeat: -1 });
        gsap.to(ring, { rotation: '+=360', duration: 28, ease: 'none', repeat: -1 });
        gsap.to(dots, { y: -14, duration: 4, ease: 'sine.inOut', yoyo: true, repeat: -1 });
        gsap.to(sparks, {
            scale: 1.35,
            rotation: 45,
            duration: 1.6,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            stagger: 0.8,
        });
        floats.forEach((chip, i) => {
            gsap.to(chip, {
                y: -10,
                rotation: i % 2 ? -1.5 : 1.5,
                duration: 2.6 + i * 0.4,
                ease: 'sine.inOut',
                yoyo: true,
                repeat: -1,
            });
        });

        // Pointer parallax: layers shift sideways by depth, the photo tilts toward the cursor
        const media = $('[data-hero="media"]');
        const layers = [[panel, 18], [ring, 30], [dots, 24], ...sparks.map((s) => [s, 40]), ...floats.map((f) => [f, 34])]
            .map(([el, depth]) => [gsap.quickTo(el, 'x', { duration: 0.8, ease: 'power3' }), depth]);
        const tiltY = gsap.quickTo(photo, 'rotationY', { duration: 0.8, ease: 'power3' });
        const tiltX = gsap.quickTo(photo, 'rotationX', { duration: 0.8, ease: 'power3' });
        gsap.set(photo, { transformPerspective: 1000 });

        media.addEventListener('pointermove', (e) => {
            const r = media.getBoundingClientRect();
            const px = (e.clientX - r.left) / r.width - 0.5;
            const py = (e.clientY - r.top) / r.height - 0.5;
            layers.forEach(([to, depth]) => to(px * depth));
            tiltY(px * 8);
            tiltX(py * -8);
        });
        media.addEventListener('pointerleave', () => {
            layers.forEach(([to]) => to(0));
            tiltY(0);
            tiltX(0);
        });
    }

    // Hero depth: panel and photo drift at different speeds as you scroll away
    const heroScroll = { trigger: '[data-hero="media"]', start: 'top 20%', end: 'bottom top', scrub: true };
    gsap.to(panel, { yPercent: -14, ease: 'none', scrollTrigger: heroScroll });
    gsap.to(photo, { yPercent: 6, ease: 'none', scrollTrigger: { ...heroScroll } });

    // ------------------------------------------------------------ scroll progress
    gsap.fromTo('[data-progress]', { scaleX: 0 }, {
        scaleX: 1,
        ease: 'none',
        scrollTrigger: { start: 0, end: 'max', scrub: 0.3 },
    });

    // ----------------------------------------------------------- section headings
    $$('[data-anim="head"]').filter((head) => !head.closest('#requirements')).forEach((head) => {
        const tl = scene(head).from(head.children, { y: 30, autoAlpha: 0, stagger: 0.12 });
        const marks = $$('.hl', head);
        if (marks.length) {
            tl.fromTo(marks, { '--hl': 0 }, { '--hl': 1, duration: 0.6, ease: 'power2.inOut' }, '-=0.45');
        }
    });

    // -------------------------------------------------------------------- strands
    const strands = $$('#strands [data-anim="stagger"] > *');
    scene(strands[0].parentElement)
        .from(strands, {
            rotationY: -80,
            transformPerspective: 900,
            transformOrigin: 'left center',
            autoAlpha: 0,
            stagger: 0.1,
            duration: 1,
        })
        .from(strands.map((card) => card.querySelector('span')), {
            scale: 0,
            rotate: -120,
            stagger: 0.1,
            duration: 0.6,
            ease: 'back.out(2)',
        }, '-=0.8');

    // ---------------------------------------------------------------------- about
    const collage = $('[data-anim="collage"]');
    const [main, inset, chip] = collage.children;
    scene(collage, 'top 80%')
        .fromTo(main,
            { clipPath: 'inset(0% 100% 0% 0% round 28px)' },
            { clipPath: 'inset(0% 0% 0% 0% round 28px)', duration: 1.2, ease: 'expo.out' })
        .from(inset, { y: 60, rotate: 8, autoAlpha: 0, duration: 0.9 }, '-=0.8')
        .from(chip, { x: -40, scale: 0.8, autoAlpha: 0, duration: 0.7, ease: 'back.out(1.7)' }, '-=0.5');

    const checks = $$('#about [data-anim="stagger"] > li');
    scene(checks[0].parentElement, 'top 90%')
        .from(checks, { x: -40, autoAlpha: 0, stagger: 0.08, duration: 0.6 })
        .from(checks.map((li) => li.querySelector('i')), {
            scale: 0,
            stagger: 0.08,
            duration: 0.4,
            ease: 'back.out(3)',
        }, '-=0.5');

    // --------------------------------------------------------------- how it works
    const steps = $$('#how [data-anim="stagger"] > *');
    scene(steps[0].parentElement)
        .from(steps, {
            x: (i) => [-120, 0, 120][i] ?? 0,
            y: (i) => (i === 1 ? 80 : 0),
            autoAlpha: 0,
            stagger: 0.12,
            duration: 1,
        })
        .from(steps.map((step) => step.children[1]), {
            rotationY: 180,
            scale: 0.4,
            transformPerspective: 400,
            stagger: 0.12,
            duration: 0.7,
            ease: 'back.out(1.7)',
        }, '-=0.7');

    // --------------------------------------------------------------- requirements
    const reqAside = $('#requirements [data-anim="head"]');
    const reqRows = $$('#requirements [data-anim="stagger"] > li');
    scene('#requirements', 'top 80%')
        .from(reqAside, { xPercent: -25, autoAlpha: 0, duration: 1 })
        .from(reqAside.children, { y: 20, autoAlpha: 0, stagger: 0.1 }, '-=0.6')
        .from(reqRows, { x: 60, autoAlpha: 0, stagger: 0.09, duration: 0.7 }, 0.2)
        .from(reqRows.map((row) => row.firstElementChild), {
            scale: 0,
            rotate: 45,
            stagger: 0.09,
            duration: 0.5,
            ease: 'back.out(2.5)',
        }, 0.4);

    // --------------------------------------------------------------------- voices
    const [portraitGrid, quoteGrid] = $$('#voices [data-anim="stagger"]');
    const portraits = [...portraitGrid.children];
    scene(portraitGrid)
        .fromTo(portraits,
            { clipPath: 'inset(100% 0% 0% 0% round 24px)', y: 40 },
            { clipPath: 'inset(0% 0% 0% 0% round 24px)', y: 0, stagger: 0.12, duration: 1.1, ease: 'expo.out' })
        .from(portraits.map((fig) => fig.querySelector('figcaption')), {
            yPercent: 100,
            autoAlpha: 0,
            stagger: 0.12,
            duration: 0.6,
        }, '-=0.8');

    const quotes = [...quoteGrid.children];
    scene(quoteGrid)
        .from(quotes, { x: (i) => (i % 2 ? 90 : -90), autoAlpha: 0, stagger: 0.15, duration: 1 })
        .from($$('.text-gold svg', quoteGrid), {
            scale: 0,
            rotate: -90,
            stagger: 0.05,
            duration: 0.4,
            ease: 'back.out(3)',
        }, '-=0.5');

    // ---------------------------------------------------------------------- stats
    const statsPanel = $('[data-count]').closest('[data-anim]');
    const statsTl = scene(statsPanel)
        .from(statsPanel, {
            rotationX: 35,
            scale: 0.88,
            transformPerspective: 1000,
            transformOrigin: 'center bottom',
            autoAlpha: 0,
            duration: 1.1,
        })
        .from(statsPanel.children, { y: 30, autoAlpha: 0, stagger: 0.1, duration: 0.6 }, '-=0.6');

    $$('[data-count]').forEach((el) => {
        const text = el.textContent.trim();
        const match = text.match(/\d+/);
        if (!match) return;
        const counter = { value: 0 };
        el.textContent = text.replace(match[0], '0');
        statsTl.fromTo(counter, { value: 0 }, {
            value: Number(match[0]),
            duration: 1.6,
            ease: 'power2.out',
            onUpdate: () => { el.textContent = text.replace(match[0], Math.round(counter.value)); },
        }, 0.5);
    });

    // ----------------------------------------------------------------------- team
    const members = $$('#team [data-anim="stagger"] > *');
    scene(members[0].parentElement).from(members, {
        y: 140,
        rotation: (i) => (i - (members.length - 1) / 2) * 12,
        transformOrigin: '50% 120%',
        autoAlpha: 0,
        stagger: 0.1,
        duration: 1,
        ease: 'back.out(1.3)',
    });

    // ----------------------------------------------------------------------- band
    $$('[data-anim="pop"]').forEach((band) => {
        scene(band)
            .from(band, {
                rotationX: -50,
                scale: 0.85,
                transformPerspective: 900,
                transformOrigin: 'center top',
                autoAlpha: 0,
                duration: 1.1,
                ease: 'expo.out',
            })
            .from(band.children, { y: 24, autoAlpha: 0, stagger: 0.12, duration: 0.6 }, '-=0.6');
    });

    // ------------------------------------------------------------------- bulletin
    const posts = $$('#bulletin [data-anim="stagger"] > *');
    scene(posts[0].parentElement)
        .from(posts, { y: 90, autoAlpha: 0, stagger: 0.15, duration: 1 })
        .fromTo(posts.map((post) => post.querySelector('img')), { scale: 1.35 }, { scale: 1, stagger: 0.15, duration: 1.4 }, 0)
        .from(posts.map((post) => post.querySelector('img + span')), { x: -24, autoAlpha: 0, stagger: 0.15, duration: 0.5 }, 0.5);

    // --------------------------------------------------------------------- footer
    const footer = $('footer[data-anim]');
    scene(footer, 'top 95%').from(footer.children, { y: 30, autoAlpha: 0, stagger: 0.1, duration: 0.7 });

    return () => split.revert();
});
