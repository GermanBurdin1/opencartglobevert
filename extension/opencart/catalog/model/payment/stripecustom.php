<?php

namespace Opencart\Catalog\Model\Extension\Opencart\Payment;

use Stripe\Stripe;

class StripeCustom extends \Opencart\System\Engine\Model
{
	public function getMethods(array $address = []): array
	{
		$this->log->write('StripeCustom: getMethods called'); // Лог вызова метода

		$status = false;

		// Проверяем статус метода оплаты StripeCustom
		if ($this->config->get('payment_stripecustom_status')) {
			// Проверяем наличие подписок
			if ($this->cart->hasSubscription()) {
				$this->log->write('StripeCustom: cart has subscription, disabling StripeCustom');
				$status = false;
			} elseif (!$this->config->get('config_checkout_payment_address')) {
				$status = true; // Если не требуется адрес оплаты
			} elseif (!$this->config->get('payment_stripecustom_geo_zone_id')) {
				$status = true; // Если нет привязки к геозоне
			} else {
				// Проверяем, подходит ли адрес под геозону
				$query = $this->db->query(
					"SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" .
						(int)$this->config->get('payment_stripecustom_geo_zone_id') .
						"' AND country_id = '" . (int)$address['country_id'] .
						"' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')"
				);

				$status = $query->num_rows > 0;
				$this->log->write('StripeCustom: Geo Zone Check - ' . ($status ? 'Enabled' : 'Disabled'));
			}
		} else {
			$this->log->write('StripeCustom: payment_stripecustom_status is disabled');
		}

		$method_data = [];

		if ($status) {
			// Данные для отображения StripeCustom как метода оплаты
			$option_data['custom'] = [
				'code' => 'stripecustom.custom',
				'name' => 'Stripe',
			];

			$method_data = [
				'code'       => 'stripecustom',
				'name'       => 'Stripe',
				'option'     => $option_data,
				'sort_order' => $this->config->get('payment_stripecustom_sort_order'),
			];

			$this->log->write('StripeCustom: Method added to payment options: ' . json_encode($method_data));
		} else {
			$this->log->write('StripeCustom: Method not added due to status conditions');
		}

		return $method_data;
	}

	public function processPayment($order_id, $amount, $currency, $payment_method_id)
	{
		try {
			// Инициализация Stripe
			Stripe::setApiKey('sk_test_51Qb3cWGaUr31i20XKP9abzRDyZN7MvrCAddZ4SjKObnXIzA7qb8oMRTxW0jHhjjdhUhUA3xlIqZw4y5wkNez57dO009PoSYysU');

			// Создание PaymentIntent
			$payment_intent = \Stripe\PaymentIntent::create([
				'amount' => $amount * 100, // Сумма в центах
				'currency' => $currency,
				'description' => 'Order #' . $order_id,
				'payment_method' => $payment_method_id,
				'confirmation_method' => 'manual', // Подтверждение вручную
				'confirm' => true, // Подтверждение сразу
				'return_url' => $this->url->link('checkout/success', '', true),
			]);

			$this->log->write('StripeCustom: PaymentIntent успешно создан для Order ID ' . $order_id);

			return $payment_intent;
		} catch (\Stripe\Exception\ApiErrorException $e) {
			// Логирование ошибки
			$this->log->write('StripeCustom Payment Error: ' . $e->getMessage());
			return false;
		}
	}
}
