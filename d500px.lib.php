<?php

/**
 * Exception handling class.
 */
class D500pxException extends Exception {}



/**
 * Primary 500px API implementation class
 */
class D500px {
  /**
   * @var $source the 500px api 'source'
   */
  protected $source = 'drupal';
  protected $signature_method;
  protected $consumer;
  protected $token;


  /********************************************//**
   * Authentication
   ***********************************************/
  /**
   * Constructor for the 500px class
   */
  public function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
    $this->signature_method = new OAuthSignatureMethod_HMAC_SHA1();
    $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
    if (!empty($oauth_token) && !empty($oauth_token_secret)) {
      $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
    }
  }


  public function get_request_token() {
    $url = variable_get('d500px_api', D500PX_API) . '/v1/oauth/request_token';
    try {
      $params = array('oauth_callback' => url('d500px/oauth', array('absolute' => TRUE)));
      $response = $this->auth_request($url, $params);
    }
    catch (D500pxException $e) {
      watchdog('D500px', '!message', array('!message' => $e->__toString()), WATCHDOG_ERROR);
      return FALSE;
    }
    parse_str($response, $token);
    $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }


  
}