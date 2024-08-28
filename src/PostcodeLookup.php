<?php

namespace Drupal\webform_postcode_austria;

/**
 * Perform postcode lookup.
 */
class PostcodeLookup {
  protected $data;

  public function loadData () {
    $contents = file_get_contents('https://data.rtr.at/api/v1/tables/plz.json');
    $contents = json_decode($contents, 1);

    $data = [];

    foreach ($contents['data'] as $item) {
      $data[$item['plz']] = $item;
    }

    $this->data = $data;
  }

  public function getPostcode(string $postcode) {
    $this->loadData();

    return $this->data[$postcode];
  }
}
