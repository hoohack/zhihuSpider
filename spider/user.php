<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-21 15:25:27
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2016-04-19 18:31:16
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
		if (empty($result))
		{
			echo "--------user $u_id not existed--------\n";
		}
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [addFollowList 增加用户关系]
	 * @param [type] $user_follow_list [description]
	 */
	public static function addFollowList($user_follow_list)
	{
		echo "--------start adding user follow relation--------\n";
		$tmp_pdo = PDO_MySQL::getInstance();
		$fields = array('id', 'u_id', 'u_name', 'u_follow_id', 'u_follow_name');
		$result = $tmp_pdo->insertAll(self::FOLLOW_TABLE_NAME, $fields, $user_follow_list, 1);
		$tmp_pdo = null;
		echo "--------add user follow relation done--------\n";
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
	 * [getFolloweeCount 返回用户关注者数量]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
	public static function getFollowerCount($u_id)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$params = array(
			'where' => array(
				'u_follow_id' => $u_id
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

	public static function update($user_data, $u_id)
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$where = array(
			'where' => array(
				'u_id' => $u_id
			)
		);
		$result = $tmp_pdo->update(self::TABLE_NAME, $where, $user_data);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [addressCountList 根据地区统计]
	 * @return [type] [description]
	 */
	public static function addressCountList()
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$conditions = array(
			'fields' => 'address, count(*) as address_count',
			'sort' => array('address_count' => 0),
			'group_by' => 'address',
			'limit' => 11
		);
		$result = $tmp_pdo->getAll(self::TABLE_NAME, $conditions);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [majorCountList 根据专业统计数量]
	 * @return [type] [description]
	 */
	public static function majorCountList()
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$conditions = array(
			'fields' => 'major, count(*) as major_count',
			'sort' => array('major_count' => 0),
			'group_by' => 'major',
			'limit' => 11
		);
		$result = $tmp_pdo->getAll(self::TABLE_NAME, $conditions);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [businessCountList 根据行业统计数量]
	 * @return [type] [description]
	 */
	public static function businessCountList()
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$conditions = array(
			'fields' => 'business, count(*) as business_count',
			'sort' => array('business_count' => 0),
			'group_by' => 'business',
			'limit' => 11
		);
		$result = $tmp_pdo->getAll(self::TABLE_NAME, $conditions);
		$tmp_pdo = null;
		return $result;
	}
}