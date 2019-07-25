<?php
/**
 * Plugin Name: VR WooCommerce - Forma de pagamento
 * Plugin URI: https://brasa.art.br
 * Description: VR WooCommerce - Forma de pagamento by Brasa.art.br
 * Author: Brasa
 * Author URI: https://brasa.art.br
 * Version: 0.1
 * Text Domain: vr-woocommerce
 * Domain Path: /languages/
 *
 * @package WordPress
 * @author  Brasa.art.br
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'VR_WC' ) ) {

	/**
	 * Main Class.
	 */
	class VR_WC {


		/**
		* Plugin version.
		*
		* @var string
		*/
		const VERSION = '1.0.0';


		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return object single instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
		/**
		 * Retorna o diretório do arquivo
		 *
		 * @return string
		 */
		public static function dir_path() {
			return plugin_dir_path( __FILE__ );
		}
		/**
		 * Constructor
		 */
		private function __construct() {
			$this->dir_path = plugin_dir_path( __FILE__ );
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', array( $this, 'fallback_notice' ) );
			} else {
				if ( 'BRL' != get_woocommerce_currency() ){
					add_action( 'admin_notices', array( $this, 'fallback_currency' ) );
					return false;
				}
				$this->load_plugin_textdomain();
				$this->includes();
			}
		}

        /**
         * Method to call and run all the things that you need to fire when your plugin is activated.
         *
         */
        public static function activate() {
            include_once 'includes/vr-wc-activate.php';

            VR_WC_Activate::activate();

        }

        /**
         * Method to call and run all the things that you need to fire when your plugin is deactivated.
         *
         */
        public static function deactivate() {
            //include_once 'includes/vr-wc-deactivate.php';
            //VR_WC_Deactivate::deactivate();
        }

		/**
		 * Method to includes our dependencies.
		 *
		 * @var string
		 */
		public function includes() {
			include_once 'includes/classes/class-vr-api.php';
			include_once 'includes/class-woocommerce-gateway.php';
			vr_wc_gateway_init();
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @access public
		 * @return bool
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'vr-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
		}

		/**
		 * Fallback notice.
		 *
		 * We need some plugins to work, and if any isn't active we'll show you!
		 */
		public function fallback_notice() {
			echo '<div class="error">';
			echo '<p>' . __( 'VR WooCommerce: Needs the WooCommerce Plugin activated.', 'vr-woocommerce' ) . '</p>';
			echo '</div>';
		}

		/**
		 * Bloquear funcionamento do plugin em outras moedas
		 *
		 */
		public function fallback_currency() {
			echo '<div class="error">';
			echo '<p>' . __( 'VR WooCommerce works only with BRL currency (Brazil)', 'vr-woocommerce' ) . '</p>';
			echo '</div>';
		}
	}
}

/**
* Hook to run when your plugin is activated
*/
register_activation_hook( __FILE__, array( 'VR_WC', 'activate' ) );

/**
* Hook to run when your plugin is deactivated
*/
register_deactivation_hook( __FILE__, array( 'VR_WC', 'deactivate' ) );

/**
* Initialize the plugin.
*/
add_action( 'plugins_loaded', array( 'VR_WC', 'get_instance' ) );