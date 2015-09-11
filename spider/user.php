<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-21 15:25:27
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-09-11 11:01:56
 */
class User {

	const TABLE_NAME = 'user';

	const FOLLOW_TABLE_NAME = 'user_follow';

	/**
	 * [existed 判断用户是否已存在]
	 * @param  [type] $params [description]
	 * @param  [type] $table  [description]
	 * @return [type]         [description]
	 */
	public static function existed($params, $table)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$result = $tmp_pdo->count($table, $params);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [add 新增一个用户]
	 * @param [type] $params [description]
	 */
	public static function add($params)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
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
		$result = $tmp_pdo->insert(self::TABLE_NAME, $params);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [addMulti 增加多个用户]
	 * @param [type] $data [description]
	 */
	public static function addMulti($data)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$fields = array('u_id', 'u_name', 'address', 'img_url', 'business', 'gender', 'education', 'major', 'description',
			'followees_count', 'followers_count', 'special_count', 'follow_topic_count', 'pv_count', 'approval_count', 'thank_count',
			'ask_count', 'answer_count', 'started_count', 'public_edit_count', 'article_count', 'duplicate_count');
		$result = $tmp_pdo->insertAll(self::TABLE_NAME, $fields, $data, 1);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [info 返回用户信息]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
	public static function info($u_id)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$params = array(
			'where' => array(
				'u_id' => $u_id
			)
		);

		$result = $tmp_pdo->getOneRow(self::TABLE_NAME, $params);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [addFollowList 增加用户关系]
	 * @param [type] $user_follow_list [description]
	 */
	public static function addFollowList($user_follow_list)
	{
		echo "--------start adding follower--------\n";
		$tmp_pdo = PDO_MySQL::getInstance();
		$fields = array('id', 'u_id', 'u_follow_id', 'u_follow_name');
		$result = $tmp_pdo->insertAll(self::FOLLOW_TABLE_NAME, $fields, $user_follow_list);
		$tmp_pdo = null;
		echo "--------add follower done--------\n";
		return $result;
	}

	/**
	 * [getFollowUserList 返回用户关系列表]
	 * @param  [type] $u_id [description]
	 * @param  [type] $page [description]
	 * @return [type]       [description]
	 */
	public static function getFollowUserList($u_id, $page)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
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
		$result = $tmp_pdo->getAll(self::FOLLOW_TABLE_NAME, $params);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [getFolloweeCount 返回用户关注人数量]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
	public static function getFolloweeCount($u_id)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$params = array(
			'where' => array(
				'u_id' => $u_id
			)
		);
		$result = $tmp_pdo->count(self::FOLLOW_TABLE_NAME, $params);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [totalCount 返回用户总数量]
	 * @return [type] [description]
	 */
	public static function totalCount()
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$result = $tmp_pdo->count(self::TABLE_NAME, array());
		$tmp_pdo = null;
		return $result;
	}
}