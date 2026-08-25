import PhotoSwipe from 'photoswipe';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

const defaultOptions = {
    children: 'a',
};

document.addEventListener('DOMContentLoaded', () => {
    const e = document.querySelectorAll('[data-photoswipe]');

    for (let i = 0; i < e.length; i += 1) {
        const optionsEvent = new CustomEvent('folder-gallery:photoswipe:options', {
            detail: {
                ...defaultOptions,
            },
        });
        document.dispatchEvent(optionsEvent);

        const options = {
            ...optionsEvent.detail,
            gallery: e[i],
            pswpModule: PhotoSwipe,
        };

        const lightbox = new PhotoSwipeLightbox(options);

        document.dispatchEvent(
            new CustomEvent('folder-gallery:photoswipe:afterInit', {
                detail: {
                    lightbox,
                    gallery: e[i],
                },
            }),
        );

        // Parse data-pswp-webp-src attribute
        lightbox.addFilter('itemData', (itemData) => {
            const webpSrc = itemData.element.dataset.pswpWebpSrc;
            if (webpSrc) {
                itemData.webpSrc = webpSrc;
            }
            const avifSrc = itemData.element.dataset.pswpAvifSrc;
            if (avifSrc) {
                itemData.avifSrc = avifSrc;
            }
            return itemData;
        });

        // use <picture> instead of <img>
        lightbox.on('contentLoad', (contentLoadEvent) => {
            const { content } = contentLoadEvent;

            if (content.data.webpSrc || content.data.avifSrc) {
                // prevent to stop the default behavior
                contentLoadEvent.preventDefault();

                content.pictureElement = document.createElement('picture');

                const sourceJpg = document.createElement('source');
                sourceJpg.srcset = content.data.src;
                sourceJpg.type = 'image/jpeg';

                content.element = document.createElement('img');
                content.element.src = content.data.src;
                content.element.setAttribute('alt', '');
                content.element.className = 'pswp__img';

                if (content.data.webpSrc) {
                    const sourceWebp = document.createElement('source');
                    sourceWebp.srcset = content.data.webpSrc;
                    sourceWebp.type = 'image/webp';
                    content.pictureElement.appendChild(sourceWebp);
                }

                if (content.data.avifSrc) {
                    const sourceAvif = document.createElement('source');
                    sourceAvif.srcset = content.data.avifSrc;
                    sourceAvif.type = 'image/avif';
                    content.pictureElement.appendChild(sourceAvif);
                }
                content.pictureElement.appendChild(sourceJpg);
                content.pictureElement.appendChild(content.element);

                content.state = 'loading';

                if (content.element.complete) {
                    content.onLoaded();
                } else {
                    content.element.onload = () => {
                        content.onLoaded();
                    };

                    content.element.onerror = () => {
                        content.onError();
                    };
                }
            }
        });

        // by default PhotoSwipe appends <img>,
        // but we want to append <picture>
        lightbox.on('contentAppend', (contentAppendEvent) => {
            const { content } = contentAppendEvent;
            if (content.pictureElement && !content.pictureElement.parentNode) {
                contentAppendEvent.preventDefault();
                content.slide.container.appendChild(content.pictureElement);
            }
        });

        // for next/prev navigation with <picture>
        // by default PhotoSwipe removes <img>,
        // but we want to remove <picture>
        lightbox.on('contentRemove', (contentRemoveEvent) => {
            const { content } = contentRemoveEvent;
            if (content.pictureElement && content.pictureElement.parentNode) {
                contentRemoveEvent.preventDefault();
                content.pictureElement.remove();
            }
        });

        // Add a caption to the lightbox if there is a figcaption.
        // If there is no figcaption element use the contents of the
        // alt attribute if any.
        lightbox.on('uiRegister', () => {
            lightbox.pswp.ui.registerElement({
                name: 'custom-caption',
                order: 9,
                isButton: false,
                appendTo: 'root',
                html: '',
                onInit: (el, pswp) => {
                    pswp.on('change', () => {
                        const slideElement = pswp.currSlide.data.element;

                        let captionHTML = '';

                        if (slideElement) {
                            const figure = slideElement.closest('figure');
                            const figcaption = figure?.querySelector(':scope > figcaption');

                            if (figcaption && figcaption.textContent.trim() !== '') {
                                captionHTML = figcaption.innerHTML;
                            } else {
                                const alt = slideElement.querySelector('img')?.getAttribute('alt')?.trim();

                                if (alt) {
                                    captionHTML = alt;
                                }
                            }
                        }

                        el.innerHTML = captionHTML;
                        el.hidden = captionHTML === '';
                    });
                },
            });
        });

        lightbox.init();
    }
});
