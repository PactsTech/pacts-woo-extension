<?php

namespace PactsWooExtension\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use PactsExtension;

final class PactsGatewayBlocks extends AbstractPaymentMethodType
{
	private $gateway;

	protected $name = 'pacts';

	public function initialize()
	{
		$this->settings = get_option('woocommerce_pacts_settings', []);
		$gateways = WC()->payment_gateways->payment_gateways();
		$this->gateway = $gateways[$this->name];
	}

	public function is_active()
	{
		return $this->gateway->is_available();
	}

	public function get_payment_method_script_handles()
	{
		$script_path       = '/assets/js/frontend/blocks.js';
		$script_asset_path = \PactsExtension::plugin_abspath() . 'assets/js/frontend/blocks.asset.php';
		$script_asset = file_exists($script_asset_path)
			? require($script_asset_path)
			: ['dependencies' => [], 'version' => '1.2.0'];
		$script_url = \PactsExtension::plugin_url() . $script_path;
		wp_register_script(
			'wc-pacts-payments-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		if (function_exists('wp_set_script_translations')) {
			$languagesPath = \PactsExtension::plugin_abspath() . 'languages/';
			wp_set_script_translations('wc-pacts-payment-blocks', 'pacts-gateway-blocks', $languagesPath);
		}
		return ['wc-pacts-payments-blocks'];
	}

	public function get_payment_method_data()
	{
		$settings = $this->gateway->settings;
		$supports = array_filter($this->gateway->supports, [$this->gateway, 'supports']);
		$addresses = [];
		$suffix = 'Address';
		foreach ($settings as $key => $value) {
			$ending = substr($key, -strlen($suffix));
			if ($suffix == $ending && !empty($value)) {
				$chain = str_replace($suffix, '', $key);
				$addresses[$chain] = $value;
			}
		}
		return [
			'title' => $settings['title'],
			'description' => $settings['description'],
			'supports' => $supports,
			'addresses' => $addresses
		];
	}
}
