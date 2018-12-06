<?php

use Journal3\Opencart\Model;

class ModelJournal3Newsletter extends Model {

	public function all($filters = array()) {
		$filter_sql = "";

		$count = (int)$this->db->query("SELECT COUNT(*) AS total FROM `{$this->dbPrefix('journal3_newsletter')}` {$filter_sql}")->row['total'];

		$sql = "SELECT * FROM `{$this->dbPrefix('journal3_newsletter')}` {$filter_sql}";

		$query = $this->db->query($sql);

		$result = array();

		foreach ($query->rows as $row) {
			$result[] = array(
				'id'       => $row['newsletter_id'],
				'name'     => $row['name'],
				'email'    => $row['email'],
				'store_id' => $row['store_id'],
			);
		}

		return array(
			'count' => $count,
			'items' => $result,
		);
	}

	public function unsubscribe($email) {
		$this->dbQuery("DELETE FROM `{$this->dbPrefix('journal3_newsletter')}` WHERE email = '{$this->dbEscape($email)}'");
	}

}
