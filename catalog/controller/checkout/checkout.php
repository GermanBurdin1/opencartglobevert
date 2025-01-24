<?php
namespace Opencart\Catalog\Controller\Checkout;
/**
 * Class Checkout
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class Checkout extends \Opencart\System\Engine\Controller {
	/**
	 * @return void
	 */
	public function index(): void {
		if (!isset($this->session->data['order_id'])) {
			$this->load->model('checkout/order');
			$order_data = [
                'invoice_prefix' => $this->config->get('config_invoice_prefix') ?? 'INV-', // Префикс для инвойса
                'store_id' => $this->config->get('config_store_id') ?? 0, // ID магазина
                'store_name' => $this->config->get('config_name') ?? 'Default Store', // Имя магазина
                'store_url' => $this->config->get('config_url') ?? HTTP_SERVER, // URL магазина
                'customer_id' => $this->customer->getId() ?? 0, // ID клиента
                'customer_group_id' => $this->customer->getGroupId() ?? 1, // Группа клиента
                'firstname' => $this->customer->getFirstName() ?? 'Guest', // Имя клиента
                'lastname' => $this->customer->getLastName() ?? 'User', // Фамилия клиента
                'email' => $this->customer->getEmail() ?? 'guest@example.com', // Email
                'telephone' => $this->customer->getTelephone() ?? '', // Телефон
                'payment_address_id' => $this->session->data['payment_address_id'] ?? 0, // ID платежного адреса
                'payment_firstname' => $this->session->data['payment_firstname'] ?? 'John',
                'payment_lastname' => $this->session->data['payment_lastname'] ?? 'Doe',
                'payment_company' => $this->session->data['payment_company'] ?? '',
                'payment_address_1' => $this->session->data['payment_address_1'] ?? 'Address 1',
                'payment_address_2' => $this->session->data['payment_address_2'] ?? '',
                'payment_city' => $this->session->data['payment_city'] ?? 'City',
                'payment_postcode' => $this->session->data['payment_postcode'] ?? '12345',
                'payment_country' => $this->session->data['payment_country'] ?? 'Country',
                'payment_country_id' => $this->session->data['payment_country_id'] ?? 0,
                'payment_zone' => $this->session->data['payment_zone'] ?? 'Zone',
                'payment_zone_id' => $this->session->data['payment_zone_id'] ?? 0,
                'payment_address_format' => $this->session->data['payment_address_format'] ?? '',
                'payment_method' => $this->session->data['payment_method'] ?? 'StripeCustom',
                'shipping_address_id' => $this->session->data['shipping_address_id'] ?? 0,
                'shipping_firstname' => $this->session->data['shipping_firstname'] ?? 'John',
                'shipping_lastname' => $this->session->data['shipping_lastname'] ?? 'Doe',
                'shipping_company' => $this->session->data['shipping_company'] ?? '',
                'shipping_address_1' => $this->session->data['shipping_address_1'] ?? 'Address 1',
                'shipping_address_2' => $this->session->data['shipping_address_2'] ?? '',
                'shipping_city' => $this->session->data['shipping_city'] ?? 'City',
                'shipping_postcode' => $this->session->data['shipping_postcode'] ?? '12345',
                'shipping_country' => $this->session->data['shipping_country'] ?? 'Country',
                'shipping_country_id' => $this->session->data['shipping_country_id'] ?? 0,
                'shipping_zone' => $this->session->data['shipping_zone'] ?? 'Zone',
                'shipping_zone_id' => $this->session->data['shipping_zone_id'] ?? 0,
                'shipping_address_format' => $this->session->data['shipping_address_format'] ?? '',
                'shipping_method' => $this->session->data['shipping_method'] ?? 'Standard Shipping',
                'comment' => $this->session->data['comment'] ?? '',
                'total' => $this->cart->getTotal(),
                'affiliate_id' => $this->session->data['affiliate_id'] ?? 0,
                'commission' => $this->session->data['commission'] ?? 0.0,
                'marketing_id' => $this->session->data['marketing_id'] ?? 0,
                'tracking' => $this->session->data['tracking'] ?? '',
                'language_id' => $this->config->get('config_language_id') ?? 1,
                'currency_code' => $this->session->data['currency'] ?? 'USD',
                'ip' => $this->request->server['REMOTE_ADDR'] ?? '127.0.0.1',
                'forwarded_ip' => $this->request->server['HTTP_X_FORWARDED_FOR'] ?? '',
                'user_agent' => $this->request->server['HTTP_USER_AGENT'] ?? '',
                'accept_language' => $this->request->server['HTTP_ACCEPT_LANGUAGE'] ?? 'en-US'
            ];

			$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
			$this->log->write('Order ID created: ' . $this->session->data['order_id']);
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart', 'language=' . $this->config->get('config_language')));
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			if (!$product['minimum']) {
				$this->response->redirect($this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true));

				break;
			}
		}

		$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'))
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language'))
		];

		if (!$this->customer->isLogged()) {
			$data['register'] = $this->load->controller('checkout/register');
		} else {
			$data['register'] = '';
		}

		// if ($this->customer->isLogged() && $this->config->get('config_checkout_payment_address')) {
		// 	$data['payment_address'] = $this->load->controller('checkout/payment_address');
		// } else {
		// 	$data['payment_address'] = '';
		// }

		$data['payment_address'] = $this->load->controller('checkout/payment_address');

		// if ($this->customer->isLogged() && $this->cart->hasShipping()) {
		// 	$data['shipping_address'] = $this->load->controller('checkout/shipping_address');
		// }  else {
		// 	$data['shipping_address'] = '';
		// }

		if ($this->cart->hasShipping()) {
			$data['shipping_method'] = $this->load->controller('checkout/shipping_method');
		}  else {
			$data['shipping_method'] = '';
		}

		$data['payment_method'] = $this->load->controller('checkout/payment_method');
		$data['confirm'] = $this->load->controller('checkout/confirm');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/checkout', $data));
	}
}
