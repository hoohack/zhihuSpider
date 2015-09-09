<?php
/**
 * @Author: hector
 * @Date:   2015-08-22 10:19:54
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-09-09 14:36:34
 */
/**
 * [getUserInfo 获取用户]
 * @param  [type] $result [description]
 * @return [type]         [description]
 */
function getUserInfo($result)
{
	$user = array();

	preg_match('#<a class="name" href="/people/(.*?)">(.*?)</a>#', $result, $out);
	$user['u_id'] = empty($out[1]) ? '' : $out[1];
	$user['u_name'] = empty($out[2]) ? '' : $out[2];

	preg_match('#<span class="location item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['address'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<img class="avatar avatar-l" alt=".*?" src="(.*?)" srcset=".*?" />#', $result, $out);
	$img_url_tmp = empty($out[1]) ? '' : $out[1];
	$user['img_url'] = getImg($img_url_tmp, $user['u_id']);

	preg_match('#<span class="business item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['business'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<i class="icon icon-profile-(.*?)male"></i>#', $result, $out);
	$user['gender'] = empty($out[1]) ? 'male' : 'female';

	preg_match('#<span class="education item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['education'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<span class="education-extra item" title=["|\'](.*?)["|\']>#', $result, $out);
	$user['major'] = empty($out[1]) ? '' : $out[1];

	preg_match('#<span class="content">\s(.*?)\s</span>#s', $result, $out);
	$user['description'] = empty($out[1]) ? '' : trim(strip_tags($out[1]));

	preg_match('#<span class="zg-gray-normal">关注了</span><br />\s<strong>(.*?)</strong><label> 人</label>#', $result, $out);
	$user['followees_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#<span class="zg-gray-normal">关注者</span><br />\s<strong>(.*?)</strong><label> 人</label>#', $result, $out);
	$user['followers_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#<strong>(.*?) 个专栏</strong>#', $result, $out);
	$user['special_count'] = empty($out[1]) ? 0 : intval($out[1]);

	preg_match('#<strong>(.*?) 个话题</strong>#', $result, $out);
	$user['follow_topic_count'] = empty($out[1]) ? 0 : intval($out[1]);

	preg_match('#<span class="zm-profile-header-user-agree"><span class="zm-profile-header-icon"></span><strong>(.*?)</strong>赞同</span>#', $result, $out);
	$user['approval_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#<span class="zm-profile-header-user-thanks"><span class="zm-profile-header-icon"></span><strong>(.*?)</strong>感谢</span>#', $result, $out);
	$user['thank_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#提问\s<span class="num">(.*?)</span>#', $result, $out);
	$user['ask_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#回答\s<span class="num">(.*?)</span>#', $result, $out);
	$user['answer_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#专栏文章\s<span class="num">(.*?)</span>#', $result, $out);
	$user['article_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#个人主页被 <strong>(.*?)</strong> 人浏览#', $result, $out);
	$user['pv_count'] = empty($out[1]) ? 0 : intval($out[1]);

	preg_match('#收藏\s<span class="num">(.*?)</span>#', $result, $out);
	$user['started_count'] = empty($out[1]) ? 0 : $out[1];

	preg_match('#公共编辑\s<span class="num">(.*?)</span>#', $result, $out);
	$user['public_edit_count'] = empty($out[1]) ? 0 : $out[1];


	return $user;
}

/**
 * [getImg 处理防盗链图片]
 * @param  [type] $url  [description]
 * @param  [type] $u_id [description]
 * @return [type]       [description]
 */
function getImg($url, $u_id)
{
	//如果文件已经存在，则不必再获取
	if (file_exists('./images/' . $u_id . ".jpg"))
	{
		return "images/$u_id" . '.jpg';
	}
	if (empty($url))
	{
		return '';
	}
    $context_options = array(  
		'http' =>  
		array(
			'header' => "Referer:http://www.zhihu.com",  
	));
	  
	$context = stream_context_create($context_options);  
	$img = file_get_contents($url, FALSE, $context);
	file_put_contents('./images/' . $u_id . ".jpg", $img);
	return "images/$u_id" . '.jpg';
}

/**
 * [dealUserInfo 返回用户名和昵称列表]
 * @param  [type] $user_list [description]
 * @return [type]            [description]
 */
function dealUserInfo($user_list, $u_id)
{
	$info_list = array();
	$new_user_id_list = array();
	foreach ($user_list as $user)
	{
		preg_match('#<h2 class="zm-list-content-title"><a data-tip=".*?" href="http://www.zhihu.com/people/(.*?)" class="zg-link" title="(.*?)">#', $user, $out);
		$new_user_id_list[] = $out[1];
		$info = array('', $u_id, empty($out[1]) ? '' : $out[1], empty($out[2]) ? '' : $out[2]);
		array_push($info_list, $info);
	}
	$new_user_list = Curl::getMultiUser($new_user_id_list);
	$result = array($new_user_list, $info_list);
	usleep(100);
	return $result;
}

function getOnePageUserList($result, $u_id, $user_type = 'followees', $count)
{
	$follow_user_list = array();
	$user_list = array();
	preg_match_all('#<h2 class="zm-list-content-title"><a data-tip=".*?" href="http://www.zhihu.com/people/(.*?)" class="zg-link" title="(.*?)">#', $result, $out);
	$user_list = Curl::getMultiUser($out[1]);
	for ($i = 0; $i < $count; $i++)
	{
		$user = array('', $u_id, empty($out[1][$i]) ? '' : $out[1][$i], empty($out[2][$i]) ? '' : $out[2][$i]);
		array_push($follow_user_list, $user);
	}
	User::addMulti($user_list);
	return $follow_user_list;
}

/**
 * [getUserList 返回用户列表]
 * @param  [type]  $curl      [description]
 * @param  [type]  $result    [description]
 * @param  [type]  $u_id  	  [description]
 * @param  string  $user_type [description]
 * @param  integer $count     [description]
 * @return [type]             [description]
 */
function getUserList($result, $u_id, $user_type = 'followees', $count)
{
	$following_users = array();
	$more_user_list = array();
	if ($count <= 20)
	{
		$following_users = getOnePageUserList($result, $u_id, $user_type, $count);
	}
	else
	{
		preg_match('#<input type="hidden" name="_xsrf" value="(.*?)"/>#', $result, $out);
    	$_xsrf = empty($out[1]) ? '' : trim($out[1]);
    	preg_match('#<div class="zh-general-list clearfix" data-init="(.*?)">#', $result, $out);
    	$url_params = empty($out[1]) ? '' : json_decode(html_entity_decode($out[1]), true);
		if (!empty($_xsrf) && !empty($url_params) && is_array($url_params))
    	{
			$params = $url_params['params'];
			$total_page = ceil($count/20);
			for ($page = 1; $page <= $total_page; ++$page)
			{
				$params['offset'] = ($page - 1 ) * 20;
				$post_fields = array(
					'method' => 'next',
					'params' =>  json_encode($params),
					'_xsrf' => $_xsrf
				);
				$more_user = Curl::request('POST', 'http://www.zhihu.com/node/' . $url_params['nodename'], $post_fields);
				$more_user_result = json_decode($more_user, true);
				$more_user_tmp_list = $more_user_result['msg'];
				$result = dealUserInfo($more_user_tmp_list, $u_id);
				$more_user_list = array_merge($more_user_list, $result[0]);
				$following_users = array_merge($following_users, $result[1]);
			}
			User::addMulti($more_user_list);
		}
		else
		{
			return array();
		}
	}

	return $following_users;
}