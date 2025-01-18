<?php
namespace Opencart\Catalog\Model\Checkout;
/**
 * Class PaymentMethod
 *
 * @package Opencart\Catalog\Model\Checkout
 */
class PaymentMethod extends \Opencart\System\Engine\Controller {
	/**
	 * @param array $payment_address
	 *
	 * @return array
	 */
	// public function getMethods(array $payment_address = []): array {
	// 	$method_data = [];

	// 	$this->load->model('setting/extension');

	// 	$results = $this->model_setting_extension->getExtensionsByType('payment');
	// 	$this->log->write('результаты: ' . json_encode($results));
	// 	$this->log->write('Payment method added: ' . json_encode($method_data));

	// 	foreach ($results as $result) {
	// 		$this->log->write('Processing payment method: ' . $result['code']);
	// 		if ($this->config->get('payment_' . $result['code'] . '_status')) {
	// 			$this->load->model('extension/' . $result['extension'] . '/payment/' . $result['code']);

	// 			$payment_methods = $this->{'model_extension_' . $result['extension'] . '_payment_' . $result['code']}->getMethods($payment_address);

	// 			if ($payment_methods) {
	// 				$method_data[$result['code']] = $payment_methods;
	// 				$this->log->write('Added method to $method_data: ' . $result['code'] . ' => ' . json_encode($payment_methods));
	// 			}
	// 		}
	// 	}

	// 	$sort_order = [];

	// 	foreach ($method_data as $key => $value) {
	// 		$sort_order[$key] = $value['sort_order'];
	// 	}

	// 	array_multisort($sort_order, SORT_ASC, $method_data);

	// 	return $method_data;
	// }

	public function getMethods(array $payment_address = []): array {
		$method_data = [];

		// Прямой SQL-запрос для получения всех активных методов оплаты
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = 'payment'");
		// $this->log->write('Результаты SQL-запроса для всех методов оплаты: ' . json_encode($query->rows));

		foreach ($query->rows as $result) {
			// $this->log->write('Обработка метода оплаты: ' . $result['code']);

			// Проверяем, включён ли метод оплаты
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				// $this->log->write('Метод оплаты ' . $result['code'] . ' включён.');

				// Проверяем, доступен ли файл модели для данного метода
				$model_path = 'extension/' . $result['extension'] . '/payment/' . $result['code'];
				if (file_exists(DIR_APPLICATION . 'model/' . $model_path . '.php')) {
					$this->load->model($model_path);

					$payment_methods = $this->{'model_extension_' . $result['extension'] . '_payment_' . $result['code']}->getMethods($payment_address);

					if ($payment_methods) {
						$method_data[$result['code']] = $payment_methods;
						$this->log->write('Добавлен метод оплаты: ' . $result['code'] . ' => ' . json_encode($payment_methods));
					} else {
						$this->log->write('Метод оплаты ' . $result['code'] . ' не возвращает данные.');
					}
				} else {
					$this->log->write('Файл модели отсутствует для метода оплаты: ' . $result['code']);
				}
			} else {
				$this->log->write('Метод оплаты ' . $result['code'] . ' отключён.');
			}
		}

		// Добавляем stripecustom напрямую, если он отсутствует в результате запроса
		if ($this->config->get('payment_stripecustom_status')) {
			// $this->log->write('Добавление метода оплаты stripecustom напрямую.');

			$option_data['stripecustom'] = [
				'code' => 'stripecustom',
				'name' => 'Stripe Payment',
			];

			$method_data['stripecustom'] = [
				'code'       => 'stripecustom',
				'name'       => 'Stripe Payment',
				'option'     => $option_data,
				'sort_order' => $this->config->get('payment_stripecustom_sort_order'),
			];

			// $this->log->write('Метод оплаты stripecustom добавлен напрямую: ' . json_encode($method_data['stripecustom']));
		}

		// Сортируем методы оплаты по `sort_order`
		$sort_order = [];
		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $method_data);

		// $this->log->write('Итоговые методы оплаты: ' . json_encode($method_data));

		return $method_data;
	}

}
