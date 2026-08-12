const initLottieAnimation = (root = document) => {
    const load = async (scope = root) => {
        const canvases = scope.querySelectorAll("canvas[data-canvas-type='lottie']");
        const { DotLottie } = await import("https://cdn.jsdelivr.net/npm/@lottiefiles/dotlottie-web/+esm");
        canvases.forEach((canvas) => {
            const src = canvas.getAttribute("data-canvas-src");
            const id = canvas.getAttribute("data-canvas-id");
            const rawData = canvas.getAttribute("data-canvas-data");
            const options = { canvas, autoplay: true, loop: true };
            if (rawData) {
                try {
                    options.data = JSON.parse(rawData);
                } catch (e) {
                    if (src) options.src = src;
                }
            } else if (src) {
                options.src = src;
            }
            const instance = new DotLottie(options);
            window[id] = instance;
        });
    };
    const lazyStart = async () => {
        document.getElementById("lottie-js");
        await load();
    };
    const all = root.querySelectorAll("canvas[data-canvas-type='lottie']");
    if (all && all.length > 0) {
        let lazy = false;
        all.forEach((canvas) => {
            if (canvas.getAttribute("data-canvas-smart-lazy-load")) lazy = true;
        });
        if (lazy) {
            document.body.addEventListener("mouseover", lazyStart, { once: true });
            document.body.addEventListener("touchmove", lazyStart, { once: true });
            window.addEventListener("scroll", lazyStart, { once: true });
            document.body.addEventListener("keydown", lazyStart, { once: true });
        } else {
            lazyStart();
        }
    }
};
initLottieAnimation();
