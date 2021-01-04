<?php

namespace Drupal\webform_postcodeapi\Classes;

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
  public static function isValidHouseNumberAddition($house_number_ext) {
    if (empty($house_number_ext)) {
      return TRUE;
    }
    return (bool) preg_match('~^[0-9a-z]{1,4}$~i', $house_number_ext);
  }

}
