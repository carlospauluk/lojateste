<?php

use Journal3\Opencart\ModuleController;
use Journal3\Utils\Request;

class ControllerJournal3Newsletter extends ModuleController {

	/**
	 * @param \Journal3\Options\Parser $parser
	 * @param $index
	 * @return array
	 */
	protected function parseGeneralSettings($parser, $index) {
		return array(
			'action'     => $this->model_journal3_links->url('journal3/newsletter/newsletter', 'module_id=' . $this->module_id, true),
			'agree_data' => $this->model_journal3_links->getInformation($parser->getSetting('agree')),
		);
	}

	/**
	 * @param \Journal3\Options\Parser $parser
	 * @param $index
	 * @return array
	 */
	protected function parseItemSettings($parser, $index) {
		return array();
	}

	/**
	 * @param \Journal3\Options\Parser $parser
	 * @param $index
	 * @return array
	 */
	protected function parseSubitemSettings($parser, $index) {
		return array();
	}

	public function newsletter() {
		try {
			$module_id = (int)$this->input('GET', 'module_id');
			$email = $this->input('POST', 'email', '');
			$agree = $this->input('POST', 'agree', '');

			if (!$this->index(array('module_id' => $module_id, 'module_type' => 'newsletter',))) {
				throw new \Exception('Invalid module id!');
			}

			$agree_data = $this->model_journal3_links->getInformation($this->settings['agree']);

			if ($agree_data && !$agree) {
				throw new \Exception($agree_data['error']);
			}

			if ((utf8_strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$this->load->language('information/contact');

				throw new \Exception($this->language->get('error_email'));
			}

			$this->load->model('journal3/newsletter');
			$this->load->model('journal3/image');

			$email_data = array(
				'title'      => $this->config->get('config_name'),
				'logo'       => $this->model_journal3_image->resize($this->config->get('config_logo')),
				'store_name' => $this->config->get('config_name'),
				'store_url'  => $this->config->get(Request::isHttps() ? 'config_ssl' : 'config_url'),
			);

			if ($this->model_journal3_newsletter->isSubscribed($email)) {
				$unsubscribe = (bool)$this->input('GET', 'unsubscribe', '');

				if ($unsubscribe) {
					$this->model_journal3_newsletter->unsubscribe($email);

					$data['message'] = $this->settings['unsubscribedMessage'];

					if ($this->settings['unsubscribedEmail']) {
						$email_data['message'] = $this->settings['unsubscribedEmailMessage'];

						$this->load->controller('journal3/mail/send', array(
							'to'      => $email,
							'subject' => $this->config->get('config_name'),
							'message' => $this->load->view('journal3/module/newsletter_unsubscribed_email', $email_data),
						));
					}

					if ($this->settings['adminAlerts']) {
						$email_data['message'] = sprintf('Customer unsubscribed: %s.', $email);

						$this->load->controller('journal3/mail/send', array(
							'to'      => $this->config->get('config_email'),
							'subject' => $this->config->get('config_name'),
							'message' => $this->load->view('journal3/module/newsletter_admin_email', $email_data),
						));
					}
				} else {
					$data['message'] = $this->settings['unsubscribeMessage'];
					$data['unsubscribe'] = true;
				}
			} else {
				$this->model_journal3_newsletter->subscribe($email);

				$data['message'] = $this->settings['subscribedMessage'];

				if ($this->settings['subscribedEmail']) {
					$email_data['message'] = $this->settings['subscribedEmailMessage'];

					$this->load->controller('journal3/mail/send', array(
						'to'      => $email,
						'subject' => $this->config->get('config_name'),
						'message' => $this->load->view('journal3/module/newsletter_subscribed_email', $email_data),
					));
				}

				if ($this->settings['adminAlerts']) {
					$email_data['message'] = sprintf('New customer subscribed: %s.', $email);

					$this->load->controller('journal3/mail/send', array(
						'to'      => $this->config->get('config_email'),
						'subject' => $this->config->get('config_name'),
						'message' => $this->load->view('journal3/module/newsletter_admin_email', $email_data),
					));
				}
			}

			$this->renderJson(self::SUCCESS, $data);
		} catch (Exception $e) {
			$this->renderJson(self::ERROR, $e->getMessage());
		}
	}

}
