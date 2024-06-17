## Security

### 0.1.1
- solve some security problems ([#1](https://github.com/LaqiraProtocol/laqirapay-wordpress-plugin-production/issues/1))

### 0.1.0
- double check ajax nounce verification in '/includes/woocommerce/class-wc-gateway-laqirapay.php'.
- create JWT token from PHP and send to client browser and confirm it in each Ajax call
- Use 'array_key_exists' to check if a key exists in arrays instead 'isset'.
- Use esc_html__() to translate strings and prevent XSS attacks.
- Use function pointers to specify the output type of the function.
- Fix bugs and generalize error management.
- double check data encryption in Browser console in checkout page (js console).


## ToDo
- minify main JS plugin file.
- remove all console.log()'s .

### Security checklist

- Test all input areas on the plugin : checked
- Check requests : checked 
- Check the source code : checked
- Check permissions : checked
- Check data validation and sanitization : checked
- Check data escaping / secure output : checked


## Description

- This plugin is basically a WooCommerce extension (ًwoocommerce payment gateway) and it should not be able to work without installing the current version of WooCommerce. To fix this issue, version control considerations for PHP, WordPress and WooCommerce are included in the main plugin file.
- Since this plugin must be compatible with the latest versions of WooCommerce, all its WooCommerce functions are written based on the 'WooCommerce CRUD class' to be active and usable in accordance with the 'High-Performance Order Storage (HPOS)' feature without any problems. According to the WooCommerce documentation, this will help optimize the database
- Since the hooks used in the plugin are limited and all are written in a standard way, they will not conflict with other payment gateways and other WooCommerce extensions. So, it also helps to cover the existing security topics through the core WordPress JooCommerce
- WooCommerce hooks used in this plugin are as follows:
 - 'add_action('woocommerce_thankyou', 'clear_cart_after_payment');'
  - To display the information after the successful payment of the user and guide him to the payment page
 - 'add_action('woocommerce_order_status_changed', 'custom_empty_cart_on_status_change', 10, 4);'
  - To delete the user's cart session if for any reason the status of his order changes non-automatically (outside the plugin process): (for example, the admin or manager may decide to cancel or complete the order of that customer)


### Test Results
The plugin has been tested on the mentioned versions. The following tests have been conducted:

- ***WordPress 6.2***
  - Installation: Successful
  - Activation: Successful
  - Configuration: Successful
- **PHP 7.4**
  - Installation: Successful
  - Activation: Successful
  - Configuration: Successful
- **PHP 8.0**
  - Installation: Successful
  - Activation: Successful
  - Configuration: Successful

<table>
  <thead>
    <tr>
      <th>WOOCOMMERCE VERSION</th>
      <th>WORDPRESS VERSION</th>
      <th>PHP VERSION</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>8.7</td>
      <td>6.3</td>
      <td>7.4</td>
    </tr>
    <tr>
      <td>8.6</td>
      <td>6.3</td>
      <td>7.4</td>
    </tr>
    <tr>
      <td>8.5</td>
      <td>6.3</td>
      <td>7.4</td>
    </tr>
    <tr>
      <td>8.4</td>
      <td>6.3</td>
      <td>7.4</td>
    </tr>
    <tr>
      <td>8.3</td>
      <td>6.3</td>
      <td>7.4</td>
    </tr>
    <tr>
      <td>8.2</td>
      <td>6.2</td>
      <td>7.4</td>
    </tr>
  </tbody>
</table>


