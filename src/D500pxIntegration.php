<?php

/**
 * @file
 * Contains Drupal\D500px\D500pxIntegration.
 *
 */

namespace Drupal\D500px;
use Drupal\Core\Config\ConfigFactory;


/**
 * Primary 500px API implementation class
 * @package Drupal\D500px
 */
class D500pxIntegration {
  // Inspired by
  // https://github.com/dcousineau/twitteroauth/blob/master/src/Johntron/TwitterOAuth.php

  /* Respons format. */
  public $format = 'json';

  /* Decode returned json data. */
  public $decode_json = true;

  /* Set the useragent. */
  public $useragent = 'PHP500pxOAuth';

  /* Immediately retry the API call if the response was not successful. */
  public $retry = true;

  /* Number of times to retry the API call if the response was not successful. */
  public $retryAttempts = 3;

  /* Number of times the current request has been retried. */
  public $currentRetries = 0;

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $config_factory;

  /**
   * Constructor for the 500px class
   */
  public function __construct(ConfigFactory $config_factory, $oauth_token = null, $oauth_token_secret = null) {
    $this->config_factory = $config_factory;
    $config = $config_factory->get('d500px.settings');

    $this->consumer = new \OAuth(
      $config->get('d500px_consumer_key'),
      $config->get('d500px_consumer_secret'),
      OAUTH_SIG_METHOD_HMACSHA1,
      OAUTH_AUTH_TYPE_AUTHORIZATION // go for the gold!
    );

    $this->consumer->setRequestEngine(OAUTH_REQENGINE_STREAMS); // we don't need curl

    if (!empty($oauth_token) && !empty($oauth_token_secret)) {
      $this->consumer->setToken($oauth_token, $oauth_token_secret);
      $this->token = array($oauth_token, $oauth_token_secret);
    }
    else {
      $this->token = null;
    }

    $this->request_token_url = $config->get('d500px_api') . '/v1/oauth/request_token';
    $this->authorize_url = $config->get('d500px_api') . '/v1/oauth/authorize';
    $this->authenticate_url = $config->get('d500px_api') . '/v1/oauth/authenticate';
    $this->access_token_url = $config->get('d500px_api') . '/v1/oauth/access_token';
    $this->generic_url = $config->get('d500px_api') . '/v1/';
  }


  /**
   * Set access token and secret
   */
  public function setToken($token, $secret) {
    $this->consumer->setToken($token, $secret);
  }

  /**
   * Get a request_token from Twitter
   *
   * @return array a key/value array containing oauth_token and oauth_token_secret
   */
  public function getRequestToken($oauth_callback = null) {
    $this->token = $this->consumer->getRequestToken(
      $this->request_token_url,
      $oauth_callbacka
    );

    return $this->token;
  }

  /**
   * Returns the last response info from the oauth client
   * If available, also adds in the following custom headers:
   *     `'status_code'`
   *     `'status_message'`
   *     `'remaining_hits'`: for rate limiting
   *     `'reset_time_in_seconds'`: when the rate limit resets
   *     `'access_level'`: for authenticated requests, the permission level of the access token
   *
   * @return array
   */
  public function getLastResponseInfo() {
    $responseInfo = $this->consumer->getLastResponseInfo();
    $headers = $this->consumer->getLastResponseHeaders();
    if (!empty($headers)) {
      $status = $this->extractHeader($headers, 'Status:');
      if ($status !== false) {
        $statusParts = explode(' ', $status);
        $responseInfo['status_code'] = array_shift($statusParts);
        $responseInfo['status_message'] = implode(' ', $statusParts);
      }
      $retryAfter = $this->extractHeader($headers, 'Retry-After:');
      if ($retryAfter !== false) {
        $responseInfo['remaining_hits'] = 0;
        $responseInfo['reset_time_in_seconds'] = time() + $retryAfter;
      } else {
        $remainingHits = $this->extractHeader($headers, 'X-RateLimit-Remaining:');
        if ($remainingHits !== false) {
          $responseInfo['remaining_hits'] = $remainingHits;
        }
        $resetTimeInSeconds = $this->extractHeader($headers, 'X-RateLimit-Reset:');
        if ($resetTimeInSeconds !== false) {
          $responseInfo['reset_time_in_seconds'] = $resetTimeInSeconds;
        }
      }
      $accessLevel = $this->extractHeader($headers, 'X-Access-Level');
      if ($accessLevel !== false) {
        $responseInfo['access_level'] = $accessLevel;
      }
    }
    return $responseInfo;
  }

  public static function extractHeader($headers, $start, $end = '\n') {
    $pattern = '/' . $start . '(.*?)' . $end . '/';
    if (preg_match($pattern, $headers, $result)) {
      return trim($result[1]);
    } else {
      return false;
    }
  }

  /**
   * Exchange request token and secret for an access token and
   * secret, to sign API calls.
   */
  public function getAccessToken($oauth_verifier = false) {
    $token = $this->consumer->getAccessToken(
      $this->access_token_url,
      null,
      $oauth_verifier
    );
    if ($token !== false) {
      $this->token = $token;
      $this->consumer->setToken(
        $token['oauth_token'],
        $token['oauth_token_secret']
      );
    }
    return $token;
  }

  /**
   * Returns the default HTTPHeaders for the OAuth client
   *
   * @return array
   */
  public function getHTTPHeaders() {
    return array(
      'User-Agent' => $this->useragent
    );
  }

  /**
   * GET wrapper for oAuthRequest.
   * @return object
   */
  public function get($url, $parameters = array()) {
    return $this->fetch($url, $parameters, OAUTH_HTTP_METHOD_GET);
  }

  /**
   * POST wrapper for oAuthRequest.
   */
  public function post($url, $parameters = array()) {
    return $this->fetch($url, $parameters, OAUTH_HTTP_METHOD_POST);
  }

  /**
   * DELETE wrapper for oAuthReqeust.
   */
  public function delete($url, $parameters = array()) {
    return $this->fetch($url, $parameters, OAUTH_HTTP_METHOD_DELETE);
  }

  /**
   * Abstracts calling OAuth::fetch
   */
  protected function fetch($url, $parameters = array(), $method = OAUTH_HTTP_METHOD_GET) {
    $result = $this->consumer->fetch(
      $this->normalizeUrl($url),
      $parameters,
      $method,
      $this->getHTTPHeaders()
    );

    if ($result === true) {
      $response = $this->consumer->getLastResponse();
      $this->currentRetries = 0;
      if ($this->format === 'json' && $this->decode_json) {
        return json_decode($response);
      }
      return $response;
    }

    else if ($this->retry) {
      if ($this->currentRetries < $this->retryAttempts) {
        $this->currentRetries++;
        $this->fetch($url, $parameters, $method);
      }
    }

    $this->currentRetries = 0;

    // Logs an error
    \Drupal::logger('D500px')->error("500px returned an error for " . $url);
  }

  /**
   * Adds on the baseurl and format extension if they don't already exist
   *
   * @param string $path
   * @return string
   */
  public function normalizeUrl($path) {
    $url =  $this->generic_url . $path;
    return $url;
  }


  /**
   * GET wrapper for get.
   * @return object
   */
  public function getPhotos($parameters = array()) {
    $photos = $this->get('photos', $parameters)->photos;
    return $this->themePhotos($photos);
  }

  public function themePhotos($photos = array()) {
    $themed_photos = NULL;
    foreach ($photos as $photo_obj) {
      $themed_photos .= $this->themePhoto($photo_obj);
    }

    return $themed_photos;
  }


  public function themePhoto($photo_obj) {
    $size = $photo_obj->images[0]->size;
    $nsfw = $photo_obj->nsfw;
    $photo_page_url = $photo_obj->url;
    $title = $photo_obj->name;
    $img_url = $photo_obj->image_url;

    $d500px_photo_sizes_array = $this->d500px_photo_get_sizes();
    $d500px_photo_size_array = $d500px_photo_sizes_array[$size];

    $attributes['height'] = $d500px_photo_size_array['height'];
    $attributes['width'] = $d500px_photo_size_array['width'];
    $attributes['class'][] = 'd500px_photo_size_'. $size;
    $attributes['class'][] = 'd500px_photo';
    $attributes['class'] = implode(' ', $attributes['class']);

    $image = array(
      '#theme' => 'image',
      '#style_name' => NULL,
      '#uri' => $img_url,
      '#alt' => $title,
      '#title' => $title,
      '#width' => $attributes['width'],
      '#height' => $attributes['height'],
      '#attributes' => array('class' => $attributes['class']),
    );

    // TODO I dont think this is the way
    return \Drupal::service('renderer')->render($image)->__toString();
  }

  private function d500px_photo_get_sizes() {
    $d500px_photo_sizes_array = array(
      1 => array('height' => 70, 'width' => 70),
      2 => array('height' => 140, 'width' => 140),
      3 => array('height' => 280, 'width' => 280),
      100 => array('height' => 100, 'width' => 100),
      200 => array('height' => 200, 'width' => 200),
      440 => array('height' => 440, 'width' => 440),
      600 => array('height' => 600, 'width' => 600),
    );

    return $d500px_photo_sizes_array;
  }

}
