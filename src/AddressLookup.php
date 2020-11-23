<?php

namespace Drupal\webform_postcodeapi;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\ClientFactory;
use Drupal\webform_postcodeapi\Classes\FormValidation;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

/**
 * Performs address lookups.
 */
class AddressLookup {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The HTTP client factory to fetch the remote data with.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * Webform Postcode API configuration.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new AddressLookup.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Http\ClientFactory $client_factory
   *   The HTTP client factory to fetch the RSS feed data with.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct(CacheBackendInterface $cache_backend, ClientFactory $client_factory, ConfigFactory $config_factory) {
    $this->cacheBackend = $cache_backend;
    $this->httpClientFactory = $client_factory;
    $this->config = $config_factory->get('webform_postcodeapi.settings');
  }

  /**
   * Retrieves an address based on zipcode and house number.
   *
   * @param string $zipcode
   *   The zipcode.
   * @param string $houseNumber
   *   The house number.
   *
   * @return array|null
   *   An address array when available, or NULL.
   */
  public function getAddress(string $zipcode, string $houseNumber) {
    $api_url = $this->config->get('postcodenlapi_url');
    $api_key = $this->config->get('postcodenlapi_key');

    if (empty($zipcode) || empty($houseNumber) || empty($api_key)) {
      return NULL;
    }

    if (!FormValidation::isValidPostalcode($zipcode) || !FormValidation::isValidHouseNumber($houseNumber)) {
      return NULL;
    }

    $cache_id = implode(':', ['webform_postcodeapi', $zipcode, $houseNumber]);
    $cache = $this->cacheBackend->get($cache_id);
    if ($cache) {
      return Json::decode($cache->data);
    }
    $zipcode = preg_replace('/\s/', '', $zipcode);
    $url = implode('/', [$api_url, $zipcode, $houseNumber]);
    $request = new Request('GET', $url);

    try {
      $response = $this->httpClientFactory->fromOptions([
        'timeout' => 10,
        'headers' => [
          'x-api-key' => $api_key,
        ],
      ])->send($request);

      $response_body = (string) $response->getBody();
      $this->cacheBackend->set($cache_id, $response_body, strtotime('+1 hour'));
      return Json::decode($response_body);
    }
    catch (RequestException $e) {
      watchdog_exception('webform_postcodeapi', $e);
      return NULL;
    }
  }

}
