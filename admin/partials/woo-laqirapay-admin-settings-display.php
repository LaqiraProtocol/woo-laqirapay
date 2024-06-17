<?php

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>WooLaqiraPay Settings</h2>

    <?php
    if (current_user_can('administrator')) {
        settings_errors(); ?>
        <form method="POST" action="options.php">
            <?php
            //Securing the form and adding configuration fields
            settings_fields('woo_laqirapay_options');
            do_settings_sections('woo-laqirapay-settings');
            submit_button();

            ?>
        </form>

        
        <?php
        } else {
           echo  "<h2>".__('Access Denied...','woo-laqirapay')."</h2>";
           echo  "<p>".__("You don't have right permission to this setting page","woo-laqirapay")."</p>";
        }


        ?>
        </div>
</div>