<?php

/**
 * Plugin Name:7StarPay Payment Gateway for crypto currencies (七星支付，虚拟货币支付)
 * Plugin URI: https://www.7starpay.com/
 * Description: Easily accept payment with digital currency.
 * Version: 2.3.6
 * Tested up to: 5.9.2
 * Author: 7Star Group.
 * Author URI: https://www.7starpay.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: 7starpay
 */

    if (! defined ( 'ABSPATH' )){
        exit (); // Exit if accessed directly
    }

    function wc_7starpay_log($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }

    define('C_WC_7STARPAY_ID','wc7starpaygateway');
    define('C_WC_7STARPAY_DIR',rtrim(plugin_dir_path(__FILE__),'/'));
    define('C_WC_7STARPAY_URL',rtrim(plugin_dir_url(__FILE__),'/'));

    define('C_WC_7STARPAY_OPENAPI_HOST','https://api.blockchaingate.com/v2/');
    define('C_WC_7STARPAY_WEB', 'https://www.7starpay.com/wallet/starpay');
    /*
    // this is for testnet
    define('C_WC_7STARPAY_OPENAPI_HOST','https://test.blockchaingate.com/v2/');
    define('C_WC_7STARPAY_WEB', 'http://localhost:4200/wallet/starpay');
    */
    add_action( 'plugins_loaded', 'init_7star_pay_gateway_class' );

    function init_7star_pay_gateway_class() {
        wc_7starpay_log('init_7star_pay_gateway_class getting started');
        if( !class_exists('WC_Payment_Gateway') ){
            return;
        }
        require_once( plugin_basename( 'class-wc-7star-pay-gateway.php' ) );    
    }

    add_filter( 'woocommerce_payment_gateways', 'add_7star_pay_gateway_class' );
    function add_7star_pay_gateway_class( $methods ) {
        $methods[] = 'WC_7StarPay_Gateway'; 
        return $methods;
    }


    //Set hearbeat to check order status for WeChat pay
    add_action( 'init', 'wc_7starpay_init_heartbeat' );
    function wc_7starpay_init_heartbeat(){
        wp_enqueue_script('heartbeat');
    }

    add_filter( 'heartbeat_settings', 'wc_7starpay_setting_heartbeat' );
    function wc_7starpay_setting_heartbeat( $settings ) {
        $settings['interval'] = 5;
        return $settings;
    }

    add_filter('heartbeat_received', 'wc_7starpay_heartbeat_received', 10, 2);
    add_filter('heartbeat_nopriv_received', 'wc_7starpay_heartbeat_received', 10, 2 );
    function wc_7starpay_heartbeat_received($response, $data){
        if(!isset($data['orderId'])){
            return;
        }

        $gateway = new WC_7StarPay_Gateway();
        $isCompleted = $gateway->is_order_completed($data['orderId']);

        if($isCompleted){
            $response['status'] = 'SUCCESS';
        }

        return $response;
    }

    add_action( 'woocommerce_product_options_advanced', 'add_7star_custom_fields' );

    add_action( 'woocommerce_process_product_meta', 'add_7star_save_custom_fields' );

    function add_7star_save_custom_fields( $post_id ){

        $starpay_giveaway_rate = $_POST["starpay_giveaway_rate"];
        update_post_meta( $post_id, 'starpay_giveaway_rate', esc_html( $starpay_giveaway_rate ) );


        $starpay_tax_rate = $_POST["starpay_tax_rate"];
        update_post_meta( $post_id, 'starpay_tax_rate', esc_html( $starpay_tax_rate ) );


        $starpay_locked_days = $_POST["starpay_locked_days"];
        update_post_meta( $post_id, 'starpay_locked_days', esc_html( $starpay_locked_days ) );

    
    }

    function add_7star_custom_fields() {
        $starpay_giveaway_rate = get_post_meta( get_the_ID(), 'starpay_giveaway_rate', true );
        woocommerce_wp_text_input(
            array(
                'id'                => 'starpay_giveaway_rate',
                'value'             => $starpay_giveaway_rate,
                'label'             => __( 'Giveaway rate', 'woocommerce' ),
                'placeholder'       => '',
               'desc_tip'    		=> true,
                'description'       => __( "Giveaway rate for 7StarPay.", 'woocommerce' ),
                'type'              => 'number',
                'custom_attributes' => array(
                        'step' 	=> 'any',
                        'min'	=> '0'
                    )
            )
        );
   
        woocommerce_wp_text_input(
            array(
                'id'                => 'starpay_tax_rate',
                'value'             => get_post_meta( get_the_ID(), 'starpay_tax_rate', true ),
                'label'             => __( 'Tax rate', 'woocommerce' ),
                'placeholder'       => '',
               'desc_tip'    		=> true,
                'description'       => __( "Tax rate for 7StarPay.", 'woocommerce' ),
                'type'              => 'number',
                'custom_attributes' => array(
                        'step' 	=> 'any',
                        'min'	=> '0'
                    )
            )
        );

        woocommerce_wp_select( array(
            'id'          => 'starpay_locked_days',
            'value'       => get_post_meta( get_the_ID(), 'starpay_locked_days', true ),
            'label'       => 'Locked days',
            'desc_tip'    		=> true,
            'description'       => __( "Locked days for 7StarPay.", 'woocommerce' ),
            'options'     => array( '' => 'Please select', '90' => '90', '366' => '366'),
        ) );

    }
?>
