<?php

use Journal3\Opencart\Model;
use Journal3\Utils\Arr;

class ModelJournal3Style extends Model {

	public function all($filters = array()) {
		$filter_sql = "";

		$filter_sql .= "WHERE `style_type` = '{$this->dbEscape(Arr::get($filters, 'type'))}'";

		$count = (int)$this->db->query("SELECT COUNT(*) AS total FROM `{$this->dbPrefix('journal3_style')}` {$filter_sql}")->row['total'];

		$sql = "SELECT * FROM `{$this->dbPrefix('journal3_style')}` {$filter_sql} ORDER BY style_name";

		$query = $this->db->query($sql);

		$result = array();

		foreach ($query->rows as $row) {
			$result[] = array(
				'id'   => $row['style_name'],
				'name' => $row['style_name'],
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
				`{$this->dbPrefix('journal3_style')}`
			WHERE 
				`style_name` = '{$this->dbEscape($id)}'
				AND `style_type` = '{$this->dbEscape($type)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Style not found!');
		}

		return array(
			'name'  => $query->row['style_name'],
			'value' => $this->decode($query->row['style_value'], $query->row['serialized']),
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
				`{$this->dbPrefix('journal3_style')}` 
			WHERE
				`style_name` = '{$this->dbEscape($name)}'
				AND `style_type` = '{$this->dbEscape($type)}'
		");

		if ($query->row['total'] > 0) {
			throw new Exception("Style name already exists!");
		}

		$this->db->query("
			INSERT INTO `{$this->dbPrefix('journal3_style')}` (
				`style_name`,
				`style_type`,
				`style_value`,
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
				`{$this->dbPrefix('journal3_style')}` 
			WHERE
				`style_name` != '{$this->dbEscape($id)}'
				AND `style_type` = '{$this->dbEscape($type)}' 
				AND `style_name` = '{$this->dbEscape($name)}'
		");

		if ($query->row['total'] > 0) {
			throw new Exception("Style name already exists!");
		}

		$this->db->query("
			UPDATE `{$this->dbPrefix('journal3_style')}` 
			SET 
				`style_name` = '{$this->dbEscape($name)}',
				`style_value` = '{$this->dbEscape($value)}',
				`serialized` = '{$this->dbEscapeInt($serialized)}'
			WHERE
				`style_name` = '{$this->dbEscape($id)}'
				AND `style_type` = '{$this->dbEscape($type)}'
		");

		return $this->get($name, $type);
	}

	public function copy($id, $type) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_style')}`
			WHERE 
				`style_name` = '{$this->dbEscape($id)}'
				AND `style_type` = '{$this->dbEscape($type)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Style not found!');
		}

		$type = $query->row['style_type'];

		$data = array(
			'name'  => $query->row['style_name'] . '_COPY',
			'value' => $this->decode($query->row['style_value'], $query->row['serialized']),
		);

		return $this->add($type, $data);
	}

	public function remove($id, $type) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_style')}`
			WHERE 
				`style_name` = '{$this->dbEscape($id)}'
				AND `style_type` = '{$this->dbEscape($type)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Style not found!');
		}

		$this->db->query("
			DELETE FROM
				`{$this->dbPrefix('journal3_style')}`
			WHERE 
				`style_name` = '{$this->dbEscape($id)}'
				AND `style_type` = '{$this->dbEscape($type)}'
		");
	}

}
