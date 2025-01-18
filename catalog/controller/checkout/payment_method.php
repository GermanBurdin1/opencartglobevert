<?php

namespace Opencart\Catalog\Controller\Checkout;

/**
 * Class PaymentMethod
 *
 * @package Opencart\Catalog\Controller\Checkout
 */
class PaymentMethod extends \Opencart\System\Engine\Controller
{
	/**
	 * @return string
	 */
	public function index(): string
	{
		$this->load->language('checkout/payment_method');

		if (isset($this->session->data['payment_method'])) {
			$data['payment_method'] = $this->session->data['payment_method']['name'];
			$data['code'] = $this->session->data['payment_method']['code'];
		} else {
			$data['payment_method'] = '';
			$data['code'] = '';
		}

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}

		$this->load->model('catalog/information');

		$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

		if ($information_info) {
			$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information.info', 'language=' . $this->config->get('config_language') . '&information_id=' . $this->config->get('config_checkout_id')), $information_info['title']);
		} else {
			$data['text_agree'] = '';
		}

		$data['language'] = $this->config->get('config_language');

		return $this->load->view('checkout/payment_method', $data);
	}

	/**
	 * @return void
	 */
	public function getMethods(): void
	{
		$this->load->language('checkout/payment_method');

		$json = [];

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			if (!$product['minimum']) {
				$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);

				break;
			}
		}

		if (!$json) {
			// Validate if customer session data is set
			if (!isset($this->session->data['customer'])) {
				$json['error'] = $this->language->get('error_customer');
			}

			if ($this->config->get('config_checkout_payment_address') && !isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
			}

			// Validate shipping
			if ($this->cart->hasShipping()) {
				// Validate shipping address
				if (!isset($this->session->data['shipping_address']['address_id'])) {
					$json['error'] = $this->language->get('error_shipping_address');
				}

				// Validate shipping method
				if (!isset($this->session->data['shipping_method'])) {
					$json['error'] = $this->language->get('error_shipping_method');
				}
			}
		}

		if (!$json) {
			$payment_address = [];

			if ($this->config->get('config_checkout_payment_address') && isset($this->session->data['payment_address'])) {
				$payment_address = $this->session->data['payment_address'];
			} elseif ($this->config->get('config_checkout_shipping_address') && isset($this->session->data['shipping_address']['address_id'])) {
				$payment_address = $this->session->data['shipping_address'];
			}

			// Payment methods
			$this->load->model('checkout/payment_method');

			// $method_data['email_payment'] = [
			// 	'code'       => 'email_payment',
			// 	'title'      => 'Оплата по Email',
			// 	'terms'      => '',
			// 	'sort_order' => $this->config->get('payment_email_payment_sort_order') ?: 0
			// ];

			$payment_methods = $this->model_checkout_payment_method->getMethods($payment_address);

			// $this->log->write('Payment methods before processing: ' . json_encode($payment_methods));

			if ($payment_methods) {
				// $payment_methods['email_payment'] = $method_data['email_payment'];
				$json['payment_methods'] = $this->session->data['payment_methods'] = $payment_methods;
			} else {
				$json['error'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact', 'language=' . $this->config->get('config_language')));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @return void
	 */
	public function save(): void
	{
		// $this->log->write('МЕТОД save ВЫЗЫВАЕТСЯ!!!!');
		// $this->log->write('POST data: ' . json_encode($this->request->post));
		$this->load->language('checkout/payment_method');

		$json = [];

		// Step 1: Validate cart has products and stock
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
			$this->log->write('Redirect: No products or insufficient stock.');
		}

		// Step 2: Validate minimum quantity requirements
		$products = $this->cart->getProducts();
		foreach ($products as $product) {
			if (!$product['minimum']) {
				$json['redirect'] = $this->url->link('checkout/cart', 'language=' . $this->config->get('config_language'), true);
				$this->log->write('Redirect: Minimum quantity requirement not met.');
				break;
			}
		}

		// Step 3: Check other validations if no errors yet
		if (!$json) {
			// $this->log->write('Step 3: Validating address and payment method');

			// Validate has payment address if required
			if ($this->config->get('config_checkout_payment_address') && !isset($this->session->data['payment_address'])) {
				$json['error'] = $this->language->get('error_payment_address');
				$this->log->write('Error: Payment address not set.');
			}

			// Validate shipping
			if ($this->cart->hasShipping()) {
				if (!isset($this->session->data['shipping_address']['address_id'])) {
					$json['error'] = $this->language->get('error_shipping_address');
					$this->log->write('Error: Shipping address not set.');
				}

				if (!isset($this->session->data['shipping_method'])) {
					$json['error'] = $this->language->get('error_shipping_method');
					$this->log->write('Error: Shipping method not set.');
				}
			}

			// Validate payment methods
			// $this->log->write('Available payment methods: ' . json_encode($this->session->data['payment_methods']));

			if (isset($this->request->post['payment_method']) && isset($this->session->data['payment_methods'])) {
				$selected_method = $this->request->post['payment_method'];
				// $this->log->write('Selected payment method: ' . $selected_method);

				// Проверяем, существует ли метод оплаты в session
				if (!isset($this->session->data['payment_methods'][$selected_method])) {
					$json['error'] = $this->language->get('error_payment_method');
					$this->log->write("Error: Payment method '{$selected_method}' missing in session.");
				} else {
					// Успешно сохраняем выбранный метод оплаты
					$this->session->data['payment_method'] = $this->session->data['payment_methods'][$selected_method]['option'][$selected_method];
					// $this->log->write('Payment method saved in session: ' . json_encode($this->session->data['payment_method']));
					$json['success'] = $this->language->get('text_success');
				}
			} else {
				$json['error'] = $this->language->get('error_payment_method');
				$this->log->write('Error: Payment method not set or missing in session.');
			}
		}

		// Step 4: Save payment method if no errors
		if (!$json) {
			$payment = explode('.', $this->request->post['payment_method']);
			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$payment[0]]['option'][$payment[1]];

			// $this->log->write('Selected payment method saved: ' . json_encode($this->session->data['payment_method']));
			$json['success'] = $this->language->get('text_success');
		}

		// $this->log->write('Final response: ' . json_encode($json));

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	/**
	 * @return void
	 */
	public function comment(): void
	{
		$this->load->language('checkout/payment_method');

		$json = [];

		if (isset($this->session->data['order_id'])) {
			$order_id = (int)$this->session->data['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$json) {
			$this->session->data['comment'] = $this->request->post['comment'];

			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->model_checkout_order->editComment($order_id, $this->request->post['comment']);
			}

			$json['success'] = $this->language->get('text_comment');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @return void
	 */
	public function agree(): void
	{
		$this->load->language('checkout/payment_method');

		$json = [];

		if (isset($this->request->post['agree'])) {
			$this->session->data['agree'] = $this->request->post['agree'];
		} else {
			unset($this->session->data['agree']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
