<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-21 15:25:27
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-24 11:48:04
 */
class User {
	private $u_id;

	private $u_name;

	private $address;

	private $img_url;

	private $business;

	private $gender;

	private $education;

	private $major;

	private $description;

	private $followees_count;

	private $followers_count;

	private $special_count;

	private $follow_topic_count;

	private $pv_count;

	private $approval_count;

	private $thank_count;

	private $ask_count;

	private $answer_count;

	private $started_count;

	private $public_edit_count;

	private $article_count;

	const TABLE_NAME = 'user';

	const FOLLOW_TABLE_NAME = 'user_follow';

	public function __set($property_name, $value)
	{
		$this->$property_name = $value;
	}

	public function __get($property_name)
	{
		return isset($this->$property_name) ? $this->$property_name : NULL;
	}

	public function existed($params, $table)
	{
		$result = PDO_MySQL::count($table, $params);
		return $result;
	}

	public function add()
	{
		$existed_params = array(
			'where' => array(
				'u_id' => $this->u_id
			)
		);
		if ($this->existed($existed_params, self::TABLE_NAME))
		{
			return;
		}
		$params = array(
			'id' => '',
			'u_id' => $this->u_id,
			'u_name' => $this->u_name,
			'address' => $this->address,
			'img_url' => $this->img_url,
			'business' => $this->business,
			'gender' => $this->gender,
			'education' => $this->education,
			'major' => $this->major,
			'description' => $this->description,
			'followees_count' => $this->followees_count,
			'followers_count' => $this->followers_count,
			'special_count' => $this->special_count,
			'follow_topic_count' => $this->follow_topic_count,
			'pv_count' => $this->pv_count,
			'approval_count' => $this->approval_count,
			'thank_count' => $this->thank_count,
			'ask_count' => $this->ask_count,
			'answer_count' => $this->answer_count,
			'started_count' => $this->started_count,
			'public_edit_count' => $this->public_edit_count,
			'article_count' => $this->article_count
		);
		return PDO_MySQL::insert(self::TABLE_NAME, $params);
	}

	public function info($u_id)
	{
		$params = array(
			'where' => array(
				'u_id' => $u_id
			)
		);

		$result = PDO_MySQL::getOneRow(self::TABLE_NAME, $params);
		return $result;
	}

	public function addFollow($user_follow)
	{
		$existed_params = array(
			'where' => array(
				'u_id' => $user_follow['u_id'],
				'u_follow_id' => $user_follow['u_follow_id']
			)
		);

		if ($this->existed($existed_params, self::FOLLOW_TABLE_NAME))
		{
			return;
		}

		$params = array(
			'id' => '',
			'u_id' => $user_follow['u_id'],
			'u_follow_id' => $user_follow['u_follow_id'],
			'u_follow_name' => $user_follow['u_follow_name']
		);

		return PDO_MySQL::insert(self::FOLLOW_TABLE_NAME, $params);
	}

	public function getFollowUserList($u_id, $page)
	{
		$params = array(
			'where' => array(
				'u_id' => $u_id,
			),
			'limit' => 20
		);
		if ($page != 1)
		{
			$params['offset'] = ($page - 1) * 20;
		}

		return PDO_MySQL::getAll(self::FOLLOW_TABLE_NAME, $params);
	}

	public function getFollowCount()
	{
		$params = array(
			'where' => array(
				'u_id' => $this->u_id
			)
		);

		return PDO_MySQL::count(self::FOLLOW_TABLE_NAME, $params);
	}
}