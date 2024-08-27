<?php

namespace Drupal\webform_postcode_austria\Classes;

/**
 * Form validation functions.
 */
class FormValidation {

  /**
   * Validates input is a postal code.
   */
  public static function isValidPostalCode($zip_code) {
    if (empty($zip_code)) {
      return TRUE;
    }
    return (bool) preg_match('/^[1-9][\d]{3}(?!sa|sd|ss)[a-z]{2}$/i', $zip_code);
  }

}
