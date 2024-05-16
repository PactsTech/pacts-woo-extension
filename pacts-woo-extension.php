<?php

/**
 * Plugin Name: Pacts Woo Extension
 * Version: 0.1.0
 * Author: The WordPress Contributors
 * Author URI: https://woo.com
 * Text Domain: pacts-woo-extension
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package extension
 */

defined('ABSPATH') || exit;

if (!defined('MAIN_PLUGIN_FILE')) {
	define('MAIN_PLUGIN_FILE', __FILE__);
}

require_once plugin_dir_path(__FILE__) . '/vendor/autoload_packages.php';

use PactsWooExtension\WooCommerce\PactsGateway;
use PactsWooExtension\WooCommerce\Blocks\PactsGatewayBlocks;

// phpcs:disable WordPress.Files.FileName

/**
 * WooCommerce fallback notice.
 *
 * @since 0.1.0
 */
function pacts_woo_extension_missing_wc_notice()
{
	/* translators: %s WC download URL link. */
	echo '<div class="error"><p><strong>' . sprintf(esc_html__('Pacts Woo Extension requires WooCommerce to be installed and active. You can download %s here.', 'pacts_woo_extension'), '<a href="https://woo.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
}

register_activation_hook(__FILE__, 'pacts_woo_extension_activate');

/**
 * Activation hook.
 *
 * @since 0.1.0
 */
function pacts_woo_extension_activate()
{
	if (!class_exists('WooCommerce')) {
		add_action('admin_notices', 'pacts_woo_extension_missing_wc_notice');
		return;
	}
}

if (!class_exists('PactsWooExtension')) {
	/**
	 * The pacts_woo_extension class.
	 */
	class PactsWooExtension
	{
		/**
		 * This class instance.
		 *
		 * @var \pacts_woo_extension single instance of this class.
		 */
		private static $instance;

		/**
		 * Constructor.
		 */
		public function __construct()
		{
			add_filter('woocommerce_payment_gateways', [__CLASS__, 'register_gateway']);
			add_action('woocommerce_blocks_loaded', [__CLASS__, 'register_gateway_blocks']);
		}

		/**
		 * Gets the main instance.
		 *
		 * Ensures only one instance can be loaded.
		 *
		 * @return \pacts_woo_extension
		 */
		public static function instance()
		{
			if (null === self::$instance) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public static function register_gateway($gateways)
		{
			$options = get_option('woocommerce_pacts_settings', []);
			if (isset($options['hide_for_non_admin_users'])) {
				$hide_for_non_admin_users = $options['hide_for_non_admin_users'];
			} else {
				$hide_for_non_admin_users = 'no';
			}
			if (('yes' === $hide_for_non_admin_users && current_user_can('manage_options')) || 'no' === $hide_for_non_admin_users) {
				$gateways[] = PactsGateway::class;
			}
			return $gateways;
		}

		public static function register_gateway_blocks()
		{
			if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
				add_action(
					'woocommerce_blocks_payment_method_type_registration',
					function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
						$payment_method_registry->register(new PactsGatewayBlocks());
					}
				);
			}
		}

		public static function plugin_url()
		{
			return untrailingslashit(plugins_url('/', __FILE__));
		}

		public static function plugin_abspath()
		{
			return trailingslashit(plugin_dir_path(__FILE__));
		}
	}
}

/**
 * Initialize the plugin.
 *
 * @since 0.1.0
 */
function pacts_woo_extension_init()
{
	load_plugin_textdomain('pacts_woo_extension', false, plugin_basename(dirname(__FILE__)) . '/languages');
	if (!class_exists('WooCommerce')) {
		add_action('admin_notices', 'pacts_woo_extension_missing_wc_notice');
		return;
	}
	PactsWooExtension::instance();
}

add_action('plugins_loaded', 'pacts_woo_extension_init', 10);
