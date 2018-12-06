<?php

use Journal3\Opencart\Model;
use Journal3\Utils\Arr;
use Journal3\Utils\Str;

class ModelJournal3BlogComment extends Model {

	public function all($filters = array()) {
		$filter_sql = "";

		if (($filter = Arr::get($filters, 'name')) !== null) {
			$filter_sql .= " AND `module_name` LIKE '%{$this->dbEscape($filter)}%'";
		}

		$sql = "
			FROM
				`{$this->dbPrefix('journal3_blog_comments')}` {$filter_sql} bc
			LEFT JOIN
				`{$this->dbPrefix('journal3_blog_post_description')}` bpd ON bc.post_id = bpd.post_id
			WHERE
				bpd.language_id = '{$this->dbEscapeInt($this->config->get('config_language_id'))}'			 			
		";

		$count = (int)$this->db->query("SELECT COUNT(*) AS total {$sql}")->row['total'];

		$result = array();

		if ($count) {
			$query = $this->db->query("
				SELECT
					bc.comment_id,
                    bc.name as author,
                    bpd.name as post_name,
                    bc.parent_id as parent_id,
                    bc.status as status
				{$sql}
			");

			foreach ($query->rows as $row) {
				$result[] = array(
					'id'   => $row['comment_id'],
					'name' => $row['author'] ? $row['author'] . ' @ ' . $row['post_name'] : $row['post_name'],
				);
			}
		}

		return array(
			'count' => $count,
			'items' => $result,
		);
	}

	/**
	 * @throws Exception
	 */
	public function get($id) {
		$query = $this->db->query("
            SELECT
                *
            FROM 
            	`{$this->dbPrefix('journal3_blog_comments')}`
            WHERE 
            	`comment_id` = '{$this->dbEscapeInt($id)}'
        ");

		if ($query->num_rows === 0) {
			throw new Exception('Comment not found!');
		}


		$result = array(
			'name'    => $query->row['name'],
			'email'   => $query->row['email'],
			'website' => $query->row['website'],
			'comment' => $query->row['comment'],
			'status'  => Str::toBool($query->row['status']),
		);

		return $result;
	}

	public function edit($id, $data) {
		$this->db->query("
            UPDATE `{$this->dbPrefix('journal3_blog_comments')}`
            SET
            	name = '{$this->dbEscape(Arr::get($data, 'name', ''))}',
                email = '{$this->dbEscape(Arr::get($data, 'email', ''))}',
                website = '{$this->dbEscape(Arr::get($data, 'website', ''))}',
                comment = '{$this->dbEscape(Arr::get($data, 'comment', ''))}',
                status = '{$this->dbEscape(Str::fromBool(Arr::get($data, 'status')))}'
            WHERE
            	comment_id = '{$this->dbEscapeInt($id)}'
        ");

		return null;
	}

	public function remove($id) {
		$this->db->query("DELETE FROM `{$this->dbPrefix('journal3_blog_comments')}` WHERE comment_id = {$this->dbEscapeInt($id)}");
	}

}
