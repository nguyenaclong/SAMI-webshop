"use strict";
function GSPB_Accordion_Toggle(target) {
    let item = target.closest('.gs-accordion-item');
    let wrapper = target.closest('.gs-accordion');
    let enableScroll = wrapper.getAttribute('data-scroll');
    let contentWrap = item.querySelector('.gs-accordion-item__content');
    if (item.classList.contains('gsopen')) {
        if (wrapper.classList.contains('togglelogic')) {
            const items = wrapper.getElementsByClassName('gs-accordion-item');
            for (let i = 0; i < items.length; i++) {
                items[i].classList.replace("gsopen", "gsclose");
                let titlearea = items[i].querySelector('.gs-accordion-item__title');
                titlearea.setAttribute('aria-expanded', 'false');
                let contentarea = items[i].querySelector('.gs-accordion-item__content');
                contentarea.style.maxHeight = null;
            }
        } else {
            item.classList.replace("gsopen", "gsclose");
            item.querySelector('.gs-accordion-item__title').setAttribute('aria-expanded', 'false');
            contentWrap.style.maxHeight = null;
        }

    } else {
        let collapsingContent = null;
        if (wrapper.classList.contains('togglelogic')) {
            const items = wrapper.getElementsByClassName('gs-accordion-item');
            for (let i = 0; i < items.length; i++) {
                if (items[i] !== item && items[i].classList.contains('gsopen')) {
                    collapsingContent = items[i].querySelector('.gs-accordion-item__content');
                }
                items[i].classList.replace("gsopen", "gsclose");
                let titlearea = items[i].querySelector('.gs-accordion-item__title');
                titlearea.setAttribute('aria-expanded', 'false');
                let contentarea = items[i].querySelector('.gs-accordion-item__content');
                contentarea.style.maxHeight = null;
            }
        }
        item.classList.replace("gsclose", "gsopen");
        contentWrap.style.maxHeight = contentWrap.scrollHeight + "px";
        item.querySelector('.gs-accordion-item__title').setAttribute('aria-expanded', 'true');
        if (enableScroll) {
            if (collapsingContent) {
                // When auto-close is enabled, wait for the previously open item to
                // finish collapsing so the layout has settled before scrolling.
                // Otherwise the scroll position is calculated against an outdated
                // height and lands on the wrong place.
                let scrolled = false;
                const doScroll = function () {
                    if (scrolled) return;
                    scrolled = true;
                    collapsingContent.removeEventListener('transitionend', onCollapseEnd);
                    item.scrollIntoView({ behavior: 'smooth' });
                };
                const onCollapseEnd = function (e) {
                    if (e.target === collapsingContent && e.propertyName === 'max-height') {
                        doScroll();
                    }
                };
                collapsingContent.addEventListener('transitionend', onCollapseEnd);
                // Fallback in case the transition does not fire (e.g. reduced motion).
                setTimeout(doScroll, 400);
            } else {
                item.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }
}
document.addEventListener('click', function (ev) {
    let target = ev.target;
    if (target.classList.contains('gs-accordion-item__title') || target.closest('.gs-accordion-item__title')) {
        GSPB_Accordion_Toggle(ev.target);
    }
}, false);

let accordionItems = document.querySelectorAll('.gs-accordion-item__title');

const GSPB_Accordion_MaxHeight = () => {
    for (let i = 0; i < accordionItems.length; i++) {
        let item = accordionItems[i].closest('.gs-accordion-item');
        if (item.classList.contains('gsopen')) {
            let contentWrap = item.querySelector('.gs-accordion-item__content');
            contentWrap.style.maxHeight = contentWrap.scrollHeight + "px";
        }
    }
}

GSPB_Accordion_MaxHeight();
window.addEventListener('resize', GSPB_Accordion_MaxHeight);

for (let i = 0; i < accordionItems.length; i++) {
    accordionItems[i].addEventListener('keydown', function (e) {
        const keyDown = e.key !== undefined ? e.key : e.keyCode;
        if ((keyDown === 'Enter' || keyDown === 13) ||
            (['Spacebar', ' '].indexOf(keyDown) >= 0 || keyDown === 32)) {
            e.preventDefault();
            GSPB_Accordion_Toggle(accordionItems[i]);
        }
    });
}