<?php
/**
 * Plugin Name: VR WooCommerce - Forma de pagamento
 * Plugin URI: https://brasa.art.br
 * Description: VR WooCommerce - Forma de pagamento by Brasa.art.br
 * Author: SkyVerge
 * Author URI: https://brasa.art.br
 * Version: 0.1
 * Text Domain: vr-woocommerce
 * Domain Path: /i18n/languages/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   vr-woocommerce
 * @author    Brasa Design
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */
 
defined( 'ABSPATH' ) or exit;

/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function vr_wc_add_to_gateways( $gateways ) {
	$gateways[] = 'VR_WC_Gateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'vr_wc_add_to_gateways' );


/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function vr_wc_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=offline_gateway' ) . '">' . __( 'Configure', 'vr-woocommerce' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'vr_wc_plugin_links' );


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
 * @author 		SkyVerge
 */

function vr_wc_gateway_init() {

	class VR_WC_Gateway extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'vr-wc-gateway';
			$this->icon               = apply_filters('woocommerce_offline_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'VR Card Payments', 'vr-woocommerce' );
			$this->method_description = __( 'Allows VR Card Payments' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
		  
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}
	
	
		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'wc_offline_form_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'vr-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable VR Card Payment', 'vr-woocommerce' ),
					'default' => 'yes'
				),
				
				'title' => array(
					'title'       => __( 'Title', 'vr-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'vr-woocommerce' ),
					'default'     => __( 'VR Card Payment', 'vr-woocommerce' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'vr-woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'vr-woocommerce' ),
					'default'     => __( 'Please remit payment to Store Name upon pickup or delivery.', 'vr-woocommerce' ),
					'desc_tip'    => true,
				),
				'instructions' => array(
					'title'       => __( 'Instructions', 'vr-woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page and emails.', 'vr-woocommerce' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'endpoint' => array(
					'title'       	=> __( 'Endpoint', 'vr-woocommerce' ),
					'type'        	=> 'select',
					'description' 	=> __( 'Instructions that will be added to the thank you page and emails.', 'vr-woocommerce' ),
					'default'     	=> 'producao',
					'desc_tip'		=> true,
					'options'		=> array(
						'dev'			=> __('Developer Portal (Mock)', 'vr-woocommerce'),
						'homologacao'	=> __('Homologacão','vr-woocommerce'),
						'producao'		=> __('Produção', 'vr-woocommerce' )
					)
				),
				'client_id' => array(
					'title'       => __( 'Client ID', 'vr-woocommerce' ),
					'type'        => 'text',
					'description' => __( '<a href="https://dev.vr.com.br/api-portal/myapps">Create an app and get the CLIENT_ID', 'vr-woocommerce' ),
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
				echo wpautop( wptexturize( $this->instructions ) );
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
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
	
			$order = wc_get_order( $order_id );
			
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Awaiting offline payment', 'vr-woocommerce' ) );
			
			// Reduce stock levels
			$order->reduce_order_stock();
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
	
  } // end \WC_Gateway_Offline class
}