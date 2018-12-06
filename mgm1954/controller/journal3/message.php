<?php

use Journal3\Opencart\Controller;

class ControllerJournal3Message extends Controller {

	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->model('journal3/message');
		$this->load->language('error/permission');
	}

	public function all() {
		try {
			$this->renderJson(self::SUCCESS, $this->model_journal3_message->all());
		} catch (Exception $e) {
			$this->renderJson(self::ERROR, $e->getMessage());
		}
	}

	public function get() {
		try {
			$id = $this->input(self::GET, 'id');

			$this->renderJson(self::SUCCESS, $this->model_journal3_message->get($id));
		} catch (Exception $e) {
			$this->renderJson(self::ERROR, $e->getMessage());
		}
	}

	public function remove() {
		try {
			if (!$this->user->hasPermission('modify', 'journal3/message')) {
				throw new Exception($this->language->get('text_permission'));
			}

			$id = $this->input(self::GET, 'id');

			$this->renderJson(self::SUCCESS, $this->model_journal3_message->remove($id));
		} catch (Exception $e) {
			$this->renderJson(self::ERROR, $e->getMessage());
		}
	}

}
