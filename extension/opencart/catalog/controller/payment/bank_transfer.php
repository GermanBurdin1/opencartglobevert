<?php

namespace Opencart\Catalog\Controller\Extension\Opencart\Payment;

/**
 * Class BankTransfer
 *
 * @package
 */
class BankTransfer extends \Opencart\System\Engine\Controller
{
	/**
	 * @return string
	 */
	public function index(): string
	{
		$this->load->language('extension/opencart/payment/bank_transfer');

		$data['bank'] = nl2br($this->config->get('payment_bank_transfer_bank_' . $this->config->get('config_language_id')));

		$data['language'] = $this->config->get('config_language');

		return $this->load->view('extension/opencart/payment/bank_transfer', $data);
	}

	/**
	 * @return void
	 */
	public function confirm(): void
	{
		$this->log->write('Entered confirm() method in StripeCustom controller.');

		$this->load->language('extension/opencart/payment/bank_transfer');

		$json = [];

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method']['code'] != 'bank_transfer.bank_transfer') {
			$json['error'] = $this->language->get('error_payment_method');
		}

		if (!$json) {
			$comment  = $this->language->get('text_instruction') . "\n\n";
			$comment .= $this->config->get('payment_bank_transfer_bank_' . $this->config->get('config_language_id')) . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->load->model('checkout/order');

			$order_id = $this->session->data['order_id'];
			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_bank_transfer_order_status_id'), $comment, true);

			$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);

			$order_info = $this->model_checkout_order->getOrder($order_id);
			$this->log->write('Order Info: ' . json_encode($order_info));
			if ($order_info) {
				// Préparation du contenu de l'email
				$subject = 'Nouvelle commande n°' . $order_id;
				$message = "Détails de la commande :\n";
				$message .= "Numéro de commande : " . $order_info['order_id'] . "\n";
				$message .= "Nom du client : " . $order_info['firstname'] . " " . $order_info['lastname'] . "\n";
				$message .= "Email : " . $order_info['email'] . "\n";
				$message .= "Téléphone : " . $order_info['telephone'] . "\n";
				$message .= "Adresse : " . $order_info['payment_address_1'] . ", " . $order_info['payment_city'] . "\n";
				$message .= "Montant total : " . $this->currency->format($order_info['total'], $order_info['currency_code']) . "\n\n";
				$message .= "Méthode de paiement choisie : " . $this->language->get('heading_title') . "\n";

				// Envoi de l'email
				$mail = new \Opencart\System\Library\Mail('smtp', [
					'parameter'     => '', // Пусто, если не используется.
					'smtp_hostname' => 'smtp.gmail.com', // SMTP-хост.
					'smtp_username' => 'globervert@gmail.com', // Логин SMTP.
					'smtp_password' => 'ваш_пароль', // Пароль SMTP (или пароль приложения).
					'smtp_port'     => 587, // Порт для TLS.
					'smtp_timeout'  => 10 // Таймаут соединения.
				]);
				$mail->setTo('globervert@gmail.com');
				$this->log->write('Отправитель: ' . $this->config->get('config_email'));
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setText($message);
				$this->log->write('Email will be sent to: ' . 'globervert@gmail.com');
				$mail->send();
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
