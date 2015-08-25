<?php
	function getGoodsName($result)
	{
		preg_match('#<h2 class="til">(.*?)</h2>#', $result, $out);
		return empty($out[1]) ? '' : $out[1];
	}

	$prefix = 'http://www.up24.com/goods_detail?goods_id=';
    $aURLs = array();
    for ($i = 436; $i <= 560; $i++)
    {
        $aURLs[] = $i;
    }
	// $aURLs = array(312, 533, 233, 236, 534, 560, 351, 391, 349, 350, 519, 320, 321, 322, 323, 324); // array of URLs

    $master = curl_multi_init(); // init the curl Multi
    
    $aCurlHandles = array(); // create an array for the individual curl handles

    foreach ($aURLs as $id=>$url) { //add the handles for each url
        $ch = curl_init(); // init curl, and then setup your options
        curl_setopt($ch, CURLOPT_URL, $prefix . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // returns the result - very important
        curl_setopt($ch, CURLOPT_HEADER, 0); // no headers in the output
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $aCurlHandles[$url] = $ch;
        curl_multi_add_handle($master,$ch);
    }

    $goods_name_list = array();
    do {
        while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM) ;
        if ($execrun != CURLM_OK)
            break;
        // a request was just completed -- find out which one
        while ($done = curl_multi_info_read($master)) {
        	// get the info and content returned on the request
            $info = curl_getinfo($done['handle']);
            $output = curl_multi_getcontent($done['handle']);
            if (empty(getGoodsName($output)))
            {
                print_r($info);
            }
            $goods_name_list[] = getGoodsName($output);
            curl_multi_remove_handle($master, $done['handle']);
        }

        if ($running)
        {
        	curl_multi_select($master, 30);
        }
    } while($running);
    curl_multi_close($master);
    echo "<pre>";
    print_r($goods_name_list);