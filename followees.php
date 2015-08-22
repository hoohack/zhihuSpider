<?php
/**
 * @Author: hector
 * @Date:   2015-08-22 10:19:02
 * @Last Modified by:   hector
 * @Last Modified time: 2015-08-22 23:53:29
 */

require_once './spider/curl.php';
require_once './spider/user.php';
require_once './spider/pdo_mysql.php';
require_once './function.php';
$curl = new Curl();
$u_id = isset($_GET['u_id']) ? $_GET['u_id'] : '';
if (empty($u_id))
{
	echo "没有指定用户";
	exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$result = $curl->request('GET', 'http://www.zhihu.com/people/' . $u_id . '/followees');
$current_user = new User();
$user_info = $current_user->info($u_id);
if (empty($user_info))
{
	$t_user = getUserInfo($result);
	$t_user->add();
	$user_info = $current_user->info($u_id);
}

$followee_users = getUserList($page, $curl, $result, $u_id, 'followees', $user_info['followees_count']);

foreach ($followee_users as $f_user)
{
	$tmp_user = new User();
	$params = array(
		'u_id' => $u_id,
		'u_follow_id' => $f_user['username']
	);
	$tmp_user->addFollow($params);
}

foreach ($followee_users as $tmp_user)
{
	echo $tmp_user['username'] . "  " . $tmp_user['nickname'] . "<br>";
}

echo "共{$page}/" . ceil($user_info['followees_count']/20) . "页" . "<a href='?u_id={$u_id}&page=" . ($page + 1) . "' >下一页</a>";