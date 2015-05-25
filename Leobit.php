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
    private $email;
    private $apiUrl = 'https://api.leobit.net/';
    
    protected $accessToken;
    protected $ch;
    
    public static $PRIVATE_METHODS = array('balance', 'cancelorder', 'placeorder', 'withdraw', 'openorders');
    
    
    public function __construct($key = null, $secret = null, $email = null) {
        
        $this->ch = curl_init();
        
        $this->key = $key;
        $this->secret = $secret;
        $this->email = $email;    
        
    }
    
    public function __destruct() {
        curl_close($this->ch);
    }
    
    public function setCredentials($key, $secret, $email) {
        $this->key = $key;
        $this->email = $email;
        $this->secret = $secret;
    }
    
    public function query($path, array $req = array(), $verb = 'post', $isTokenRequest = false) {
        
        if(in_array($path, self::$PRIVATE_METHODS) && !$isTokenRequest) {
            
            if(!$this->accessToken) {
                
                if(!isset($this->email, $this->key, $this->secret))
                    throw new \Exception('For calling private methods you have to set KEY, SECRET and EMAIL.');
                
                $mt = explode(' ', microtime());
                $data['nonce'] = $mt[1] . substr($mt[0], 2, 6);
                $data['apiKey'] = $this->key;
                $data['signature'] = $this->get_signature($data['nonce']);

                $response = $this->query('authenticate', $data, 'post', true);  

                if(!$response['success']) 
                    throw new \Exception($response['result']['message']);
                
                $this->accessToken = $response['result']['token'];

            }

            $req['token'] = $this->accessToken;
        }
        
        
        $post_data = http_build_query($req, '', '&');
        
        $url = $this->apiUrl . $path;
        
        $curlOptions = array(
            CURLOPT_FOLLOWLOCATION => true, 
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; Leobit PHP Client; ' . php_uname('s') . '; PHP/' . phpversion() . ')'
        );
       
        if ($verb == 'post') 
            $curlOptions[CURLOPT_POSTFIELDS] = $post_data;
        elseif ($verb == 'get') {
            $curlOptions[CURLOPT_HTTPGET] = true;
            $url.='/?'.$post_data;
        } else 
            throw new \Exception('API supports only POST and GET methods.');
        
        $curlOptions[CURLOPT_URL] = $url;
        
        curl_setopt_array($this->ch, $curlOptions);
		
	$res = curl_exec($this->ch);
        
        if ($res === false)
		throw new \Exception('Could not get reply: ' . curl_error($this->ch));
        
        $dec = json_decode($res, true);
	if (is_null($dec))
		throw new \Exception('Invalid data received, please make sure connection is working and requested API exists, data: '.$res);
        
	return $dec;
   }
    
    private function get_signature($nonce) {
	  
	$message = $nonce.$this->email.$this->key;
	  
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
