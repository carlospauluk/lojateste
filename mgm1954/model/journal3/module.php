<?php

use Journal3\Opencart\Model;
use Journal3\Utils\Arr;

class ModelJournal3Module extends Model {

	public function all($filters = array()) {
		$filter_sql = "";

		$filter_sql .= "WHERE `module_type` = '{$this->dbEscape(Arr::get($filters, 'type'))}'";

		if (($filter = Arr::get($filters, 'name')) !== null) {
			$filter_sql .= " AND `module_name` LIKE '%{$this->dbEscape($filter)}%'";
		}

		$count = (int)$this->db->query("SELECT COUNT(*) AS total FROM `{$this->dbPrefix('journal3_module')}` {$filter_sql}")->row['total'];

		$sql = "SELECT * FROM `{$this->dbPrefix('journal3_module')}` {$filter_sql} ORDER BY module_name";

		$query = $this->db->query($sql);

		$result = array();

		foreach ($query->rows as $row) {
			$result[] = array(
				'id'   => $row['module_id'],
				'name' => $row['module_name'],
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
				`{$this->dbPrefix('journal3_module')}`
			WHERE 
				`module_id` = '{$this->dbEscapeInt($id)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Module not found!');
		}

		return $this->decode($query->row['module_data'], true);
	}

	public function add($type, $data) {
		$name = Arr::get($data, 'general.name');

		$query = $this->db->query("
			SELECT
				COUNT(*) AS total 
			FROM
				`{$this->dbPrefix('journal3_module')}` 
			WHERE
				`module_name` = '{$this->dbEscape($name)}'
				AND `module_type` = '{$this->dbEscape($type)}'
		");

		if ($query->row['total'] > 0) {
			throw new Exception("Module name already exists!");
		}

		$this->db->query("
			INSERT INTO `{$this->dbPrefix('journal3_module')}` (
				`module_name`,
				`module_type`,
				`module_data`
			) VALUES (
				'{$this->dbEscape($name)}',
				'{$this->dbEscape($type)}',
				'{$this->dbEscape($this->encode($data, true))}'
			)
		");

		return (string)$this->db->getLastId();
	}

	public function edit($id, $type, $data) {
		$name = Arr::get($data, 'general.name');

		$query = $this->db->query("
			SELECT 
				COUNT(*) AS total 
			FROM 
				`{$this->dbPrefix('journal3_module')}` 
			WHERE 
				`module_name` = '{$this->dbEscape($name)}'
				AND `module_type` = '{$this->dbEscape($type)}'
				AND `module_id` != '{$this->dbEscapeInt($id)}'
		");

		if ($query->row['total'] > 0) {
			throw new Exception("Module name already exists!");
		}

		$this->db->query("
			UPDATE `{$this->dbPrefix('journal3_module')}` 
			SET 
				`module_name` = '{$this->dbEscape($name)}',
				`module_data` = '{$this->dbEscape($this->encode($data, true))}'
			WHERE `module_id` = '{$this->dbEscapeInt($id)}'
		");

		return $this->get($id);
	}

	public function copy($id) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_module')}`
			WHERE 
				`module_id` = '{$this->dbEscapeInt($id)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Module not found!');
		}

		$type = $query->row['module_type'];

		$data = $this->decode($query->row['module_data'], true);
		$data['general']['name'] = $data['general']['name'] . ' Copy';

		return $this->add($type, $data);
	}

	public function remove($id) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('journal3_module')}`
			WHERE 
				`module_id` = '{$this->dbEscapeInt($id)}'
		");

		if ($query->num_rows === 0) {
			throw new Exception('Module not found!');
		}

		$this->db->query("
			DELETE FROM
				`{$this->dbPrefix('journal3_module')}`
			WHERE 
				`module_id` = '{$this->dbEscapeInt($id)}'
		");
	}

	public function explodeAttributeValues($separator) {
		$this->db->query("TRUNCATE TABLE `{$this->dbPrefix('journal3_product_attribute')}`");

		$attribute_values = $this->db->query("
			SELECT
				*
			FROM
				`{$this->dbPrefix('product_attribute')}`
		")->rows;

		foreach ($attribute_values as $attribute_value) {
			$values = explode($separator, $attribute_value['text']);

			foreach ($values as $value) {
				$value = trim($value);

				if ($value) {
					$this->db->query("
						INSERT INTO `{$this->dbPrefix('journal3_product_attribute')}` (
							`product_id`,
							`attribute_id`,
							`language_id`,
							`text`,
							`sort_order`
						) VALUES (
							'{$attribute_value['product_id']}',
							'{$attribute_value['attribute_id']}',
							'{$attribute_value['language_id']}',
							'{$value}',
							'0'
						)
					");
				}
			}
		}

	}

}
