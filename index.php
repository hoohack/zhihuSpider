<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-10 17:41:33
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-24 16:53:51
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

$result = $curl->request('GET', 'http://www.zhihu.com/people/' . $u_id . '/about');

$current_user = getUserInfo($result);
echo User::add($current_user);
$user_info = User::info($current_user['u_id']);

echo "知乎用户数据" . "<br><br><br>";

echo "用户ID:{$user_info['u_id']} " . "<br>";
echo "用户名:{$user_info['u_name']}" . "<br>";
echo "用户头像:<img src='{$user_info['img_url']}' />" . "<br>";
echo "居住地:{$user_info['address']}" . "<br>";
echo "所在行业:{$user_info['business']}" . "<br>";
echo "性别:{$user_info['gender']}" . "<br>";
echo "毕业院校:{$user_info['education']}" . "<br>";
echo "专业:{$user_info['major']}" . "<br>";
echo "个人简介:{$user_info['description']}" . "<br>";
echo "关注了:{$user_info['followees_count']} 人" . "<br>";
echo "关注者:{$user_info['followers_count']} 人" . "<br>";
echo "关注了 {$user_info['special_count']} 个专栏" . "<br>";
echo "关注了 {$user_info['follow_topic_count']} 个话题" . "<br>";
echo "主页被 {$user_info['pv_count']} 人浏览" . "<br>";
echo "获得赞同:{$user_info['approval_count']}" . "<br>";
echo "获得感谢:{$user_info['thank_count']}" . "<br>";
echo "提问:{$user_info['ask_count']}" . "<br>";
echo "回答:{$user_info['answer_count']}" . "<br>";
echo "专栏文章:{$user_info['article_count']}" . "<br>";
echo "收藏:{$user_info['started_count']}" . "<br>";
echo "公共编辑:{$user_info['public_edit_count']}" . "<br>";

echo "<a href='followees.php?u_id={$u_id}'>查看用户关注了哪些人</a>";

?>