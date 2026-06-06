/* ============================================================================
   ADKLAB — Оптовый интернет-магазин (front-end)
   • Жёсткая привязка выбора типоразмера к существующим строкам таблицы
   • Сабмит попап-формы «Запросить расчёт» через AJAX
   • Корректное поле количества в корзине (без спиннеров, max 100000)
   ============================================================================ */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ── Селектор типоразмера ─────────────────────────────────────────── */
        var sel = document.querySelector('.wc-size-selector');
        if (sel) {
            var hidden = sel.querySelector('select');
            var chips  = Array.prototype.slice.call(sel.querySelectorAll('.wc-size-chip'));
            var rows   = Array.from(hidden ? hidden.options : [])
                .filter(function (o) { return o.value; })
                .map(function (o) { return o.value.split(' × '); });

            function currentSel() {
                var s = {};
                sel.querySelectorAll('.wc-size-chip.active').forEach(function (c) { s[c.dataset.col] = c.dataset.val; });
                return s;
            }
            function applyRow(row) {
                chips.forEach(function (c) {
                    c.classList.toggle('active', c.dataset.val === row[parseInt(c.dataset.col)]);
                });
                if (hidden) hidden.value = row.join(' × ');
            }
            function pickRow(col, val) {
                var s = currentSel();
                var cands = rows.filter(function (r) { return r[parseInt(col)] === val; });
                if (!cands.length) return null;
                cands.sort(function (a, b) { return score(b) - score(a); });
                function score(r) {
                    var n = 0;
                    Object.keys(s).forEach(function (c) {
                        if (c !== col && r[parseInt(c)] === s[c]) n++;
                    });
                    return n;
                }
                return cands[0];
            }
            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var row = pickRow(chip.dataset.col, chip.dataset.val);
                    if (row) applyRow(row);
                });
            });
            if (rows.length) applyRow(rows[0]);
        }

        /* ── Поле количества в корзине ────────────────────────────────────── */
        function fixCartQty() {
            document.querySelectorAll('.woocommerce-cart input.qty, .woocommerce-cart .quantity input[type="number"]')
                .forEach(function (el) {
                    el.style.setProperty('width', '110px', 'important');
                    el.style.setProperty('height', '40px', 'important');
                    el.style.setProperty('text-align', 'center', 'important');
                    el.setAttribute('max', '100000');
                });
        }
        fixCartQty();
        document.addEventListener('updated_cart_totals', fixCartQty);
    });

    /* ── Сабмит попап-формы «Запросить расчёт» ────────────────────────────── */
    window.adklabShopSubmit = function (e) {
        e.preventDefault();
        var form    = document.getElementById('adklabRequestForm');
        var submit  = form.querySelector('.adklab-form-submit');
        var success = form.querySelector('.adklab-form-success');
        var error   = form.querySelector('.adklab-form-error');

        var ok = true;
        form.querySelectorAll('[required]').forEach(function (el) {
            var valid = el.type === 'checkbox' ? el.checked : el.value.trim() !== '';
            el.style.outline = valid ? '' : '2px solid #e53935';
            if (!valid) ok = false;
        });
        if (!ok) return;

        submit.disabled = true;
        submit.textContent = 'Отправляем…';
        success.style.display = 'none';
        error.style.display = 'none';

        var ajaxUrl = (typeof wc_add_to_cart_params !== 'undefined')
            ? wc_add_to_cart_params.ajax_url : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, { method: 'POST', body: new FormData(form) })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    success.style.display = 'block';
                    submit.style.display = 'none';
                } else {
                    error.style.display = 'block';
                    submit.disabled = false;
                    submit.textContent = 'Отправить заявку';
                }
            })
            .catch(function () {
                error.style.display = 'block';
                submit.disabled = false;
                submit.textContent = 'Отправить заявку';
            });
    };

    // Закрытие попапа по Escape
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        var m = document.getElementById('adklab-modal');
        if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
    });
})();
