<?php

namespace Drupal\webform_postcode_austria;

use Drupal\Component\Serialization\Json;

/**
 * Perform postcode lookup.
 */
class PostcodeLookup {
  protected $data;

  public function loadData () {
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
      $data[$item['plz']] = $item;
    }

    $this->data = $data;
  }

  public function getPostcode(string $postcode) {
    $this->loadData();

    if (!$this->data) {
      return null;
    }

    if (!array_key_exists($postcode, $this->data)) {
      return null;
    }

    return $this->data[$postcode];
  }
}
