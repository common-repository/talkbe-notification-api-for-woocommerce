<?php

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/* if uninstall.php is not called by WordPress, die */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;
$wp_woocommerce_api_keys = $wpdb->prefix . 'woocommerce_api_keys';
$wp_webhook = $wpdb->prefix.'wc_webhooks';
$woocommerce_talkbe_data = get_option('woocommerce_talkbe_data');
if (isset($woocommerce_talkbe_data) && !empty($woocommerce_talkbe_data)) {
    $truncated_key = $woocommerce_talkbe_data['truncated_key'];
    if ($truncated_key) {
        $removed = $wpdb->delete($wp_woocommerce_api_keys, array('truncated_key' => $truncated_key));
        /* remove from option */
        if ($removed) {
            delete_option('woocommerce_talkbe_data');
        }
    }
}
/* Delete Webhook related to talkbe */
$sql = 'select * from ' . $wp_webhook . ' where name like "talkbe%"';
$result = $wpdb->get_results($sql);
if (isset($result) && !empty($result)) {
    foreach ($result as $row) {
        $where = array('webhook_id' => $row->webhook_id);
        $wpdb->delete($wp_webhook, $where);
    }
}
