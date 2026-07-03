/**
 * PoyrazTech Yönetim Paneli — istemci tarafı etkileşimleri.
 *
 * Bağımlılıksız (vanilla) JS. İçerik:
 *  - Mobil sidebar aç/kapa
 *  - Kullanıcı menüsü açılır kutusu
 *  - Şifre göster/gizle
 *  - Silme vb. işlemler için onay
 *  - Kur çevirici (veri kur.php JSON servisinden gelir)
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        initUserMenu();
        initPasswordToggles();
        initConfirmForms();
        initConverter();
    });

    /* --------------------------------------------------------------------- */
    /* Sidebar                                                               */
    /* --------------------------------------------------------------------- */
    function initSidebar() {
        var toggle = document.querySelector('[data-sidebar-toggle]');
        var sidebar = document.querySelector('[data-sidebar]');
        var backdrop = document.querySelector('[data-sidebar-backdrop]');
        if (!toggle || !sidebar) {
            return;
        }

        function open() {
            document.body.classList.add('sidebar-open');
            if (backdrop) { backdrop.hidden = false; }
        }
        function close() {
            document.body.classList.remove('sidebar-open');
            if (backdrop) { backdrop.hidden = true; }
        }

        toggle.addEventListener('click', function () {
            if (document.body.classList.contains('sidebar-open')) {
                close();
            } else {
                open();
            }
        });

        if (backdrop) {
            backdrop.addEventListener('click', close);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { close(); }
        });

        // Menü bağlantısına tıklanınca mobilde kapat.
        sidebar.addEventListener('click', function (e) {
            if (e.target.closest('a') && window.innerWidth < 992) {
                close();
            }
        });
    }

    /* --------------------------------------------------------------------- */
    /* Kullanıcı menüsü                                                       */
    /* --------------------------------------------------------------------- */
    function initUserMenu() {
        var wrap = document.querySelector('[data-user-menu]');
        if (!wrap) { return; }
        var toggle = wrap.querySelector('[data-user-menu-toggle]');
        var dropdown = wrap.querySelector('[data-user-menu-dropdown]');
        if (!toggle || !dropdown) { return; }

        function close() {
            dropdown.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = !dropdown.hidden;
            dropdown.hidden = isOpen;
            toggle.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) { close(); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { close(); }
        });
    }

    /* --------------------------------------------------------------------- */
    /* Şifre göster/gizle                                                     */
    /* --------------------------------------------------------------------- */
    function initPasswordToggles() {
        var toggles = document.querySelectorAll('[data-password-toggle]');
        Array.prototype.forEach.call(toggles, function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-password-toggle');
                var input = document.getElementById(id);
                if (!input) { return; }
                var isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                btn.classList.toggle('is-visible', isPassword);
            });
        });
    }

    /* --------------------------------------------------------------------- */
    /* İşlem onayı (silme vb.)                                                */
    /* --------------------------------------------------------------------- */
    function initConfirmForms() {
        var forms = document.querySelectorAll('form[data-confirm]');
        Array.prototype.forEach.call(forms, function (form) {
            form.addEventListener('submit', function (e) {
                var message = form.getAttribute('data-confirm') || 'Emin misiniz?';
                if (!window.confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }

    /* --------------------------------------------------------------------- */
    /* Kur çevirici                                                          */
    /* --------------------------------------------------------------------- */
    function initConverter() {
        var form = document.querySelector('[data-converter]');
        if (!form) { return; }

        var amountEl = form.querySelector('[data-converter-amount]');
        var fromEl = form.querySelector('[data-converter-from]');
        var toEl = form.querySelector('[data-converter-to]');
        var outEl = form.querySelector('[data-converter-output]');
        var noteEl = form.querySelector('[data-converter-note]');
        var swapEl = form.querySelector('[data-converter-swap]');
        var endpoint = form.getAttribute('data-converter-endpoint') || 'kur.php';

        var rates = null; // { TRY:1, USD:.., EUR:.. } — 1 birim kaç TRY

        function parseAmount(value) {
            if (typeof value !== 'string') { return NaN; }
            // Binlik ayıracı nokta, ondalık ayıracı virgül kabul edilir.
            value = value.trim().replace(/\s/g, '').replace(/\./g, '').replace(',', '.');
            return parseFloat(value);
        }

        function format(num) {
            return num.toLocaleString('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function compute() {
            if (!rates) { return; }
            var amount = parseAmount(amountEl.value);
            var from = fromEl.value;
            var to = toEl.value;

            if (isNaN(amount)) {
                outEl.textContent = '—';
                return;
            }
            var fromRate = rates[from];
            var toRate = rates[to];
            if (!fromRate || !toRate) {
                outEl.textContent = '—';
                if (noteEl) { noteEl.textContent = 'Kur verisi eksik.'; }
                return;
            }
            var result = (amount * fromRate) / toRate;
            outEl.textContent = format(result) + ' ' + to;
        }

        function loadRates() {
            if (noteEl) { noteEl.textContent = 'Kurlar yükleniyor…'; }
            fetch(endpoint + (endpoint.indexOf('?') === -1 ? '?' : '&') + 'action=rates', {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data.ok && data.rates) {
                        rates = data.rates;
                        if (noteEl) {
                            noteEl.textContent = data.stale
                                ? 'Son kayıtlı kurlar kullanılıyor (' + (data.fetched_at || '') + ').'
                                : 'Güncel kurlar (' + (data.fetched_at || '') + ').';
                        }
                        compute();
                    } else {
                        if (noteEl) { noteEl.textContent = 'Kur verisi alınamadı.'; }
                    }
                })
                .catch(function () {
                    if (noteEl) { noteEl.textContent = 'Kur servisine ulaşılamadı.'; }
                });
        }

        if (amountEl) { amountEl.addEventListener('input', compute); }
        if (fromEl) { fromEl.addEventListener('change', compute); }
        if (toEl) { toEl.addEventListener('change', compute); }
        if (swapEl) {
            swapEl.addEventListener('click', function () {
                var tmp = fromEl.value;
                fromEl.value = toEl.value;
                toEl.value = tmp;
                compute();
            });
        }

        loadRates();
    }
})();
