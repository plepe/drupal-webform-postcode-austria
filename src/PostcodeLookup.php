<?php

namespace Drupal\webform_postcode_austria;

use Drupal\Core\Database\Database;

/**
 * Perform postcode lookup.
 */
class PostcodeLookup {
  public function getPostcode(string $postcode) {
    if (!$postcode) {
      return null;
    }

    $connection = Database::getConnection();
    $query = $connection->select('webform_postcode_austria', 'c')
      ->fields('c')
      ->condition('plz', $postcode)
      ->execute();

    return $query->fetchAssoc() ?: null;
  }

}
