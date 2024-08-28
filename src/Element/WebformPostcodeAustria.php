<?php

namespace Drupal\webform_postcode_austria\Element;

use Drupal\webform\Element\WebformCompositeBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform_postcode_austria\Classes\FormValidation;

/**
 * Provides a 'webform_postcode_austria' composite webform element.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("webform_postcode_austria")
 */
class WebformPostcodeAustria extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + [
      '#theme' => 'webform_postcode_austria',
      '#element_validate' => [
        [static::class, 'validateWebformPostcodeAustria'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    $elements = [];
    $elements['plz'] = [
      '#type' => 'textfield',
      '#title' => t('Postal code'),
      '#attributes' => ['class' => ['js-webform-postcode-austria-plz']],
    ];
    $elements['ort'] = [
      '#type' => 'textfield',
      '#title' => t('City/Town'),
      '#maxlength' => 60,
      '#after_build' => [[static::class, 'setDisabledState']],
      '#attributes' => ['class' => ['js-webform-postcode-austria-ort']],
    ];
    $elements['bundesland'] = [
      '#type' => 'textfield',
      '#title' => t('Bundesland'),
      '#maxlength' => 60,
      '#after_build' => [[static::class, 'setDisabledState']],
      '#attributes' => ['class' => ['js-webform-postcode-austria-bundesland']],
    ];

    if (empty($element['#required'])) {
      $required_composite_elements = [
        'plz',
        'ort',
        'bundesland',
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
      [':input[name="' . $composite_name . '[plz]"]' => ['empty' => TRUE]],
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
      [':input[name="' . $composite_name . '[plz]"]' => ['empty' => FALSE]],
      [':input[name="' . $composite_name . '[ort]"]' => ['empty' => FALSE]],
      [':input[name="' . $composite_name . '[bundesland]"]' => ['empty' => FALSE]],
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
  public static function validateWebformPostcodeAustria(array &$element, FormStateInterface $form_state) {
    // phpcs:enable
    $required_composite_elements = [
      'plz',
      'ort',
      'bundesland',
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

    $plz = $element['plz']['#value'];
    if (!FormValidation::isValidPostalCode($plz)) {
      $form_state->setError($element['plz'], t('Postal code must consist of 4 numbers.'));
    }
  }

}
