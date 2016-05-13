<?php

	/*
		// IOS receipt 
		stdClass::__set_state(array(
			'receipt' =>
				stdClass::__set_state(array(
					'original_purchase_date_pst' => '2014-05-17 17:48:39 America/Los_Angeles',
					'purchase_date_ms' => '1400374119583',
					'unique_identifier' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
					'original_transaction_id' => '1000000111096489',
					'bvrs' => '1.0',
					'transaction_id' => '1000000111096489',
					'quantity' => '1',
					'unique_vendor_identifier' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
					'item_id' => 'MY_ITEM_ID',
					'product_id' => 'MY_PRODUCT_ID',
					'purchase_date' => '2014-05-18 00:48:39 Etc/GMT',
					'original_purchase_date' => '2014-05-18 00:48:39 Etc/GMT',
					'purchase_date_pst' => '2014-05-17 17:48:39 America/Los_Angeles',
					'bid' => 'MY_BID',
					'original_purchase_date_ms' => '1400374119583',
				)),
			'status' => 0,
		))

		// Google Receipt 
		receipt: {"orderId":"GPA.1391-9806-9510-85261","packageName":"com.sixwaves.iabtest","productId":"com.sixwaves.iab.cash.20","
		purchaseTime":1461665313490,"purchaseState":0,"
		purchaseToken":"mobdheofeamcpaddlcnfnhah.AO-J1Ow1ysahE5-jeqtDUlGxmxa2Se8tFJ2RNnkkWwEUew7OkOlfMBq8TSlcZOVdygzi-V0JujA2fKCAfWzTfQxdde640808zcbo-Ldj9gTOSz91wExfpne2rpNv7zb3I1PevSsy74U7"}


	*/
		
	// iOS 
	function postIOS($endpoint_url, $postData)
	{
		$ch = curl_init($endpoint_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		$response = json_decode(curl_exec($ch));
		curl_close($ch);
		return $response;
	}
	
	// $signed_data : receipt json data 
	// $signature   : signature
	$androidPublicKey = "Your android public Key";
	function postAndroid($signed_data, $signature, $public_key_base64) 
	{
		$key =	"-----BEGIN PUBLIC KEY-----\n".
			chunk_split($public_key_base64, 64,"\n").
			'-----END PUBLIC KEY-----';   
		
		//using PHP to create an RSA key
		$key = openssl_get_publickey($key);
		//$signature should be in binary format, but it comes as BASE64. 
		//So, I'll convert it.
		$signature = base64_decode($signature);   
		//using PHP's native support to verify the signature
		$result = openssl_verify(
				$signed_data,
				$signature,
				$key,
				OPENSSL_ALGO_SHA1);
				
				
				
		error_log("#### postAndroid resp: ". $result);
				
		if (0 === $result) 
		{
			return false;
		}
		else if (1 !== $result)
		{
			return false;
		}
		else 
		{
			return true;
		}
	} 
	
	$finalResult = array( "status"=> "404");
	$sessionKey = '1234';
	$statusStr = "";
	if(!isset($_POST['key'])) {
		$sessionKey = $_POST['key'];
		$statusStr .= "key not found";
	}


	if(!isset($_POST['receipt'])) {
		$statusStr .= " receipt not found";
	}
	error_log("receipt: ". $_POST['receipt']);
	
	// dev, prod
	if(!isset($_POST['en'])) {
		$statusStr .= " environment not found";
	}
	
	$os;
	if(!isset($_POST['os'])) {
		$os = $_POST['os'];
		$statusStr .= " os not found";
	}
	
	// For android
	$sing;
	if(!isset($_POST['sing'])) {
		$sing = $_POST['sing'];
		$statusStr .= " sing not found";
	}
	
	// For MMO devs
	if( $sessionKey = 'Your session key')
	{
		$postData = json_encode(
			array('receipt-data' => $_POST['receipt'])
		);

		// Apple Store 
		if( $_POST['os'] == 'ios')
		{
			$response = postIOS("https://buy.itunes.apple.com/verifyReceipt", $postData);
			if ($_POST['en'] == 'dev') {
				$response = postIOS("https://sandbox.itunes.apple.com/verifyReceipt", $postData);
			}
			if ($response->status == 0) {
				$transaction_id = $response->receipt->transaction_id;
				$finalResult = array( "status"=> "200", "reason"=> " Apple Store OK", "transaction_id"=> $transaction_id);
					
				$rspStr = "unique_identifier: ". $response->receipt->unique_identifier;
				$rspStr .= " original_transaction_id: ". $response->receipt->original_transaction_id;
				$rspStr .= " quantity: ". $response->receipt->quantity;
				$rspStr .= " unique_vendor_identifier: ". $response->receipt->unique_vendor_identifier;
				$rspStr .= " item_id: ". $response->receipt->item_id;
				$rspStr .= " product_id: ". $response->receipt->product_id;
				$rspStr .= " purchase_date: ". $response->receipt->purchase_date;
				$rspStr .= " original_purchase_date: ". $response->receipt->original_purchase_date;
				
				error_log(" Succ response: ". $rspStr);
			
			}else {
				$statusStr .= " Apple Store Error code:". $response->status;
				$finalResult = array( "status"=> "500", "reason"=> $statusStr );
			}
		
		// Google Play
		}else {
			
			$rsp = postAndroid($_POST['receipt'], $_POST['sing'], $androidPublicKey);
			if($rsp ) 
			{
				$finalResult = array( "status"=> "200", "reason"=> " Google Play OK");
			}else {
				$finalResult = array( "status"=> "500", "reason"=> " Google Play Error". $statusStr );
			}
		}
		
	}else {
		
		$finalResult = array( "status"=> "404", "reason"=> $statusStr);
	}
	
	error_log("". json_encode($finalResult));
	echo(json_encode($finalResult));
	
	
?>