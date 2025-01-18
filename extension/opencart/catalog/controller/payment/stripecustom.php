<?php

namespace Opencart\Catalog\Controller\Extension\Opencart\Payment;

class StripeCustom extends \Opencart\System\Engine\Controller
{
	/**
	 * @return void
	 */
	public function index(): void
	{
		$this->log->write('Generated URL: ' . $this->url->link('extension/opencart/payment/stripecustom/confirm', '', true));
		$this->load->language('extension/opencart/payment/stripe');
		// $this->log->write('StripeCustom: index method called.');
		$data['public_key'] = $this->config->get('payment_stripe_public_key');
		$data['language'] = $this->config->get('config_language');
		$data['action'] = $this->url->link('extension/opencart/payment/stripecustom/confirm', '', true);


		// Рендеринг шаблона
		$html = $this->load->view('extension/opencart/payment/stripe', $data);

		// Возвращаем результат
		$this->response->setOutput($html);
	}

	/**
	 * @return void
	 */
	public function confirm(): void
	{
		$this->load->language('extension/opencart/payment/stripe');

		$json = [];

		// Проверка наличия ID заказа
		if (!isset($this->session->data['order_id'])) {
			$this->log->write('Order ID not found in session.');
			$json['error'] = $this->language->get('error_order');
		} else {
			$this->log->write('Order ID found in session: ' . $this->session->data['order_id']);
		}

		if (!$json) {
			$order_id = $this->session->data['order_id'];
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->log->write('Loading model: extension/opencart/payment/stripecustom');
				$this->load->model('extension/opencart/payment/stripecustom');

				$amount = $order_info['total'];
				$currency = $order_info['currency_code'];

				// Получение paymentMethodId, отправленного с клиента
				if (isset($this->request->post['paymentMethodId'])) {
					$payment_method_id = $this->request->post['paymentMethodId'];
					$this->log->write('Received paymentMethodId: ' . $payment_method_id);
				} else {
					$json['error'] = 'PaymentMethodId not provided';
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));
					return;
				}

				// Процесс оплаты
				$payment = $this->model_extension_opencart_payment_stripecustom->processPayment(
					$order_id,
					$amount,
					$currency,
					$payment_method_id
				);

				if ($payment) {
					// Добавление комментария к заказу
					$comment  = $this->language->get('text_payment_success') . "\n\n";
					$comment .= 'Transaction ID: ' . $payment['id'];

					// Обновление статуса заказа
					$this->model_checkout_order->addHistory(
						$order_id,
						(int)$this->config->get('payment_stripe_order_status_id'),
						$comment,
						true
					);

					$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
				} else {
					$json['error'] = $this->language->get('error_payment_failed');
				}
			} else {
				$json['error'] = $this->language->get('error_order');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		$this->log->write('Confirm method response: ' . json_encode($json));
		header('Content-Type: application/json'); // Явно устанавливаем заголовок
		echo json_encode($json);
		exit;
	}
}
