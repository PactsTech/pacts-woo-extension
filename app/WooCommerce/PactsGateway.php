<?php

namespace PactsWooExtension\WooCommerce;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use Exception;
use Web3\Contract;
use phpseclib\Math\BigInteger;

class PactsGateway extends \WC_Payment_Gateway
{
	const ERC20_ABI = '[{"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"sender","type":"address"},{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';

	const PACTS_ABI = '[{"inputs":[{"internalType":"string","name":"storeName_","type":"string"},{"internalType":"uint256","name":"cancelBlocks_","type":"uint256"},{"internalType":"uint256","name":"disputeBlocks_","type":"uint256"},{"internalType":"address","name":"reporter","type":"address"},{"internalType":"bytes32","name":"reporterPublicKey_","type":"bytes32"},{"internalType":"address","name":"arbiter","type":"address"},{"internalType":"bytes32","name":"arbiterPublicKey_","type":"bytes32"},{"internalType":"address","name":"token_","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[],"name":"AccessControlBadConfirmation","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"},{"internalType":"bytes32","name":"neededRole","type":"bytes32"}],"name":"AccessControlUnauthorizedAccount","type":"error"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"}],"name":"Aborted","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"}],"name":"Canceled","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"}],"name":"Completed","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"},{"indexed":false,"internalType":"bytes","name":"shipmentBuyer","type":"bytes"},{"indexed":false,"internalType":"bytes","name":"shipmentReporter","type":"bytes"}],"name":"Delivered","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":true,"internalType":"address","name":"arbiter","type":"address"},{"indexed":false,"internalType":"string","name":"storeName_","type":"string"},{"indexed":false,"internalType":"uint256","name":"cancelBlocks_","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"disputeBlocks_","type":"uint256"},{"indexed":false,"internalType":"address","name":"token_","type":"address"}],"name":"Deployed","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"arbiter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"},{"indexed":false,"internalType":"string","name":"disputeUrl","type":"string"}],"name":"Disputed","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"},{"indexed":false,"internalType":"bytes","name":"shipmentBuyer","type":"bytes"},{"indexed":false,"internalType":"bytes","name":"shipmentReporter","type":"bytes"}],"name":"Failed","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"arbiter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"},{"indexed":false,"internalType":"uint256","name":"sellerDeposit","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"buyerDeposit","type":"uint256"}],"name":"Resolved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"bytes32","name":"role","type":"bytes32"},{"indexed":true,"internalType":"bytes32","name":"previousAdminRole","type":"bytes32"},{"indexed":true,"internalType":"bytes32","name":"newAdminRole","type":"bytes32"}],"name":"RoleAdminChanged","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"bytes32","name":"role","type":"bytes32"},{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":true,"internalType":"address","name":"sender","type":"address"}],"name":"RoleGranted","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"bytes32","name":"role","type":"bytes32"},{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":true,"internalType":"address","name":"sender","type":"address"}],"name":"RoleRevoked","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"},{"indexed":false,"internalType":"bytes","name":"shipmentBuyer","type":"bytes"},{"indexed":false,"internalType":"bytes","name":"shipmentReporter","type":"bytes"}],"name":"Shipped","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"seller","type":"address"},{"indexed":true,"internalType":"address","name":"buyer","type":"address"},{"indexed":true,"internalType":"address","name":"reporter","type":"address"},{"indexed":false,"internalType":"string","name":"orderId","type":"string"},{"indexed":false,"internalType":"string","name":"storeName_","type":"string"},{"indexed":false,"internalType":"uint256","name":"price","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"shipping","type":"uint256"}],"name":"Submitted","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"payee","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"}],"name":"Withdrawn","type":"event"},{"inputs":[],"name":"ARBITER_ROLE","outputs":[{"internalType":"bytes32","name":"","type":"bytes32"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"DEFAULT_ADMIN_ROLE","outputs":[{"internalType":"bytes32","name":"","type":"bytes32"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"REPORTER_ROLE","outputs":[{"internalType":"bytes32","name":"","type":"bytes32"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"VERSION","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"}],"name":"abort","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"arbiterPublicKey","outputs":[{"internalType":"bytes32","name":"","type":"bytes32"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"}],"name":"cancel","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"cancelBlocks","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"}],"name":"complete","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"}],"name":"deliver","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"},{"internalType":"string","name":"disputeUrl","type":"string"}],"name":"dispute","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"disputeBlocks","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"}],"name":"fail","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"getArbiter","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"}],"name":"getOrder","outputs":[{"internalType":"uint256","name":"sequence_","type":"uint256"},{"internalType":"uint8","name":"state","type":"uint8"},{"internalType":"address","name":"buyer","type":"address"},{"internalType":"bytes32","name":"buyerPublicKey","type":"bytes32"},{"internalType":"address","name":"reporter","type":"address"},{"internalType":"bytes32","name":"reporterPublicKey_","type":"bytes32"},{"internalType":"address","name":"arbiter","type":"address"},{"internalType":"bytes32","name":"arbiterPublicKey_","type":"bytes32"},{"internalType":"uint256","name":"price","type":"uint256"},{"internalType":"uint256","name":"shipping","type":"uint256"},{"internalType":"uint256","name":"lastModifiedBlock","type":"uint256"},{"internalType":"bytes","name":"metadata","type":"bytes"},{"internalType":"bytes","name":"shipmentBuyer","type":"bytes"},{"internalType":"bytes","name":"shipmentReporter","type":"bytes"},{"internalType":"bytes","name":"shipmentArbiter","type":"bytes"},{"internalType":"string","name":"disputeUrl","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getReporter","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"bytes32","name":"role","type":"bytes32"}],"name":"getRoleAdmin","outputs":[{"internalType":"bytes32","name":"","type":"bytes32"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"bytes32","name":"role","type":"bytes32"},{"internalType":"uint256","name":"index","type":"uint256"}],"name":"getRoleMember","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"bytes32","name":"role","type":"bytes32"}],"name":"getRoleMemberCount","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getSeller","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"bytes32","name":"role","type":"bytes32"},{"internalType":"address","name":"account","type":"address"}],"name":"grantRole","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"bytes32","name":"role","type":"bytes32"},{"internalType":"address","name":"account","type":"address"}],"name":"hasRole","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"bytes32","name":"role","type":"bytes32"},{"internalType":"address","name":"callerConfirmation","type":"address"}],"name":"renounceRole","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"reporterPublicKey","outputs":[{"internalType":"bytes32","name":"","type":"bytes32"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"},{"internalType":"uint256","name":"sellerDeposit","type":"uint256"},{"internalType":"uint256","name":"buyerDeposit","type":"uint256"}],"name":"resolve","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"bytes32","name":"role","type":"bytes32"},{"internalType":"address","name":"account","type":"address"}],"name":"revokeRole","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"},{"internalType":"bytes","name":"shipmentBuyer","type":"bytes"},{"internalType":"bytes","name":"shipmentReporter","type":"bytes"},{"internalType":"bytes","name":"shipmentArbiter","type":"bytes"}],"name":"ship","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"storeName","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"},{"internalType":"bytes32","name":"buyerPublicKey","type":"bytes32"},{"internalType":"address","name":"reporter","type":"address"},{"internalType":"address","name":"arbiter","type":"address"},{"internalType":"uint256","name":"price","type":"uint256"},{"internalType":"uint256","name":"shipping","type":"uint256"},{"internalType":"bytes","name":"metadata","type":"bytes"}],"name":"submit","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"bytes4","name":"interfaceId","type":"bytes4"}],"name":"supportsInterface","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"token","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"arbiter","type":"address"},{"internalType":"bytes32","name":"arbiterPublicKey_","type":"bytes32"}],"name":"updateArbiter","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"reporter","type":"address"},{"internalType":"bytes32","name":"reporterPublicKey_","type":"bytes32"}],"name":"updateReporter","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"string","name":"orderId","type":"string"}],"name":"withdraw","outputs":[],"stateMutability":"nonpayable","type":"function"}]';

	const RPC_URLS = [
		'arbitrum' => 'https://arb1.arbitrum.io/rpc',
		'arbitrumSepolia' => 'https://sepolia-rollup.arbitrum.io/rpc',
		'bsc' => 'https://rpc.ankr.com/bsc',
		'base' => 'https://mainnet.base.org',
		'avalanche' => 'https://api.avax.network/ext/bc/C/rpc',
		'polygon' => 'https://polygon-rpc.com',
	];

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
		$this->has_fields = false;
		$this->supports = ['products'];

		$this->method_title = esc_html__('Pacts', 'pacts');
		$this->method_description = esc_html__(
			'Allow your customers to pay with your pacts order processor.',
			'pacts'
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->addresses = PactsGateway::get_addresses($this->settings);

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
			'token' => [
				'title' => esc_html__('Token', 'pacts'),
				'label' => esc_html__('Token', 'pacts'),
				'type' => 'select',
				'required' => true,
				'options' => [
					'none' => esc_html__('Select One', 'pacts'),
					'usdc' => esc_html__('USDC', 'pacts'),
					'usdc' => esc_html__('USDC', 'pacts'),
					'usdt' => esc_html__('USDT', 'pacts'),
					'dai' => esc_html__('DAI', 'pacts'),
				]
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
			'avalancheAddress' => [
				'title' => esc_html__('Avalanche C-Chain Address', 'pacts'),
				'type' => 'text',
				'default' => null,
				'description' => esc_html__(
					'Pacts order processor address on Avalanche',
					'pacts'
				),
			],
			'baseAddress' => [
				'title' => esc_html__('Base Address', 'pacts'),
				'type' => 'text',
				'default' => null,
				'description' => esc_html__(
					'Pacts order processor address on Base',
					'pacts'
				),
			],
			'bscAddress' => [
				'title' => esc_html__('Binance Smart Chain Address', 'pacts'),
				'type' => 'text',
				'default' => null,
				'description' => esc_html__(
					'Pacts order processor address on Binance Smart Chain',
					'pacts'
				),
			],
			'polygonAddress' => [
				'title' => esc_html__('Polygon Address', 'pacts'),
				'type' => 'text',
				'default' => null,
				'description' => esc_html__(
					'Pacts order processor address on Polygon',
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
			],
		];
	}

	public function process_payment($orderId)
	{
		global $woocommerce;
		$order = wc_get_order($orderId);
		$this->verify_order_payment($order);
		$order->add_meta_data('chain', $_POST['chain']);
		$order->add_meta_data('hash', $_POST['hash']);
		$order->add_meta_data('pactsId', $_POST['id']);
		$order->save_meta_data();
		$order->payment_complete();
		$woocommerce->cart->empty_cart();
		$url = $this->get_return_url($order);
		return [
			'result' => 'success',
			'redirect' => $url
		];
	}

	public static function get_addresses($settings)
	{
		$chains = array_keys(self::RPC_URLS);
		$addresses = [];
		foreach ($chains as $chain) {
			$key = $chain . 'Address';
			if (array_key_exists($key, $settings)) {
				$address = $settings[$key];
				if (isset($address) && $address !== '') {
					$addresses[$chain] = $address;
				}
			}
		}
		return $addresses;
	}

	private function verify_order_payment($order)
	{
		$id = $_POST['id'];
		$parts = explode('-', $id);
		if (count($parts) !== 2) {
			throw new Exception('invalid order id ' . $id);
		}
		$id_str = strval($order->get_id());
		if ($parts[0] !== $id_str) {
			throw new Exception('order id mismatch ' . $id_str);
		}
		$chain = $_POST['chain'];
		$address_key = $chain . 'Address';
		if (!isset($this->settings[$address_key])) {
			throw new Exception($chain . ' chain has no address configured');
		}
		$address = $this->settings[$address_key];
		$contract = $this->get_contract_instance($chain, $address, self::PACTS_ABI);
		$order_data = null;
		$contract->call('getOrder', $id, function ($error, $data) use (&$order_data) {
			if ($error !== null) {
				$message = $error->getMessage();
				throw new Exception('rpc error occured ' . $message);
			}
			$order_data = $data;
		});
		if (!isset($order_data)) {
			throw new Exception('get order call failed');
		}
		$state = $order_data['state'];
		if (!$state->equals(new BigInteger(0))) {
			throw new Exception('order is in incorrect state: ' . $state);
		}
		$decimals = $this->get_token_decimals($chain, $contract);
		$price = $order_data['price'];
		$shipping = $order_data['shipping'];
		$contract_total = $price->add($shipping);
		$order_total_float = $order->get_total() * 100;
		$order_total_int = intval($order_total_float);
		$order_total_big = new BigInteger($order_total_int);
		$leftover = $decimals->subtract(new BigInteger(2));
		$multiplier = PactsGateway::pow(new BigInteger(10), $leftover);
		$normalized = $order_total_big->multiply($multiplier);
		if (!$contract_total->equals($normalized)) {
			throw new Exception('contract total does not equal order total');
		}
	}

	private function get_token_decimals($chain, $contract)
	{
		$token_data = null;
		$contract->call('token', function ($error, $data) use (&$token_data) {
			if ($error !== null) {
				$message = $error->getMessage();
				throw new Exception('rpc error occured ' . $message);
			}
			$token_data = $data;
		});
		if (!isset($token_data)) {
			throw new Exception('token data is not set');
		}
		$address = $token_data[0];
		$token_contract = $this->get_contract_instance($chain, $address, self::ERC20_ABI);
		$decimals_data = null;
		$token_contract->call('decimals', function ($error, $data) use (&$decimals_data) {
			if ($error !== null) {
				$message = $error->getMessage();
				throw new Exception('rpc error occured ' . $message);
			}
			$decimals_data = $data;
		});
		if (!isset($decimals_data)) {
			throw new Exception('decimals data is not set');
		}
		return $decimals_data[0];
	}

	private static function pow($base, $exponent)
	{
		$exponent_copy = $exponent->copy();
		$result = new BigInteger(1);
		while ($exponent_copy->compare(new BigInteger(0)) > 0) {
			$result = $result->multiply($base);
			$exponent_copy = $exponent_copy->subtract(new BigInteger(1));
		}
		return $result;
	}

	private function get_contract_instance($chain, $address, $abi)
	{
		if (!isset(self::RPC_URLS[$chain])) {
			throw new Exception($chain . ' chain is unsupported');
		}
		$url = self::RPC_URLS[$chain];
		$contract = new Contract($url, $abi);
		return $contract->at($address);
	}
}
