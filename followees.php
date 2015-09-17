<?php
/**
 * @Author: hector
 * @Date:   2015-08-22 10:19:02
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-09-16 18:45:19
 */

require_once './spider/curl.php';
require_once './spider/user.php';
require_once './spider/pdo_mysql.php';
require_once './function.php';
$u_id = isset($_GET['u_id']) ? $_GET['u_id'] : '';
if (empty($u_id))
{
	echo "没有指定用户";
	exit;
}

$params = array(
	'where' => array(
		'u_id' => $u_id
	)
);

$current_user = array();
$result = Curl::request('GET', 'http://www.zhihu.com/people/' . $u_id . '/followees');
echo $result;exit;
if (!User::existed($params, 'user'))
{
	$current_user = getUserInfo($result);
	User::add($current_user);
}

$user_info = User::info($u_id);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$user_followees_count = User::getFollowCount($u_id);

if ($user_info['followees_count'] != $user_followees_count)
{
	$followee_users = getUserList($result, $u_id, 'followees', $user_info['followees_count']);
	User::addFollowList($followee_users);
}

$followee_users = User::getFollowUserList($u_id, $page);

foreach ($followee_users as $tmp_user)
{
	if (isset($tmp_user['u_id']))
	{
		echo "<a href='index.php?u_id=" . $tmp_user['u_id'] . "'>" . $tmp_user['u_id'] . "</a>" . " ";
	}
	if (isset($tmp_user['u_follow_id']))
	{
		echo "<a href='index.php?u_id=" . $tmp_user['u_follow_id'] . "'>" . $tmp_user['u_follow_id'] . "</a>" . " ";
	}
	if (isset($tmp_user['u_name']))
	{
		echo $tmp_user['u_name'];
	}
	if (isset($tmp_user['u_follow_name']))
	{
		echo $tmp_user['u_follow_name'];
	}
	echo "<br>";
}

if ($page != 1)
{
	echo "<a href='?u_id={$u_id}&page=" . ($page - 1) . "'>上一页</a>";
}

echo "共{$page}/" . ceil($user_info['followees_count']/20) . "页";

if ($page != ceil($user_info['followees_count']/20))
{
	echo "<a href='?u_id={$u_id}&page=" . ($page + 1) . "' >下一页</a>";
}