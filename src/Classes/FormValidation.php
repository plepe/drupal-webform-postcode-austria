<?php

namespace Drupal\webform_postcodeapi\Classes;

/**
 * Form validation functions.
 */
class FormValidation {

  /**
   * Validates input is a postal code.
   */
  public static function isValidPostalCode($postal_code) {
    if (empty($postal_code)) {
      return TRUE;
    }
    return (bool) preg_match('~\A[1-9]\d{3} ?[a-zA-Z]{2}\z~', $postal_code);
  }

  /**
   * Validates input is a house number.
   */
  public static function isValidHouseNumber($house_number) {
    if (empty($house_number)) {
      return TRUE;
    }
    return (bool) preg_match('~^[0-9]+$~', $house_number);
  }

  /**
   * Validates input is a house number addition.
   */
  public static function isValidHouseNumberAddition($house_number_addition) {
    if (empty($house_number_addition)) {
      return TRUE;
    }
    return (bool) preg_match('~^[0-9a-z]{1,4}$~i', $house_number_addition);
  }

}
