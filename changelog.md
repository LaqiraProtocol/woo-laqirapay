## Changelog

### 0.3.3
- [fix] fix load static data from cid 

### 0.3.2
- [fix] fix woocommerce hook to show payment gateway after user change order details in checkout form ([#33](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/33))

### 0.3.1
- [fix] fix Statics Data Load From Out of Plugins.fix issue.([#31](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/31)).
- [fix] remove WooLaqiraPay submenu in Admin dashboard.
- [fix] fix icon result on final modal result page.

### 0.3.0
- [fix] add Call Back links on transaction success event modal .fix issue.([#15](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/15))
- [fix] fix issue.([#26](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/26))
- [add] add admin feature to select order status after order recovery by tx hash method. issue.([#29](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/29))
- [fix] fix some order notes on order create/update by laqirapay gateway .issue([#7](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/7))
- [add] add ability to show laqirapay metaboxes only on orders with WC_woo_laqirapay payment gateway on edit order page in admin area.([#30](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/30))
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