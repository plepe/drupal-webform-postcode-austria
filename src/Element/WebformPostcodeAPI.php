<?php

namespace Drupal\webform_postcodeapi\Element;

use Drupal\webform\Element\WebformCompositeBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform_postcodeapi\Classes\FormValidation;

/**
 * Provides a 'webform_postcodeapi' composite webform element.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("webform_postcodeapi")
 */
class WebformPostcodeAPI extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + [
      '#theme' => 'webform_postcodeapi',
      '#element_validate' => [
        [static::class, 'validateWebformPostcodeAPI'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    $elements = [];
    $elements['zip_code'] = [
      '#type' => 'textfield',
      '#title' => t('Zip code'),
      '#attributes' => ['class' => ['js-webform-postcodeapi-zip-code']],
    ];
    $elements['house_number'] = [
      '#type' => 'number',
      '#title' => t('House number'),
      '#maxlength' => 12,
      '#attributes' => ['class' => ['js-webform-postcodeapi-house-number']],
    ];
    $elements['house_number_ext'] = [
      '#type' => 'textfield',
      '#title' => t('House number addition'),
      '#maxlength' => 8,
    ];
    $elements['street'] = [
      '#type' => 'textfield',
      '#title' => t('Street'),
      '#after_build' => [[static::class, 'setDisabledState']],
      '#attributes' => ['class' => ['js-webform-postcodeapi-street']],
    ];
    $elements['town'] = [
      '#type' => 'textfield',
      '#title' => t('City/Town'),
      '#maxlength' => 60,
      '#after_build' => [[static::class, 'setDisabledState']],
      '#attributes' => ['class' => ['js-webform-postcodeapi-town']],
    ];

    if (empty($element['#required'])) {
      $required_composite_elements = [
        'zip_code',
        'house_number',
        'street',
        'town',
      ];
      foreach ($required_composite_elements as $required_composite_element) {
        $elements[$required_composite_element]['#after_build'][] = [static::class, 'setRequiredState'];
      }
    }

    return $elements;
  }

  /**
   * Performs the after_build callback: set disabled state.
   */
  public static function setDisabledState(array $element, FormStateInterface $form_state) {
    // Add #states targeting the specific element and table row.
    preg_match('/^(.+)\[[^]]+]$/', $element['#name'], $match);
    $composite_name = $match[1];
    $element['#states']['disabled'] = [
      [':input[name="' . $composite_name . '[zip_code]"]' => ['empty' => TRUE]],
      [':input[name="' . $composite_name . '[house_number]"]' => ['empty' => TRUE]],
    ];
    // Add .js-form-wrapper to wrapper (ie td) to prevent #states API from
    // disabling the entire table row when this element is disabled.
    $element['#wrapper_attributes']['class'][] = 'js-form-wrapper';
    return $element;
  }

  /**
   * Performs the after_build callback: set required state.
   */
  public static function setRequiredState(array $element, FormStateInterface $form_state) {
    preg_match('/^(.+)\[[^]]+]$/', $element['#name'], $match);
    $composite_name = $match[1];
    $element['#states']['required'] = [
      [':input[name="' . $composite_name . '[zip_code]"]' => ['empty' => FALSE]],
      [':input[name="' . $composite_name . '[house_number]"]' => ['empty' => FALSE]],
      [':input[name="' . $composite_name . '[street]"]' => ['empty' => FALSE]],
      [':input[name="' . $composite_name . '[town]"]' => ['empty' => FALSE]],
    ];
    return $element;
  }

  /**
   * Validate the form element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form for the form this element belongs to.
   */
  // phpcs:disable
  public static function validateWebformPostcodeAPI(array &$element, FormStateInterface $form_state) {
    // phpcs:enable
    $required_composite_elements = [
      'zip_code',
      'house_number',
      'street',
      'town',
    ];
    $element_has_data = FALSE;
    foreach ($required_composite_elements as $required_composite_element) {
      if (!empty($element[$required_composite_element]['#value'])) {
        $element_has_data = TRUE;
        break;
      }
    }

    if ($element_has_data) {
      foreach ($required_composite_elements as $required_composite_element) {
        if (empty($element[$required_composite_element]['#value'])) {
          $form_state->setError($element[$required_composite_element], t('@composite_element_label is required', [
            '@composite_element_label' => $element[$required_composite_element]['#title'],
          ]));
        }
      }
    }

    $zip_code = $element['zip_code']['#value'];
    $house_number = $element['house_number']['#value'];
    $house_number_ext = $element['house_number_ext']['#value'];
    if (!FormValidation::isValidPostalCode($zip_code)) {
      $form_state->setError($element['zip_code'], t('Zip code must consist of 4 numbers + 2 letters without spaces.'));
    }

    if (!FormValidation::isValidHouseNumber($house_number)) {
      $form_state->setError($element['house_number'], t('The house number is invalid. Please use house number addition for additions to your house number.'));
    }

    if (!FormValidation::isValidHouseNumberAddition($house_number_ext)) {
      $form_state->setError($element['house_number_ext'], t('The house number addition is invalid, please use only numbers and/or letters.'));
    }
  }

}
