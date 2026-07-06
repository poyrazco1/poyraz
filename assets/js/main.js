/* D4stattoo — public site etkileşimleri (bağımlılıksız vanilla JS) */
(function () {
    'use strict';

    /* --- Mobil menü ------------------------------------------------------ */
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            toggle.classList.toggle('open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        });
    }

    /* --- Dropdownlar (mobilde tıkla-aç; masaüstünde hover CSS ile) ------- */
    document.querySelectorAll('.has-dropdown > .drop-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var li = btn.parentElement;
            var open = li.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            // aynı seviyedeki diğerlerini kapat
            li.parentElement.querySelectorAll('.has-dropdown.open').forEach(function (other) {
                if (other !== li) {
                    other.classList.remove('open');
                    var b = other.querySelector('.drop-btn');
                    if (b) b.setAttribute('aria-expanded', 'false');
                }
            });
        });
    });

    /* --- Dil menüsü -------------------------------------------------------- */
    var langSw = document.querySelector('.lang-switcher');
    if (langSw) {
        var langBtn = langSw.querySelector('.lang-btn');
        langBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = langSw.classList.toggle('open');
            langBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function () {
            langSw.classList.remove('open');
        });
    }

    /* --- Galeri filtresi ---------------------------------------------------- */
    var filterBtns = document.querySelectorAll('[data-filter]');
    var galleryItems = document.querySelectorAll('[data-cat]');
    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var cat = btn.getAttribute('data-filter');
            galleryItems.forEach(function (item) {
                var show = cat === 'all' || item.getAttribute('data-cat') === cat;
                item.style.display = show ? '' : 'none';
            });
        });
    });

    /* --- Lightbox ------------------------------------------------------------ */
    var lightbox = document.getElementById('lightbox');
    if (lightbox) {
        var lbImg = lightbox.querySelector('img');
        var lbCap = lightbox.querySelector('.lightbox-caption');
        document.querySelectorAll('[data-lightbox]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                lbImg.src = el.getAttribute('data-lightbox');
                lbImg.alt = el.getAttribute('data-caption') || '';
                if (lbCap) lbCap.textContent = el.getAttribute('data-caption') || '';
                lightbox.hidden = false;
                document.body.style.overflow = 'hidden';
            });
        });
        function closeLb() {
            lightbox.hidden = true;
            document.body.style.overflow = '';
        }
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox || e.target.classList.contains('lightbox-close')) closeLb();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !lightbox.hidden) closeLb();
        });
    }

    /* --- SSS akordeon ---------------------------------------------------------- */
    document.querySelectorAll('.faq-item .faq-q').forEach(function (q) {
        q.addEventListener('click', function () {
            var item = q.closest('.faq-item');
            var body = item.querySelector('.faq-a');
            var isOpen = item.classList.contains('open');
            if (isOpen) {
                body.style.maxHeight = '0';
                item.classList.remove('open');
            } else {
                body.style.maxHeight = body.scrollHeight + 'px';
                item.classList.add('open');
            }
        });
    });

    /* --- Popup teklif formu (10 sn sonra, oturumda bir kez) ------------------ */
    var popup = document.getElementById('quotePopup');
    if (popup) {
        var KEY = 'd4_popup_seen';
        var seen = false;
        try { seen = sessionStorage.getItem(KEY) === '1'; } catch (err) { /* gizli mod */ }
        var closePopup = function () {
            popup.hidden = true;
            try { sessionStorage.setItem(KEY, '1'); } catch (err) { /* yoksay */ }
        };
        if (!seen) {
            setTimeout(function () {
                // form sayfalarında popup açma
                if (!document.querySelector('.form-card')) popup.hidden = false;
            }, 10000);
        }
        popup.addEventListener('click', function (e) {
            if (e.target === popup || e.target.hasAttribute('data-popup-close')) closePopup();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !popup.hidden) closePopup();
        });
    }
})();
