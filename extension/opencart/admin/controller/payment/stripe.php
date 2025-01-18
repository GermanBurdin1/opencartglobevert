<?php
namespace Opencart\Admin\Controller\Extension\Payment;

class Stripe extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language('extension/payment/stripe');

        $data['payment_stripe_status'] = $this->config->get('payment_stripe_status');
        $data['payment_stripe_secret_key'] = $this->config->get('payment_stripe_secret_key');
        $data['payment_stripe_public_key'] = $this->config->get('payment_stripe_public_key');

        $data['save'] = $this->url->link('extension/payment/stripe.save', 'user_token=' . $this->session->data['user_token'], true);

        $this->response->setOutput($this->load->view('extension/payment/stripe', $data));
    }

    public function save(): void {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting('payment_stripe', $this->request->post);

        $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
    }
}
