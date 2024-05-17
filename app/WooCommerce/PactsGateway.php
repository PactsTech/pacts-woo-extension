<?php

namespace PactsWooExtension\WooCommerce;

class PactsGateway extends \WC_Payment_Gateway
{
	/**
	 * @var string
	 */
	// @phpcs:ignore
	public $id = 'pacts';

	/**
	 * @var array<mixed>
	 */
	// @phpcs:ignore
	public $addresses;

	public function __construct()
	{
		$this->method_title = esc_html__('Pacts', 'pacts');
		$this->method_description = esc_html__(
			'Allow your customers to pay with your pacts order processor.',
			'pacts'
		);
		$this->supports = ['products'];
		$this->init_form_fields();
		$this->init_settings();
		$this->has_fields = false;
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
	}

	public function init_form_fields()
	{
		$this->form_fields = [
			'enabled' => [
				'title' => esc_html__('Enable/Disable', 'pacts'),
				'label' => esc_html__('Enable', 'pacts'),
				'type' => 'checkbox',
				'default' => 'no'
			],
			'title' => [
				'title' => esc_html__('Title', 'pacts'),
				'type' => 'text',
				'description' => esc_html__(
					'This controls the title which the user sees during checkout.',
					'pacts'
				),
				'default' => esc_html__('Pacts', 'pacts')
			],
			'description' => [
				'title' => esc_html__('Description', 'pacts'),
				'type' => 'textarea',
				'description' => esc_html__(
					'This controls the description which the user sees during checkout.',
					'pacts'
				),
				'default' => esc_html__(
					'You can pay with supported networks and cryptocurrencies.',
					'pacts'
				),
			],
			'order_button_text' => [
				'title' => esc_html__('Order button text', 'pacts'),
				'type' => 'text',
				'description' => esc_html__('Pay button on the checkout page', 'pacts'),
				'default' => esc_html__('Proceed to Pacts', 'pacts'),
			],
			'arbitrumAddress' => [
				'title' => esc_html__('Arbitrum Address', 'pacts'),
				'type' => 'text',
				'default' => null,
				'description' => esc_html__(
					'Pacts order processor address on Arbitrum',
					'pacts'
				),
			],
			'arbitrumSepoliaAddress' => [
				'title' => esc_html__('Arbitrum Sepolia Address', 'pacts'),
				'type' => 'text',
				'default' => null,
				'description' => esc_html__(
					'Pacts order processor address on Arbitrum Sepolia',
					'pacts'
				),
			]
		];
	}

	public function process_payment($orderId)
	{
		global $woocommerce;
		$order = new \WC_Order($orderId);
		// TODO verify transaction on-chain
		$url = $order->get_checkout_order_received_url();
		$woocommerce->cart->empty_cart();
		return [
			'result' => 'success',
			'redirect' => $url
		];
	}
}
