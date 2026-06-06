<?php
/**
 * ADKLAB — Оптовый интернет-магазин (WooCommerce)
 * ────────────────────────────────────────────────────────────────────────────
 * Превращает обычный WooCommerce в оптовый каталог «цена по запросу»:
 *  • цена скрыта → бейдж «Цена по запросу»
 *  • выбор типоразмера чипами (парсится из таблицы под H3 «Типоразмеры»)
 *    с жёсткой привязкой к существующим комбинациям
 *  • вкладки товара из H3-секций описания
 *  • бейдж бренда + короткое название на карточках
 *  • корзина без цен и итогов, кнопка «Запросить расчёт» + попап-форма (AJAX)
 *
 * Настройка через define() в wp-config.php (все опциональны):
 *   define('ADKLAB_SHOP_BRANDS',        'IZOterm|TRUBOFLEX'); // regex брендов
 *   define('ADKLAB_SHOP_REQUEST_EMAIL', 'sales@company.ru');  // куда слать заявки
 *   define('ADKLAB_SHOP_PHONE',         '+7 (902) 565-02-61'); // телефон в ошибке
 *   define('ADKLAB_SHOP_PHONE_LINK',    '+79025650261');       // tel:
 *   define('ADKLAB_SHOP_BADGE',         'Цена по запросу — оптовые поставки');
 *   define('ADKLAB_SHOP_PRIVACY_URL',   '/privacy-policy/');
 *
 * Зависит от стилей формы из contact-form.php (.adklab-contact-form …).
 */

defined('ABSPATH') || exit;

if ( ! function_exists('adklab_shop_cfg') ) {
    function adklab_shop_cfg($key, $default = '') {
        $map = [
            'brands'    => 'ADKLAB_SHOP_BRANDS',
            'email'     => 'ADKLAB_SHOP_REQUEST_EMAIL',
            'phone'     => 'ADKLAB_SHOP_PHONE',
            'phone_lnk' => 'ADKLAB_SHOP_PHONE_LINK',
            'badge'     => 'ADKLAB_SHOP_BADGE',
            'privacy'   => 'ADKLAB_SHOP_PRIVACY_URL',
        ];
        $const = $map[$key] ?? '';
        if ( $const && defined($const) ) return constant($const);
        return $default;
    }
}

// Активируем только если WooCommerce подключён
add_action('plugins_loaded', function() {
    if ( ! class_exists('WooCommerce') ) return;
    adklab_shop_init();
}, 20);

function adklab_shop_init() {

    /* ── Активы ──────────────────────────────────────────────────────────── */
    add_action('wp_enqueue_scripts', function() {
        $base = plugin_dir_url(__FILE__) . 'assets/';
        $ver  = '1.0.0';
        wp_enqueue_style('adklab-shop',  $base . 'wholesale-shop.css', [], $ver);
        wp_enqueue_script('adklab-shop', $base . 'wholesale-shop.js', [], $ver, true);
    });

    /* ── Цена по запросу: товар всегда покупаем и в наличии ──────────────── */
    add_filter('woocommerce_is_purchasable', '__return_true');
    add_filter('woocommerce_product_is_in_stock', '__return_true');

    // Гасим стандартные WooCommerce-уведомления о добавлении
    add_filter('wc_add_to_cart_message_html', '__return_empty_string');
    add_action('woocommerce_before_single_product', fn() => wc_clear_notices(), 20);

    /* ── Вкладки товара из H3-секций описания ────────────────────────────── */
    add_filter('woocommerce_product_tabs', 'adklab_shop_product_tabs', 98);

    /* ── Бейдж бренда + короткое название на карточках ───────────────────── */
    add_action('woocommerce_before_shop_loop_item_title', function() {
        global $product;
        if ( ! $product ) return;
        $p = adklab_shop_parse_title($product->get_name());
        if ( $p['brand'] )
            echo '<span class="product-card__brand">' . esc_html($p['brand']) . '</span>';
    }, 5);
    remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
    add_action('woocommerce_shop_loop_item_title', function() {
        global $product;
        if ( ! $product ) return;
        $p = adklab_shop_parse_title($product->get_name());
        echo '<h2 class="woocommerce-loop-product__title">' . esc_html($p['name']) . '</h2>';
    }, 10);

    add_filter('woocommerce_breadcrumb_defaults', function($a){ $a['delimiter'] = ' / '; return $a; });

    /* ── Страница товара: своя шапка + цена-бейдж ────────────────────────── */
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

    add_action('woocommerce_before_single_product_summary', 'adklab_shop_header', 5);

    /* ── Выбор типоразмера + строка «количество + в корзину» ─────────────── */
    add_action('woocommerce_before_add_to_cart_quantity', 'adklab_shop_size_selector', 5);
    add_action('woocommerce_before_add_to_cart_quantity', function() {
        echo '<div class="wc-qty-label">Количество</div><div class="cart-bottom-row">';
    }, 10);
    add_action('woocommerce_after_add_to_cart_button', function() { echo '</div>'; }, 5);

    /* ── Типоразмер: валидация + сохранение в заказе ─────────────────────── */
    add_filter('woocommerce_add_to_cart_validation', function($valid, $pid) {
        $p = get_post($pid);
        $d = adklab_shop_parse_sizes($p->post_content ?? '');
        if ( ! empty($d['cols']) && empty($_POST['adklab_size']) ) {
            wc_add_notice('Пожалуйста, выберите типоразмер.', 'error');
            return false;
        }
        return $valid;
    }, 10, 2);
    add_filter('woocommerce_add_cart_item_data', function($data, $pid) {
        if ( ! empty($_POST['adklab_size']) )
            $data['adklab_size'] = sanitize_text_field($_POST['adklab_size']);
        return $data;
    }, 10, 2);
    add_filter('woocommerce_get_item_data', function($data, $item) {
        if ( ! empty($item['adklab_size']) )
            $data[] = ['name' => 'Типоразмер', 'value' => $item['adklab_size']];
        return $data;
    }, 10, 2);
    add_action('woocommerce_checkout_create_order_line_item', function($item, $k, $vals) {
        if ( ! empty($vals['adklab_size']) )
            $item->add_meta_data('Типоразмер', $vals['adklab_size'], true);
    }, 10, 3);

    /* ── Корзина: оптовый вид ────────────────────────────────────────────── */
    add_filter('woocommerce_cart_needs_payment', '__return_false');
    add_filter('woocommerce_coupons_enabled', '__return_false');
    add_action('woocommerce_before_cart', fn() => wc_clear_notices());
    add_filter('the_title', function($t){ return (is_cart() && $t === 'Cart') ? 'Корзина' : $t; });

    add_filter('wc_empty_cart_message', function() {
        return '<div class="adklab-empty-cart">'
             . '<h2>Ваша корзина пуста</h2>'
             . '<p>Выберите интересующую продукцию в каталоге и отправьте запрос на расчёт.</p>'
             . '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="btn btn-primary adklab-shop-btn">Перейти в каталог</a>'
             . '</div>';
    });

    // Скрываем цены в строках корзины и блок итогов
    add_filter('woocommerce_cart_item_price',    fn() => '');
    add_filter('woocommerce_cart_item_subtotal', fn() => '');
    add_action('woocommerce_before_cart', function() {
        remove_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10);
    }, 1);

    // Кнопка «Запросить расчёт» + попап
    add_action('woocommerce_after_cart', 'adklab_shop_request_popup');

    // AJAX-обработчик заявки
    add_action('wp_ajax_adklab_shop_request',        'adklab_shop_handle_request');
    add_action('wp_ajax_nopriv_adklab_shop_request', 'adklab_shop_handle_request');
}

/* ════════════════════════════════════════════════════════════════════════
   Функции
   ════════════════════════════════════════════════════════════════════════ */

// Разбор названия «Brand Модель — Описание» → бренд + короткое имя
function adklab_shop_parse_title($title) {
    $brands = adklab_shop_cfg('brands', 'IZOterm|TRUBOFLEX');
    $brand = ''; $model = ''; $short = $title;
    if ( preg_match('/^(' . $brands . ')(\s+[^\s—–-]+)?\s*[—–-]+\s*(.+)$/u', $title, $m) ) {
        $brand = trim($m[1]);
        $model = trim($m[2]);
        $short = ($model ? $model . ' — ' : '') . trim($m[3]);
    }
    return ['brand' => $brand, 'name' => $short];
}

// Вкладки из H3-секций + краткое описание в «Описание»
function adklab_shop_product_tabs($tabs) {
    global $post, $product;
    if ( ! $post ) return $tabs;
    $content = apply_filters('the_content', $post->post_content);
    if ( ! $content ) return $tabs;

    $parts = preg_split('/<h3[^>]*>(.*?)<\/h3>/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ( count($parts) < 3 ) return $tabs;

    $excerpt = $product ? wpautop($product->get_short_description()) : '';
    $intro   = trim($parts[0]);
    if ( $excerpt || $intro ) {
        $html = ($excerpt ? '<div class="product-short-desc">' . $excerpt . '</div>' : '') . ($intro ?: '');
        $tabs['description']['callback'] = function() use ($html) {
            echo '<div class="product-tab-content">' . $html . '</div>';
        };
    } else {
        unset($tabs['description']);
    }
    for ( $i = 1; $i < count($parts) - 1; $i += 2 ) {
        $title = strip_tags($parts[$i]);
        $body  = trim($parts[$i + 1] ?? '');
        $tabs['adklab_tab_' . $i] = [
            'title'    => $title,
            'priority' => 20 + $i,
            'callback' => function() use ($body) {
                echo '<div class="product-tab-content">' . $body . '</div>';
            },
        ];
    }
    unset($tabs['additional_information']);
    return $tabs;
}

// Шапка страницы товара: заголовок + бейдж + категория
function adklab_shop_header() {
    global $post, $product;
    if ( ! $product ) return;
    $p     = adklab_shop_parse_title($product->get_name());
    $terms = get_the_terms($post->ID, 'product_cat');
    $badge = adklab_shop_cfg('badge', 'Цена по запросу — оптовые поставки');
    ?>
    <div class="sp-header">
        <h1 class="product_title entry-title">
            <?php if ($p['brand']) : ?><span class="sp-brand"><?php echo esc_html($p['brand']); ?></span> <?php endif; ?>
            <?php echo esc_html($p['name'] ?: $product->get_name()); ?>
        </h1>
        <div class="sp-meta-row">
            <span class="wholesale-price-badge"><?php echo esc_html($badge); ?></span>
            <?php if ($terms && ! is_wp_error($terms)) : foreach ($terms as $t) : ?>
                <a href="<?php echo esc_url(get_term_link($t)); ?>" class="sp-category-tag"><?php echo esc_html($t->name); ?></a>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <?php
}

// Селектор типоразмера (чипы по колонкам + скрытый select)
function adklab_shop_size_selector() {
    global $post;
    $d = adklab_shop_parse_sizes($post->post_content);
    if ( empty($d['cols']) ) return;
    ?>
    <div class="wc-size-selector">
        <?php foreach ($d['cols'] as $ci => $values) :
            $label = preg_replace('/,?\s*(мм|м²|м|см)\s*$/u', '', $d['headers'][$ci] ?? '');
        ?>
        <div class="wc-size-param">
            <span class="wc-size-param__label"><?php echo esc_html($label); ?></span>
            <div class="wc-size-chips" data-col="<?php echo $ci; ?>">
                <?php foreach ($values as $vi => $val) : ?>
                <button type="button" class="wc-size-chip<?php echo $vi === 0 ? ' active' : ''; ?>"
                        data-col="<?php echo $ci; ?>" data-val="<?php echo esc_attr($val); ?>"><?php echo esc_html($val); ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <select name="adklab_size" id="adklab_size" aria-hidden="true" tabindex="-1">
            <option value=""></option>
            <?php foreach ($d['rows'] as $row) : $v = implode(' × ', $row); ?>
            <option value="<?php echo esc_attr($v); ?>"><?php echo esc_html($v); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
}

// Парсинг таблицы под H3 «Типоразмеры» → колонки + строки (с единицами)
function adklab_shop_parse_sizes($content) {
    $res = ['headers' => [], 'cols' => [], 'rows' => []];
    if ( ! preg_match('/<h3[^>]*>[^<]*[Тт]ипоразмер[^<]*<\/h3>(.*?)(?=<h3|$)/is', $content, $m) )
        return $res;
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $m[1], $trs);

    $headers = []; $rows = []; $first = true;
    foreach ($trs[1] as $tr) {
        preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $tr, $cells);
        $vals = array_map(fn($c) => trim(strip_tags($c)), $cells[1]);
        if ($first) { $headers = $vals; $first = false; continue; }
        if ( empty(array_filter($vals)) ) continue;
        $row = [];
        foreach ($vals as $i => $v) {
            if ($v === '') { $row[] = $v; continue; }
            $h = mb_strtolower($headers[$i] ?? '');
            if      (strpos($h, 'м²') !== false || strpos($h, 'площ') !== false) $row[] = $v . ' м²';
            elseif  (strpos($h, 'длин') !== false)                               $row[] = $v . ' м';
            elseif  (strpos($h, 'мм') !== false || strpos($h, 'толщ') !== false || strpos($h, 'шир') !== false || strpos($h, 'диам') !== false) $row[] = $v . ' мм';
            else    $row[] = $v;
        }
        $rows[] = $row;
    }
    $cols = [];
    foreach ($rows as $row) {
        foreach ($row as $ci => $v) {
            if ($v === '') continue;
            if ( ! isset($cols[$ci]) ) $cols[$ci] = [];
            if ( ! in_array($v, $cols[$ci], true) ) $cols[$ci][] = $v;
        }
    }
    $res['headers'] = $headers; $res['cols'] = $cols; $res['rows'] = $rows;
    return $res;
}

// Кнопка «Запросить расчёт» + попап-форма (предзаполнена составом корзины)
function adklab_shop_request_popup() {
    $lines = []; $i = 1;
    foreach ( WC()->cart->get_cart() as $item ) {
        $line = $i . '. ' . $item['data']->get_name();
        if ( ! empty($item['adklab_size']) ) $line .= ' — ' . $item['adklab_size'];
        $line .= ', ' . $item['quantity'] . ' шт.';
        $lines[] = $line; $i++;
    }
    $cart_text  = implode("\n", $lines);
    $nonce      = wp_create_nonce('adklab_shop_request');
    $privacy    = adklab_shop_cfg('privacy', '/privacy-policy/');
    $phone      = adklab_shop_cfg('phone', '');
    $phone_lnk  = adklab_shop_cfg('phone_lnk', '');
    ?>
    <div class="adklab-request-wrap">
        <button type="button" class="btn btn-primary adklab-shop-btn"
                onclick="document.getElementById('adklab-modal').style.display='flex';document.body.style.overflow='hidden';">
            Запросить расчёт
        </button>
    </div>
    <div id="adklab-modal" class="adklab-modal" style="display:none;">
        <div class="adklab-modal__overlay"
             onclick="document.getElementById('adklab-modal').style.display='none';document.body.style.overflow='';"></div>
        <div class="adklab-modal__box">
            <button type="button" class="adklab-modal__close"
                    onclick="document.getElementById('adklab-modal').style.display='none';document.body.style.overflow='';">&#10005;</button>
            <h2 class="adklab-modal__title">Запросить расчёт</h2>
            <p class="adklab-modal__sub">Менеджер свяжется с вами для согласования объёма, условий и выставления счёта.</p>
            <form id="adklabRequestForm" onsubmit="adklabShopSubmit(event);" novalidate class="adklab-contact-form">
                <div class="adklab-form-field">
                    <label>Имя *</label>
                    <input type="text" name="name" placeholder="Ваше имя" required>
                </div>
                <div class="adklab-form-field">
                    <label>Телефон или Email *</label>
                    <input type="text" name="contact" placeholder="+7 900 000-00-00 или email@mail.ru" required>
                </div>
                <div class="adklab-form-field">
                    <label>Компания / Организация</label>
                    <input type="text" name="company" placeholder="ООО «Название» или ИП Фамилия">
                </div>
                <div class="adklab-form-field">
                    <label>Состав заказа *</label>
                    <textarea name="message" required><?php echo esc_textarea($cart_text); ?></textarea>
                </div>
                <div class="adklab-form-consent">
                    <input type="checkbox" id="adklab_consent" name="consent" required>
                    <label for="adklab_consent" class="adklab-consent-label">
                        Я даю согласие на обработку персональных данных в соответствии с
                        <a href="<?php echo esc_url(home_url($privacy)); ?>">политикой конфиденциальности</a> (ФЗ-152)
                    </label>
                </div>
                <input type="hidden" name="action" value="adklab_shop_request">
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                <div class="adklab-form-success" style="display:none;">✅ Заявка принята! Менеджер свяжется с вами в рабочее время.</div>
                <div class="adklab-form-error" style="display:none;">Ошибка отправки.<?php if ($phone) : ?> Позвоните нам: <a href="tel:<?php echo esc_attr($phone_lnk); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?></div>
                <button type="submit" class="adklab-form-submit" style="width:100%;padding:16px;">Отправить заявку</button>
            </form>
        </div>
    </div>
    <?php
}

// AJAX: приём заявки → письмо
function adklab_shop_handle_request() {
    if ( ! check_ajax_referer('adklab_shop_request', 'nonce', false) )
        wp_send_json_error('Ошибка безопасности');

    $name    = sanitize_text_field($_POST['name'] ?? '');
    $company = sanitize_text_field($_POST['company'] ?? '');
    $contact = sanitize_text_field($_POST['contact'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    if ( ! $name || ! $contact ) wp_send_json_error('Заполните обязательные поля');

    $to   = adklab_shop_cfg('email', get_option('admin_email'));
    $subj = 'Запрос расчёта с сайта ' . get_bloginfo('name');
    $body = "Запрос расчёта\n\n"
          . "Имя: $name\n"
          . ($company ? "Компания: $company\n" : '')
          . "Контакт: $contact\n\n"
          . $message;

    wp_mail($to, $subj, $body, ['Content-Type: text/plain; charset=UTF-8'])
        ? wp_send_json_success()
        : wp_send_json_error('Ошибка отправки');
}
