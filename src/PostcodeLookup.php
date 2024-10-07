<?php

namespace Drupal\webform_postcode_austria;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Database;
use PhpOffice\PhpSpreadsheet\Reader;

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
    $last_imported_file = \Drupal::config('webform_postcode_austria.settings')->get('last_imported_file');
    if (!$last_imported_file) {
      webform_postcode_austria_import_data();
    }

    $connection = Database::getConnection();
    $query = $connection->select('webform_postcode_austria', 'c')
      ->fields('c')
      ->execute();

    $this->data = $query->fetchAllAssoc('plz'); // Fetch all results as an associative array keyed by 'id'.
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

}
