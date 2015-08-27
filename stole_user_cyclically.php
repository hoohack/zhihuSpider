<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-26 11:38:18
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-26 11:43:46
 */
require_once './spider/user.php';
require_once './function.php';
require_once './spider/curl.php';
require_once './spider/pdo_mysql.php';
$user_cookie = '_za=a41e1b8b-517a-4fea-9465-88e8c80ba17e;q_cl=3198dbc291fa40d7b717f9a4dd5ec90e|1439792872000|1439792872000;_xsrf=981ffd949fbc70e73cc4bb2559243ac8;cap_id="YmViMDk0YTdjMjUyNDc4MjhmOWU5MDkyMTg3NWRlNGY=|1439792872|7eb10c44aead609ab6e63f3eb2b5856149076942";z_c0="QUFEQTRZbzZBQUFYQUFBQVlRSlZUZjhMLVZYNnBhUDBYYzJIOFJtUGs2aFlianFRU3NRR3hRPT0=|1439792895|4f033f6e2f99a39b152a59c32496dfc954cbe6fd";__utma=51854390.888606616.1439792875.1439792875.1439891906.2;__utmb=51854390.2.10.1439891906;__utmc=51854390;__utmt=1;__utmv=51854390.100-1|2=registration_date=20141017=1^3=entry_date=20141017=1__utmz=51854390.1439891906.2.2.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided)';

$request_queue = array('mora-hu');

while (1)
{
	echo "begin get user info...\n";
	if (empty($request_queue))
	{
		break;
	}
	$u_id = array_shift($request_queue);
	$current_user = array();
	$result = Curl::request('GET', 'http://www.zhihu.com/people/' . $u_id . '/followees');
	$params = array(
		'where' => array(
			'u_id' => $u_id
		)
	);

	if (!User::existed($params, 'user'))
	{
		$current_user = getUserInfo($result);
		User::add($current_user);
	}

	$user_info = User::info($u_id);
	$user_followees_count = User::getFollowCount($u_id);

	if ($user_info['followees_count'] != $user_followees_count)
	{
		$followee_users = getUserList($result, $u_id, 'followees', $user_info['followees_count']);
		foreach ($followee_users as $user)
		{
			array_push($request_queue, $user[2]);
		}
		User::addFollowList($followee_users);
	}

	usleep(100);
	echo "add new " . $user_info['followees_count'] . " users\n";
}
