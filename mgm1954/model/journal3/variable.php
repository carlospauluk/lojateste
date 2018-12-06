<?php

use Journal3\Opencart\Model;
use Journal3\Utils\Arr;

class ModelJournal3Variable extends Model {

	public function all($filters = array()) {
		$filter_sql = "";

		$filter_sql .= "WHERE `variable_type` = '{$this->dbEscape(Arr::get($filters, 'type'))}'";

		$count = (int)$this->db->query("SELECT COUNT(*) AS total FROM `{$this->dbPrefix('journal3_variable')}` {$filter_sql}")->row['total'];

		if (in_array(Arr::get($filters, 'type', ''), array('breakpoint', 'font_size', 'value'))) {
			$order = 'length(variable_value), variable_value';
		} else {
			$order = 'variable_name';
		}

		$sql = "SELECT * FROM `{$this->dbPrefix('journal3_variable')}` {$filter_sql} ORDER BY ${order}";

		$query = $this->db->query($sql);

		$result = array();

		foreach ($query->rows as $row) {
			$result[] = array(
				'id'   => $row['variable_name'],
				'name' => $row['variable_name'],
			);
		}

		return array(
			'count' => $count,
			'items' => $result,
		);
	}

	public function get($id, $type) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_variable')}`
			WHERE 
				`variable_name` = '{$this->dbEscape($id)}'
				AND `variable_type` = '{$this->dbEscape($type)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Variable not found!');
		}

		return array(
			'name'  => $query->row['variable_name'],
			'value' => $this->decode($query->row['variable_value'], $query->row['serialized']),
		);
	}

	public function add($type, $data) {
		$name = Arr::get($data, 'name');
		$value = Arr::get($data, 'value');
		$serialized = is_scalar($value) ? 0 : 1;
		$value = $this->encode($value, $serialized);

		$query = $this->db->query("
			SELECT
				COUNT(*) AS total 
			FROM
				`{$this->dbPrefix('journal3_variable')}` 
			WHERE
				`variable_name` = '{$this->dbEscape($name)}'
				AND `variable_type` = '{$this->dbEscape($type)}'
		");

		if ($query->row['total'] > 0) {
			throw new Exception("Variable name already exists!");
		}

		$this->db->query("
			INSERT INTO `{$this->dbPrefix('journal3_variable')}` (
				`variable_name`,
				`variable_type`,
				`variable_value`,
				`serialized`
			) VALUES (
				'{$this->dbEscape($name)}',
				'{$this->dbEscape($type)}',
				'{$this->dbEscape($value)}',
				'{$this->dbEscapeInt($serialized)}'
			)
		");

		return $name;
	}

	public function edit($id, $type, $data) {
		$name = Arr::get($data, 'name');
		$value = Arr::get($data, 'value');
		$serialized = is_scalar($value) ? 0 : 1;
		$value = $this->encode($value, $serialized);

		$query = $this->db->query("
			SELECT 
				COUNT(*) AS total 
			FROM 
				`{$this->dbPrefix('journal3_variable')}` 
			WHERE
				`variable_name` != '{$this->dbEscape($id)}'
				AND `variable_type` = '{$this->dbEscape($type)}' 
				AND `variable_name` = '{$this->dbEscape($name)}'
		");

		if ($query->row['total'] > 0) {
			throw new Exception("Variable name already exists!");
		}

		$this->db->query("
			UPDATE `{$this->dbPrefix('journal3_variable')}` 
			SET 
				`variable_name` = '{$this->dbEscape($name)}',
				`variable_value` = '{$this->dbEscape($value)}',
				`serialized` = '{$this->dbEscapeInt($serialized)}'
			WHERE
				`variable_name` = '{$this->dbEscape($id)}'
				AND `variable_type` = '{$this->dbEscape($type)}'
		");

		return $this->get($name, $type);
	}

	public function copy($id, $type) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_variable')}`
			WHERE 
				`variable_name` = '{$this->dbEscape($id)}'
				AND `variable_type` = '{$this->dbEscape($type)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Variable not found!');
		}

		$type = $query->row['variable_type'];

		$data = array(
			'name'  => $query->row['variable_name'] . '_COPY',
			'value' => $this->decode($query->row['variable_value'], $query->row['serialized']),
		);

		return $this->add($type, $data);
	}

	public function remove($id, $type) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_variable')}`
			WHERE 
				`variable_name` = '{$this->dbEscape($id)}'
				AND `variable_type` = '{$this->dbEscape($type)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Variable not found!');
		}

		$this->db->query("
			DELETE FROM
				`{$this->dbPrefix('journal3_variable')}`
			WHERE 
				`variable_name` = '{$this->dbEscape($id)}'
				AND `variable_type` = '{$this->dbEscape($type)}'
		");
	}

}
