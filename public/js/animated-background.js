// Optimized subtle snowfall particle animation
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.createElement('canvas');
    canvas.id = 'animated-bg-canvas';
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.zIndex = '0';
    canvas.style.pointerEvents = 'none';
    canvas.style.mixBlendMode = 'normal';

    document.body.insertBefore(canvas, document.body.firstChild);
    const ctx = canvas.getContext('2d');

    function resizeCanvas() {
        const ratio = window.devicePixelRatio || 1;
        canvas.width = Math.max(1, Math.floor(window.innerWidth * ratio));
        canvas.height = Math.max(1, Math.floor(window.innerHeight * ratio));
        canvas.style.width = window.innerWidth + 'px';
        canvas.style.height = window.innerHeight + 'px';
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function isLightTheme() {
        return (document.body && document.body.classList.contains('light-theme')) ||
               (document.documentElement && document.documentElement.classList.contains('light-theme'));
    }

    // Create two small offscreen sprites (soft circle): one white (for dark mode), one dark (for light mode)
    function createSprite(colorR, colorG, colorB) {
        const s = document.createElement('canvas');
        const size = 32; // small sprite
        s.width = size;
        s.height = size;
        const c = s.getContext('2d');
        const gx = c.createRadialGradient(size/2, size/2, 0, size/2, size/2, size/2);
        gx.addColorStop(0, `rgba(${colorR},${colorG},${colorB},1)`);
        gx.addColorStop(0.4, `rgba(${colorR},${colorG},${colorB},0.6)`);
        gx.addColorStop(1, `rgba(${colorR},${colorG},${colorB},0)`);
        c.fillStyle = gx;
        c.fillRect(0,0,size,size);
        return s;
    }

    const whiteSprite = createSprite(255,255,255);
    const darkSprite = createSprite(50,50,50);

    let particles = [];

    function createParticle(width, height) {
        const size = 0.8 + Math.random() * 3.6; // px radius
        const x = Math.random() * width;
        const y = Math.random() * height; // start inside viewport so particles are visible immediately
        const speed = 0.45 + Math.random() * 1.1; // gentle
        const drift = (Math.random() - 0.5) * 0.6;
        const wobble = 0.6 + Math.random() * 1.6;
        // increase base alpha ranges for better visibility
        const alphaBaseDark = 0.12 + Math.random() * 0.18; // more visible on dark
        const alphaBaseLight = 0.06 + Math.random() * 0.06; // visible on light
        const phase = Math.random() * Math.PI * 2;
        return { x, y, size, speed, drift, wobble, alphaBaseDark, alphaBaseLight, phase };
    }

    function initParticles() {
        const width = window.innerWidth;
        const height = window.innerHeight;
        const area = width * height;
        // lower density to reduce CPU: larger viewports get fewer particles per area
        const count = Math.min(300, Math.max(60, Math.floor(area / 18000)));
        particles = new Array(count);
        for (let i = 0; i < count; i++) particles[i] = createParticle(width, height);
    }

    initParticles();

    // throttle re-init on resize
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            initParticles();
            resizeCanvas();
        }, 250);
    });

    let last = performance.now();

    function draw() {
        const now = performance.now();
        const dt = Math.min(48, now - last) / 16.6667; // clamp dt to avoid jumps
        last = now;

        const width = window.innerWidth;
        const height = window.innerHeight;

        // transparent clear
        ctx.clearRect(0, 0, width, height);

        const light = isLightTheme();
        const sprite = light ? darkSprite : whiteSprite; // dark particles for light mode, white for dark

        // minor performance micro-optimizations: local refs
        const sprW = sprite.width;
        const sprH = sprite.height;
        const pList = particles;
        for (let i = 0, len = pList.length; i < len; i++) {
            const p = pList[i];
            p.phase += 0.002 * p.wobble * dt;
            p.x += p.drift * dt * 0.8 + Math.sin(p.phase) * 0.08 * dt;
            p.y += p.speed * dt;

            const alphaBase = light ? p.alphaBaseLight : p.alphaBaseDark;
            const alpha = Math.max(0.02, alphaBase + Math.sin(p.phase * 0.7) * (alphaBase * 0.3));

            ctx.globalAlpha = alpha;
            const drawSize = Math.max(2, Math.min(16, p.size * 3));
            ctx.drawImage(sprite, 0, 0, sprW, sprH, p.x - drawSize/2, p.y - drawSize/2, drawSize, drawSize);

            if (p.y - p.size > height) {
                // respawn near top with slight randomness
                p.x = Math.random() * width;
                p.y = -Math.random() * 20;
                p.size = 0.6 + Math.random() * 3.0;
                p.speed = 0.4 + Math.random() * 1.0;
                p.drift = (Math.random() - 0.5) * 0.5;
                p.wobble = 0.5 + Math.random() * 1.5;
                p.phase = Math.random() * Math.PI * 2;
            }
        }

        ctx.globalAlpha = 1;
        requestAnimationFrame(draw);
    }

    draw();

    // Observe theme changes (particles check theme each frame, this is just for future hooks)
    const observer = new MutationObserver(() => {});
    observer.observe(document.body, { attributes: true });
});
