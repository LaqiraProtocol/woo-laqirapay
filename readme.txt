=== Laqira Payments for WooCommerce ===
Contributors: LaqiraProtocol
Donate link: 
Tags: woocommerce, payment gateway, crypto, blockchain, web3
Requires at least: 6.3
Tested up to: 7.0
Stable tag: 0.9.37
Requires PHP: 8.1
WC requires at least: 8.2
WC tested up to: 10.6.2
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Laqira Payments for WooCommerce: Fully Decentralized Asset-Agnostic Multi-Network Crypto Payment Gateway for WooCommerce.

== Description ==

Laqira Payments is a secure, efficient, and **Fully Decentralized Asset-Agnostic Multi-Network Payment Gateway** for WooCommerce, enabling businesses to accept digital payments seamlessly with advanced blockchain technology. It offers enhanced flexibility and security, transparent financial statements, and in-app wallet features for users.

### Key Features:

* **Fully Decentralized:** Uses blockchain for secure, anonymous transactions without third-party oversight.
* **Asset-Agnostic:** Allows payments in **any origin asset** and provides stablecoin in destination to providers, minimizing risk and maximizing stability.
* **Multi-Network Support:** Supports diverse network payments, with user options dependent on provider acceptance and configuration.
* **Chainlink Price Feeds:** Automatic exact amount detection and stablecoin depeg detection for vendor settlement.
* **Security:** Utilizes HttpOnly cookies for JWT authorization and employs robust input hardening and sanitization throughout.

### How LaqiraPayments Works:

1.  **Provider Registration:** Register on LaqiraPayments and receive a dedicated smart contract address (API key) where transparent financial records are carved.
2.  **Plugin Setup:** Install the LaqiraPayments plugin, input your API key, and configure settings.
3.  **Customer Payment:** Customers select the LaqiraPayments method, choose a network and cryptocurrency, connect their wallet, and complete the transaction.

== External Services ==

This plugin connects to external services to complete wallet connections, read blockchain data, send blockchain transactions, load payment metadata, and open provider or explorer links. No external request is made just by activating the plugin. Requests happen when an administrator configures the plugin, when a customer opens the checkout payment interface, when a customer connects a wallet, when the plugin reads blockchain/payment metadata, when a customer submits an on-chain payment, or when a user clicks an external link shown by the plugin.

1) Laqira services
* What the service is used for: Laqira services are used for provider registration, support/documentation links, and Laqira Pay account or wallet pages.
* Domains that may be opened or requested: `laqirapay.com`, `laqirahub.com`, `laqira-payments.com`, `laqira.io`.
* What data is sent and when: When an administrator or customer clicks a Laqira link, the browser sends the normal HTTPS request data such as IP address, user agent, referrer, and any data included in the destination URL. The plugin may also send configured provider/payment metadata when the merchant uses Laqira-hosted payment configuration endpoints.
* Terms of Service / Privacy Policy: Please review the terms and privacy policy published on the relevant Laqira service page.

2) WalletConnect / Reown / Web3Modal services
* What the service is used for: These services power the wallet connection modal, wallet discovery, WalletConnect sessions, WalletConnect relay/RPC features, session verification, telemetry/status endpoints used by the wallet connection libraries, and wallet deep-link routing.
* Domains that may be requested by the generated checkout bundle: `api.web3modal.org`, `rpc.walletconnect.org`, `relay.walletconnect.org`, `pulse.walletconnect.org`, `echo.walletconnect.com`, `verify.walletconnect.com`, `verify.walletconnect.org`, `secure.walletconnect.org`, `secure-mobile.walletconnect.com`, `secure-mobile.walletconnect.org`, `walletconnect.com`, `walletconnect.org`, `reown.com`.
* What data is sent and when: When a customer opens the wallet modal, searches/selects a wallet, scans a QR code, connects a wallet, or signs/submits a blockchain transaction, the browser may send wallet connection metadata, project ID, selected chain/network, wallet address or session identifiers, relay messages, transaction/session metadata, and normal HTTPS/WebSocket request data such as IP address and user agent.
* Terms of Service: https://reown.com/terms-of-service
* Privacy Policy: https://reown.com/privacy-policy

3) Wallet application providers and wallet discovery/deep-link services
* What the service is used for: The bundled wallet connection libraries include support for wallet-specific discovery pages, mobile deep links, and optional wallet-specific connection flows.
* Domains that may be opened or requested depending on the wallet selected by the customer: `metamask.io`, `metamask.app.link`, `metamask-sdk.api.cx.metamask.io`, `mm-sdk-analytics.api.cx.metamask.io`, `fwd.metamask.io`, `rainbow.me`, `rainbow.download`, `rnbwapp.com`, `enhanced-provider.rainbow.me`, `learn.rainbow.me`, `www.rainbowkit.com`, `app.safe.global`, `phantom.app`, `solflare.com`, `t.me`, `go.cb-w.com`, `meldcrypto.com`, `apps.apple.com`, `play.google.com`, `chrome.google.com`, `microsoftedge.microsoft.com`, `addons.mozilla.org`, `addons.opera.com`, `trustwallet.com`.
* What data is sent and when: These requests occur only when the wallet connection UI is loaded or when the customer chooses or opens a supported wallet. The browser may send wallet/app selection metadata, chain/network information, wallet connection/session identifiers, public wallet address where required by the selected wallet flow, and normal HTTPS request data such as IP address and user agent.
* Terms of Service / Privacy Policy: These are provided by the selected wallet or wallet directory. For MetaMask, see https://metamask.io/terms.html and https://consensys.io/privacy-notice/ . For Rainbow, see https://rainbow.me/privacy and https://app.userainbow.com/static/documents/terms-of-service.pdf . For other wallets, please review that wallet provider's terms and privacy policy before use.

4) Blockchain RPC providers
* What the service is used for: The plugin and bundled wallet libraries send JSON-RPC requests to blockchain nodes so they can read smart contract data, check balances and allowances, estimate gas, and broadcast transactions requested by the customer.
* Domains that may be requested depending on the configured network, selected wallet, and bundled network defaults: `bsc-dataseed.binance.org`, `rpc.walletconnect.org`, `56.rpc.thirdweb.com`, `api.avax.network`, `arb1.arbitrum.io`, `mainnet.base.org`, `polygon.drpc.org`, `rpc.mantle.xyz`, `eth.merkle.io`, `mainnet.infura.io`, `goerli.infura.io`, `sepolia.infura.io`, `arbitrum-mainnet.infura.io`, `arbitrum-goerli.infura.io`, `polygon-mainnet.infura.io`, `polygon-mumbai.infura.io`, `optimism-mainnet.infura.io`, `optimism-goerli.infura.io`, `avalanche-mainnet.infura.io`, `avalanche-fuji.infura.io`, `linea-mainnet.infura.io`, `linea-goerli.infura.io`, `celo-mainnet.infura.io`, `celo-alfajores.infura.io`, `aurora-mainnet.infura.io`, `aurora-testnet.infura.io`, `palm-mainnet.infura.io`, `palm-testnet.infura.io`, `starknet-mainnet.infura.io`, `starknet-goerli.infura.io`, `starknet-goerli2.infura.io`.
* What data is sent and when: When checkout loads blockchain data or when the customer approves or submits an on-chain payment, the browser or server sends JSON-RPC request data such as wallet address, transaction payload data, contract addresses, chain ID/network details, and normal HTTPS request data such as the customer's or store server IP address.
* Under which conditions: Requests are sent only when the payment method is used and blockchain interaction is required. The exact endpoint may be merchant-configured, supplied by payment/network metadata, provided by the selected wallet, or included by the bundled wallet/RPC libraries.
* Terms of Service / Privacy Policy: If WalletConnect RPC is used, see https://reown.com/terms-of-service and https://reown.com/privacy-policy . If Infura is used, see https://www.infura.io/terms-of-service and https://consensys.io/privacy-notice/ . If another RPC provider is configured or selected by a wallet, review that provider's own terms and privacy policy.

5) Blockchain explorers, explorer APIs, and transaction links
* What the service is used for: The plugin and bundled blockchain libraries may generate links to blockchain explorers or include explorer API metadata so customers or administrators can inspect transaction hashes, addresses, or network data.
* Domains that may be opened or referenced depending on the selected network: `bscscan.com`, `api.bscscan.com`, `etherscan.io`, `api.etherscan.io`, `goerli.etherscan.io`, `sepolia.etherscan.io`, `arbiscan.io`, `api.arbiscan.io`, `basescan.org`, `api.basescan.org`, `mantlescan.xyz`, `api.mantlescan.xyz`, `snowtrace.io`, `api.snowtrace.io`, `polygonscan.com`, `solscan.io`.
* What data is sent and when: A request is sent when a customer or administrator clicks an explorer link, or when a bundled library queries explorer API metadata for a supported chain. The browser or API request may include the transaction hash, wallet or contract address, selected network, and normal HTTPS request data such as IP address and user agent.
* Terms of Service / Privacy Policy: The exact explorer depends on the selected network. Please review the terms and privacy policy of the selected explorer before use.

6) Remote payment metadata, token metadata, ABI, and IPFS endpoints
* What the service is used for: The checkout interface reads remote JSON, ABI, token, network, and metadata files so it can load supported assets, contract ABIs, chain metadata, wallet metadata, and payment configuration data.
* Domains that may be requested depending on the selected network and bundled library metadata: `ipfs.io`, `arweave.net`, `4byte.sourcify.dev`, `raw.githubusercontent.com`, `docs.soliditylang.org`, and any merchant-configured CID, ABI, token, network, or RPC metadata URL.
* What data is sent and when: When the plugin refreshes blockchain configuration data or the checkout interface needs token/network/ABI metadata, the browser or server requests the remote URL and sends normal HTTPS request data such as IP address and user agent. If the URL includes a token, contract, network, or transaction identifier, that identifier is sent as part of the request URL.
* Terms of Service / Privacy Policy: These URLs are defined by the payment configuration, selected network, bundled wallet libraries, or blockchain metadata being used. The site owner should review the terms and privacy policy of the remote host that serves those files.

7) Optional media/embed domains bundled with third-party admin libraries
* What the service is used for: Bundled third-party UI libraries include optional media/embed support and contain references to media providers in their distributed source code.
* Domains referenced by the bundled admin UI library: `www.youtube.com`, `player.vimeo.com`.
* What data is sent and when: Laqira Payments does not automatically load YouTube or Vimeo media during normal checkout or payment processing. A request would be sent only if a page explicitly initializes the bundled embed feature for one of those services.
* Terms of Service / Privacy Policy: Please review the terms and privacy policy of the selected media provider before using that optional embed functionality.

Note: Some bundled Composer, JavaScript, CSS, and license files contain documentation URLs, issue tracker URLs, example URLs, standards URLs, or project homepages. Those references are not automatically contacted by Laqira Payments during normal plugin operation. The lists above focus on domains that the generated runtime bundle, wallet libraries, configured blockchain/payment metadata, or user-clicked links may actually request or open.

**Important Note:** All external service calls are initiated only by explicit user actions (such as connecting a wallet or submitting a payment) or by administrator configuration. No tracking or requests occur on plugin activation or page loads without user interaction.

== Source Code ==

Development and build tools are available at https://github.com/LaqiraProtocol/laqira-payments

== Installation ==

1.  **Prerequisite:** Ensure **WooCommerce** is installed, configured, and ready to use on your WordPress site.
2.  **Install the Plugin:** Go to **Plugins > Add New > Upload Plugin** in your WordPress dashboard and upload the LaqiraPayments plugin .zip file.
3.  **Activate:** After installation, click **Activate**.
4.  **Configure Settings:**
    * Navigate to **WooCommerce > Settings > Payments** and enable **LaqiraPayments**.
    * Enter your **Laqira Contract Address**, **Laqira RPC Url**, and **WalletConnect Project ID** (default values are provided but should be verified).
    * **API Key:** Obtain your unique **Laqira Pay Provider API Key** (which serves as your provider address) from your account on `https://laqirapay.com`.

**Minimum Requirements:**

* **PHP version:** 8.1 or higher  (as per validation report and plugin file)
* **WordPress version:** 6.3 or higher (as per validation report and plugin file)
* **WooCommerce version:** 8.2 or higher (as per validation report and plugin file)

== Screenshots ==

1.  The Laqira Payments settings screen in WooCommerce.
2.  The checkout flow where the customer selects a network and digital asset.
3.  The order recovery and transaction details experience for completed or repairable orders.

== Changelog ==

= 0.9.37 =
* Fixed - Migrated public WooCommerce payment identifiers, checkout block handles, AJAX actions, and custom helper functions to plugin-specific Laqira Payments names while preserving legacy settings compatibility.
* Fixed - Removed remote Google Fonts references from bundled and generated assets used by the release package.
* Security - Re-audited request input handling, nonce verification, and WooCommerce admin capability checks around AJAX, settings, and order metabox flows.
* Security - Removed the legacy whole-request fallback from the shared request input helper so only explicitly expected request keys are read.
* Fixed - Replaced hardcoded plugin path detection with WordPress-safe plugin APIs and plugin constants.
* Fixed - Hardened the GitHub Actions release build so production Composer dependencies are rebuilt with `--no-dev`, generated assets are sanitized, and the final package is validated before zipping.
* Fixed - Corrected distribution ignore rules so Composer runtime source files inside `vendor` are preserved, preventing activation failures on clean WordPress installs.
* Fixed - Excluded nested dependency lock files such as `vendor/**/package-lock.json` from the release package.
* Fixed - Restored WooCommerce order-pay rendering compatibility for retrying failed or interrupted payments in classic and block checkout contexts.
* Fixed - Split admin order recovery metabox behavior so recorded `tx_hash` orders use the recovery status metabox while orders without a transaction hash use the failed transaction recovery form.
* Changed - Refined WordPress.org documentation, explicit external service domain disclosures, screenshots text, and release strategy notes.

= 0.9.36 =
* Fixed - Prepared the WordPress.org release candidate after clean-install activation and transaction smoke testing.

= 0.9.35 =
* Fixed - Fix some issues (Remove plugin update class , readme.txt, vendor folder)

= 0.9.34 =
* Fixed - Updated the plugin distribution process so generated release zip files exclude development-only files and unnecessary vendor artifacts.
* Fixed - Expanded the external services documentation in the readme to clearly describe wallet connectivity, RPC, remote JSON metadata, and explorer links.
* Fixed - Replaced the remaining non-prefixed constants and the legacy recovery shortcode with plugin-specific names to improve WordPress.org compliance.
* Fixed - Added direct-access guards for the remaining PHP entry points, including generated asset PHP files.

= 0.9.33 =
* Security - Hardened AJAX/admin request handling with stricter unslash/validation flows and defensive order checks in transaction confirmation paths.
* Security - Improved SQL safety in legacy transaction persistence by using prepared placeholders for table identifiers and IDs, and documenting unavoidable direct queries.
* Fixed - Replaced discouraged functions (`strip_tags`, `parse_url`) with WordPress-safe alternatives and added missing translators comments for placeholder strings.
* Fixed - Reworked global function naming to plugin-prefixed names and synchronized bootstrap calls to match the new function names.
* Fixed - Added direct-access guards for remaining PHP entry files and aligned uninstall SQL handling with safer `$wpdb->prepare()` usage.
* Chore - Updated `readme.txt` tags.

= 0.9.32 =
* Fixed: Fix some language strings.
* Fixed: Optimize webpack bundle.

= 0.9.31 =
* Changed - Updated plugin display name to Laqira Payments for WooCommerce.
* Changed - Updated plugin slug  to laqira-payments.
* Fixed: Restored the settings view by renaming the admin templates to the `laqira-payments-…` filenames expected by `AdminController`.
* Fixed: Deferred the activation log until `init` to avoid `_load_textdomain_just_in_time` warnings caused by loading translations too early.
* Fixed: Prevented SQL syntax errors in the legacy AJAX confirmations by inlining the sanitized table name before preparing `SELECT COUNT(1)` queries.

= 0.9.30 =
* Security: Added nonce enforcement for order recovery confirmations.
* Security: Restricted admin-only confirmation to users with WooCommerce management capabilities.
* Fixed: Guarded template entry points against direct access.
* Fixed: Switched the gateway icon to a bundled local asset.
* Fixed: Added missing `rel="noopener noreferrer"` to external links opened in a new tab.
* Fixed: Updated web3p/web3.php to 0.3.2.
* Fixed: Updated bundled Semantic UI assets to 2.5.0.
* Changed: Updated plugin display name to Laqira Payments for WooCommerce.

= 0.9.29 =
* Fixed: Removed dynamic translation usage for non-literal strings to comply with WordPress i18n standards.
* Fixed: Removed custom plugin update mechanism to meet WordPress.org repository guidelines.
* Fixed: Improved settings registration by adding sanitize callbacks for all registered options.
* Fixed: Ensured support for array and nested option values during sanitization.
* Fixed: Removed discouraged manual textdomain loading when using WordPress.org language packs.
* Fixed: General code cleanup and compliance improvements for WordPress.org review.

= 0.9.28 =
* Added support for additional blockchain networks: zkSync
* Added support for additional blockchain networks: Optimism

= 0.9.27 =
* Security - Prevent duplicate on-chain payments by locking orders during processing and rejecting additional transaction hashes once recorded.
* Fixed - Disable repeated payment submissions in the checkout modal while a payment request is processing.

= 0.9.26 =
* Changed - change plugin slug
* Security - rename and refactor the admin transactions list table to sanitize query parameters and replace debug output with escaped JSON.

= 0.9.25 =
* Changed - fix perg_replace #3 parameters to ensure not null (php 8.1+)

= 0.9.24 =
* Changed - update test environments

= 0.9.23 =
* Security - Reuse the shared `laqira_payments_filter_input` helper for exchange-rate submissions so superglobals are unslashed before sanitization.
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
* **Changed** - Aligned exchange rate view config formatting and updated persistence tests.
* **Changed** - Refined admin settings sections to include dedicated exchange rate and order recovery tabs.
* **Changed** - Refactored order recovery admin settings and cleaned up the settings view.

= 0.9.18 =
* **Security** - Fixed PHPCS & semgrep issues.

= 0.9.17 =
* **Security** - Fixed PHPCS & semgrep issues.

= 0.9.16 =
* **Fixed** - Fixed INPUT\_REQUEST.

= 0.9.15 =
* **Enhanced** - Ran PHPCS tests.

= 0.9.14 =
* **Security** - Hardened input handling and escaping for LaqiraPayments across AJAX controllers, logging, and helper utilities.
* **Fixed** - Improved CLI input handling and sanitization fallbacks.
* **Changed** - Allowed scripts in confirmation markup.

= 0.9.13 =
* **Fixed** - Guarded blockchain integrations until required settings (API key, contract address, RPC URL) are saved.

= 0.9.12 =
* **Security** - Locked down rendered templates and sanitized admin/front-end output.
* **Security** - Hardened transaction confirmation flows and replaced raw SQL with prepared statements.
* **Security** - Fortified logging and request sanitization.
* **Security** - Improved blockchain error handling and documentation hygiene.

= 0.9.11 =
* **Fixed** - Handled hex transaction statuses for PHP 8 compatibility.

= 0.9.10 =
* **Fixed** - Guarded WooCommerce cart reset when session is unavailable.

= 0.9.9 =
* **Fixed** - Removed leading newline before PHP tag in `LegacyAjax.php` to avoid header warnings.
* **Fixed** - Handled null CID values in blockchain lookups.
* **Fixed** - Localized settings script data.

= 0.9.8 =
* **Changed** - Raised the minimum supported PHP version to 8.1 and updated project documentation.

= 0.9.7 =
* **Fixed** - Prevented plugin bootstrap from triggering activation and handled missing composer autoloaders gracefully.
* **Security** - Enforced secure cookies, default SSL verification and sanitized transaction metadata rendering in admin area.
* **Fixed** - Handled unavailable checkout data, stopped repeated cart refresh loops and restored Place order button behaviour.
* **Fixed** - Normalized stored transaction hashes, guarded against missing ABI data and ensured confirmations persist to the database.
* **Fixed** - Added multi structure permalink support.
* **Fixed** - Fetched Web3 data automatically and cleared its cache when any plugin settings option updated.

= 0.6.2 =
* **Changed** - Changed plugin name to LaqiraPayments from WooLaqiraPayments.

== Upgrade Notice ==

= 0.9.34 =
This release focuses on WordPress.org review compliance: cleaner distribution archives, clearer external-services disclosure, prefixed naming cleanup, and direct-access guards for remaining PHP entry points.

= 0.9.33 =
This release focuses on security/compliance hardening: safer input handling, stricter SQL preparation in legacy flows, updated prefixed function naming, and WordPress.org coding-standard remediations.

= 0.9.31 =
This release fixes the admin settings rendering, defers the activation log so translations load safely, and prevents legacy AJAX confirmations from generating SQL syntax errors.

= 0.9.30 =
This release hardens order recovery confirmation requests and aligns asset loading with WordPress.org requirements.

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

= Do I need SSL for my store to use LaqiraPayments? =
Yes, using an SSL certificate (HTTPS) is strongly recommended for all e-commerce sites to ensure the security and integrity of payment transactions.

= How do I get my API Key? =
Your unique Laqira Pay Provider API Key (which acts as your provider address) can be obtained by connecting your wallet and registering as a Provider on the LaqiraPayments website (`https://laqira-payments.com`).

= What happens if a transaction fails? =
LaqiraPayments includes robust **Order Recovery** features that help track and manage failed or pending transactions, including a dedicated section on the WooCommerce Order Editing page to repair orders by TXHash.

== Translations ==

* English (en\_US)
* Chinese (zh\_CN)
* Hindi (hi\_IN)
* Spanish (es\_ES)
* Italian (it\_IT)
* Turkish (tr\_TR)

== Support ==

For support or bug reports, please visit the official support page at `https://laqirahub.com/laqira-pay/introduction` or the support forum.
