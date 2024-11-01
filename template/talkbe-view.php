<?php

$data = array();
$data = get_option('woocommerce_talkbe_data');
?>

<style>
    .woo-key-container {
        margin-left: -20px;
        background-color: white;
        padding: 30px;
        min-height: 800px;
    }

    .woo-key-row {
        margin-top: 20px;
    }

    .woo-key-btn {
        width: 150px;
        height: 30px;
        font-weight: 600;
        background: #02cf7d !important;
        border-color: #00C375 !important;
    }

    .woo-key-table thead tr th,
    .woo-key-table tbody tr td {
        text-align: center;
        padding: 10px;
        border: solid 1px #e2e0e0;
    }

    .woo-key-table {
        margin-top: 20px;
        border-collapse: collapse;
    }

    .woo-key-table th {
        background: #ebf6eb;
    }

    .woo-key-table th,
    .woo-key-table td {
        border: 1px solid #ccc;
        padding: 8px;
    }

    .woo-key-table tr:nth-child(even) {
        background: #efefef;
    }

    .woo-key-table tr:hover {
        cursor: pointer;
    }
</style>

<div class="woo-key-container">
    <div class="woo-key-heading">
        <img width="125" height="40" src="<?php echo esc_url(TFWA_FILE_URL);?>assets/images/logo-talkbe-v4.png" alt="logo-talkbe">
        <br /><br />
        <h1><?php _e('WooCommerce Integration', TALKBE_FOR_WOOCOMMERCE_API); ?></h1>
    </div>
    <div class="woo-key-row">

        <?php if (empty($data)) { ?>
            <div style="margin: 30px;">
                <img width="25" height="25" src="<?php echo esc_url(TFWA_FILE_URL);?>assets/images/add-icon.png" alt="add-icon" style="float: left;margin: -5px 10px 0 0;">
                <?php _e('Click on "Create Key" to create the keys for', TALKBE_FOR_WOOCOMMERCE_API); ?>
                <a href="https://web.talkbe.app/integrations/woocommerce"><?php _e('https://web.talkbe.app/integrations/woocommerce', TALKBE_FOR_WOOCOMMERCE_API); ?></a>
                <br /><br />
            </div>
            <button class="button button-primary woo-key-btn" id="create-woo-key">
                <?php _e('Create Key', TALKBE_FOR_WOOCOMMERCE_API); ?>
            </button>
        <?php } else { ?>
            <div style="margin: 30px;">
                <img width="25" height="25" src="<?php echo esc_url(TFWA_FILE_URL);?>assets/images/green-check.png" alt="grren-check" style="float: left; margin: 0 10px 0 0;"><?php _e('You have already created the access key. To finish the configuration, please go to', TALKBE_FOR_WOOCOMMERCE_API); ?> <a href="https://web.talkbe.app/integrations/woocommerce"><?php _e('https://web.talkbe.app/integrations/woocommerce', TALKBE_FOR_WOOCOMMERCE_API); ?></a>
                <br />
                <?php _e('You can see the keys by clicking on "Show Key" or you can decide to revoke the key and TalkBe will not be able to register new webhooks. Existing TalkBe webhooks will be removed.', TALKBE_FOR_WOOCOMMERCE_API); ?>
                <br />
                <br />
            </div>
            <button class="button button-primary woo-key-btn" id="revoke-woo-key" style="background-color: #d60000 !important;
    border-color: #c30000 !important;">
                <?php _e('Revoke Key', TALKBE_FOR_WOOCOMMERCE_API); ?>
            </button>
        <?php } ?>

        <?php if (!empty($data)) { ?>
            <button class="button button-primary woo-key-btn" id="show-woo-key" style="margin-left:20px;">
                <?php _e('Show Key', TALKBE_FOR_WOOCOMMERCE_API); ?>
            </button>
        <?php } ?>
    </div>
    <div class="woo-key-data" style="display:none;">
        <table class="woo-key-table" id="woo-key-table" width="100%">
            <thead>
                <tr>
                    <th><?php _e('User', TALKBE_FOR_WOOCOMMERCE_API); ?></th>
                    <!--     <th><?php _e('Description', TALKBE_FOR_WOOCOMMERCE_API); ?></th> -->
                    <!--     <th><?php _e('Permissions', TALKBE_FOR_WOOCOMMERCE_API); ?></th> -->
                    <th><?php _e('Consumer Key', TALKBE_FOR_WOOCOMMERCE_API); ?></th>
                    <th><?php _e('Consumer Secret', TALKBE_FOR_WOOCOMMERCE_API); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data)) {

                    $user_name = get_user_by('id', $data['user_id'])->display_name;
                ?>
                    <tr>
                        <td><?php echo esc_html($user_name); ?></td>
                        <!--            <td><?php echo esc_html($data['description']); ?></td>  -->
                        <!--            <td><?php echo esc_html($data['permissions']); ?></td>  -->
                        <td class='ck-copy' onclick='copyConsumerKey("<?php echo esc_js($data["consumer_key"]); ?>")'>
                            <?php echo esc_html($data['consumer_key']); ?>
                        </td>
                        <td class='cs-copy' onclick='copyConsumerSecret("<?php echo esc_js($data["consumer_secret"]); ?>")'>
                            <?php echo esc_html($data['consumer_secret']); ?>
                        </td>
                    </tr>
                <?php
                } ?>
            </tbody>
        </table>

    </div>
    <div style="margin-top:100px">
        <p style="text-align:center">
            <img src="<?php echo esc_url(TFWA_FILE_URL);?>assets/images/talkbe-woocom-integration.jpg" width="425" height="350" alt="talkbe-woocom-integration">
        </p>
    </div>
</div>

<script type="text/javascript">
    /* create consumer key and consumer secret */
    jQuery(document).on('click', '#create-woo-key', function(e) {
        e.preventDefault();
        var ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
        var data = {
            action: 'create_key_for_wc',
        };
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(res) {
                var res = JSON.parse(res);
                alert(res.message);
                location.reload();
            },
        });
    });
    jQuery(document).on('click', '#revoke-woo-key', function(e) {
        e.preventDefault();
        var delete_confirmation = confirm('If you delete the key Talkbe will no longer receive notifications from woocoommerce. Are you sure you want to continue?');
        if (delete_confirmation === false) {
            return;
        }
        var ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
        var data = {
            action: 'revoke_key_for_wc',
        };
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(res) {
                var res = JSON.parse(res);
                alert(res.message);
                location.reload();
            },
        });
    });
    /* toggle table data */
    jQuery(document).on('click', '#show-woo-key', function(e) {
        e.preventDefault();
        var text = jQuery(this).text().trim();
        if (text == 'Show Key') {
            var update_text = 'Hide Key';
        } else if (text == 'Hide Key') {
            var update_text = 'Show Key';
        }
        jQuery(this).text(update_text);
        jQuery('.woo-key-data').toggle();
    });

    /* copy consumer key */
    function copyConsumerKey(ck = '') {
        if (ck) {
            var copied = copyText(ck);
            if (copied) {
                var html = '<b style="color:green;">Copied</b>';
                jQuery('.ck-copy').html(html);
                setTimeout(function() {
                    jQuery('.ck-copy').text(ck);
                }, 1000);
            }
        }
    }

    /* copy secret key */
    function copyConsumerSecret(cs = '') {
        if (cs) {
            var copied = copyText(cs);
            if (copied) {
                var html = '<b style="color:green;">Copied</b>';
                jQuery('.cs-copy').html(html);
                setTimeout(function() {
                    jQuery('.cs-copy').text(cs);
                }, 1000);
            }
        }
    }

    /* copy text */
    function copyText(text) {
        var copyText = text.trim();
        let input = document.createElement('input');
        input.setAttribute('type', 'text');
        input.value = copyText;
        document.body.appendChild(input);
        input.select();
        document.execCommand("copy");
        return document.body.removeChild(input);
    }
</script>