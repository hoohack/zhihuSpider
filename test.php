<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-25 11:25:51
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2015-08-26 09:48:56
 */
require_once './spider/user.php';
require_once './function.php';
$user_cookie = '_za=a41e1b8b-517a-4fea-9465-88e8c80ba17e;q_cl=3198dbc291fa40d7b717f9a4dd5ec90e|1439792872000|1439792872000;_xsrf=981ffd949fbc70e73cc4bb2559243ac8;cap_id="YmViMDk0YTdjMjUyNDc4MjhmOWU5MDkyMTg3NWRlNGY=|1439792872|7eb10c44aead609ab6e63f3eb2b5856149076942";z_c0="QUFEQTRZbzZBQUFYQUFBQVlRSlZUZjhMLVZYNnBhUDBYYzJIOFJtUGs2aFlianFRU3NRR3hRPT0=|1439792895|4f033f6e2f99a39b152a59c32496dfc954cbe6fd";__utma=51854390.888606616.1439792875.1439792875.1439891906.2;__utmb=51854390.2.10.1439891906;__utmc=51854390;__utmt=1;__utmv=51854390.100-1|2=registration_date=20141017=1^3=entry_date=20141017=1__utmz=51854390.1439891906.2.2.utmcsr=google|utmccn=(organic)|utmcmd=organic|utmctr=(not%20provided)';
$header = array(
"content-type: application/x-www-form-urlencoded; 
charset=UTF-8"
);
$arr = array('mora-hu', '168532', 'reeze', 'laruence', 'hantianfeng');
$len = count($arr);
$ch_arr = array();
$mh = curl_multi_init();
$options = array(
	CURLOPT_HEADER => 0,
	CURLOPT_HTTPHEADER => $header,
	CURLOPT_COOKIE => $user_cookie,
	CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_SSL_VERIFYPEER => true,
	CURLOPT_TIMEOUT => 60,
	CURLOPT_NOSIGNAL => 1,
	CURLOPT_CONNECTTIMEOUT => 30
);
for ($i = 0; $i < $len; $i++)
{
	$ch1 = curl_init();
	$options[CURLOPT_URL] = 'http://www.zhihu.com/people/' . $arr[$i] . '/about';
	curl_setopt_array($ch1, $options);
	curl_multi_add_handle($mh, $ch1);
	array_push($ch_arr, $ch1);
}
$active = null; 
$res_arr = array();
$user_arr = array();
do {
	while (($execrun = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM) ;
	if ($execrun != CURLM_OK) { break; }
	
	while ($done = curl_multi_info_read($mh)) 
    {
        $info = curl_getinfo($done['handle']);
        // exit;
    	$res_arr = curl_multi_getcontent($done['handle']);
    	// if ($res_arr)
    	// {
    		$user_arr[] = getUserInfo($res_arr);
    	// }
    	// $ch = curl_init();
        // $options[CURLOPT_URL] = 'http://www.zhihu.com/people/' . $arr[$i++] . '/about';
        // curl_setopt_array($ch, $options);
        // curl_multi_add_handle($mh, $ch);
        curl_multi_remove_handle($mh, $done['handle']);
    }
	// 
	if ($active)
		curl_multi_select($mh, 10);
} 
while($active); 
// exit;
echo "<pre>";

for ($i = 0; $i < $len; $i++)
{
	curl_close($ch_arr[$i]);
}
curl_multi_close($mh);
print_r($user_arr);exit;

echo "<pre>";
for ($i = 0; $i < $len; $i++)
{
	print_r($user_arr[$i]);
}