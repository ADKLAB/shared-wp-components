<?php
/**
 * Форма обратной связи
 * Шорткод: [adklab_contact_form email="..." subject="..." extra_field="company"]
 *
 * Атрибуты:
 *   email        — адрес получателя (обязательно)
 *   subject      — тема письма
 *   extra_field  — дополнительное поле: "company" | "none" (по умолчанию none)
 *   success_text — текст после успешной отправки
 *   privacy_url  — ссылка на политику конфиденциальности (по умолчанию /privacy)
 *   btn_text     — текст кнопки отправки
 */

defined('ABSPATH') || exit;

add_shortcode('adklab_contact_form', 'adklab_contact_form_render');

function adklab_contact_form_render(array $atts): string {
    $atts = shortcode_atts([
        'email'        => 'adk-lab@yandex.ru',
        'subject'      => 'Новая заявка с сайта ' . get_bloginfo('name'),
        'extra_field'  => 'none',
        'success_text' => 'Ваше сообщение отправлено. Мы ответим в ближайшее время!',
        'privacy_url'  => home_url('/privacy'),
        'btn_text'     => 'Отправить заявку',
    ], $atts, 'adklab_contact_form');

    $sent  = false;
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adklab_cf_nonce'])) {
        $result = adklab_contact_form_process($atts);
        $sent   = $result['sent'];
        $error  = $result['error'];
    }

    ob_start();
    ?>
    <div class="adklab-contact-form-wrap">

        <?php if ($sent): ?>
            <div class="adklab-form-success">
                <?php echo esc_html($atts['success_text']); ?>
            </div>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="adklab-form-error">
                    <?php echo esc_html($error); ?>
                </div>
            <?php endif; ?>

            <form class="adklab-contact-form" method="post" action="" novalidate>
                <?php wp_nonce_field('adklab_contact_form', 'adklab_cf_nonce'); ?>

                <div class="adklab-form-field">
                    <label for="adklab_name">Имя *</label>
                    <input type="text" id="adklab_name" name="adklab_name"
                           value="<?php echo esc_attr($_POST['adklab_name'] ?? ''); ?>"
                           placeholder="Ваше имя" required>
                </div>

                <div class="adklab-form-field">
                    <label for="adklab_contact">Телефон или Email *</label>
                    <input type="text" id="adklab_contact" name="adklab_contact"
                           value="<?php echo esc_attr($_POST['adklab_contact'] ?? ''); ?>"
                           placeholder="+7 900 000-00-00 или email@mail.ru"
                           autocomplete="off" required>
                    <span class="adklab-field-note" id="adklab_contact_note"></span>
                </div>

                <?php if ($atts['extra_field'] === 'company'): ?>
                <div class="adklab-form-field">
                    <label for="adklab_company">Компания / Организация</label>
                    <input type="text" id="adklab_company" name="adklab_company"
                           value="<?php echo esc_attr($_POST['adklab_company'] ?? ''); ?>"
                           placeholder="ООО «Название» или ИП Фамилия">
                </div>
                <?php endif; ?>

                <div class="adklab-form-field">
                    <label for="adklab_message">Сообщение *</label>
                    <textarea id="adklab_message" name="adklab_message"
                              placeholder="Опишите ваш вопрос или запрос…"
                              required><?php echo esc_textarea($_POST['adklab_message'] ?? ''); ?></textarea>
                </div>

                <div class="adklab-form-consent">
                    <input type="checkbox" id="adklab_consent" name="adklab_consent" value="1"
                           <?php echo !empty($_POST['adklab_consent']) ? 'checked' : ''; ?> required>
                    <label for="adklab_consent">
                        Я даю согласие на обработку персональных данных в соответствии с
                        <a href="<?php echo esc_url($atts['privacy_url']); ?>">политикой конфиденциальности</a>
                        (ФЗ-152)
                    </label>
                </div>

                <button type="submit" class="adklab-form-submit"><?php echo esc_html($atts['btn_text']); ?></button>
            </form>

        <?php endif; ?>
    </div>

    <script>
    (function () {
        var field = document.getElementById('adklab_contact');
        var note  = document.getElementById('adklab_contact_note');
        if (!field) return;

        function formatPhone(raw) {
            var digits = raw.replace(/\D/g, '');
            if (!digits.length) return '';
            if (digits[0] === '8' || digits[0] === '7') digits = '7' + digits.slice(1);
            else digits = '7' + digits;
            digits = digits.slice(0, 11);
            var r = '+7';
            if (digits.length > 1) r += ' (' + digits.slice(1, 4);
            if (digits.length >= 4) r += ')';
            if (digits.length > 4)  r += ' ' + digits.slice(4, 7);
            if (digits.length > 7)  r += '-' + digits.slice(7, 9);
            if (digits.length > 9)  r += '-' + digits.slice(9, 11);
            return r;
        }

        function detectMode(val) {
            if (val.indexOf('@') !== -1) return 'email';
            if (/[\d\+\(\)\-\s]/.test(val) && val.length > 0) return 'phone';
            return 'unknown';
        }

        function updateNote(mode) {
            note.textContent = mode === 'phone' ? 'Формат: +7 (900) 000-00-00'
                             : mode === 'email' ? 'Email' : '';
        }

        field.addEventListener('input', function () {
            field.setCustomValidity('');
            var mode = detectMode(field.value);
            if (mode === 'phone') {
                field.value = formatPhone(field.value);
            }
            updateNote(mode);
        });

        field.addEventListener('keydown', function (e) {
            if (detectMode(field.value) === 'phone') {
                var ok = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
                if (ok.indexOf(e.key) === -1 && !/[\d\+]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                }
            }
        });

        var form = field.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                var val = field.value.trim();
                if (!val) {
                    field.setCustomValidity('Укажите телефон или email.');
                    field.reportValidity();
                    e.preventDefault();
                    return;
                }
                var digits   = val.replace(/\D/g, '');
                var isPhone  = digits.length >= 11;
                var isEmail  = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                if (!isPhone && !isEmail) {
                    field.setCustomValidity('Введите корректный номер телефона или email.');
                    field.reportValidity();
                    e.preventDefault();
                }
            });
        }
    })();
    </script>
    <?php
    return ob_get_clean();
}

function adklab_contact_form_process(array $atts): array {
    if (!wp_verify_nonce($_POST['adklab_cf_nonce'] ?? '', 'adklab_contact_form')) {
        return ['sent' => false, 'error' => 'Ошибка проверки формы. Попробуйте снова.'];
    }

    $name    = sanitize_text_field($_POST['adklab_name']    ?? '');
    $contact = sanitize_text_field($_POST['adklab_contact'] ?? '');
    $message = sanitize_textarea_field($_POST['adklab_message'] ?? '');
    $company = sanitize_text_field($_POST['adklab_company'] ?? '');
    $consent = !empty($_POST['adklab_consent']);

    if (!$name)    return ['sent' => false, 'error' => 'Пожалуйста, укажите ваше имя.'];
    if (!$contact) return ['sent' => false, 'error' => 'Укажите телефон или email для связи.'];
    if (!$message) return ['sent' => false, 'error' => 'Пожалуйста, напишите сообщение.'];
    if (!$consent) return ['sent' => false, 'error' => 'Необходимо дать согласие на обработку данных.'];

    $contact_label = strpos($contact, '@') !== false ? 'Email' : 'Телефон';
    $body = "Имя: $name\n$contact_label: $contact";
    if ($company) $body .= "\nКомпания: $company";
    $body .= "\n\nСообщение:\n$message";
    $body .= "\n\n---\nОтправлено с сайта: " . get_bloginfo('url');

    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    wp_mail($atts['email'], $atts['subject'], $body, $headers);

    return ['sent' => true, 'error' => ''];
}

// Стили формы
add_action('wp_head', 'adklab_contact_form_styles');
function adklab_contact_form_styles(): void { ?>
<style>
.adklab-contact-form-wrap { max-width: 560px; }
.adklab-form-success {
    background: #f0fff4; border: 1px solid #38a169;
    padding: 16px 20px; border-radius: 8px; color: #276749; margin-bottom: 20px;
    font-weight: 500;
}
.adklab-form-error {
    background: #fff5f5; border: 1px solid #e53e3e;
    padding: 16px 20px; border-radius: 8px; color: #c53030; margin-bottom: 20px;
}
.adklab-contact-form { display: flex; flex-direction: column; gap: 20px; }
.adklab-form-field { display: flex; flex-direction: column; gap: 6px; }
.adklab-form-field label { font-weight: 600; font-size: 0.9rem; color: #1a1a2e; }
.adklab-form-field input,
.adklab-form-field textarea {
    border: 1px solid #dee2e6; border-radius: 8px;
    padding: 12px 16px; font-size: 1rem; font-family: inherit;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #fff; color: #1a1a2e; width: 100%;
}
.adklab-form-field input:focus,
.adklab-form-field textarea:focus {
    outline: none; border-color: #1565c0;
    box-shadow: 0 0 0 3px rgba(21,101,192,0.12);
}
.adklab-form-field textarea { min-height: 130px; resize: vertical; }
.adklab-field-note { font-size: 0.8rem; color: #6c757d; min-height: 1em; }
.adklab-form-consent { display: flex; gap: 10px; align-items: flex-start; font-size: 0.85rem; color: #6c757d; }
.adklab-form-consent input { width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px; cursor: pointer; }
.adklab-form-consent a { color: #1565c0; }
.adklab-form-submit {
    background: #e91e8c; color: #fff; border: none;
    padding: 14px 32px; border-radius: 8px; font-size: 1rem;
    font-weight: 700; cursor: pointer; align-self: flex-start;
    transition: background 0.2s, transform 0.2s;
}
.adklab-form-submit:hover { background: #c2185b; transform: translateY(-2px); }
</style>
<?php }
