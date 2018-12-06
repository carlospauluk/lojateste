<?php

use Journal3\Opencart\Model;

class ModelJournal3Message extends Model {

	public function all($filters = array()) {
		$filter_sql = "";

		$count = (int)$this->db->query("SELECT COUNT(*) AS total FROM `{$this->dbPrefix('journal3_message')}` {$filter_sql}")->row['total'];

		$sql = "SELECT * FROM `{$this->dbPrefix('journal3_message')}` {$filter_sql}";

		$query = $this->db->query($sql);

		$result = array();

		foreach ($query->rows as $row) {
			$result[] = array(
				'id'     => $row['message_id'],
				'name'   => $row['name'],
				'email'  => $row['email'],
				'fields' => $this->decode($row['fields'], true),
			);
		}

		return array(
			'count' => $count,
			'items' => $result,
		);
	}

	public function get($id) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_message')}`
			WHERE 
				`message_id` = '{$this->dbEscapeInt($id)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Message not found!');
		}

		$data = $query->row;
		$data['fields'] = array();

		foreach ($this->decode($query->row['fields'], true) as $field) {
			if (is_array($field['value'])) {
				$field['value'] = implode(', ', $field['value']);
			}
			$data['fields'][] = $field;
		}

		return $data;
	}

	public function remove($id) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_message')}`
			WHERE 
				`message_id` = '{$this->dbEscapeInt($id)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Message not found!');
		}

		$this->db->query("
			DELETE FROM
				`{$this->dbPrefix('journal3_message')}`
			WHERE 
				`message_id` = '{$this->dbEscapeInt($id)}'
		");
	}

}
