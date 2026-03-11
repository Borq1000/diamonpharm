# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

OpenCart 3.0.3.6 e-commerce installation for diamondpharm.ru (аптека). Основана на русской сборке opencart-russia.ru с ProStore темой и множеством кастомных модулей.

- **Core**: OpenCart 3.0.3.6, PHP, MySQL (`oc_` prefix)
- **Theme**: ProStore
- **Checkout**: Simple Checkout (проприетарная система, ionCube-защита)
- **Languages**: Russian (основной), English, Ukrainian

## Architecture

### Directory Structure

```
├── admin/              # Панель администратора (backend)
├── catalog/            # Фронтенд приложение
├── system/             # Ядро OpenCart + кастомные OCMod-файлы
├── upload/             # Целевая директория для PHPStan и php-cs-fixer
├── tools/phpstan/      # PHPStan extension для Registry
└── 555/                # Архив оригинальной сборки opencart-3.0.3.6-rs (бэкап, не трогать)
```

### MVC Pattern

- **Controllers**: `{admin|catalog}/controller/`
- **Models**: `{admin|catalog}/model/`
- **Views**: `{admin|catalog}/view/template/` — Twig-шаблоны (`.twig`)
- **Registry**: DI-контейнер (`system/engine/registry.php`) — все сервисы доступны через `$this->load->*`

### Модификации (OCMod)

OpenCart использует XML-based модификации вместо прямого редактирования ядра. Активные OCMod-файлы в `system/`:

- `tweak.ocmod.xml` — русская сборка: транслитерация имён файлов при загрузке, курсы CBR вместо ECB, расширение колонки меню, файловый менеджер с breadcrumbs
- `tweak-54fz.ocmod.xml` — соответствие 152-ФЗ: согласие на обработку данных в форме контактов
- `simple_twig_fix.ocmod.xml` — фикс для Simple Checkout
- `gallery_rb_fix.ocmod.xml` — фикс для galleryrb
- `system/library/image_quality.ocmod.xml` — качество JPEG (90→85)
- `prostore_slider_modal.ocmod.xml` (в корне) — модальный слайдер ProStore

После изменения OCMod-файлов: **Admin → Extensions → Modifications → Refresh**.

## Development Commands

### Статический анализ (PHPStan)

```bash
phpstan analyse
# Уровень: 1, конфиг: phpstan.neon, цель: ./upload/
# Кастомный extension: tools/phpstan/RegistryPropertyReflectionExtension.php
# (понимает динамические свойства OpenCart Registry)
```

### Форматирование кода (PHP-CS-Fixer)

```bash
php-cs-fixer fix
# Конфиг: .php-cs-fixer.php — PER-CS2.0, TabIndent, DoctrineAnnotation
# Цель: ./upload/, исключает vendor/
```

### Отладка

```bash
tail -f system/storage/logs/error.log
```

## Кастомные модули проекта

### ProStore-модули (`catalog/controller/extension/module/prostore_*`)

Все компоненты фронтенда темы:
- `prostore_blog` / `prostore_blog_mod` / `prostorecat_blog` / `prostoretag_blog` — блог
- `prostore_main_slider` / `prostore_main_slider_hq` — главный слайдер
- `prostore_brands` — витрина брендов
- `prostore_advantages` — блок преимуществ
- `prostore_product_tabs` — табы товара
- `prostore_subscribe` — подписка
- `prostore_reviews` / `prostore_review_shop` — отзывы
- `prostore_promo` / `prostore_stories` — промо-блоки

### Cheaper30 (`catalog/controller/extension/module/cheaper30.php`)

Модуль «Узнать стоимость» / «Получить прайс». При отправке формы:
1. Сохраняет запрос через `model_extension_module_cheaper30->writesendquick()`
2. POST-запрос на webhook `https://ep.morekit.io/a359cca6e99605a5accc4028d8681c98` (CRM-интеграция)
3. Передаёт UTM-метки, yclid, roistat, _ym_uid

### Diamond Categories (`catalog/controller/extension/module/diamond_categories.php`)

Кастомный модуль — сетка категорий с картинками и количеством товаров. Изображения выводятся без ресайза (`image/` + путь).

### Callback (`catalog/controller/extension/module/callback.php`)

Форма «Перезвоните мне» — сохраняет заявку и отправляет email-уведомление.

### Found Cheaper (`catalog/controller/extension/module/found_cheaper.php`)

«Нашли дешевле?» — сбор данных о ценах конкурентов. Аналогичен cheaper30 по структуре, привязан к настройкам ProStore.

### SyncMS (`catalog/controller/extension/module/syncms.php`)

Синхронизация с CRM. Поддерживает **cron для автоматической синхронизации** (в отличие от остальных модулей).

### GalleryRB (`catalog/controller/extension/module/galleryrb.php`)

Галерея с карусельным и masonry-макетами. Зависимости: Magnific Popup, опционально Owl Carousel / Masonry.js.

### Optimg (admin) (`admin/controller/extension/module/optimg.php`)

Оптимизатор изображений каталога. Статус оптимизации хранится в `cache/optimised.dat`.

## Конфигурация

```php
// config.php и admin/config.php
DB_DRIVER: 'mysqli'
DB_HOSTNAME: 'localhost'
DB_DATABASE: 'a0231004_app_opencart_0'
DB_PREFIX: 'oc_'
```

## Simple Checkout

Проприетарная замена стандартного OpenCart-чекаута с **ionCube-защитой**:
- Контроллеры: `catalog/controller/checkout/simplecheckout*.php` (11 файлов)
- JS: `catalog/view/javascript/simplecheckout.js`
- Library: `system/library/simple/` — ядро с version-specific ionCube-encoded файлами (`php/` подкаталог для PHP 5.3–7.2)
- Основной файл `simple.php` — диспетчер, загружает нужную encoded-версию по текущей PHP
- **Нельзя** редактировать encoded-файлы; менять логику можно только в контроллерах и шаблонах

## .htaccess — ключевые правила

- **NitroPack**: кеш-заголовки (1 год для изображений/шрифтов, 1 неделя для XML/TXT), CSS/JS через `serveFile.php`
- **HTTPS**: принудительный редирект `http → https`
- **WWW**: `www.diamondpharm.ru → diamondpharm.ru` (301)
- **Register**: `/account/register → /account/simpleregister` (301)
- **Memory limit**: `php_value memory_limit 2028M`
- **Защита**: блокировка прямого доступа к `.tpl`, `.twig`, `.ini`, `.log`, `.txt`

## Важные пути

| Что | Где |
|-----|-----|
| Логи ошибок | `system/storage/logs/error.log` |
| Кеш шаблонов | `system/storage/cache/` |
| Модификации (авто) | `system/storage/modification/` — не редактировать напрямую |
| Конфиги системы | `system/config/{admin,catalog,default}.php` |
| ionCube Simple Checkout | `system/library/simple/php/` — не редактировать |

## Важные примечания

- **Общение**: всегда отвечай на русском языке
- После изменений шаблонов нужно очистить кеш: Admin → Dashboard → Developer Settings → Refresh Cache
- `system/storage/modification/` генерируется автоматически — правь только исходные OCMod-файлы
- PHPStan и php-cs-fixer применяются к директории `upload/`, не к корню
- `555/` — архив оригинальной русской сборки, не модифицировать
