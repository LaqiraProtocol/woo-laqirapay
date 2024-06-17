<?php
//
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <div class="info-box">
    <h3><?php echo  __("WooLaqiraPay Order Recovery by Transaction Hash",'woo-laqirapay');?></h3>
    <p>
    <span class="dashicons dashicons-warning"></span>
        <?php echo __("In this section, you can view detailed information by entering a transaction hash.
         If a customer's order corresponds to this hash, the order details will be displayed along with the
          transaction information retrieved from the blockchain. If the transaction is marked as incomplete 
          on the website, you can use the blockchain data to finalize the customer's order.",'woo-laqirapay');?>
    </p>
    </div>
    <hr>


    <?php
    if (current_user_can('administrator')) {
        settings_errors();
        echo do_shortcode('[lqr_recovery]');
    } else {
        echo  "<h2>" . __('Access Denied...', 'woo-laqirapay') . "</h2>";
        echo  "<p>" . __("You don't have right permission to this setting page", "woo-laqirapay") . "</p>";
    }


    ?>
</div>
</div>