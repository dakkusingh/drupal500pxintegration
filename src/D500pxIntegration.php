<?php

/**
 * @file
 * Contains Drupal\D500px\D500pxIntegration.
 *
 */

namespace Drupal\D500px;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Url;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

/**
 * Primary 500px API implementation class
 * @package Drupal\D500px
 */
class D500pxIntegration {

  /**
   * @var $source the 500px api 'source'
   */
  protected $source = 'drupal';
  protected $signature_method;
  protected $consumer;
  protected $token;

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $config_factory;

  /**
   * Constructor for the 500px class
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->config_factory = $config_factory;
    $config = $config_factory->get('d500px.settings');

    $consumer_key = 'HDYoibKfCYC3MMylaZ9PCh3U9TeLE6NblQN53WtI';
    $consumer_secret = 'sXgBYV7zxy8XvJRkk78MaUzS9k2YRzTRVcFth5VC';

    $this->consumer = new \OAuth($consumer_key, $consumer_secret);
    $this->request_token_url = $config->get('d500px_api') . '/v1/oauth/request_token';
    $this->authorize_url = $config->get('d500px_api') . '/v1/oauth/authorize';
    $this->authenticate_url = $config->get('d500px_api') . '/v1/oauth/authenticate';
    $this->access_token_url = $config->get('d500px_api') . '/v1/oauth/access_token';
    $this->generic_url = $config->get('d500px_api') . '/v1/';
  }

public function getRequestToken3() {
  $accessToken = 'HDYoibKfCYC3MMylaZ9PCh3U9TeLE6NblQN53WtI';
  $consumer_secret = 'sXgBYV7zxy8XvJRkk78MaUzS9k2YRzTRVcFth5VC';

  $client = new Client([
    'base_uri' => $this->generic_url,
    //'handler' => $stack,
  ]);

  $request = $client->post('/v1/oauth/request_token');
  $request->addHeader('Authorization', 'oauth_consumer_key=' .$accessToken);
  $request->addHeader('Authorization', 'oauth_token=' .$consumer_secret);
  $request->addHeader('Authorization', 'oauth_nonce=2672821620');

  $response = $request->send();
  echo $response->getBody();
}

















  public function getRequestToken() {
    $url = $this->request_token_url;

    try {
      $url = Url::fromUserInput('/d500px/oauth', array('absolute' => TRUE))->toString();
      $params = array('oauth_callback' => $url);
      $response = $this->authRequest($url, $params);
    }
    catch (Exception $e) {
      \Drupal::logger('D500px')->error($e->__toString());
      return FALSE;
    }
    parse_str($response, $token);
    $this->token = new \OAuth($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }

  public function getAuthorizeUrl($token) {
    $url = $this->authorize_url;
    $url .= '?oauth_token=' . $token['oauth_token'];

    return $url;
  }

  public function getAuthenticateUrl($token) {
    $url = $this->authenticate_url;
    $url .= '?oauth_token=' . $token['oauth_token'];

    return $url;
  }

  public function getAccessToken() {
    $url = $this->access_token_url;
    try {
      $response = $this->authRequest($url);
    }
    catch (Exception $e) {
      \Drupal::logger('D500px')->error($e->__toString());
      return FALSE;
    }

    parse_str($response, $token);
    $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
    return $token;
  }

  /**
   * Performs an authenticated request.
   */
  public function authRequest($url, $params = array(), $method = 'GET') {
    $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $params);
    $request->sign_request($this->signature_method, $this->consumer, $this->token);

    switch ($method) {
      case 'GET':
        return $this->request($request->to_url());

      case 'POST':
        return $this->request($request->get_normalized_http_url(), $request->get_parameters(), 'POST');
    }
  }

  /**
   * Performs a request.
   *
   * @throws Exception
   */
  protected function request($url, $params = array(), $method = 'GET') {
    $data = '';
    if (count($params) > 0) {
      if ($method == 'GET') {
        $url .= '?' . http_build_query($params, '', '&');
      }
      else {
        $data = http_build_query($params, '', '&');
      }
    }

    $headers = array();
    $headers['Authorization'] = 'Oauth';
    $headers['Content-type'] = 'application/x-www-form-urlencoded';
    $response = $this->doRequest($url, $headers, $method, $data);

    if (!isset($response->error)) {
      return $response->data;
    }
    else {
      $error = $response->error;
      $data = $this->parseResponse($response->data);
      if (isset($data['error'])) {
        $error = $data['error'];
      }
      throw new Exception($error);
    }
  }

  /**
   * Actually performs a request.
   *
   * This method can be easily overriden through inheritance.
   *
   * @param string $url
   *   The url of the endpoint.
   * @param array $headers
   *   Array of headers.
   * @param string $method
   *   The HTTP method to use (normally POST or GET).
   * @param array $data
   *   An array of parameters
   * @return
   *   stdClass response object.
   */
  protected function doRequest($url, $headers, $method, $data) {
    // @todo replace drupal_http_request()
    /*
    $client = \Drupal::httpClient();
    $request = $client->createRequest('GET', $feed->url);
    $request->addHeader('If-Modified-Since', gmdate(DATE_RFC1123, $last_fetched));

    try {
      $response = $client->get($feed->uri, [
        'headers' => [
          'If-Modified-Since' => gmdate(DATE_RFC1123, $last_fetched),
        ],
      ]);
      // Expected result.
      // getBody() returns an instance of Psr\Http\Message\StreamInterface.
      // @see http://docs.guzzlephp.org/en/latest/psr7.html#body
      $data = $response->getBody();
    }
    catch (RequestException $e) {
      watchdog_exception('my_module', $e);
    }
    */

    return drupal_http_request($url, array('headers' => $headers, 'method' => $method, 'data' => $data));
  }

  protected function parseResponse($response) {
    // @see http://drupal.org/node/985544
    // json_decode large integer issue
    $length = strlen(PHP_INT_MAX);
    $response = preg_replace('/"(id|in_reply_to_status_id)":(\d{' . $length . ',})/', '"\1":"\2"', $response);
    return json_decode($response, TRUE);
  }

  /**
   * Creates an API endpoint URL.
   *
   * @param string $path
   *   The path of the endpoint.
   * @return
   *   The complete path to the endpoint.
   */
  protected function createUrl($path) {
    $url = $this->generic_url . $path;
    return $url;
  }

  /**
   * Calls a 500px API endpoint.
   */
  public function call($path, $params = array(), $method = 'GET') {
    $url = $this->createUrl($path);

    try {
      $response = $this->authRequest($url, $params, $method);
    }
    catch (Exception $e) {
      //watchdog('D500px', '!message', array('!message' => $e->__toString()), WATCHDOG_ERROR);
      \Drupal::logger('D500px')->error($e->__toString());
      return FALSE;
    }

    if (!$response) {
      return FALSE;
    }

    return $this->parseResponse($response);
  }

}
