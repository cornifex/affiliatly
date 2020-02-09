<?php
/*
* PHP Integration with Affiliatly.com
* version 09.09.2016 1.411
* Copyright Overcode.bg
*/

global $affiliatly_root;
$affiliatly_root = 'https://www.affiliatly.com/';

/*
* start tracking the visitor
* if he/she entered your site with ?aff=XX parameter
* $id_affiliatly is your Affiliatly code in format: AF-10XXX
* you can find it in your Affiliatly's admin panel, tab Account :: Your Affiliatly Code
*/
function affiliatly_start_tracking($id_affiliatly)
{
	global $affiliatly_root;

	echo '<script type="text/javascript" src="'.$affiliatly_root.'easy_affiliate.js"></script>
<script type="text/javascript">startTracking(\''.$id_affiliatly.'\');</script>';
}

/*
* this will mark a purchase for the visitor and will associate the purchase with the affiliate who
* refferred the client to your site
******* parameters
* $id_affiliatly - your Affiliatly code, see above for details
* $order - the ID of the purchase, it must be unique, if a second request is made with the same order ID the API will return false
* $price - the price of the order, without currency symbol, example 99 or 99.99 
* $security_hash - hash used to strengthen the request, if the sent hash is not equal with the one for your Affiliatly account, 
* $coupon_code - enter the coupon code if the client have used one
* $skus - array containing the product ID (SKU), quantity and price, used when tracking by SKU is enabled, example 
* $skus = array(
		1=>array('id'=>123, 'quantity'=>1, 'price'=>49), // where 'id'=>123 is the product ID
		2=>array('id'=>456, 'quantity'=>6, 'price'=>99),
	);
* $client_email - string, if you wish to use our "Tracking by email feature", you will need to pass the client's email on every purchase
*
* the API will return false.
* You can see your security hash or genereate new one in your Affiliatly admin panel, tab Account
*/
function affiliatly_mark_purchase($id_affiliatly, $order, $price, $security_hash = '', $coupon_code = '', $skus = array(), $client_email = '' )
{
	global $affiliatly_root;

	if( ( !isset($_COOKIE['easy_affiliate']) || empty($_COOKIE['easy_affiliate']) ) && empty($coupon_code) && empty($client_email) ) // 
		return false;

	// $cookie_data = ($_COOKIE['easy_affiliate']);
	$cookie_data = isset($_COOKIE['easy_affiliate']) ? $_COOKIE['easy_affiliate'] : '';
	preg_match_all('/&id_user=([0-9]+)/', $cookie_data, $result);
	$id_user = $result[1][0];

	preg_match_all('/&aff_uid=([0-9]+)/', $cookie_data, $result);
	$id_affiliate = $result[1][0];

	$id_order = $order;
	$price = $price;
	$coupon_code = $coupon_code;
	$skus = json_encode($skus);
	
	$post = 'mode=mark_php&id_affiliatly='.$id_affiliatly.'&id_user='.$id_user.'&aff_uid='.$id_affiliate;
	$post .= '&order='.$order.'&price='.$price.'&from=php&hash='.$security_hash.'&coupon_code='.$coupon_code.'&skus='.$skus;
	$post .= '&client_email='.$client_email;
	$url_to_listener = $affiliatly_root.'api_request.php';

	$result = affiliatly_connectCURL($url_to_listener, $post);
}

/*
* you can now send request to change the status of the order
* $id_affiliatly - your Affiliatly code, see above for details
* $order - the ID of the purchase
* $status: 
** 0 - Not Paid (affiliates do not earn commission)
** 1 - Paid (affiliates will earn commission)
* $security_hash - hash used to strengthen the request
*/
function affiliatly_order_status($id_affiliatly, $order, $status, $security_hash)
{
	global $affiliatly_root;

	if( empty($security_hash) )
		return false;

	$post = 'mode=order_status&id_affiliatly='.$id_affiliatly.'&order='.$order.'&status='.$status.'&from=php&hash='.$security_hash;
	$url_to_listener = $affiliatly_root.'api_request.php';
	affiliatly_connectCURL($url_to_listener, $post);
}


/*
* connect to URL using cURL method
* $cookie contains the cookie string if there is any
* $cacheIt is == true if we want the respond to be cached
* $post if there is need the request to be POST,
* thre prefix variable is when the url for the requests is the same, but we need to cache the different respond
*/
function affiliatly_connectCURL( $url, $post)
{
	$initCurl = affiliatly_initCURL( $url, $post );
	$ch = curl_exec($initCurl);

	return $ch;
}

/*
* initialize and setup the CURL options
*/
function affiliatly_initCURL( $url, $post )
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 20);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 12);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($curl, CURLOPT_ENCODING, "gzip");
	curl_setopt($curl, CURLOPT_FORBID_REUSE, true);

	if( $post==true )
	{
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		$headers[] = "Content-Type: application/x-www-form-urlencoded";
		$headers[] = "Connection: close";
		$headers[] = 'Content-length: '.strlen($post);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	}
	else
		curl_setopt($curl, CURLOPT_HTTPGET, true);

	return $curl;
}

?>