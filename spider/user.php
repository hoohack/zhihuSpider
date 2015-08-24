<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-21 15:25:27
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-24 18:23:18
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

	public static function existed($params, $table)
	{
		$result = PDO_MySQL::count($table, $params);
		return $result;
	}

	public static function add($params)
	{
		$existed_params = array(
			'where' => array(
				'u_id' => $params['u_id']
			)
		);
		if (self::existed($existed_params, self::TABLE_NAME))
		{
			return;
		}
		$params['id'] = '';
		return PDO_MySQL::insert(self::TABLE_NAME, $params);
	}

	public static function addMulti($data)
	{
		$fields = array('u_id', 'u_name', 'address', 'img_url', 'business', 'gender', 'education', 'major', 'description',
			'followees_count', 'followers_count', 'special_count', 'follow_topic_count', 'pv_count', 'approval_count', 'thank_count',
			'ask_count', 'answer_count', 'started_count', 'public_edit_count', 'article_count');
		return PDO_MySQL::insertAll(self::TABLE_NAME, $fields, $data);
	}

	public static function info($u_id)
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

	public static function addFollowList($user_follow_list)
	{
		$fields = array('id', 'u_id', 'u_follow_id', 'u_follow_name');
		return PDO_MySQL::insertAll(self::FOLLOW_TABLE_NAME, $fields, $user_follow_list);
	}

	public static function getFollowUserList($u_id, $page)
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

	public static function getFollowCount($u_id)
	{
		$params = array(
			'where' => array(
				'u_id' => $u_id
			)
		);

		return PDO_MySQL::count(self::FOLLOW_TABLE_NAME, $params);
	}
}