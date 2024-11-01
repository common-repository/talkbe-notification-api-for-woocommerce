<?php

/*
Plugin Name: Talkbe For WooCommerce
Plugin URI: https://talkbe.app/
Description: Integrate WooCommerce events with Talkbe.app and send messages on WhatsApp
Version: 1.0.0
Author: Talkbe
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/ 

defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

class woocommerce_talkbe
{
    public $conn;
    public $wp_woocommerce_api_keys;
    public $webhook;
    public $action;

    public function __construct()
    {
        global $wpdb;
        $this->conn = $wpdb;
        $this->wp_woocommerce_api_keys = $this->conn->prefix . 'woocommerce_api_keys';
        $this->webhook = $this->conn->prefix . 'wc_webhooks';
        /* init */
        $this->fn_wk_init();
    }

    public function fn_wk_init()
    {
        /* define */
        define('TALKBE_FOR_WOOCOMMERCE_API', 'talkbe-for-woocommerce-api');
        define('TFWA_FILE_VERSION', '1.0.0');
        define('TFWA_PREFIX', 'tfwa_');
        define('TFWA_FILE_PATH', plugin_dir_path(__FILE__));
        define('TFWA_FILE_URL', plugin_dir_url(__FILE__));
                
        /* register activation hook */
        register_activation_hook(__FILE__,  array($this, 'fn_wt_create_ex_field'));
        /* Deactivation Hook */
        register_deactivation_hook(__FILE__, array($this, 'fn_wt_deactivation_hook'));

        /* add submenu in woocommerce */
        add_action('admin_menu', array($this, 'fn_wt_add_menu_in_wc'));
        /* create woocommerce key */
        add_action('wp_ajax_create_key_for_wc', array($this, 'fn_wt_create_key_for_wc'));
        add_action('wp_ajax_revoke_key_for_wc', array($this, 'fn_wt_revoke_key_for_wc'));
        /* add chat link for phone number */
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'fn_wt_add_link_for_phone'));
    }

    public function fn_wt_create_ex_field()
    {
        add_option('woocommerce_talkbe_status', 0);
        /* Activate the webhook if active */
        $this->action = 'activation';
        $this->fn_wt_webhook_actions();
    }

    public function fn_wt_add_menu_in_wc()
    {
        /* Talkbe page menu */
        add_submenu_page(
            'woocommerce',
            esc_html(__('Talkbe', TALKBE_FOR_WOOCOMMERCE_API)),
            esc_html(__('Talkbe', TALKBE_FOR_WOOCOMMERCE_API)),
            'manage_woocommerce',
            'talkbe',
            array($this, 'fn_wt_show_key_manager'),
        );
    }

    public function fn_wt_show_key_manager()
    {
        if (file_exists(TFWA_FILE_PATH . '/template/talkbe-view.php')) {
            include_once(TFWA_FILE_PATH . '/template/talkbe-view.php');
        }
    }

    public function fn_wt_create_key_for_wc()
    {
        if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'create_key_for_wc') {
            $output = array();
            /* create consumer key */
            $consumer_key_create    = 'ck_' . wc_rand_hash();
            /* create consumer secret  */
            $consumer_secret_create = 'cs_' . wc_rand_hash();
            /* user id */
            $user_id = get_current_user_id();
            /* description */
            $description = 'talkbe-plugin';
            /* permission */
            $permissions = 'read_write';
            /* consumer key */
            $consumer_key = $consumer_key_create;
            /* consumer secret */
            $consumer_secret = $consumer_secret_create;
            /* truncated key */
            $truncated_key = substr($consumer_key_create, -7);
            /* woo_key_array to insert in table */
            $woo_key_array = array(
                'user_id'         => $user_id,
                'description'     => $description,
                'permissions'     => $permissions,
                'consumer_key'    => $consumer_key,
                'consumer_secret' => $consumer_secret,
                'truncated_key'   => $truncated_key
            );
            if (!empty($woo_key_array)) {
                /* insert in table */
                update_option('woocommerce_talkbe_data', $woo_key_array);
                /* woo_key_array to insert in table with wc_api_hash*/
                $woo_key_array = array(
                    'user_id'         => $user_id,
                    'description'     => $description,
                    'permissions'     => $permissions,
                    'consumer_key'    => wc_api_hash($consumer_key),
                    'consumer_secret' => $consumer_secret,
                    'truncated_key'   => $truncated_key
                );
                $woocommerce_default = $this->conn->insert($this->wp_woocommerce_api_keys, $woo_key_array);
                if ($woocommerce_default) {
                    $flg = 1;
                    $message = esc_html(__('Woocommerce Consumer Key and Consumer Secret is created.', TALKBE_FOR_WOOCOMMERCE_API));
                } else {
                    $flg = 0;
                    $message = esc_html(__('Something is wrong to create Consumer Key and Consumer Secret.', TALKBE_FOR_WOOCOMMERCE_API));
                }
                $output = array(
                    'flg' => $flg,
                    'message' => $message
                );
            }
        }
        echo json_encode($output);
        wp_die();
    }

    public function fn_wt_revoke_key_for_wc()
    {

        $flg = 0;
        $message = __('There is an error to delete the keys', TALKBE_FOR_WOOCOMMERCE_API);
        $output = array('flg' => $flg, 'message' => $message);

        if (isset($_POST['action']) && sanitize_text_field($_POST['action']) == 'revoke_key_for_wc') {
            $existing_key = get_option('woocommerce_talkbe_data');
            $ex_trunc_key = $existing_key['truncated_key'];
            delete_option('woocommerce_talkbe_data');

            $sql = 'delete from ' . $this->wp_woocommerce_api_keys . ' where truncated_key = "' . $ex_trunc_key . '"';
            $result = $this->conn->query($sql);
            if ($result) {
                $flg = 1;
                $message = __('Keys deleted successfully', TALKBE_FOR_WOOCOMMERCE_API);
                $this->action = 'revoke';
                $this->fn_wt_webhook_actions();
            } else {
                $flg = 0;
                $message = __('There is an error to delete keys.', TALKBE_FOR_WOOCOMMERCE_API);
            }
            $output = array('flg' => $flg, 'message' => $message);
        }
        echo json_encode($output);
        wp_die();
    }
    public function fn_wt_add_link_for_phone($order)
    {
        /* get billing phone */
        $billing_phone = $order->get_billing_phone();
        /* get shipping phone */
        $shipping_phone = $order->get_shipping_phone();

        $html = '';
        if ($billing_phone ==  $shipping_phone) {
            $html .=  '<h3>' . esc_html(__('Talkbe', TALKBE_FOR_WOOCOMMERCE_API)) . '</h3>';
            $html .= '<a href="https://web.talkbe.app/chat/' . $billing_phone . '" target="_blank">' . $billing_phone . '</a>';
        } else if ($billing_phone != '' &&  $shipping_phone == '') {
            $html .=  '<h3>' . esc_html(__('Talkbe', TALKBE_FOR_WOOCOMMERCE_API)) . '</h3>';
            $html .= '<a href="https://web.talkbe.app/chat/' . $billing_phone . '" target="_blank">' . $billing_phone . '</a>';
        } else if ($billing_phone == '' &&  $shipping_phone != '') {
            $html .=  '<h3>' . esc_html(__('Talkbe', TALKBE_FOR_WOOCOMMERCE_API)) . '</h3>';
            $html .= '<a href="https://web.talkbe.app/chat/' . $shipping_phone . '" target="_blank">' . $shipping_phone . '</a>';
        } else {
            $html .=  '<h3>' . esc_html(__('Talkbe', TALKBE_FOR_WOOCOMMERCE_API)) . '</h3>';
            $html .= '<a href="https://web.talkbe.app/chat/' . $billing_phone . '" target="_blank">' . $billing_phone . '</a>';
            $html .= '<br>';
            $html .= '<a href="https://web.talkbe.app/chat/' . $shipping_phone . '" target="_blank">' . $shipping_phone . '</a>';
        }
        echo esc_html($html);
    }

    public function fn_wt_deactivation_hook()
    {
        $this->action = 'deactivation';
        $this->fn_wt_webhook_actions();
    }

    public function fn_wt_webhook_actions()
    {
        $sql = 'select * from ' . $this->webhook . ' where name like "talkbe%"';
        $result = $this->conn->get_results($sql);
        if (isset($result) && !empty($result)) {
            foreach ($result as $row) {
                if ($this->action == 'activation') {
                    $status = 'active';
                    $data = array('status' => $status);
                    $where = array('webhook_id' => $row->webhook_id);
                    $this->conn->update($this->webhook, $data, $where);
                } else if ($this->action == 'deactivation') {
                    $status = 'paused';
                    $data = array('status' => $status);
                    $where = array('webhook_id' => $row->webhook_id);
                    $this->conn->update($this->webhook, $data, $where);
                } else if ($this->action == 'revoke') {
                    $where = array('webhook_id' => $row->webhook_id);
                    $this->conn->delete($this->webhook, $where);
                }
            }
        }
    }
}

$woocommerce_talkbe = new woocommerce_talkbe();
