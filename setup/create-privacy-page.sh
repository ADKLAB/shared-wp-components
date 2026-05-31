#!/bin/bash
# Создаёт страницу «Политика конфиденциальности» на WordPress-сайте.
# Запускать из корня WordPress (там где wp-config.php).
#
# Использование:
#   bash create-privacy-page.sh
#
# Страница создаётся со слагом /privacy и шорткодом [adklab_privacy_policy ...].
# Контактные данные организации задаются ниже:

ORG="ООО «Название»"
ADDRESS="000000, г. Город, ул. Улица, д. 1"
EMAIL="info@example.ru"
PHONE="8 900 000-00-00"
SITE=$(wp option get siteurl 2>/dev/null)
DATE=$(date +%d.%m.%Y)

CONTENT="[adklab_privacy_policy org=\"${ORG}\" address=\"${ADDRESS}\" email=\"${EMAIL}\" phone=\"${PHONE}\" site=\"${SITE}\" date=\"${DATE}\"]"

# Проверяем, существует ли страница с таким слагом
EXISTING_ID=$(wp post list --post_type=page --name=privacy --fields=ID --format=ids 2>/dev/null)

if [ -n "$EXISTING_ID" ]; then
    wp post update "$EXISTING_ID" \
        --post_title="Политика конфиденциальности" \
        --post_content="$CONTENT" \
        --post_status=publish
    echo "Страница обновлена (ID: $EXISTING_ID)"
else
    wp post create \
        --post_type=page \
        --post_title="Политика конфиденциальности" \
        --post_name=privacy \
        --post_content="$CONTENT" \
        --post_status=publish
    echo "Страница создана"
fi
