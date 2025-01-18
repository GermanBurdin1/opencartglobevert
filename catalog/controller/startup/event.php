<?php
namespace Opencart\Catalog\Controller\Startup;
/**
 * Class Event
 *
 * @package Opencart\Catalog\Controller\Startup
 */
class Event extends \Opencart\System\Engine\Controller {
	/**
	 * @return void
	 */
	public function index(): void {
		// Add events from the DB
		$this->load->model('setting/event');

		$results = $this->model_setting_event->getEvents();

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'extension/opencart/payment/stripecustom/confirm') {
            $this->log->write('Custom route detected: ' . $this->request->get['route']);

            // Подключаем контроллер
            require_once DIR_EXTENSION . 'opencart/catalog/controller/payment/stripecustom.php';

            // Проверяем существование класса
            if (class_exists('\Opencart\Catalog\Controller\Extension\Opencart\Payment\StripeCustom')) {
                $controller = new \Opencart\Catalog\Controller\Extension\Opencart\Payment\StripeCustom($this->registry);

                // Проверяем существование метода
                if (method_exists($controller, 'confirm')) {
                    $controller->confirm();
                    exit; // Завершаем выполнение после успешного вызова метода
                } else {
                    $this->log->write('Error: confirm method does not exist in StripeCustom controller.');
                }
            } else {
                $this->log->write('Error: StripeCustom controller class not found.');
            }

            // Если что-то пошло не так, возвращаем JSON с ошибкой
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(['error' => 'Invalid route']));
            exit; // Завершаем выполнение, чтобы не отображался HTML
        }


		foreach ($results as $result) {
			$part = explode('/', $result['trigger']);

			if ($part[0] == 'catalog') {
				array_shift($part);

				$this->event->register(implode('/', $part), new \Opencart\System\Engine\Action($result['action']), $result['sort_order']);
			}

			if ($part[0] == 'system') {
				$this->event->register($result['trigger'], new \Opencart\System\Engine\Action($result['action']), $result['sort_order']);
			}
		}
	}
}
