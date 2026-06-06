<?php
/**
 * Бейдж «Мой бизнес» — вспомогательные функции.
 *
 * Бейдж встраивается напрямую в footer.php темы (не через wp_footer hook).
 *
 * Использование в footer.php:
 *   <?php adklab_moy_biznes_badge(); ?>
 *
 * Логотип «Мой бизнес» поставляется вместе с компонентом
 * (assets/moy-biznes.png) — копировать в тему не нужно.
 *
 * Настройка через define в wp-config.php (опционально):
 *   define('ADKLAB_MOY_BIZNES_LOGO', 'https://site.ru/path/to/logo.png'); // свой логотип
 *   define('ADKLAB_MOY_BIZNES_LINK', 'https://мойбизнес.рф');
 */

defined('ABSPATH') || exit;

function adklab_moy_biznes_badge(): void {
    $logo = defined('ADKLAB_MOY_BIZNES_LOGO')
        ? ADKLAB_MOY_BIZNES_LOGO
        : plugin_dir_url(__FILE__) . 'assets/moy-biznes.png';

    $link = defined('ADKLAB_MOY_BIZNES_LINK')
        ? ADKLAB_MOY_BIZNES_LINK
        : 'https://мойбизнес.рф';
    ?>
    <span class="footer-support">
        <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener"
           class="footer-support__badge"
           title="Центр предпринимательства «Мой бизнес»">
            <img src="<?php echo esc_url($logo); ?>"
                 alt="Мой бизнес" class="footer-support__logo">
        </a>
    </span>
    <?php
}
