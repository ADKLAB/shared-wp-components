<?php
/**
 * Бейдж «Изготовлено при поддержке Центра предпринимательства Мой бизнес»
 * Автоматически добавляется в wp_footer всех страниц.
 *
 * Настройка через define в wp-config.php или через опцию WordPress:
 *   define('ADKLAB_MOY_BIZNES_LOGO', '/path/to/logo.png'); // абсолютный URL
 *   define('ADKLAB_MOY_BIZNES_LINK', 'https://mybusiness.ru'); // ссылка при клике
 */

defined('ABSPATH') || exit;

add_action('wp_footer', 'adklab_moy_biznes_badge', 100);

function adklab_moy_biznes_badge(): void {
    // Путь к картинке — сначала смотрим define, потом ищем в активной теме
    if (defined('ADKLAB_MOY_BIZNES_LOGO')) {
        $logo_url = ADKLAB_MOY_BIZNES_LOGO;
    } else {
        $logo_url = get_template_directory_uri() . '/assets/images/moy-biznes.png';
    }

    $link = defined('ADKLAB_MOY_BIZNES_LINK') ? ADKLAB_MOY_BIZNES_LINK : 'https://mybusiness.buryatia.ru';
    ?>
    <div class="adklab-moy-biznes-badge">
        <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener"
           title="Изготовлено при поддержке Центра предпринимательства «Мой бизнес»">
            <img src="<?php echo esc_url($logo_url); ?>"
                 alt="Изготовлено при поддержке Мой бизнес">
        </a>
    </div>
    <style>
    .adklab-moy-biznes-badge {
        position: fixed; bottom: 20px; right: 20px; z-index: 9999;
        opacity: 0.88; transition: opacity 0.2s, transform 0.2s;
    }
    .adklab-moy-biznes-badge:hover { opacity: 1; transform: scale(1.03); }
    .adklab-moy-biznes-badge img {
        width: 150px; height: auto;
        filter: drop-shadow(0 3px 10px rgba(0,0,0,0.18));
        display: block;
    }
    </style>
    <?php
}
