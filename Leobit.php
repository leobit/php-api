<?php

/**
 * @package Leobit API
 * @author Leobit team
 * @version 0.1
 * @access public
 */

class Leobit {
    
	private $key;
	private $secret;
    private $ch;
	private $client_id;
    private $accessToken;
    private $apiUrl = 'https://api.leobit.net/';
    public static $PRIVATE_METHODS = array('balance', 'cancelorder', 'placeorder', 'withdraw', 'openorders');
    
	public function __construct($key, $secret, $email)
	{
        $this->ch = curl_init();

		if (isset($secret) && isset($key) && isset($email))
		{
			$this->key = $key;
			$this->secret = $secret;
			$this->client_id = $email;
            
		} else
			die("NO KEY/SECRET/CLIENT ID");
	}
    
    public function __destruct() {
        curl_close($this->ch);
    }
    
	public function query($path, array $req = array(), $verb = 'post', $isTokenRequest = false) {
        
        if(in_array($path, self::$PRIVATE_METHODS) && !$isTokenRequest) {
            
            if(!$this->accessToken) {

                $mt = explode(' ', microtime());
                $data['nonce'] = $mt[1] . substr($mt[0], 2, 6);
                $data['apiKey'] = $this->key;
                $data['signature'] = $this->get_signature($data['nonce']);

                $response = $this->query('authenticate', $data, 'post', true);  

                if(!$response['success']) die($response['message']);
                $this->accessToken = $response['result']['token'];

            } 

            $req['token'] = $this->accessToken;
        }
        
        
        $post_data = http_build_query($req, '', '&');
        
        $url = $this->apiUrl . $path;
        
        if ($verb == 'post') curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data);
        elseif ($verb == 'get') $url.='/?'.$post_data;
        
        $curlOptions = array(
            CURLOPT_URL => $url,
            //CURLOPT_SSL_VERIFYPEER => 1,
            //CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true, // allowing redirects and https
            CURLOPT_SSL_VERIFYPEER => false, //allowing redirects and https
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; Leobit PHP Client; ' . php_uname('s') . '; PHP/' . phpversion() . ')'
        );
        curl_setopt_array($this->ch, $curlOptions);
		
		$res = curl_exec($this->ch);
        
        if ($res === false)
			throw new \Exception('Could not get reply: ' . curl_error($this->ch));
		
		curl_reset($this->ch);
        $dec = json_decode($res, true);
		if (is_null($dec))
			throw new \Exception('Invalid data received, please make sure connection is working and requested API exists');
        
		return $dec;
	}
    
    private function get_signature($nonce) {
	  
	  $message = $nonce.$this->client_id.$this->key;
	  
	  return strtoupper(hash_hmac('sha256', $message, $this->secret));
        
	}
    
	public function placeOrder($type, $amount, $price, $currency = 'btc') {
		
        return $this->query('placeorder', array(
            'type' => $type, 
            'quantity' => $amount, 
            'price' => $price, 
            'currency' => $currency
        ));
        
	}

	public function cancelOrder($orderId) {
		return $this->query('cancelorder',  array('orderId' => $orderId));
	}


    
    public function getBalance() {
        return $this->query('balance', array(), 'get');
    }
    
    public function cryptoWithdraw($address, $amount, $currency = 'btc') {
        return $this->query('withdraw', array(
            'address' => $address,
            'amount'  => $amount,
            'currency' => $currency
        ));
    }
    
    public function getOrderbook($type = 'both', $curreny = 'btc') {
        return $this->query('orderbook', array('type' => $type, 'currency' => $curreny), 'get');
    }
    
    public function getOpenOrders($curreny = 'btc') {
        return $this->query('openorders', array('currency' => $curreny), 'get');
    }

}



