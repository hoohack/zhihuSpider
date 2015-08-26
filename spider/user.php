<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-21 15:25:27
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-25 10:22:46
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
		$result = PDO_MySQL::count($table, $params);
		return $result;
	}

	/**
	 * [add 新增一个用户]
	 * @param [type] $params [description]
	 */
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

	/**
	 * [addMulti 增加多个用户]
	 * @param [type] $data [description]
	 */
	public static function addMulti($data)
	{
		$fields = array('u_id', 'u_name', 'address', 'img_url', 'business', 'gender', 'education', 'major', 'description',
			'followees_count', 'followers_count', 'special_count', 'follow_topic_count', 'pv_count', 'approval_count', 'thank_count',
			'ask_count', 'answer_count', 'started_count', 'public_edit_count', 'article_count');
		return PDO_MySQL::insertAll(self::TABLE_NAME, $fields, $data);
	}

	/**
	 * [info 返回用户信息]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
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

	/**
	 * [addFollowList 增加用户关系]
	 * @param [type] $user_follow_list [description]
	 */
	public static function addFollowList($user_follow_list)
	{
		$fields = array('id', 'u_id', 'u_follow_id', 'u_follow_name');
		return PDO_MySQL::insertAll(self::FOLLOW_TABLE_NAME, $fields, $user_follow_list);
	}

	/**
	 * [getFollowUserList 返回用户关系列表]
	 * @param  [type] $u_id [description]
	 * @param  [type] $page [description]
	 * @return [type]       [description]
	 */
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

	/**
	 * [getFollowCount 返回用户关注人数量]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
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