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
	 * @var string
	 */
	// @phpcs:ignore
	public $title;

	/**
	 * @var string
	 */
	// @phpcs:ignore
	public $description;

	/**
	 * @var string
	 */
	// @phpcs:ignore
	public $enabled;

	/**
	 * @var string
	 */
	// @phpcs:ignore
	public $method_title;

	/**
	 * @var string
	 */
	// @phpcs:ignore
	public $method_description;

	/**
	 * @var string
	 */
	// @phpcs:ignore
	public $order_button_text;

	/**
	 * @var bool
	 */
	// @phpcs:ignore
	public $has_fields;

	/**
	 * @var array<string>
	 */
	// @phpcs:ignore
	public $supports;

	/**
	 * @var array<mixed>
	 */
	// @phpcs:ignore
	public $form_fields;

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
		$this->title = $this->get_option('title');
		$this->enabled = $this->get_option('enabled');
		$this->description = $this->get_option('description');
		$this->order_button_text = $this->get_option('order_button_text');
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
	}

	public function init_form_fields()
	{
		$this->form_fields = [
			'enabled' => [
				'title'       => esc_html__('Enable/Disable', 'pacts'),
				'label'       => esc_html__('Enable', 'pacts'),
				'type'        => 'checkbox',
				'default'     => 'no'
			],
			'title' => [
				'title'       => esc_html__('Title', 'pacts'),
				'type'        => 'text',
				'description' => esc_html__(
					'This controls the title which the user sees during checkout.',
					'pacts'
				),
				'default'     => esc_html__('Pacts', 'pacts')
			],
			'description' => [
				'title'       => esc_html__('Description', 'pacts'),
				'type'        => 'textarea',
				'description' => esc_html__(
					'This controls the description which the user sees during checkout.',
					'pacts'
				),
				'default'     => esc_html__(
					'You can pay with supported networks and cryptocurrencies.',
					'pacts'
				),
			],
			'order_button_text' => [
				'title'       => esc_html__('Order button text', 'pacts'),
				'type'        => 'text',
				'description' => esc_html__('Pay button on the checkout page', 'pacts'),
				'default'     => esc_html__('Proceed to Pacts', 'pacts'),
			],
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
