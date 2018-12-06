<?php

use Journal3\Utils\Arr;

class ModelJournal3Layout extends \Journal3\Opencart\Model {

	public function all() {
		$layouts = array_map(function ($layout) {
			return array('id' => $layout['layout_id'], 'name' => $layout['name']);
		}, $this->model_design_layout->getLayouts());

		return array(
			'count' => count($layouts),
			'items' => $layouts,
		);
	}

	public function get($id) {
		$query = $this->db->query("
			SELECT
				layout_id,
				layout_data
			FROM
				`{$this->dbPrefix('journal3_layout')}`
			WHERE 
				`layout_id` = '{$this->dbEscapeInt($id)}'
				OR `layout_id` = -1
			ORDER BY
				`layout_id` DESC
		");

		if ($query->num_rows === 0) {
			return array();
		}

		$result = array();

		foreach ($query->rows as $row) {
			if ($row['layout_id'] > 0) {
				$data = $this->decode($row['layout_data'], true);
			} else {
				$data = array(
					'positions' => array(
						'global' => $this->decode($row['layout_data'], true),
					),
				);
			}

			$result = Arr::merge($result, $data);
		}

		return $result;
	}

	public function edit($id, $data) {
		$global = $data['positions']['global'];

		unset($data['positions']['global']);

		$this->db->query("
			INSERT INTO `{$this->dbPrefix('journal3_layout')}`
                (`layout_id`, `layout_data`)
            VALUES
                ('{$this->dbEscapeInt($id)}', '{$this->dbEscape($this->encode($data, true))}')
            ON DUPLICATE KEY UPDATE
                `layout_data` = '{$this->dbEscape($this->encode($data, true))}'
		");

		$this->db->query("
			INSERT INTO `{$this->dbPrefix('journal3_layout')}`
                (`layout_id`, `layout_data`)
            VALUES
                ('-1', '{$this->dbEscape($this->encode($global, true))}')
            ON DUPLICATE KEY UPDATE
                `layout_data` = '{$this->dbEscape($this->encode($global, true))}'
		");

		return $this->get($id);
	}

}
