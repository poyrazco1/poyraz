/* D4stattoo — public site etkileşimleri (bağımlılıksız vanilla JS) */
(function () {
    'use strict';

    /* --- Mobil off-canvas menü -------------------------------------------- */
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    var overlay = document.querySelector('[data-nav-overlay]');
    var closeBtn = document.querySelector('[data-nav-close]');

    function menuOpen() {
        if (!nav) return;
        nav.classList.add('open');
        if (overlay) { overlay.hidden = false; overlay.classList.add('open'); }
        if (toggle) { toggle.classList.add('open'); toggle.setAttribute('aria-expanded', 'true'); }
        document.body.classList.add('body-menu-open');
    }
    function menuClose() {
        if (!nav) return;
        nav.classList.remove('open');
        if (overlay) { overlay.classList.remove('open'); }
        if (toggle) { toggle.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); }
        document.body.classList.remove('body-menu-open');
    }
    function menuIsOpen() {
        return nav && nav.classList.contains('open');
    }

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            menuIsOpen() ? menuClose() : menuOpen();
        });
    }
    if (closeBtn) closeBtn.addEventListener('click', menuClose);
    if (overlay) overlay.addEventListener('click', menuClose);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menuIsOpen()) menuClose();
    });
    // Linke tıklanınca menüyü kapat (mobil)
    if (nav) {
        nav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                if (menuIsOpen()) menuClose();
            });
        });
    }

    /* --- Dropdown / accordion (mobilde tıkla-aç; masaüstünde hover CSS ile) */
    document.querySelectorAll('.has-dropdown > .drop-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var li = btn.parentElement;
            var open = li.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
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
        var closeLb = function () {
            lightbox.hidden = true;
            document.body.style.overflow = '';
        };
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

    /* --- Popup teklif formu ------------------------------------------------------
       Ziyaretçiyi boğmadan: 10 sn SONRA ya da sayfanın %35'i scroll edildikten
       sonra gösterilir; kapatınca oturum boyunca tekrar açılmaz. */
    var popup = document.getElementById('quotePopup');
    if (popup) {
        var KEY = 'd4_popup_seen';
        var seen = false;
        try { seen = sessionStorage.getItem(KEY) === '1'; } catch (err) { /* gizli mod */ }

        var markSeen = function () {
            try { sessionStorage.setItem(KEY, '1'); } catch (err) { /* yoksay */ }
        };
        var showPopup = function () {
            if (seen || !popup.hidden) return;
            if (document.querySelector('.form-card')) return; // form sayfalarında açma
            if (menuIsOpen()) return;                          // menü açıkken açma
            popup.hidden = false;
            markSeen();
            seen = true;
        };
        var closePopup = function () {
            popup.hidden = true;
            markSeen();
            seen = true;
        };

        if (!seen) {
            setTimeout(showPopup, 10000);
            var onScroll = function () {
                var doc = document.documentElement;
                var max = doc.scrollHeight - doc.clientHeight;
                if (max > 0 && window.scrollY / max >= 0.35) {
                    showPopup();
                    window.removeEventListener('scroll', onScroll);
                }
            };
            window.addEventListener('scroll', onScroll, { passive: true });
        }

        popup.addEventListener('click', function (e) {
            if (e.target === popup || e.target.closest('[data-popup-close]')) closePopup();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !popup.hidden) closePopup();
        });
    }
})();
