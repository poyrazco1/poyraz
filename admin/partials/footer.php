        </div><!-- /.content -->
    </div><!-- /.admin-main -->
</div><!-- /.admin-wrap -->
<script>
(function () {
    'use strict';
    var burger = document.querySelector('[data-sidebar-toggle]');
    var sidebar = document.getElementById('sidebar');
    if (burger && sidebar) {
        burger.addEventListener('click', function () { sidebar.classList.toggle('open'); });
    }
    // Silme onayı
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
        });
    });
})();
</script>
</body>
</html>
