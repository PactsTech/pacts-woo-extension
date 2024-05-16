<?php

namespace PactsWooExtension\Admin;

/**
 * PactsWooExtension Setup Class
 */
class Setup
{
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
		add_action('admin_menu', [$this, 'register_page']);
	}

	/**
	 * Load all necessary dependencies.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts()
	{
		if (
			!method_exists('Automattic\WooCommerce\Admin\PageController', 'is_admin_or_embed_page') ||
			!\Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page()
		) {
			return;
		}

		$script_path       = '/build/index.js';
		$script_asset_path = dirname(MAIN_PLUGIN_FILE) . '/build/index.asset.php';
		$script_asset      = file_exists($script_asset_path)
			? require $script_asset_path
			: [
				'dependencies' => array(),
				'version'      => filemtime($script_path),
			];
		$script_url        = plugins_url($script_path, MAIN_PLUGIN_FILE);

		wp_register_script(
			'pacts-woo-extension',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_register_style(
			'pacts-woo-extension',
			plugins_url('/build/index.css', MAIN_PLUGIN_FILE),
			// Add any dependencies styles may have, such as wp-components.
			array(),
			filemtime(dirname(MAIN_PLUGIN_FILE) . '/build/index.css')
		);

		wp_enqueue_script('pacts-woo-extension');
		wp_enqueue_style('pacts-woo-extension');
	}

	/**
	 * Register page in wc-admin.
	 *
	 * @since 1.0.0
	 */
	public function register_page()
	{

		if (!function_exists('wc_admin_register_page')) {
			return;
		}

		wc_admin_register_page(
			[
				'id'     => 'pacts_woo_extension-example-page',
				'title'  => __('Pacts Woo Extension', 'pacts_woo_extension'),
				'parent' => 'woocommerce',
				'path'   => '/pacts-woo-extension',
			]
		);
	}
}
