<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Payment;

class EmailPayment extends \Opencart\System\Engine\Controller {
    /**
     * Загружает настройки метода оплаты в админке
     */
    public function index(): void {
        $this->load->language('extension/opencart/payment/email_payment');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_email_payment', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension/payment', 'user_token=' . $this->session->data['user_token']));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('extension/opencart/payment/email_payment', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('extension/extension/payment', 'user_token=' . $this->session->data['user_token']);

        if (isset($this->request->post['payment_email_payment_status'])) {
            $data['payment_email_payment_status'] = $this->request->post['payment_email_payment_status'];
        } else {
            $data['payment_email_payment_status'] = $this->config->get('payment_email_payment_status');
        }

        if (isset($this->request->post['payment_email_payment_sort_order'])) {
            $data['payment_email_payment_sort_order'] = $this->request->post['payment_email_payment_sort_order'];
        } else {
            $data['payment_email_payment_sort_order'] = $this->config->get('payment_email_payment_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/opencart/payment/email_payment', $data));
    }

    /**
     * Проверяет права на изменение настроек
     */
    protected function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/opencart/payment/email_payment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
