<?php
/**
 * @Author: hector
 * @Date:   2015-08-22 10:19:02
 * @Last Modified by:   hector
 * @Last Modified time: 2015-08-22 10:31:20
 */

require_once './phpSpider/curl.php';
require_once './phpSpider/user.php';
require_once './phpSpider/pdo_mysql.php';
require_once './function.php';

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$result = $curl->request('GET', 'http://www.zhihu.com/people/' . $u_id . '/followees');
$followee_users = getUserList($page, $curl, $result, $u_id, 'followees', $current_user->followees_count);
// echo count($followee_users);exit;
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

echo "共{$page}/" . ceil($current_user->followees_count/20) . "页" . "<a href='?page=" . ($page + 1) . "' >下一页</a>";