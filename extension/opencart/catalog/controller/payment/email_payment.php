<?php
namespace Opencart\Catalog\Controller\Extension\Opencart\Payment;

class EmailPayment extends \Opencart\System\Engine\Controller {
    /**
     * Показ страницы метода оплаты
     */
    public function index(): string {
        $this->load->language('extension/opencart/payment/email_payment');

        $data['text_instruction'] = $this->language->get('text_instruction');
        $data['text_description'] = $this->language->get('text_description');
        $data['text_payment'] = $this->language->get('text_payment');

        return $this->load->view('extension/opencart/payment/email_payment', $data);
    }

    /**
     * Подтверждение заказа
     */
    public function confirm(): void {
        $this->load->language('extension/opencart/payment/email_payment');

        $json = [];

        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');

            // Добавляем статус заказа и сообщение
            $this->model_checkout_order->addHistory(
                $this->session->data['order_id'],
                $this->config->get('payment_email_payment_order_status_id'),
                $this->language->get('text_order_comment')
            );

            $json['success'] = 'Оплата по Email подтверждена!';
        } else {
            $json['error'] = 'Ошибка при подтверждении оплаты!';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
