<?php
/**
 * WooCommerce: русификация, SEO, базовые настройки
 *
 * Подключается через adklab-components.php.
 * Контентные данные (описания, реквизиты, title-теги) задаются через defines
 * в wp-config.php или functions.php конкретного проекта.
 *
 * Доступные defines:
 *   ADKLAB_ORG_NAME        — название организации
 *   ADKLAB_ORG_ADDRESS     — адрес (streetAddress)
 *   ADKLAB_ORG_CITY        — город
 *   ADKLAB_ORG_REGION      — регион
 *   ADKLAB_ORG_POSTAL      — индекс
 *   ADKLAB_ORG_PHONE       — телефон (строка или JSON-массив нескольких)
 *   ADKLAB_ORG_EMAIL       — email
 *   ADKLAB_ORG_BRAND       — бренд (строка) или несколько через запятую
 *   ADKLAB_SITE_DESC       — описание главной страницы (meta description)
 *   ADKLAB_OG_IMAGE        — URL картинки для OG по умолчанию
 *   ADKLAB_DISABLE_COUPONS — '1' чтобы отключить купоны (по умолчанию '1')
 */

defined('ABSPATH') || exit;

// ── WooCommerce: купоны ──────────────────────────────────────────────────────
if ((defined('ADKLAB_DISABLE_COUPONS') ? ADKLAB_DISABLE_COUPONS : '1') === '1') {
    add_filter('woocommerce_coupons_enabled', '__return_false');
}

// ── WooCommerce: корзина ─────────────────────────────────────────────────────
add_action('woocommerce_before_cart', function() { wc_clear_notices(); });
add_filter('woocommerce_cart_needs_payment', '__return_false');

// ── WooCommerce: убрать текст политики на чекауте ───────────────────────────
add_filter('woocommerce_get_privacy_policy_text',      '__return_empty_string');
add_filter('woocommerce_checkout_privacy_policy_text', '__return_empty_string');

// ── Русификация WooCommerce (gettext) ────────────────────────────────────────
add_filter('gettext',              'adklab_wc_translate', 10, 3);
add_filter('gettext_with_context', 'adklab_wc_translate', 10, 3);

function adklab_wc_translate($translated, $text) {
    static $map = null;
    if ($map === null) {
        $map = [
            // Корзина
            'Your cart is currently empty!'         => 'Ваша корзина пуста.',
            'Your cart is currently empty.'         => 'Ваша корзина пуста.',
            'No products in the cart.'              => 'Ваша корзина пуста.',
            'Return to shop'                        => 'Перейти в каталог',
            'New in store'                          => 'Новинки каталога',
            'Cart'                                  => 'Корзина',
            'Cart totals'                           => 'Итог заказа',
            'Subtotal'                              => 'Сумма',
            'Total'                                 => 'Итого',
            'Shipping'                              => 'Доставка',
            'Proceed to checkout'                   => 'Оформить заказ',
            'Update cart'                           => 'Обновить корзину',
            'Continue shopping'                     => 'Продолжить покупки',
            'Remove this item'                      => 'Убрать товар',
            'Remove'                                => 'Удалить',
            'Product'                               => 'Товар',
            'Price'                                 => 'Цена',
            'Quantity'                              => 'Количество',
            'Coupon:'                               => 'Купон:',
            'Apply coupon'                          => 'Применить купон',
            'Have a coupon?'                        => 'Есть купон?',
            'Coupon code'                           => 'Код купона',
            'Enter code'                            => 'Введите код',
            // Оформление заказа
            'Checkout'                              => 'Оформление заказа',
            'Place order'                           => 'Оформить заказ',
            'Your order'                            => 'Ваш заказ',
            'Order total'                           => 'Итого',
            'Order summary'                         => 'Состав заказа',
            'Billing details'                       => 'Контактные данные',
            'Order notes'                           => 'Комментарий к заказу',
            'Optional'                              => 'Необязательно',
            'Notes about your order, e.g. special notes for delivery.' => 'Пожелания по заказу или доставке.',
            'Payment'                               => 'Оплата',
            'Payment method'                        => 'Способ оплаты',
            'Please fill in your details above to proceed.' => 'Пожалуйста, заполните поля выше.',
            'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => 'Способы оплаты временно недоступны. Пожалуйста, свяжитесь с нами.',
            'First name'                            => 'Имя',
            'Last name'                             => 'Фамилия',
            'Company name (optional)'               => 'Компания (необязательно)',
            'Country / Region'                      => 'Страна',
            'Street address'                        => 'Адрес',
            'Apartment, suite, unit, etc. (optional)' => 'Квартира, офис (необязательно)',
            'Town / City'                           => 'Город',
            'State / County'                        => 'Регион',
            'Postcode / ZIP'                        => 'Индекс',
            'Phone'                                 => 'Телефон',
            'Email address'                         => 'Email',
            'Additional information'                => 'Дополнительно',
            // Товары
            'Add to cart'                           => 'В корзину',
            'Read more'                             => 'Подробнее',
            'Select options'                        => 'Выбрать',
            'Out of stock'                          => 'Нет в наличии',
            'In stock'                              => 'В наличии',
            'In Stock'                              => 'В наличии',
            'On backorder'                          => 'Под заказ',
            'SKU:'                                  => 'Артикул:',
            'Category:'                             => 'Категория:',
            'Categories:'                           => 'Категории:',
            'Tag:'                                  => 'Тег:',
            'Tags:'                                 => 'Теги:',
            'Sale!'                                 => 'Акция!',
            'Related products'                      => 'Похожие товары',
            'You may also like&hellip;'             => 'Вам также может понравиться',
            'Description'                           => 'Описание',
            'Reviews (%d)'                          => 'Отзывы (%d)',
            'Clear'                                 => 'Сбросить',
            'Choose an option'                      => 'Выберите вариант',
            'Please select some product options before adding this product to your cart.' => 'Пожалуйста, выберите параметры товара перед добавлением в корзину.',
            'You cannot add another "%s" to your cart.' => 'Вы не можете добавить ещё один «%s» в корзину.',
            // Уведомления
            'has been added to your cart.'          => 'добавлен в корзину.',
            '"%s" has been added to your cart.'     => '«%s» добавлен в корзину.',
            'View cart'                             => 'Перейти в корзину',
            'Cart updated.'                         => 'Корзина обновлена.',
            'Sorry, this product cannot be purchased.' => 'Извините, этот товар недоступен для покупки.',
            'An error occurred. Please try again.'  => 'Произошла ошибка. Попробуйте ещё раз.',
            'Invalid email address.'                => 'Неверный адрес email.',
            'Billing %s is a required field.'       => 'Поле «%s» обязательно для заполнения.',
            'Shipping %s is a required field.'      => 'Поле «%s» обязательно для заполнения.',
            'Please enter a valid email address.'   => 'Введите корректный адрес email.',
            'Please enter a valid phone number.'    => 'Введите корректный номер телефона.',
            'Invalid postcode / ZIP.'               => 'Неверный почтовый индекс.',
            // Поиск и страницы
            'Search results for: &ldquo;%s&rdquo;' => 'Результаты поиска: «%s»',
            'No products were found matching your selection.' => 'Товары не найдены.',
            'No products found'                     => 'Товары не найдены',
            'Nothing found'                         => 'Ничего не найдено',
            'It seems we can&rsquo;t find what you&rsquo;re looking for.' => 'По вашему запросу ничего не найдено.',
            'Search'                                => 'Поиск',
        ];
    }
    return $map[$text] ?? $translated;
}

// ── Plural-формы WooCommerce ─────────────────────────────────────────────────
add_filter('ngettext', function($translated, $single, $plural, $number, $domain) {
    if ($domain !== 'woocommerce') return $translated;
    static $pmap = [
        '%d item'  => ['%d товар', '%d товара', '%d товаров'],
        '%d items' => ['%d товар', '%d товара', '%d товаров'],
    ];
    if (isset($pmap[$single])) {
        $n  = $number % 100;
        $n1 = $n % 10;
        if ($n >= 11 && $n <= 19)   return sprintf($pmap[$single][2], $number);
        if ($n1 === 1)               return sprintf($pmap[$single][0], $number);
        if ($n1 >= 2 && $n1 <= 4)   return sprintf($pmap[$single][1], $number);
        return sprintf($pmap[$single][2], $number);
    }
    return $translated;
}, 10, 5);

// ── Поиск: только товары и страницы ─────────────────────────────────────────
add_filter('pre_get_posts', function($query) {
    if ($query->is_search() && !is_admin() && $query->is_main_query()) {
        $query->set('post_type', ['post', 'page', 'product']);
    }
    return $query;
});

// ════════════════════════════════════════════════════════════════════════════
// SEO
// ════════════════════════════════════════════════════════════════════════════

// 1. robots.txt
add_filter('robots_txt', function($out, $public) {
    if (!$public) return $out;
    $out .= "Disallow: /cart/\n";
    $out .= "Disallow: /checkout/\n";
    $out .= "Disallow: /wp-login.php\n";
    $out .= "Disallow: /?s=\n";
    $out .= "\nSitemap: " . home_url('/wp-sitemap.xml') . "\n";
    return $out;
}, 99, 2);

// 2. Title-теги — проект добавляет свои через фильтр с приоритетом < 5
add_filter('pre_get_document_title', function($t) {
    $s = get_bloginfo('name');
    if (is_cart())     return 'Корзина — ' . $s;
    if (is_checkout()) return 'Оформление заказа — ' . $s;
    if (is_shop())     return 'Каталог — ' . $s;
    return $t;
}, 5);

// 3. Meta description + canonical + Open Graph + Twitter Card
add_action('wp_head', function() {
    global $post;
    $site    = get_bloginfo('name');
    $url     = home_url('/');
    $img     = defined('ADKLAB_OG_IMAGE') ? ADKLAB_OG_IMAGE : get_template_directory_uri() . '/assets/images/hero-bg.jpg';
    $type    = 'website';
    $desc    = '';
    $noindex = false;

    if (is_front_page()) {
        $desc      = defined('ADKLAB_SITE_DESC') ? ADKLAB_SITE_DESC : get_bloginfo('description');
        $canonical = $url;
    } elseif (is_product()) {
        $product = wc_get_product($post->ID);
        $raw     = $product ? wp_strip_all_tags($product->get_short_description() ?: $product->get_description()) : '';
        $desc    = $raw ? mb_substr($raw, 0, 155) : '';
        $type    = 'product';
        $canonical = get_permalink();
        if (has_post_thumbnail($post->ID)) $img = get_the_post_thumbnail_url($post->ID, 'large');
    } elseif (is_product_category()) {
        $term = get_queried_object();
        $desc = $term->description ? mb_substr(wp_strip_all_tags($term->description), 0, 155) : '';
        $canonical = get_term_link($term);
    } elseif (is_shop()) {
        $desc      = defined('ADKLAB_SITE_DESC') ? ADKLAB_SITE_DESC : '';
        $canonical = wc_get_page_permalink('shop');
    } elseif (is_page('privacy') || is_cart() || is_checkout()) {
        $noindex   = true;
        $canonical = get_permalink() ?: $url;
    } else {
        $desc      = mb_substr(wp_strip_all_tags(get_the_excerpt() ?: ''), 0, 155);
        $canonical = get_permalink() ?: $url;
    }

    if ($noindex) echo '<meta name="robots" content="noindex, follow">' . "\n";
    if ($desc)    echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    if (!empty($canonical)) echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

    $og_title = wp_get_document_title();
    echo '<meta property="og:type"        content="' . esc_attr($type) . '">' . "\n";
    echo '<meta property="og:title"       content="' . esc_attr($og_title) . '">' . "\n";
    if ($desc) echo '<meta property="og:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta property="og:url"         content="' . esc_url($canonical ?? $url) . '">' . "\n";
    echo '<meta property="og:image"       content="' . esc_url($img) . '">' . "\n";
    echo '<meta property="og:site_name"   content="' . esc_attr($site) . '">' . "\n";
    echo '<meta property="og:locale"      content="ru_RU">' . "\n";
    echo '<meta name="twitter:card"       content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title"      content="' . esc_attr($og_title) . '">' . "\n";
    if ($desc) echo '<meta name="twitter:description" content="' . esc_attr($desc) . '">' . "\n";
    echo '<meta name="twitter:image"      content="' . esc_url($img) . '">' . "\n";
}, 1);

// 4. JSON-LD: Organization + WebSite + Product + BreadcrumbList
add_action('wp_head', function() {
    global $post;
    $site_url  = home_url('/');
    $site_name = get_bloginfo('name');
    $logo_url  = get_template_directory_uri() . '/assets/images/logo.png';

    // Organization
    $org = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        '@id'      => $site_url . '#org',
        'name'     => defined('ADKLAB_ORG_NAME') ? ADKLAB_ORG_NAME : $site_name,
        'url'      => $site_url,
        'logo'     => ['@type' => 'ImageObject', 'url' => $logo_url],
    ];
    if (defined('ADKLAB_ORG_EMAIL'))   $org['email']     = ADKLAB_ORG_EMAIL;
    if (defined('ADKLAB_ORG_PHONE')) {
        $phones = array_map('trim', explode(',', ADKLAB_ORG_PHONE));
        $org['telephone'] = count($phones) === 1 ? $phones[0] : $phones;
    }
    if (defined('ADKLAB_ORG_ADDRESS') && defined('ADKLAB_ORG_CITY')) {
        $org['address'] = [
            '@type'           => 'PostalAddress',
            'streetAddress'   => ADKLAB_ORG_ADDRESS,
            'addressLocality' => ADKLAB_ORG_CITY,
            'addressRegion'   => defined('ADKLAB_ORG_REGION') ? ADKLAB_ORG_REGION : '',
            'postalCode'      => defined('ADKLAB_ORG_POSTAL') ? ADKLAB_ORG_POSTAL : '',
            'addressCountry'  => 'RU',
        ];
    }
    if (defined('ADKLAB_ORG_BRAND')) {
        $brands = array_map('trim', explode(',', ADKLAB_ORG_BRAND));
        $org['brand'] = array_map(fn($b) => ['@type' => 'Brand', 'name' => $b], $brands);
    }
    adklab_ld($org);

    // WebSite — только главная
    if (is_front_page()) {
        adklab_ld([
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            '@id'             => $site_url . '#website',
            'url'             => $site_url,
            'name'            => $site_name,
            'inLanguage'      => 'ru-RU',
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $site_url . '?s={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ]);
    }

    // Product — страница товара
    if (is_product() && function_exists('wc_get_product')) {
        $product = wc_get_product($post->ID);
        if ($product) {
            $raw = wp_strip_all_tags($product->get_short_description() ?: $product->get_description());
            adklab_ld([
                '@context'    => 'https://schema.org',
                '@type'       => 'Product',
                'name'        => get_the_title(),
                'description' => mb_substr($raw, 0, 300),
                'image'       => get_the_post_thumbnail_url($post->ID, 'large') ?: $logo_url,
                'url'         => get_permalink(),
                'brand'       => ['@type' => 'Brand', 'name' => defined('ADKLAB_ORG_BRAND') ? explode(',', ADKLAB_ORG_BRAND)[0] : $site_name],
                'offers'      => [
                    '@type'         => 'Offer',
                    'price'         => $product->get_price(),
                    'priceCurrency' => 'RUB',
                    'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url'           => get_permalink(),
                    'seller'        => ['@type' => 'Organization', 'name' => defined('ADKLAB_ORG_NAME') ? ADKLAB_ORG_NAME : $site_name],
                ],
            ]);
        }
    }

    // BreadcrumbList
    if (!is_front_page() && !is_404() && !is_cart() && !is_checkout()) {
        $crumbs = [['name' => 'Главная', 'url' => $site_url]];
        if (is_product()) {
            $crumbs[] = ['name' => 'Каталог', 'url' => home_url('/shop/')];
            $terms = get_the_terms($post->ID, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                $t = reset($terms);
                $crumbs[] = ['name' => $t->name, 'url' => get_term_link($t)];
            }
            $crumbs[] = ['name' => get_the_title(), 'url' => get_permalink()];
        } elseif (is_product_category()) {
            $t = get_queried_object();
            $crumbs[] = ['name' => 'Каталог', 'url' => home_url('/shop/')];
            $crumbs[] = ['name' => $t->name, 'url' => get_term_link($t)];
        } elseif (is_shop()) {
            $crumbs[] = ['name' => 'Каталог', 'url' => home_url('/shop/')];
        } elseif (is_page()) {
            $crumbs[] = ['name' => get_the_title(), 'url' => get_permalink()];
        }
        if (count($crumbs) > 1) {
            adklab_ld([
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => array_map(function($c, $i) {
                    return ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'item' => $c['url']];
                }, $crumbs, array_keys($crumbs)),
            ]);
        }
    }
}, 2);

// 5. Sitemap: убираем users
add_filter('wp_sitemaps_add_provider', function($provider, $name) {
    return $name === 'users' ? false : $provider;
}, 10, 2);

if (!function_exists('adklab_ld')) {
    function adklab_ld(array $data): void {
        echo '<script type="application/ld+json">'
            . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>' . "\n";
    }
}
