<?php

use Journal3\Opencart\Controller;

class ControllerJournal3Layout extends Controller {

	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->model('design/layout');
		$this->load->model('journal3/layout');
		$this->load->language('error/permission');
	}

	public function all() {
		try {
			$this->renderJson(self::SUCCESS, $this->model_journal3_layout->all());
		} catch (Exception $e) {
			$this->renderJson(self::ERROR, $e->getMessage());
		}
	}

	public function get() {
		try {
			$id = $this->input(self::GET, 'id');

			$layout = $this->model_design_layout->getLayout($id);

			if (!$layout) {
				throw new Exception('Layout not found!');
			}

			$layout_data = $this->model_journal3_layout->get($id);
			$layout_data['layout_name'] = $layout['name'];

			$this->renderJson(self::SUCCESS, $layout_data);
		} catch (Exception $e) {
			$this->renderJson(self::ERROR, $e->getMessage());
		}
	}

	public function edit() {
		try {
			if (!$this->user->hasPermission('modify', 'journal3/layout')) {
				throw new Exception($this->language->get('text_permission'));
			}

			$id = $this->input(self::GET, 'id');
			$data = $this->input(self::POST, 'data');

			$this->journal3->cache->delete();

			$this->renderJson(self::SUCCESS, $this->model_journal3_layout->edit($id, $data));
		} catch (Exception $e) {
			$this->renderJson(self::ERROR, $e->getMessage());
		}
	}

}
