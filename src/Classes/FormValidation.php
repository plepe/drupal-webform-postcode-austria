<?php
namespace Drupal\webform_postcodeapi\Classes;

/**
 * Form validation functions.
 */
class FormValidation {
  public static function isValidPostalCode($postal_code) {
    if (empty($postal_code)) {
      return true;
    }
    return (bool) preg_match('~\A[1-9]\d{3} ?[a-zA-Z]{2}\z~', $postal_code);
  }
  
  public static function isValidHouseNumber($house_number) {
    if (empty($house_number)) {
      return true;
    }
    return (bool) preg_match('~^[0-9]+$~', $house_number);
  }
  
  public static function isValidHouseNumberAddition($house_number_addition) {
    if (empty($house_number_addition)) {
      return true;
    }
    return (bool) preg_match('~^[0-9a-z]{1,4}$~i', $house_number_addition);
  }
}
