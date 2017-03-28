<?php

/**
 * @file
 * Contains Drupal\D500px\D500pxIntegration.
 */

namespace Drupal\D500px;

use Drupal\Core\Config\ConfigFactory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

/**
 * Primary 500px API implementation class.
 *
 * @package Drupal\D500px
 */
class D500pxIntegration {

  /**
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * @var string
   */
  protected $request_token_url;

  /**
   * @var string
   */
  protected $authorize_url;

  /**
   * @var string
   */
  protected $authenticate_url;

  /**
   * @var string
   */
  protected $access_token_url;

  /**
   * @var string
   */
  protected $generic_url;

  /**
   * @var string
   */
  public $website_url;


  /**
   * Constructor for the 500px class.
   */
  public function __construct(ConfigFactory $config_factory) {
    // Get the config.
    $this->config = $config_factory->get('d500px.settings');

    // Add 500px config.
    $this->request_token_url = $this->config->get('api_uri') . '/v1/oauth/request_token';
    $this->authorize_url = $this->config->get('api_uri') . '/v1/oauth/authorize';
    $this->authenticate_url = $this->config->get('api_uri') . '/v1/oauth/authenticate';
    $this->access_token_url = $this->config->get('api_uri') . '/v1/oauth/access_token';
    $this->generic_url = $this->config->get('api_uri') . '/v1/';
    $this->website_url = $this->config->get('host_uri');

    // Guzzle oAuth client.
    $stack = HandlerStack::create();

    $middleware = new Oauth1([
      'consumer_key'      => $this->config->get('oauth_consumer_key'),
      'consumer_secret'   => $this->config->get('oauth_consumer_secret'),

      // TODO investigate how to fetch tokens from 500px.
      // Until then set the token_secret to null.
      'token_secret'      => ''
    ]);

    $stack->push($middleware);

    $this->client = new Client([
      'base_uri' => $this->generic_url,
      'handler' => $stack,
      'auth' => 'oauth',
      //'debug' => true
    ]);

  }

  /**
   * Generic method to perform a request to 500px servers.
   *
   * @param $url
   * @param array $parameters
   * @param string $method
   * @return mixed
   */
  public function requestD500px($url, $parameters = array(), $method = 'GET') {
    $response = $this->client->request($method, $url, ['query' => $parameters]);

    // TODO Add some checking.
    $body = $response->getBody();

    // TODO Add some checking.
    return json_decode((string) $body);
  }

}
