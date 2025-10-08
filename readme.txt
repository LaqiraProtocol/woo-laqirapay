=== LaqiraPay ===
Contributors: LaqiraProtocol
Donate link: 
Tags: woocommerce, payment gateway, laqira, crypto, cryptocurrency, web3, metamask, blockchain, woocommerce-payment, crypto-payment-gateway
Requires at least: 6.3
Tested up to: 6.8.2
Stable tag: 0.9.26
Requires PHP: 8.1
WC requires at least: 8.3
WC tested up to: 10.2.2
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

LaqiraPay: Fully Decentralized Asset-Agnostic Multi-Network Crypto Payment Gateway for WooCommerce.

== Description ==

LaqiraPay is a secure, efficient, and **Fully Decentralized Asset-Agnostic Multi-Network Payment Gateway** for WooCommerce, enabling businesses to accept digital payments seamlessly with advanced blockchain technology. It offers enhanced flexibility and security, transparent financial statements, and in-app wallet features for users.

### Key Features:

* **Fully Decentralized:** Uses blockchain for secure, anonymous transactions without third-party oversight.
* **Asset-Agnostic:** Allows payments in **any origin asset** and provides stablecoin in destination to providers, minimizing risk and maximizing stability.
* **Multi-Network Support:** Supports diverse network payments, with user options dependent on provider acceptance and configuration.
* [cite_start]**Chainlink Price Feeds:** Automatic exact amount detection and stablecoin depeg detection for vendor settlement[cite: 12].
* [cite_start]**Security:** Utilizes HttpOnly cookies for JWT authorization and employs robust input hardening and sanitization throughout[cite: 8, 9, 10, 5].

### How LaqiraPay Works:

1.  **Provider Registration:** Register on LaqiraPay and receive a dedicated smart contract address (API key) where transparent financial records are carved.
2.  **Plugin Setup:** Install the LaqiraPay plugin, input your API key, and configure settings.
3.  **Customer Payment:** Customers select the LaqiraPay method, choose a network and cryptocurrency, connect their wallet, and complete the transaction.

== Installation ==

1.  **Prerequisite:** Ensure **WooCommerce** is installed, configured, and ready to use on your WordPress site.
2.  **Install the Plugin:** Go to **Plugins > Add New > Upload Plugin** in your WordPress dashboard and upload the LaqiraPay plugin .zip file.
3.  **Activate:** After installation, click **Activate**.
4.  **Configure Settings:**
    * Navigate to **WooCommerce > Settings > Payments** and enable **LaqiraPay**.
    * Enter your **Laqira Contract Address**, **Laqira RPC Url**, and **WalletConnect Project ID** (default values are provided but should be verified).
    * **API Key:** Obtain your unique **Laqira Pay Provider API Key** (which serves as your provider address) from your account on `https://laqirapay.com`.

**Minimum Requirements:**

* [cite_start]**PHP version:** 8.1 or higher [cite: 14] (as per validation report and plugin file)
* **WordPress version:** 6.3 or higher (as per validation report and plugin file)
* **WooCommerce version:** 8.2 or higher (as per validation report and plugin file)

== Screenshots ==

1.  Screenshot of the LaqiraPay settings page. (Place `screenshot-1.png` in the `/assets` folder)
2.  Screenshot of the WooCommerce checkout page with the LaqiraPay option. (Place `screenshot-2.png` in the `/assets` folder)
3.  [Description of a third screenshot showing a specific feature, e.g., Wallet Connection UI]

== Changelog ==

= 0.9.26 =
* Changed - change plugin slug
* Security - rename and refactor the admin transactions list table to sanitize query parameters and replace debug output with escaped JSON.

= 0.9.25 =
* Changed - fix perg_replace #3 parameters to ensure not null (php 8.1+)

= 0.9.24 =
* Changed - update test environments

= 0.9.23 =
* Security - Reuse the shared `laqirapay_filter_input` helper for exchange-rate submissions so superglobals are unslashed before sanitization.
* Security - Normalize request methods, option keys, and nonces prior to validation to eliminate PHP 8.1 deprecation warnings surfaced in QA.
* Security - Refactored the admin transactions list table to sanitize list-query parameters and remove debug output.

= 0.9.22 =
* Changed - Renamed admin/bootstrap/helpers to match WordPress `class-*.php` conventions and updated Composer autoloading.
* Security - Hardened admin settings sanitization with enforced nonce/capability checks and consistent unslash/sanitize flows.
* Fixed - Added PHPCS-compliant documentation and normalized asset loading throughout the admin bootstrap and JWT utilities.

= 0.9.20 =
* Fixed - fix some warning:
                - remove FILTER_SANITIZE_STRING constant (deprecated since PHP 8.1)
                - fix webpack.js to solve Potential Leaked Secrets
* Added - Add README.TXT file based on woocommerce and wordpress structure

= 0.9.19 =
* [cite_start]**Changed** - Aligned exchange rate view config formatting and updated persistence tests[cite: 1].
* [cite_start]**Changed** - Refined admin settings sections to include dedicated exchange rate and order recovery tabs[cite: 2, 3].
* [cite_start]**Changed** - Refactored order recovery admin settings and cleaned up the settings view[cite: 3, 4].

= 0.9.18 =
* [cite_start]**Security** - Fixed PHPCS & semgrep issues[cite: 2].

= 0.9.17 =
* [cite_start]**Security** - Fixed PHPCS & semgrep issues[cite: 3].

= 0.9.16 =
* [cite_start]**Fixed** - Fixed INPUT\_REQUEST[cite: 4].

= 0.9.15 =
* [cite_start]**Enhanced** - Ran PHPCS tests[cite: 5].

= 0.9.14 =
* [cite_start]**Security** - Hardened input handling and escaping for LaqiraPay across AJAX controllers, logging, and helper utilities[cite: 5].
* [cite_start]**Fixed** - Improved CLI input handling and sanitization fallbacks[cite: 6].
* [cite_start]**Changed** - Allowed scripts in confirmation markup[cite: 6].

= 0.9.13 =
* [cite_start]**Fixed** - Guarded blockchain integrations until required settings (API key, contract address, RPC URL) are saved[cite: 7].

= 0.9.12 =
* [cite_start]**Security** - Locked down rendered templates and sanitized admin/front-end output[cite: 8].
* [cite_start]**Security** - Hardened transaction confirmation flows and replaced raw SQL with prepared statements[cite: 9].
* [cite_start]**Security** - Fortified logging and request sanitization[cite: 10].
* [cite_start]**Security** - Improved blockchain error handling and documentation hygiene[cite: 10].

= 0.9.11 =
* [cite_start]**Fixed** - Handled hex transaction statuses for PHP 8 compatibility[cite: 11].

= 0.9.10 =
* [cite_start]**Fixed** - Guarded WooCommerce cart reset when session is unavailable[cite: 12].

= 0.9.9 =
* [cite_start]**Fixed** - Removed leading newline before PHP tag in `LegacyAjax.php` to avoid header warnings[cite: 12].
* [cite_start]**Fixed** - Handled null CID values in blockchain lookups[cite: 13].
* [cite_start]**Fixed** - Localized settings script data[cite: 13].

= 0.9.8 =
* [cite_start]**Changed** - Raised the minimum supported PHP version to 8.1 and updated project documentation[cite: 14].

= 0.9.7 =
* [cite_start]**Fixed** - Prevented plugin bootstrap from triggering activation and handled missing composer autoloaders gracefully[cite: 15].
* [cite_start]**Security** - Enforced secure cookies, default SSL verification and sanitized transaction metadata rendering in admin area[cite: 15].
* [cite_start]**Fixed** - Handled unavailable checkout data, stopped repeated cart refresh loops and restored Place order button behaviour[cite: 15].
* [cite_start]**Fixed** - Normalized stored transaction hashes, guarded against missing ABI data and ensured confirmations persist to the database[cite: 15].
* [cite_start]**Fixed** - Added multi structure permalink support[cite: 15].
* [cite_start]**Fixed** - Fetched Web3 data automatically and cleared its cache when any plugin settings option updated[cite: 15].

[cite_start]... (Other versions should be added here, following the same format, down to 0.6.2) [cite: 16, 17, 18, 19, 20, 21, 22, 23]

= 0.6.2 =
* [cite_start]**Changed** - Changed plugin name to LaqiraPay from WooLaqiraPay[cite: 23].

== Upgrade Notice ==

= 0.9.23 =
Admin exchange-rate submissions now sanitize via the shared helper to prevent PHP 8.1 warnings. Re-run QA after updating; run `composer dump-autoload` if you manage dependencies manually.

= 0.9.22 =
This release refreshes admin sanitization and file structure to satisfy WordPress coding standards. After updating, run `composer dump-autoload` if you manage dependencies locally.

= 0.9.19 =
This is a maintenance release focusing on administrative settings. Please check your **Exchange Rate** and **Order Recovery** settings sections as they have been refined and restructured for better user experience.

= 0.9.14 =
This is a **security release** with hardened input handling and sanitization throughout the plugin. Upgrading immediately is strongly recommended to ensure the highest level of security for your payment gateway.

= 0.9.8 =
The minimum required **PHP version is now 8.1**. Please ensure your hosting environment meets this requirement before upgrading to avoid deactivation or errors.

== Frequently Asked Questions ==

= Do I need SSL for my store to use LaqiraPay? =
Yes, using an SSL certificate (HTTPS) is strongly recommended for all e-commerce sites to ensure the security and integrity of payment transactions.

= How do I get my API Key? =
Your unique Laqira Pay Provider API Key (which acts as your provider address) can be obtained by connecting your wallet and registering as a Provider on the LaqiraPay website (`https://laqirapay.com`).

= What happens if a transaction fails? =
[cite_start]LaqiraPay includes robust **Order Recovery** features that help track and manage failed or pending transactions, including a dedicated section on the WooCommerce Order Editing page to repair orders by TXHash[cite: 20].

== Translations ==

* English (en\_US)
* Chinese (zh\_CN)
* Hindi (hi\_IN)
* Spanish (es\_ES)
* Italian (it\_IT)
* Turkish (tr\_TR)

== Support ==

For support or bug reports, please visit the official support page at `https://laqirahub.com/laqira-pay/introduction` or the support forum.
