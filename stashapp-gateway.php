<?php
/*
 * Plugin Name: StashApp Woocommerce Payment Gateway
 * Description: Allow cryptocurrency purchases using Stellar Cannacoin & StashApp (wallet)
 * Author: StashApp dev team
 * Author URI: https://stellarcannacoin.org
 * Version: 1.0
 * Text Domain: wc-gateway-stashapp
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2023-2024 StashApp
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   wc-gateway-stashapp
 * @author    StashApp dev team
 * @category  Admin
 * @copyright Copyright (c) 2015-2016, SkyVerge, Inc. and WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */
 
defined( 'ABSPATH' ) or exit;


// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

// add_filter('woocommerce_order_button_html', 'remove_place_order_button_for_specific_payments' );
function remove_place_order_button_for_specific_payments( $button ) {
    // HERE define your targeted payment(s) method(s) in the array
    $targeted_payments_methods = array('stashapp_gateway');
    $chosen_payment_method     = WC()->session->get('chosen_payment_method'); // The chosen payment

    // For matched payment(s) method(s), we remove place order button (on checkout page)
    if( in_array( $chosen_payment_method, $targeted_payments_methods ) && ! is_wc_endpoint_url() ) {
        $button = ''; 
    }
    return $button;
}

// jQuery - Update checkout on payment method change
// add_action( 'wp_footer', 'custom_checkout_jquery_script' );
function custom_checkout_jquery_script() {
    if ( is_checkout() && ! is_wc_endpoint_url() ) :
    ?>
    <script type="text/javascript">
    jQuery( function($){
        $('form.checkout').on('change', 'input[name="payment_method"]', function(){
            $(document.body).trigger('update_checkout');
        });
    });
    </script>
    <?php
    endif;
}




/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function wc_offline_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_Offline';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_offline_add_to_gateways' );

/**
 * Defer order emails to reduce load time
 **/
add_filter('woocommerce_defer_transactional_emails', '__return_true' );

/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_offline_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=offline_gateway' ) . '">' . __( 'Configure', 'wc-gateway-stashapp' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_offline_gateway_plugin_links' );


add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_order', 1, 1);
function custom_woocommerce_auto_complete_order() {
	return;
}

/*
 * Add custom payment pages
 **/
add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ){
    $wp_rewrite->rules = array_merge(
        ['checkout/payment/?$' => 'index.php?custom=1'],
		['checkout/patment/failed/?$' => 'index.php?custom=2'],
        $wp_rewrite->rules
    );
});

add_filter( 'template_include', 'payment_failed_template' );
function payment_failed_template( $original_template ) {
	global $wp;
  	if ($wp->request == "checkout/payment/failed") {
		$order = wc_get_order($_GET['transaction_id']);
		$order->update_status('cancelled'); 
    	include plugin_dir_path( __FILE__ ) . 'templates/failed.php';
		die;
  	} else {
    	return $original_template;
  	}
}

//add_filter( 'template_include', 'payment_processing_template' );
function payment_processing_template( $original_template ) {
	global $wp;
  	if ($wp->request == "checkout/payment") {
    	include plugin_dir_path( __FILE__ ) . 'templates/custom.php';
		die;
  	} else {
    	return $original_template;
  	}
}

add_filter( 'query_vars', function( $query_vars ){
    $query_vars[] = 'custom';
    return $query_vars;
} );
add_action( 'template_redirect', function(){
    $custom = intval( get_query_var( 'custom' ) );
    if ( $custom ) {
        include plugin_dir_path( __FILE__ ) . 'templates/custom.php';
        die;
    }
} );

/**
 * Update order on payment complete
 **/
add_filter( 'woocommerce_thankyou_order_received_text', 'misha_thank_you_subtitle', 20, 2 );
function misha_thank_you_subtitle( $thank_you_title, $order2 ){
	global $wp;
	$siteurl = get_option('siteurl');
	$order = wc_get_order($wp->query_vars['order-received']);
	$order->update_status('processing'); 
	return '<center><h1>Payment completed</h1><br>Let us know how you felt the payment through StashApp went! :)<br><br><a href="'.$siteurl.'">Back to front page</a></center>';

}

/**
 * Offline Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class 		WC_Gateway_Offline
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		Stian Instebø
 */
add_action( 'plugins_loaded', 'wc_offline_gateway_init', 11 );

function wc_offline_gateway_init() {

	class WC_Gateway_Offline extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'stashapp_gateway';
			$this->icon               = apply_filters('woocommerce_offline_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'StashApp', 'wc-gateway-stashapp' );
			$this->method_description = __( 'Allow cryptocurrency purchases using Stellar Cannacoin & StashApp (wallet)', 'wc-gateway-stashapp' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->apikey  = $this->get_option( 'apikey' );
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->storeid  = $this->get_option( 'storeid' );
			$this->storename  = $this->get_option( 'storename' );
			$this->storetx  = $this->get_option( 'storetx' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
		  
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}
		

		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'wc_offline_form_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-gateway-stashapp' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable StashApp payments', 'wc-gateway-stashapp' ),
					'default' => 'yes'
				),
				'apikey' => array(
					'title'       => __( 'API Key', 'wc-gateway-stashapp' ),
					'type'        => 'text',
					'description' => __( 'API key used to authenticate the store.', 'wc-gateway-stashapp' ),
					'default'     => __( '', 'wc-gateway-stashapp' ),
					'desc_tip'    => true,
				),
				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-stashapp' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-stashapp' ),
					'default'     => __( 'Pay with StashApp', 'wc-gateway-stashapp' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'wc-gateway-stashapp' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-gateway-stashapp' ),
					'default'     => __( 'Please scan the QR code', 'wc-gateway-stashapp' ),
					'desc_tip'    => true,
				),
				'storename' => array(
					'title'       => __( 'Store name', 'wc-gateway-stashapp' ),
					'type'        => 'text',
					'description' => __( 'Store name found in StashApp POS', 'wc-gateway-stashapp' ),
					'default'     => __( '', 'wc-gateway-stashapp' ),
					'desc_tip'    => true,
				),
				'storetx' => array(
					'title'       => __( 'Store wallet', 'wc-gateway-stashapp' ),
					'type'        => 'text',
					'description' => __( 'Stellar wallet address', 'wc-gateway-stashapp' ),
					'default'     => __( '', 'wc-gateway-stashapp' ),
					'desc_tip'    => true,
				),
				'storeid' => array(
					'title'       => __( 'Store ID', 'wc-gateway-stashapp' ),
					'type'        => 'text',
					'description' => __( 'Store ID found in StashApp POS', 'wc-gateway-stashapp' ),
					'default'     => __( '', 'wc-gateway-stashapp' ),
					'desc_tip'    => true,
				),
				
				'instructions' => array(
					'title'       => __( 'Instructions', 'wc-gateway-stashapp' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-gateway-stashapp' ),
					'default'     => '',
					'desc_tip'    => true,
				),
			) );
		}
	
	
		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) )."AAA: ";
			}
		}
	
	
		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}

		/**
		 * Payment fields
		 */
		public function payment_fields() {
			?>
		
			<div>
				<h3 style="margin-top: 10px;">
					<a href="https://stashapp.cloud" target="_blank" style="margin-right: 20px;">
						<img src="https://stashapp.cloud/wp-content/uploads/2022/09/new_logo.png" style="height: 50px; border-radius: 10px;"/>
					</a> Download StashApp
				</h3>
				<div class="navWrapper" style="height: 80px;">
					<a href="https://play.google.com/apps/testing/org.stellar.cannacoin.stashapp.wallet" target="_blank" style="margin-right: 20px;">
						<img src="https://stellarcannacoin.org/wp-content/uploads/2022/08/googlestore-logo.png" style="height: 35px;"/>
					</a>
					<a href="https://testflight.apple.com/join/cv3UCvFd" target="_blank" style="margin-right: 20px;">
						<img src="https://stellarcannacoin.org/wp-content/uploads/2022/08/appstore-logo.png"  style="height: 35px;"/>
					</a>
				</div>
				<p>
					<?php
						if ( $this->description ) {
							echo wpautop( wp_kses_post( $this->description ) );
						}
					?>
				</p>
				<p style="font-weight: 600;">
					Make sure your wallet is active and you have funds available. You will see the conversion from fiat to crypto in StashApp.
				</p>

			</div>
		
			<?php
		 
		}
	
		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
	
			$order = wc_get_order( $order_id );
			$orderItems = array();
			
			
			
			foreach( $order->get_items( 'line_item' ) as $item_id => $item ) {
				$itemArray = array(
					'_id' => $item->get_product_id(), 
					'name' =>$item->get_name(), 
					'brand' => "",
					'description' =>"Coming soon",
					'barcode' =>$item->get_product_id(),
					'price' =>$item->get_subtotal(),
					'thc' =>null, // setup product variables from ACF
					'cbd' =>null, // setup product variables from ACF
					'_sid' =>$this->storeid,
					'stock' =>null,
					'image'=>null //$item->get_image_id()
				);
				$orderItems[] = $itemArray;
			}
			
			$payload = array(
				'transactionEID' => $order_id,
				'transactionEIDUrl' => $this->get_return_url($order),
				'transactionReceiver' => $this->storetx,
				'transactionAmount' => WC()->cart->total, //WC()->cart->get_cart_subtotal, //WC()->cart->subtotal,
				'transactionItems' => $orderItems,
				'transactionOwner' => $this->storeid,
				'transactionStore' => $this->storename
			);

			$response  = wp_remote_post('https://api.stashapp.cloud/api/v1/payment/initialize', array(
				'headers'     => array('Content-Type' => 'application/json; charset=utf-8', 'x-stshapp-apikey' => $this->apikey), // 
				'body'        => json_encode($payload),
				'method'      => 'POST',
				'data_format' => 'body',
			));
			
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Awaiting StashApp payment', 'wc-gateway-stashapp' ) );
			
			// Reduce stock levels {enable by uncommenting line}
			// $order->reduce_order_stock();
			
			$json = json_decode($response['body'], true);
			
			// Redirect to payment processing screen
			return array(
				'result' 	=> 'success', 
				'redirect'	=>  get_home_url().'/checkout/payment?transaction_id='.$json['payload']['_id'] //$this->get_return_url( $order )
			);
		}
	
  }
}
