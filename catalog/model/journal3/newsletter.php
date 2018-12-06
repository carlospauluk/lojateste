<?php

use Journal3\Opencart\Model;

class ModelJournal3Newsletter extends Model {

	public function isSubscribed($email) {
		$sql = "
			SELECT COUNT(*) AS total
			FROM `{$this->dbPrefix('journal3_newsletter')}`
			WHERE email = '{$this->dbEscape($email)}'
		";

		return $this->dbQuery($sql)->row['total'] > 0;
	}

	public function subscribe($email, $name = '') {
		$sql = "
			INSERT INTO `{$this->dbPrefix('journal3_newsletter')}` (
				name,
				email,
				store_id
			) VALUES (
				'{$this->dbEscape($name)}',
				'{$this->dbEscape($email)}',
				'{$this->dbEscapeInt($this->config->get('config_store_id'))}'
			)
		";

		return $this->dbQuery($sql);
	}

	public function unsubscribe($email) {
		$this->dbQuery("DELETE FROM `{$this->dbPrefix('journal3_newsletter')}` WHERE email = '{$this->dbEscape($email)}'");
	}

}
