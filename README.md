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
