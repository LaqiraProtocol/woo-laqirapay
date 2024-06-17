# Workflow for LaqiraPay Plugin
## Introduction
This guide delineates the procedure for configuring and utilizing the LaqiraPay plugin. It covers two tailored workflows: one tailored for website administrators and another crafted for the end-users or customers.

## Administrator Workflow
1. **Registration on LaqiraPay Websit:**
   - Initially, the site administrator registers on the LaqiraPay portal via [laqirapay.com](https://laqirapay.com), completing the necessary steps to create an account.

     ![image preview](./workflow-media/001.png)


1. **API Key Acquisition:**
   - Following successful registration, the administrator is to navigate to the API section within the LaqiraPay dashboard. Here, they can generate a unique API key that will facilitate secure communication between the website and LaqiraPay's services. The API key acts as a secure identifier and should be safeguarded accordingly.

      ![image preview](./workflow-media/002.png)

1. **Plugin Download:**
   - After obtaining the API key, the administrator will be directed to the download section.
1. **Installation and Configuration:**
   - ` `Upon downloading the LaqiraPay plugin, the administrator will navigate to the 'Plugins' section of their website's administrative dashboard.

      ![image preview](./workflow-media/003.png) ![image preview](./workflow-media/004.png) ![image preview](./workflow-media/005.png)

      ![image preview](./workflow-media/006.png)

   - In the WordPress dashboard, the administrator will find a dedicated section for LaqiraPay under the 'Settings' menu. Clicking on this brings up the LaqiraPay settings panel. Here, the administrator will enter the unique API key provided during the registration process on the LaqiraPay website. This key is essential for linking the website to LaqiraPay and enabling transactions. Once the API key is correctly entered, changes are saved to finalize the setup, ensuring that LaqiraPay is now ready to process payments securely and efficiently.

      ![image preview](./workflow-media/007.png)
## User Workflow
1. **Purchase Process:**
   - Customers navigate through the website's offerings and add their desired products to the shopping cart. When ready to checkout, they proceed to the WooCommerce checkout page, where they can review their order details and prepare to make a payment. This process is intuitive and designed to ensure a smooth transition from shopping to payment, providing a hassle-free experience for users engaging with the site.

1. **Payment Selection:**
   - When customers arrive at the payment stage, they are presented with various options. For those choosing to utilize LaqiraPay, they simply select LaqiraPay as their payment method. This action seamlessly leads them to the next steps, specifically designed for the choice of payment network and asset, ultimately guiding them towards a secure and successful transaction.
   - After choosing LaqiraPay as the payment method and selecting the preferred network, customers are prompted to connect their digital wallet.

      ![image preview](./workflow-media/008.png)







     - They can choose from supported wallet options, such as MetaMask or WalletConnect, ensuring a secure and personalized transaction experience.

      ![image preview](./workflow-media/009.png)


     - Following the wallet connection, customers will select the asset they wish to use for the transaction. They can choose from a variety of native currencies or tokens available within their connected wallet, ensuring a smooth and tailored payment process with LaqiraPay.

      ![image preview](./workflow-media/010.png)

Once the customer selects an asset, a detailed modal window appears, displaying critical transaction details. This modal provides visibility into:

\- The network chosen by the customer.

\- The asset selected for the transaction.

\- The total order amount in US dollars.

\- An estimated amount of the chosen cryptocurrency equivalent to the order value.

\- Options for wallet source selection:

`  `- By default, "External Pay" is selected, prompting customers to connect to their external wallet through the browser.

 ![image preview](./workflow-media/011.png)

- When the "Internal Pay" option is selected by the customer, the modal provides visibility into their in-app balance relative to the selected token. If the balance is insufficient, the modal offers a straightforward option to charge and replenish their in-app balance, enabling them to proceed with the payment.

   ![image preview](./workflow-media/012.png)

  In the payment detail modal:

- ` `The in-app balance is displayed, showing the amount available based on the selected token. If the balance is not sufficient for the transaction, the customer has the option to "Charge" their in-app wallet to proceed with the payment.

- ` `To accommodate for price fluctuations during the transaction process, the customer is provided with the option to set a "Slippage Tolerance". This feature allows the customer to specify the maximum percentage of price movement they are willing to accept before their transaction is executed, minimizing the risk of transaction reversion due to market volatility.

- ` `The option for slippage tolerance is deactivated for payments made with NativeCoin to simplify the process, as the value is generally more stable and does not require this additional step.

   ![image preview](./workflow-media/013.png) 

1. **Payment Execution:**
   - The user confirms the payment details and executes the transaction using their preferred cryptocurrency and network. The process is designed to be straightforward, enabling a swift and secure transaction experience.
## Conclusion
The integration of the LaqiraPay payment gateway provides a dual advantage: it streamlines the payment process for end-users, allowing for a variety of cryptocurrencies and networks to be used, and it offers a simple, hassle-free setup for administrators. This effectively bridges the gap between traditional e-commerce and the burgeoning world of digital currency transactions, embodying a new era of financial flexibility and user empowerment.
