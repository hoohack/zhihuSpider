<?php
/**
 * @Author: hector
 * @Date:   2015-08-22 10:19:02
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-24 10:29:28
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
$current_user = getUserInfo($result);
$user_info = $current_user->info($u_id);
if (empty($user_info))
{
	$current_user->add();
	$user_info = $current_user->info($u_id);
}

$user_followees_count = $current_user->getFollowCount();

if ($current_user->followees_count == $user_followees_count)
{
	$followee_users = $current_user->getFollowUserList($current_user->u_id, $page);
}
else
{
	$followee_users = getUserList($page, $curl, $result, $u_id, 'followees', $user_info['followees_count']);
	foreach ($followee_users as $f_user)
	{
		$tmp_user = new User();
		$params = array(
			'u_id' => $u_id,
			'u_follow_id' => $f_user['u_id']
		);
		$tmp_user->addFollow($params);
	}
}

foreach ($followee_users as $tmp_user)
{
	if (isset($tmp_user['u_follow_id']))
	{
		echo $tmp_user['u_follow_id'] . " ";
	}
	if (isset($tmp_user['u_name']))
	{
		echo $tmp_user['u_name'];
	}
	echo "<br>";
}

echo "共{$page}/" . ceil($user_info['followees_count']/20) . "页" . "<a href='?u_id={$u_id}&page=" . ($page + 1) . "' >下一页</a>";