# Full Migration  Changelog

## 1 - fix some issues and split class-laqirapay-functions -> ajax & extras php files

Files changed:
- includes/.DS_Store
- includes/class-laqirapay-activator.php
- includes/class-laqirapay-ajax.php
- includes/class-laqirapay-deactivator.php
- includes/class-laqirapay-extras.php
- includes/class-laqirapay-functions.php
- includes/class-laqirapay-i18n.php
- includes/class-laqirapay-loader.php
- includes/class-laqirapay-main.php
- includes/class-laqirapay-translations.php
- includes/class-laqirapay-uninstallation.php
- includes/class-laqirapay-update-checker.php
- includes/index.php
- includes/woocommerce/class-wc-gateway-laqirapay-block.php
- includes/woocommerce/class-wc-gateway-laqirapay.php

## 2 - Add composer QA script placeholders

Files changed:
- composer.json
- composer.lock

## 3 - refactor: extract settings controller

Files changed:
- admin/class-laqirapay-admin.php
- app/Controllers/Admin/SettingsController.php
- app/Views/admin/field-api-key.php
- app/Views/admin/field-delete-data-uninstall.php
- app/Views/admin/field-main-contract.php
- app/Views/admin/field-main-rpc-url.php
- app/Views/admin/field-only-logged-in-user.php
- app/Views/admin/field-order-recovery-status.php
- app/Views/admin/field-walletconnect-project-id.php
- app/Views/admin/section-general.php

## 4 - refactor: introduce service layer

Files changed:
- admin/class-laqirapay-admin.php
- app/Services/BlockchainService.php
- app/Services/JwtService.php
- app/Services/WooCommerceService.php
- includes/class-laqirapay-main.php
- includes/init/register-wc-blocks.php
- includes/woocommerce/class-wc-gateway-laqirapay-block.php
- includes/woocommerce/class-wc-gateway-laqirapay.php

## 5 - feat: centralize plugin extras hooks

Files changed:
- admin/partials/laqirapay-admin-currency-rates.php
- admin/partials/laqirapay-admin-order-recovery.php
- admin/partials/laqirapay-admin-settings-display.php
- admin/partials/laqirapay-admin-transactions.php
- app/Hooks/ExtrasService.php
- app/Support/Requirements.php
- includes/class-laqirapay-ajax.php
- includes/class-laqirapay-extras.php
- includes/class-laqirapay-main.php


## 6 - fix: resolve plugin root for init scripts

Files changed:
- admin/class-laqirapay-admin.php
- admin/partials/laqirapay-admin-currency-rates.php
- admin/partials/laqirapay-admin-order-recovery.php
- admin/partials/laqirapay-admin-settings-display.php
- admin/partials/laqirapay-admin-transactions.php
- app/Hooks/ExtrasService.php
- app/Services/BlockchainService.php
- app/Services/JwtService.php
- app/Services/WooCommerceService.php
- app/Support/Requirements.php
- includes/class-laqirapay-ajax.php
- includes/class-laqirapay-extras.php
- includes/class-laqirapay-main.php
- includes/init/register-gateway.php
- includes/init/register-wc-blocks.php
- includes/woocommerce/class-wc-gateway-laqirapay-block.php
- includes/woocommerce/class-wc-gateway-laqirapay.php

## 7 - identify function categories and create services 

Files changed:
- admin/class-laqirapay-admin.php
- includes/init/register-wc-blocks.php


## 8 - Add Ajax controller with nonce checks

Files changed:
- app/Controllers/Ajax/AjaxController.php
- includes/class-laqirapay-main.php

## 9 - refactor: relocate includes into app

Files changed:
- MANIFEST.txt
- app/Controllers/Ajax/LegacyAjax.php
- app/Init/register-gateway.php
- app/Init/register-wc-blocks.php
- app/Main.php
- app/Support/AbiDecoder.php
- app/Support/Activator.php
- app/Support/Deactivator.php
- app/Support/Functions.php
- app/Support/I18n.php
- app/Support/Loader.php
- app/Support/Translations.php
- app/Support/Uninstaller.php
- app/Support/UpdateChecker.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php
- app/WooCommerce/class-wc-gateway-laqirapay.php
- includes/index.php
- laqirapay.php
- security.md

## 10 - Add Ajax controller and register actions

Files changed:

## 11 - Refactor gateway into controller-based architecture

Files changed:
- app/Controllers/Public/PaymentController.php
- app/Init/register-gateway.php
- app/Init/register-wc-blocks.php
- app/Main.php
- app/Views/public/checkout.php
- app/WooCommerce/Gateway.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php
- app/WooCommerce/class-wc-gateway-laqirapay.php

## 12 - Refactor WooCommerce gateway into controller-based architecture

Files changed:

## 13 - feat: add public assets controller

Files changed:
- app/Controllers/Public/AssetsController.php
- app/Services/UtilityService.php
- app/Views/public/assets/scripts.php
- app/Views/public/assets/styles.php


## 14 - chore: restore previous mo catalogs

Files changed:
- app/Bootstrap.php
- app/Core/I18n.php
- app/Main.php
- app/Support/I18n.php
- languages/laqirapay-en_US.l10n.php
- languages/laqirapay-es_ES.l10n.php
- languages/laqirapay-fa_IR.l10n.php
- languages/laqirapay-hi_IN.l10n.php
- languages/laqirapay-it_IT.l10n.php
- languages/laqirapay-tr_TR.l10n.php
- languages/laqirapay-zh_CN.l10n.php

## 15 - refactor: centralize i18n loading

Files changed:

## 16 - Add Settings and Transaction models and integrate

Files changed:
- app/Controllers/Admin/SettingsController.php
- app/Controllers/Ajax/AjaxController.php
- app/Controllers/Public/PaymentController.php
- app/Models/Settings.php
- app/Models/Transaction.php
- app/Services/BlockchainService.php

## 17 - relocate admin assets under app

Files changed:
- MANIFEST.txt
- admin/index.php
- app/Admin/.DS_Store
- app/Admin/class-laqirapay-admin.php
- app/Admin/css/laqirapay-admin.css
- app/Admin/index.php
- app/Admin/js/laqirapay-admin.js
- app/Admin/partials/laqirapay-admin-currency-rates.php
- app/Admin/partials/laqirapay-admin-order-recovery.php
- app/Admin/partials/laqirapay-admin-settings-display.php
- app/Admin/partials/laqirapay-admin-transactions.php
- app/Main.php
- languages/laqirapay-en_US.po
- languages/laqirapay-es_ES.po
- languages/laqirapay-fa_IR.po
- languages/laqirapay-hi_IN.po
- languages/laqirapay-it_IT.po
- languages/laqirapay-tr_TR.po
- languages/laqirapay-zh_CN.po
- languages/laqirapay.pot


## 18 - refactor public assets controller

Files changed:
- MANIFEST.txt
- app/Main.php
- public/class-laqirapay-public.php

## 19 - Refactor public asset loading to AssetsController

Files changed:

## 20 - Remove legacy isRTL helper

Files changed:
- app/Controllers/Public/PaymentController.php
- app/Hooks/ExtrasService.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php

## 21 - refactor: move public assets to assets directory

Files changed:
- MANIFEST.txt
- app/Controllers/Public/AssetsController.php
- app/Controllers/Public/PaymentController.php
- app/Views/public/settings-display.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php
- assets/public/css/font-awesome.css
- assets/public/css/laqirapay-public-rtl.css
- assets/public/css/laqirapay-public.css
- assets/public/js/laqirapay-first.js
- public/.DS_Store
- public/index.php


## 22 - Add service namespaces and update references

Files changed:
- app/Admin/class-laqirapay-admin.php
- app/Hooks/ExtrasService.php
- app/Init/register-wc-blocks.php
- app/Main.php
- app/Services/BlockchainService.php
- app/Services/JwtService.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php


## 23 - Add admin dashboard view and fix menu setup

Files changed:
- app/Admin/class-laqirapay-admin.php
- app/Admin/partials/laqirapay-admin-display.php


## 24 - Harden admin and hook outputs

Files changed:
- app/Admin/partials/laqirapay-admin-currency-rates.php
- app/Admin/partials/laqirapay-admin-order-recovery.php
- app/Admin/partials/laqirapay-admin-settings-display.php
- app/Admin/partials/laqirapay-admin-transactions.php
- app/Controllers/Ajax/LegacyAjax.php
- app/Hooks/ExtrasService.php


## 25 - Refactor inline assets into files

Files changed:
- app/Admin/class-laqirapay-admin.php
- app/Hooks/ExtrasService.php
- assets/admin/css/menu-icon.css
- assets/public/css/tx-form.css
- assets/public/js/tx-repair.js
- assets/public/js/tx-view.js

## 26 - refactor: replace legacy admin class

Files changed:
- MANIFEST.txt
- app/Admin/class-laqirapay-admin.php
- app/Controllers/Admin/AdminController.php
- app/Controllers/Admin/SettingsController.php
- app/Main.php
- docs/legacy-utils.md


## 27 - refactor: modularize transaction utilities

Files changed:
- app/Hooks/ExtrasService.php


## 28 - Add checkout validation CSS and JS files

Files changed:
- app/WooCommerce/Gateway.php
- assets/public/css/checkout-validation.css
- assets/public/js/checkout-validation.js


## 29 - remove old service classes

Files changed:
- app/Controllers/Admin/AdminController.php
- app/Controllers/Admin/SettingsController.php
- app/Controllers/Ajax/LegacyAjax.php
- app/Controllers/Public/PaymentController.php
- app/Helpers/BlockchainHelper.php
- app/Helpers/JwtHelper.php
- app/Helpers/WooCommerceHelper.php
- app/Hooks/ExtrasService.php
- app/Init/register-wc-blocks.php
- app/Main.php
- app/Services/BlockchainService.php
- app/Services/JwtService.php
- app/Services/WooCommerceService.php
- app/Support/Functions.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php


## 30 - refactor admin page rendering

Files changed:
- app/Controllers/Admin/AdminController.php


## 31 - Add TxRepairForm for centralized transaction repair form

Files changed:
- app/Hooks/ExtrasService.php
- app/Support/TxRepairForm.php

## 32 - Refactor tx repair form into reusable TxRepairForm class

Files changed:

## 33 - Enhance nonce checks, sanitize inputs, and cache blockchain data

Files changed:
- app/Controllers/Ajax/LegacyAjax.php
- app/Helpers/BlockchainHelper.php
- app/Hooks/ExtrasService.php
- assets/public/js/laqirapay-first.js

## 34 - Harden AJAX endpoints and cache blockchain metadata

Files changed:

## 35 - Add PHPUnit tests and test infrastructure

Files changed:
- .gitignore
- composer.json
- composer.lock
- phpunit.xml.dist
- tests/bootstrap.php
- tests/integration/AjaxTest.php
- tests/integration/PaymentControllerTest.php
- tests/unit/BlockchainHelperTest.php
- tests/unit/JwtHelperTest.php

## 36 - Add CI workflow for PHP tests

Files changed:
- .github/workflows/php.yml

## 37 - Handle PHP platform requirements in CI

Files changed:
- .github/workflows/php.yml

## 38 - Configure coverage filter and skip tests on unsupported PHP

Files changed:
- .github/workflows/php.yml
- phpunit.xml.dist

## 39 - refactor: rename Public controllers namespace

Files changed:
- app/Controllers/Frontend/AssetsController.php
- app/Controllers/Frontend/PaymentController.php
- app/Main.php
- app/WooCommerce/Gateway.php
- tests/integration/PaymentControllerTest.php


## 40 - Add LaqiraPay namespaces and cleanup autoload usage

Files changed:
- app/Admin/partials/laqirapay-admin-currency-rates.php
- app/Admin/partials/laqirapay-admin-order-recovery.php
- app/Admin/partials/laqirapay-admin-settings-display.php
- app/Admin/partials/laqirapay-admin-transactions.php
- app/Hooks/ExtrasService.php
- app/Main.php
- app/Support/Requirements.php
- app/Support/TxRepairForm.php


## 41 - Sanitize transaction input

Files changed:
- app/Models/Transaction.php


## 42 - Sanitize and escape admin page GET parameters

Files changed:
- app/Controllers/Admin/AdminController.php

## 43 - Sanitize template output with esc_html

Files changed:
- app/Views/admin/field-api-key.php
- app/Views/admin/field-delete-data-uninstall.php
- app/Views/admin/field-only-logged-in-user.php
- app/Views/admin/field-order-recovery-status.php
- app/Views/public/assets/styles.php


## 44 - Escape translation strings

Files changed:
- app/Admin/partials/laqirapay-admin-transactions.php
- app/Controllers/Admin/AdminController.php
- app/Controllers/Admin/SettingsController.php
- app/Controllers/Ajax/AjaxController.php
- app/Controllers/Ajax/LegacyAjax.php
- app/Controllers/Frontend/PaymentController.php
- app/Helpers/BlockchainHelper.php
- app/Helpers/JwtHelper.php
- app/Support/Translations.php
- app/Views/admin/section-general.php
- app/WooCommerce/Gateway.php

## 45 - test: add esc_html stubs

Files changed:
- tests/bootstrap.php

## 46 - Sanitize transaction list query parameters

Files changed:
- app/Admin/partials/laqirapay-admin-transactions.php

## 47 - refactor: remove extract usage and pass view data explicitly

Files changed:
- app/Controllers/Admin/SettingsController.php
- app/Controllers/Frontend/AssetsController.php
- app/Controllers/Frontend/PaymentController.php
- app/Views/admin/field-api-key.php
- app/Views/admin/field-delete-data-uninstall.php
- app/Views/admin/field-main-contract.php
- app/Views/admin/field-main-rpc-url.php
- app/Views/admin/field-only-logged-in-user.php
- app/Views/admin/field-order-recovery-status.php
- app/Views/admin/field-walletconnect-project-id.php
- app/Views/public/assets/styles.php
- app/Views/public/checkout.php

## 48 - Check WooCommerce function existence before accessing version

Files changed:
- app/Support/Requirements.php

## 49 - Introduce installer migration with logs table and IP salt

Files changed:
- app/Bootstrap.php
- app/Core/Installer.php

## 50 - feat: add log record and repository

Files changed:
- app/Domain/LogRecord.php
- app/Infrastructure/Persistence/LogRepository.php


## 51 - Add Laqira logger service

Files changed:
- app/Services/LaqiraLogger.php
- tests/unit/LaqiraLoggerTest.php

## 52 - feat: add admin logs list table

Files changed:
- app/Admin/partials/laqirapay-admin-logs.php
- app/Controllers/Admin/AdminController.php
- app/Infrastructure/View/Admin/LogsListTable.php


## 53 - Add logging settings admin page

Files changed:
- app/Controllers/Admin/AdminController.php
- app/Infrastructure/View/Admin/SettingsPage.php


## 54 - feat: add logs export endpoints

Files changed:
- app/Controllers/Rest/LogsController.php
- app/Main.php


## 55 - Add daily log archive cron

Files changed:
- app/Bootstrap.php
- app/Cron/LogsCron.php
- app/Infrastructure/Persistence/LogRepository.php
- tests/unit/LogsCronTest.php


## 56 - Add structured logging across controllers

Files changed:
- app/Controllers/Ajax/AjaxController.php
- app/Controllers/Ajax/LegacyAjax.php
- app/Controllers/Frontend/PaymentController.php
- app/Helpers/BlockchainHelper.php
- app/Helpers/JwtHelper.php
- app/Services/LaqiraLogger.php


## 9be0575e6 - Add integration and repository tests for logging

Files changed:
- tests/FakeWpdb.php
- tests/integration/LogsIntegrationTest.php
- tests/unit/JwtHelperTest.php
- tests/unit/LogRepositoryTest.php
- tests/unit/MaskHelperTest.php

## 58 - Replace administrator capability with appropriate permissions

Files changed:
- app/Controllers/Admin/AdminController.php


## 59 - Move admin templates to Views

Files changed:
- MANIFEST.txt
- app/Controllers/Admin/AdminController.php
- app/Views/admin/laqirapay-admin-currency-rates.php
- app/Views/admin/laqirapay-admin-display.php
- app/Views/admin/laqirapay-admin-logs.php
- app/Views/admin/laqirapay-admin-order-recovery.php
- app/Views/admin/laqirapay-admin-settings-display.php
- app/Views/admin/laqirapay-admin-transactions.php

## 60 - refactor: extract transactions list table

Files changed:
- app/Infrastructure/View/Admin/TransactionsListTable.php
- app/Views/admin/laqirapay-admin-transactions.php

## 61 - refactor: remove infrastructure namespace

Files changed:
- app/Admin/LogsListTable.php
- app/Admin/SettingsPage.php
- app/Admin/TransactionsListTable.php
- app/Bootstrap.php
- app/Controllers/Admin/AdminController.php
- app/Controllers/Rest/LogsController.php
- app/Cron/LogsCron.php
- app/Repositories/LogRepository.php
- app/Services/LaqiraLogger.php
- app/Views/admin/laqirapay-admin-logs.php
- app/Views/admin/laqirapay-admin-transactions.php
- tests/integration/LogsIntegrationTest.php
- tests/unit/LaqiraLoggerTest.php
- tests/unit/LogRepositoryTest.php
- tests/unit/LogsCronTest.php
- tests/unit/MaskHelperTest.php


## 62 - refactor: relocate admin assets

Files changed:
- MANIFEST.txt
- app/Controllers/Admin/AdminController.php
- assets/admin/css/laqirapay-admin.css
- assets/admin/js/laqirapay-admin.js


## 63 - Refactor admin views for order recovery and currency rates

Files changed:
- app/Controllers/Admin/AdminController.php
- app/Views/admin/admin-currency-rates.php
- app/Views/admin/admin-order-recovery.php
- app/Views/admin/laqirapay-admin-currency-rates.php
- app/Views/admin/laqirapay-admin-order-recovery.php


## 64 - Add Support namespace and update usages

Files changed:
- app/Controllers/Frontend/PaymentController.php
- app/Main.php
- app/Support/AbiDecoder.php
- app/Support/Activator.php
- app/Support/Deactivator.php
- app/Support/Functions.php
- app/Support/Loader.php
- app/Support/Translations.php
- app/Support/Uninstaller.php
- app/Support/UpdateChecker.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php
- laqirapay.php

## 65 - Add access checks before rendering admin pages

Files changed:
- app/Controllers/Admin/AdminController.php
- app/Views/admin/admin-currency-rates.php
- app/Views/admin/admin-message.php
- app/Views/admin/admin-order-recovery.php


## 66 - Remove .DS_Store files and update gitignore

Files changed:
- .DS_Store
- .gitignore
- app/Admin/.DS_Store
- assets/.DS_Store
- src/.DS_Store


## 67 - Replace external admin icon with bundled SVG

Files changed:
- app/Controllers/Admin/AdminController.php
- assets/admin/css/menu-icon.css
- assets/admin/img/laqirapay.svg


## 68 - Add logging settings views

Files changed:
- app/Admin/SettingsPage.php
- app/Controllers/Admin/AdminController.php
- app/Controllers/Admin/SettingsController.php
- app/Views/admin/field-log-disable-cache.php
- app/Views/admin/field-log-front.php
- app/Views/admin/field-log-hash-ip.php
- app/Views/admin/field-log-min-level.php
- app/Views/admin/field-log-retention-days.php
- app/Views/admin/field-log-sampling.php
- app/Views/admin/section-logging.php
- docs/ARCHITECTURE.md


## 69 - Test logs table without filters

Files changed:
- app/Admin/LogsListTable.php
- tests/unit/LogsListTableTest.php


## 70 - fix: allow order recovery form elements

Files changed:
- app/Controllers/Admin/AdminController.php


## 71 - Add secure file helper using wp_remote_get

Files changed:
- app/Helpers/BlockchainHelper.php
- app/Helpers/FileHelper.php


## 72 - Fix order recovery shortcode input and feedback

Files changed:
- app/Support/TxRepairForm.php
- assets/public/js/tx-view.js


## 73 - Ensure order recovery scripts load and remove extra input

Files changed:
- app/Hooks/ExtrasService.php
- app/Support/TxRepairForm.php
- assets/public/js/tx-view.js
- app/Support/TxRepairForm.php

## 74 - Allow attributes on recovery form in admin

Files changed:
- app/Controllers/Admin/AdminController.php
- app/Hooks/ExtrasService.php
- app/Support/TxRepairForm.php
- assets/public/js/tx-view.js

## 75 - Allow attributes on recovery form in admin

Files changed:
- app/Controllers/Admin/AdminController.php
- app/Hooks/ExtrasService.php
- app/Support/TxRepairForm.php
- assets/public/js/tx-view.js


## 76 - Add installer tests?

Files changed:
- app/Core/Installer.php
- laqirapay.php
- tests/FakeWpdb.php
- tests/unit/InstallerTest.php


## 77 - fix some issues

Files changed:
- app/Support/TxRepairForm.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php
- laqirapay.php

## 78 - fix: include level column in logs table 

Files changed:
- app/Core/Installer.php

## 79 - fix

Files changed:
- app/Controllers/Ajax/LegacyAjax.php
- app/Helpers/BlockchainHelper.php
- app/Services/LaqiraLogger.php

## 80 - feat: restore extras helpers and currency checks

Files changed:
- app/Controllers/Ajax/LegacyAjax.php
- app/Core/Installer.php
- app/Hooks/ExtrasService.php
- app/Support/Functions.php


## 81 - refactor: reorganize app layers

Files changed:
- MANIFEST.txt
- app/Bootstrap.php
- app/Domain/Models/LogRecord.php
- app/Domain/Models/Settings.php
- app/Domain/Models/Transaction.php
- app/Domain/Repositories/LogRepository.php
- app/Domain/Services/LaqiraLogger.php
- app/Domain/Services/UtilityService.php
- app/Helpers/BlockchainHelper.php
- app/Helpers/JwtHelper.php
- app/Http/Controllers/Admin/AdminController.php
- app/Http/Controllers/Admin/SettingsController.php
- app/Http/Controllers/Ajax/AjaxController.php
- app/Http/Controllers/Ajax/LegacyAjax.php
- app/Http/Controllers/Frontend/AssetsController.php
- app/Http/Controllers/Frontend/PaymentController.php
- app/Http/Controllers/Rest/LogsController.php
- app/Http/Views/admin/admin-currency-rates.php
- app/Http/Views/admin/admin-message.php
- app/Http/Views/admin/admin-order-recovery.php
- app/Http/Views/admin/field-api-key.php
- app/Http/Views/admin/field-delete-data-uninstall.php
- app/Http/Views/admin/field-log-disable-cache.php
- app/Http/Views/admin/field-log-front.php
- app/Http/Views/admin/field-log-hash-ip.php
- app/Http/Views/admin/field-log-min-level.php
- app/Http/Views/admin/field-log-retention-days.php
- app/Http/Views/admin/field-log-sampling.php
- app/Http/Views/admin/field-main-contract.php
- app/Http/Views/admin/field-main-rpc-url.php
- app/Http/Views/admin/field-only-logged-in-user.php
- app/Http/Views/admin/field-order-recovery-status.php
- app/Http/Views/admin/field-walletconnect-project-id.php
- app/Http/Views/admin/laqirapay-admin-display.php
- app/Http/Views/admin/laqirapay-admin-logs.php
- app/Http/Views/admin/laqirapay-admin-settings-display.php
- app/Http/Views/admin/laqirapay-admin-transactions.php
- app/Http/Views/admin/section-general.php
- app/Http/Views/admin/section-logging.php
- app/Http/Views/public/assets/scripts.php
- app/Http/Views/public/assets/styles.php
- app/Http/Views/public/checkout.php
- app/Http/Views/public/settings-display.php
- app/Jobs/LogsCron.php
- app/Main.php
- app/WooCommerce/Gateway.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php
- docs/legacy-utils.md
- tests/integration/AjaxTest.php
- tests/integration/LogsIntegrationTest.php
- tests/integration/PaymentControllerTest.php
- tests/unit/LaqiraLoggerTest.php
- tests/unit/LogRepositoryTest.php
- tests/unit/LogsCronTest.php
- tests/unit/MaskHelperTest.php

## 82 - Use Composer autoload

Files changed:
- app/Init/register-gateway.php
- app/Init/register-wc-blocks.php
- app/Main.php
- app/Support/Functions.php
- composer.json
- laqirapay.php


## 83 - docs: add PHPDoc blocks to controllers

Files changed:
- app/Http/Controllers/Admin/AdminController.php
- app/Http/Controllers/Admin/SettingsController.php
- app/Http/Controllers/Ajax/AjaxController.php
- app/Http/Controllers/Ajax/LegacyAjax.php
- app/Http/Controllers/Frontend/AssetsController.php
- app/Http/Controllers/Frontend/PaymentController.php
- app/Http/Controllers/Rest/LogsController.php

## 84 - refactor: split blockchain helper into services

Files changed:
- app/Helpers/BlockchainHelper.php
- app/Helpers/JwtHelper.php
- app/Helpers/WooCommerceHelper.php
- app/Hooks/ExtrasService.php
- app/Http/Controllers/Admin/AdminController.php
- app/Http/Controllers/Admin/SettingsController.php
- app/Http/Controllers/Ajax/LegacyAjax.php
- app/Http/Controllers/Frontend/PaymentController.php
- app/Init/register-wc-blocks.php
- app/Main.php
- app/Services/BlockchainService.php
- app/Services/ContractService.php
- app/Services/TransactionDecoder.php
- app/WooCommerce/class-wc-gateway-laqirapay-block.php
- tests/unit/BlockchainServiceTest.php

## 85 - docs: clarify complex blocks in helpers, services, and controllers

Files changed:
- app/Helpers/FileHelper.php
- app/Helpers/JwtHelper.php
- app/Helpers/WooCommerceHelper.php
- app/Http/Controllers/Admin/AdminController.php
- app/Http/Controllers/Ajax/AjaxController.php
- app/Http/Controllers/Ajax/LegacyAjax.php
- app/Http/Controllers/Frontend/AssetsController.php
- app/Http/Controllers/Frontend/PaymentController.php
- app/Http/Controllers/Rest/LogsController.php
- app/Services/BlockchainService.php
- app/Services/ContractService.php
- app/Services/TransactionDecoder.php


## 86 - fix: autoload main class loader

Files changed:
- app/Support/LaqiraPayLoader.php


## 87 - Revert "fix: autoload main class loader"

Files changed:
- app/Support/Loader.php

## 88 - fix: autoload translations support class

Files changed:
- MANIFEST.txt
- app/Support/LaqiraPayLoader.php
- app/Support/LaqiraPayTranslations.php


## 89 - fix: TransactionDecoder

Files changed:
- app/Services/TransactionDecoder.php

## 90 - Fix autoload for ABI decoder

Files changed:
- MANIFEST.txt
- app/Support/LaqiraPayAbiDecoder.php


## 91 - fix: fix decode tx-hash 

Files changed:
- app/Http/Controllers/Ajax/LegacyAjax.php
- app/Http/Controllers/Frontend/PaymentController.php
- app/Services/TransactionDecoder.php

## 92 - Enhance logging coverage and admin log table

Files changed:
- app/Admin/LogsListTable.php
- app/Core/Installer.php
- app/Domain/Models/Settings.php
- app/Domain/Services/LaqiraLogger.php
- app/Http/Controllers/Admin/AdminController.php
- tests/unit/InstallerTest.php

## 93 - log ajax operations

Files changed:
- app/Http/Controllers/Ajax/LegacyAjax.php

## 94 - Add comprehensive logging across plugin services

Files changed:
- app/Domain/Repositories/LogRepository.php
- app/Hooks/ExtrasService.php
- app/Http/Controllers/Frontend/AssetsController.php
- app/Http/Controllers/Rest/LogsController.php
- app/Jobs/LogsCron.php
- app/Services/TransactionDecoder.php
- tests/integration/LogsIntegrationTest.php


## 95 - Cache CID and handle contract errors

Files changed:
- app/Services/ContractService.php
- tests/unit/ContractServiceTest.php

## 96 - Add Web3 cache cron job

Files changed:
- app/Bootstrap.php
- app/Core/Installer.php
- app/Http/Controllers/Admin/AdminController.php
- app/Http/Controllers/Admin/SettingsController.php
- app/Http/Views/admin/field-api-key.php
- app/Jobs/Web3CacheCron.php
- app/Services/BlockchainService.php
- app/Services/ContractService.php
- tests/unit/BlockchainServiceTest.php
- tests/unit/ContractServiceTest.php

## 97 - refactor admin asset loading

Files changed:
- app/Http/Controllers/Admin/AdminController.php
- app/Main.php


## 98 - Add Semantic UI assets and settings tab support

Files changed:
- .gitignore
- app/Http/Controllers/Admin/AdminController.php
- assets/admin/css/laqirapay-settings.css
- assets/admin/js/laqirapay-settings.js
- assets/admin/vendor/semantic/semantic.min.css
- assets/admin/vendor/semantic/semantic.min.js


## 99 - refactor: add tabbed settings layout

Files changed:
- app/Http/Views/admin/laqirapay-admin-settings-display.php


## 100 - Register settings tab option

Files changed:
- app/Http/Controllers/Admin/SettingsController.php


## 101 - fix: fix some UI issues

Files changed:

## 102 - fix builder-plugin.yml to add test job 

Files changed:
- .github\workflows\builder-plugin.yml

