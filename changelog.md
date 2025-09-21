## Changelog

### 0.9.7
[fix] prevent plugin bootstrap from triggering activation and handle missing composer autoloaders gracefully
[security] enforce secure cookies, default SSL verification and sanitize transaction metadata rendering in admin area
[fix] handle unavailable checkout data, stop repeated cart refresh loops and restore Place order button behaviour
[fix] normalize stored transaction hashes, guard against missing ABI data and ensure confirmations persist to the database
[fix] add multi structure permalink support
[fix] fetch Web3 data automatically and clear it's cache when any plugin settings option update
[fix] refactor some ajax methods
[fix] fix some warnings

### 0.9.6
[fix] fix Place order button (hide/show) in ckeckout page (woocommerce classic/block support)
[fix] fix JWT header in checkout page on woocommerce block

### 0.9.5
[add] add check sufficient fund before user send transaction 

### 0.9.4
[add] add estimate Gas in call contract function on react 

### 0.9.3
[add] remove Laqirapay logger class and add WC_Logger class support

### 0.9.2
[fix] fix and add sone log events

### 0.9.1
[Fix] fix semantic UI loader (replace verndor with semantic folder. {gitignored!})
[add] add warning message on SSl failed.
[add] add warning message on ABI read failed.
[add] add new message on empty active network in frontend.
[fix] fix Network icon UI on frontend after connect wallet


### 0.8.6
Commit-05 ## [Unreleased] : add REST API log export and tail endpoints
Commit-04 ## [Releaseed] : Remove and disable Gas Estimate on WirteContract methods

Commit-03 ## [Unreleased] : remove some hardcode

- remove 2 defined constant and replace them in plugin admin setting panel

Commit-02 ## [Ubreleased] : dynamic JWT creation

### Security
- replace a function to create and used JWT Key instead static Key
- use HttpOnly coockie for authorize JWT instead JS inject method.
- refactor create access token /verify jwt & verify header methods.
- refactor Reactjs and remove Bearer header . we use self browser coockie 

Commit-01 ## [Unreleased] : refactor(core): finalize modular gateway and block registration

### Refactor
- Modularized WooCommerce gateway registration via separate init file
- Moved WC_laqirapay class to dedicated file with no side effects
- Registered WooCommerce Blocks integration in isolated block init file

### Internal
- Improved plugin load order safety for WooCommerce compatibility
- Ensured Composer and dependencies load only when needed

### 0.8.5 
[Add] Arbitrum One Network

### 0.8.4 
[Add] Polygon Network

### 0.8.3 
[Add] Avalanche Network

### 0.8.2 
[Add] Base Network

### 0.8.1 
[fix] fix network explorer link on transaction page in admin area (separate each network explorer)
[add] add mainnet chain config on wagmi provider
[fix] fix handle network rpc for old transactions in admin recovery mode

### 0.8.0
[fix] increase gas estimation by 10% 

### 0.7.9
[Add] add estimateGas for transactions on contract

### 0.7.8
[fix] remove price feed link from step 3

### 0.7.7
[fix] fix tx_status & network_rpc variable on automatic recovery mode

### 0.7.6
[fix] fix rpc address hadling in wagmi config

### 0.7.5
[Add] add support MultNetwork 
[fix] fix some internal issues.
[fix] Update some npm packages

### 0.7.4
[Fix] fix empty_cart method with original woocommerce method

### 0.7.3
[Change] Add the right Text for the Approve Button
[Change] Change the way of checking the shopping cart on the checkout page. Instead of using hash cards, the contents of the card with items of the last order were used in Session Woocommerce
[Enhance] Review the latest prices and update the latest prices in order and transaction to Blockchain with the latest prices set on products
[Add] Add the order repair section to the Woocommerce user panel
[Add] Add order repair section by TXHash on Woocommerce Order Editing page
[Add] Add new translation strings to po files (english & persian)
[Fix] Add user request details to order after user call payment button on step 3

### 0.7.2 
[fix] fix getTransactionReciept method to fetch valid TX Hash on blockchain on InApp Mode

### 0.7.1
[Add] control Terms & condition checkbox on checkout page if admin set woocommerce terms
[fix] fix getTransactionReciept method to fetch valid TX Hash on blockchain
[Fix] fix customer fields form on checkout page if admin select none registered user capable to do payments
[Add] Add some translation strings to handle new features.
[Add] add condition to show/hide order amount & exchange rate values on step 3 if admin use USD currency in Woocommerce

### 0.7.0
[fix] error handling on confirmation TX hash on js 
### 0.6.9
[Fix] check float value of Slippage instead Int on order confirmation on recovery Method

### 0.6.8
[Fix] check float value of Slippage instead Int on order confirmation method after response on Blockchain

### 0.6.7
[Add] Enable slippage tolerance section for stable coins(such as USDT on BSC) to calculate amount with user selected Slippage (0.6% to 2% only)

### 0.6.6
- [change] change some ABI files

### 0.6.5
- [Fix] Fix link redirection on final step .

### 0.6.4
- [Add] Add capability to fetch CID stable coins and exclude them from available assets. 

### 0.6.3
- NEW REPO

### 0.6.2
- [Change] change plugin name to LaqiraPay from WooLaqiraPay

### 0.6.1
- [Add] Add order tracking attribution to 
- [Add] Add WC_order_meta support for all billing or shipping fields on checkout form and add them to order
- [Fix] Fix approve amount for allowance method

### 0.6.0
- [Add] Add capabilty to change order details on checkout page after user change cart items (based on woodmart theme)

### 0.5.9
- [Fix] clean and fix Approve button CSS on step 3 of payment process

### 0.5.8
- [Add] show admin notice to admin when woocommerce currency changed to set new exchange rate 
- [Add] show admin notice to admin when woocommerce currency changes to previous exchange rate
- [Add] show message on plugin admin pages if plugin requirements ont met
- [Fix] force plugin to load owned CSS on frontend only
- [Add] add some translation strings to plugin

### 0.5.7
- [Fix] fix some css issues

### 0.5.6
- [Fix] fix some scss and css classes 
- [Fix] fix font load for plugin
- [Add] add some translation string to English & farsi 

### 0.5.5
- [add] add Tailwind css to plugin

### 0.5.4 
- [add] add selectable order status for compelete orders same as recovery orders
- [add] add some languages strings. 

### 0.5.3
- [fix] fix css frontpages on rtl mode.

### 0.5.2
- [fix] fix load wallet images on rainbowkit modal when site is RTL
- [fix] fix saved_exchange_rate option when admin not set it from dashboard.

### 0.5.1
- [fix] fix woocommerce checkout fields validation check on checkout page (tested on Astra,Hello Elementor,Woodmart themes).
- [fix] fix walletConnect projectID fetch process on connecting to wallets. 

### 0.5.0
- [fix] fix some php and js issues


### 0.4.6
- [fix] fix woocommerce Block integration 

### 0.4.5
- [add] Add Woocommerce Block compatibility
- [fix] fix place order visibility on classic checkout page
### 0.4.3
- [add] Add WalletConnect project ID support in setting 

### 0.4.2
- [fix] Fix currency rate when user switch between USD and other currencies.
- [fix] Fix payment gateway validation on checkout page based on native woocommerce hooks
- [fix] Disable "Place order" button when Woo LaqiraPay gateway is selected on checkout page.
- [fix] Fix laqirapay.com hyperlink on checkout page
 
### 0.4.1
- [update] change update json file for plugin 

### 0.4.0
- [add] add exchange rate for none USD currency Stores  ([#53](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/53))
- [add] add some translation strings to POT file

### 0.3.9
- [add] add translation (Farsi,Chinese,Hindi,Spanish,Italian,Turkish) po files.
- [add] add rtl support CSS for RTL languages.
- [fix] fix some translation strings in Translation Class.

### 0.3.8
- [add] add notice to user select wallet for Metamask ([#51](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/51))
- [fix] change path of token list from CID ([#50](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/50))

### 0.3.7
- [fix] ([#48](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/48))

### 0.3.6
- [add] All Assets with Public endpoint ([#46](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/46))


### 0.3.5
- [add] add auto update plugin support (on github or each self hosted - json based) - ([#45](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/45))
- [fix] fix some UI issues and match with laqirapay monolink project 

### 0.3.4
- [add] add separate Translation class to plugin multi language support ([#37](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/37))
- [add] add capability for remove all plugin DATA when plugin uninstall by user (optional) ([#38](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/38))
- [add] add Price Feed source for selected asset on order detal modal ([#43](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/43))
- [add] add asset filter input box on Select Asset Modal ([#44](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/44))


### 0.3.3
- [fix] fix load static data from cid 

### 0.3.2
- [fix] fix woocommerce hook to show payment gateway after user change order details in checkout form ([#33](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/33))

### 0.3.1
- [fix] fix Statics Data Load From Out of Plugins.fix issue.([#31](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/31)).
- [fix] remove LaqiraPay submenu in Admin dashboard.
- [fix] fix icon result on final modal result page.

### 0.3.0
- [fix] add Call Back links on transaction success event modal .fix issue.([#15](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/15))
- [fix] fix issue.([#26](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/26))
- [add] add admin feature to select order status after order recovery by tx hash method. issue.([#29](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/29))
- [fix] fix some order notes on order create/update by laqirapay gateway .issue([#7](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/7))
- [add] add ability to show laqirapay metaboxes only on orders with WC_laqirapay payment gateway on edit order page in admin area.([#30](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/30))
- [fix] add some language string to .po file for plugin translation.
 

### 0.2.9
- [fix] fix issue.([#28](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/28))
- [add] add fontAwesome 6 icons to Error/Success message in Final Modal result 
- [update] update language strins template of plugin

### 0.2.8
- [add] add some links for Plugin info in plugin directory page.([#21](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/21))
- [fix] fix issue.([#22](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/22))
- [fix] fix issue.([#23](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/23))
- [fix] fix issue.([#24](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/24))
- [fix] fix issue.([#25](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/25))
- [fix] fix issue.([#26](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/26))
- [fix] fix issue.([#27](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/27))

### 0.2.7
- [fix] fix issue ([#20](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/20))
- [fix] fix $content hook in v0.2.6

### 0.2.6
- [add] add recovery order menu page in admin dashboard.
- [add] forse customer to fill all required address field in checkout page.
- [add] add recovery order meta box in admin edit order page
- [add] add recovery order auto complete in order-pay page for orders with success status in blockchain. ([#19](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/19))
- [add] store customer address data in order at first customer try to pay with laqirapay
- [fix] fix some issue
 
### 0.2.5
- [add] add recovery order based on TxHash feature .([#18](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/18))

### 0.2.4
- [fix] fix metamask erro on transaction call events.([#17](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/17))

### 0.2.3
- [fix] fix some PHP error handling in issue ([#16](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/16))

### 0.2.2
- [fix] fix issue ([#16](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/16))

### 0.2.1
- [fix] fix issue ([#13](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/13))

### 0.2.0
- [update] update Wagmi, RainbowKit and some npm packages
- [update] fix wagmi Hooks based on new version
- [add] add Laqirapay details metabox in woocommerce shop order page 

### 0.1.4
- [add] add connect/disconnect wallet button in select network modal
- [fix] fix order total price for pending orders in /checkout/order-pay

### 0.1.3
- [fix] add conditions when transaction call in PaymentType event change .

### 0.1.2
- [fix] fix order pay action in my-account/orders
- [add] add capabilty search by TxHash and User wallet address in admin setting
- [preformance] remove old web3.js files and cryptojs

### 0.1.1
- [add] add Abi-decoder support for customerInAppPayment method.
- [fix] fix some security problems ([#1](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/1))
- [add] add InAppPayment method in front-end.
- [fix] fix woocommerce Payment gateway section in checkout page after woocommerce Ajax review order
- [add] add docstring to some ReactJS and PHP files.
- [fix] optimize .gitignore file

### 0.1.0
- [add] initial version .
- [add] support RainbowKit & wagmi & react js .
- [add] add Abi Decoder class in php.
- [add] add confirmation transaction parameters after transaction reciept.
