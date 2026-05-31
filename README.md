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

**Примеры:**
```
[adklab_contact_form email="info@company.ru" subject="Запрос с сайта"]
[adklab_contact_form email="info@company.ru" extra_field="company" subject="Оптовый запрос с сайта"]
```

### 2. Бейдж «Мой бизнес»
Автоматически добавляется в `wp_footer`. Картинка берётся из `/assets/images/moy-biznes.png` активной темы.

Для кастомизации добавьте в `wp-config.php`:
```php
define('ADKLAB_MOY_BIZNES_LOGO', 'https://site.ru/path/to/logo.png');
define('ADKLAB_MOY_BIZNES_LINK', 'https://mybusiness.buryatia.ru');
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
