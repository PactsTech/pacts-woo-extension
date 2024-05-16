<?php

namespace PactsWooExtension\WooCommerce;

class Register
{
	public function __construct()
	{
		add_filter('woocommerce_payment_gateways', [$this, 'register_payment_gateways']);
	}

	public function register_payment_gateways($gateways)
	{
		$gateways[] = PactsGateway::class;
		return $gateways;
	}
}
