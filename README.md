# ADKLAB Shared WP Components

Переиспользуемые WordPress-компоненты для всех проектов ADKLAB.

## Компоненты

### 1. Форма обратной связи
Шорткод `[adklab_contact_form]` с настраиваемыми атрибутами.

**Параметры:**
| Атрибут | По умолчанию | Описание |
|---|---|---|
| `email` | admin_email | Email получателя |
| `subject` | Новая заявка с сайта ... | Тема письма |
| `extra_field` | `none` | Доп. поле: `company` — добавляет поле «Компания» |
| `success_text` | Ваше сообщение отправлено... | Текст после успешной отправки |
| `privacy_url` | `/privacy` | Ссылка на политику конфиденциальности |
| `btn_text` | Отправить заявку | Текст кнопки отправки |

**Примеры:**
```
[adklab_contact_form email="info@company.ru" subject="Запрос с сайта"]
[adklab_contact_form email="info@company.ru" extra_field="company" subject="Оптовый запрос с сайта"]
```

### 2. Политика конфиденциальности (152-ФЗ)
Шорткод `[adklab_privacy_policy]` генерирует готовый текст политики с реквизитами организации.

**Параметры:**
| Атрибут | По умолчанию | Описание |
|---|---|---|
| `org` | название сайта | Наименование организации-оператора ПД |
| `address` | — | Юридический/фактический адрес |
| `email` | admin_email | E-mail для обращений по ПД |
| `phone` | — | Телефон |
| `site` | home_url() | URL сайта |
| `date` | сегодня | Дата последнего обновления (дд.мм.гггг) |
| `retention_years` | `3` | Срок хранения данных в годах |

**Пример:**
```
[adklab_privacy_policy org="ООО «Название»" address="670000, г. Улан-Удэ, ул. Примерная, д. 1" email="info@company.ru" phone="8 900 000-00-00"]
```

**Быстрое создание страницы через WP-CLI:**
```bash
# Отредактируйте реквизиты в файле и запустите из корня WordPress:
bash setup/create-privacy-page.sh
```
Создаёт страницу со слагом `/privacy` (или обновляет существующую).

### 3. Бейдж «Мой бизнес»
Встраивается в нижнюю строку футера (`footer-bottom`) рядом с копирайтом. Картинка берётся из `/assets/images/moy-biznes.png` активной темы.

**1. Скопируйте `moy-biznes.png` в `/assets/images/` темы.**

**2. Вызовите функцию в `footer.php` внутри блока `footer-bottom`:**
```php
<?php adklab_moy_biznes_badge(); ?>
```

**3. Добавьте CSS в `main.css` темы:**
```css
.footer-support {
    font-size: .7rem;
    color: rgba(255,255,255,.25);
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.footer-support__badge {
    display: inline-flex;
    align-items: center;
    line-height: 0;
    opacity: .85;
    transition: opacity .25s;
}
.footer-support__badge:hover { opacity: 1; }
.footer-support .footer-support__logo {
    height: 52px;
    width: auto;
    display: block;
    max-width: none; /* сброс WooCommerce max-width: 100% */
}
```

Для кастомизации добавьте в `wp-config.php`:
```php
define('ADKLAB_MOY_BIZNES_LOGO', 'https://site.ru/path/to/logo.png');
define('ADKLAB_MOY_BIZNES_LINK', 'https://мойбизнес.рф');
```

### 4. WooCommerce: русификация + SEO
Подключается автоматически. Настраивается через `define` в `wp-config.php`:

```php
define('ADKLAB_ORG_NAME',    'ООО «Название»');
define('ADKLAB_ORG_ADDRESS', 'ул. Примерная, д. 1');
define('ADKLAB_ORG_CITY',    'Улан-Удэ');
define('ADKLAB_ORG_REGION',  'Республика Бурятия');
define('ADKLAB_ORG_POSTAL',  '670000');
define('ADKLAB_ORG_PHONE',   '+7-900-000-00-00, +7-900-000-00-01'); // через запятую если несколько
define('ADKLAB_ORG_EMAIL',   'info@company.ru');
define('ADKLAB_ORG_BRAND',   'BrandA, BrandB'); // через запятую если несколько
define('ADKLAB_SITE_DESC',   'Описание для главной страницы и OG-тегов.');
define('ADKLAB_OG_IMAGE',    'https://site.ru/path/to/og-image.jpg');
define('ADKLAB_DISABLE_COUPONS', '1'); // '0' чтобы оставить купоны
```

**Что включено:**
- Полная русификация WooCommerce (корзина, чекаут, товары, уведомления)
- Правильное склонение «1 товар / 2 товара / 5 товаров»
- Отключение купонов, очистка нотисов корзины
- Убран английский текст политики на чекауте
- SEO: title-теги для `/cart`, `/checkout`, `/shop`
- Meta description, canonical, Open Graph, Twitter Card
- JSON-LD: Organization, WebSite, Product, BreadcrumbList
- robots.txt: закрыты корзина, чекаут, поиск; добавлен sitemap
- Sitemap: убраны пользователи

**Дополнение title-тегов для конкретных страниц проекта** — добавить в `functions.php` темы с приоритетом < 5:
```php
add_filter('pre_get_document_title', function($t) {
    $s = get_bloginfo('name');
    if (is_front_page()) return $s . ' — Ваш слоган';
    if (is_page('about')) return 'О нас — ' . $s;
    return $t;
}, 4);
```

## Установка

### Как mu-plugin (рекомендуется)
```bash
rsync -az adklab-components/ user@server:/path/to/wp-content/mu-plugins/adklab-components/
```
mu-plugins включаются автоматически — активировать в WP Admin не нужно.

### Как обычный плагин
```bash
rsync -az adklab-components/ user@server:/path/to/wp-content/plugins/adklab-components/
```
Затем активировать в WP Admin → Плагины.

## Деплой через GitHub Actions

Добавьте в ваш `deploy.yml`:
```yaml
- name: Deploy shared components
  run: |
    sshpass -p '${{ secrets.BEGET_PASSWORD }}' rsync -az \
      shared-components/adklab-components/ \
      ${{ secrets.BEGET_USER }}@${{ secrets.BEGET_HOST }}:/path/to/wp-content/mu-plugins/adklab-components/
```
