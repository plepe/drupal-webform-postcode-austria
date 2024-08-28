<?php

namespace Drupal\webform_postcode_austria;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Perform postcode lookup.
 */
class PostcodeLookup {
  protected $data;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Constructs a new PostcodeLookup.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   */
  public function __construct(CacheBackendInterface $cache_backend) {
    $this->cacheBackend = $cache_backend;
  }

  public function loadData () {
    $cache = $this->cacheBackend->get('webform_postcode_austria:data');
    if ($cache) {
      $this->data = $cache->data;
      return;
    }

    $contents = file_get_contents('https://data.rtr.at/api/v1/tables/plz.json');
    if ($contents === false) {
      watchdog_exception('webform_postcode_austria', new \Exception('Error downloading PLZ JSON: "' . error_get_last()['message'] . '"'));
      return;
    }

    $contents = Json::decode($contents);
    if ($contents === null) {
      watchdog_exception('webform_postcode_austria', new \Exception('Error parsing PLZ JSON: "' . json_last_error_msg() . '"'));
      return;
    }

    $data = [];

    foreach ($contents['data'] as $item) {
      $data[$item['plz']] = $this->convert($item);
    }

    $this->data = $data;
    $this->cacheBackend->set('webform_postcode_austria:data', $data, strtotime('+24 hour'));
  }

  public function getPostcode(string $postcode) {
    if (!$this->data) {
      $this->loadData();
    }

    if (!$this->data) {
      return null;
    }

    if (!array_key_exists($postcode, $this->data)) {
      return null;
    }

    return $this->data[$postcode];
  }

  public function convert ($item) {
    $bundeslandMapping = [
      'W' => 'Wien',
      'N' => 'Niederösterreich',
      'S' => 'Salzburg',
      'V' => 'Vorarlberg',
      'St' => 'Steiermark',
      'K' => 'Kärnten',
      'B' => 'Burgenland',
      'O' => 'Oberösterreich',
      'T' => 'Tirol',
    ];

    $result = [
      'plz' => $item['plz'],
      'ort' => $item['bundesland'] === 'W' ? $item['bezirk'] : $item['ort'],
      'bundesland' => $bundeslandMapping[$item['bundesland']],
    ];

    return $result;
  }

}
