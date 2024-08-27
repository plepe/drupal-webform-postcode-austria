<?php

namespace Drupal\webform_postcode_austria\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'webform_postcode_austria' element.
 *
 * @WebformElement(
 *   id = "webform_postcode_austria",
 *   label = @Translation("Webform Postcode API"),
 *   description = @Translation("Provides advanced element for upon entering postal code and house number automatically retrieve street name and town data."),
 *   category = @Translation("Composite elements"),
 *   composite = TRUE,
 *   multiline = TRUE,
 *   states_wrapper = TRUE
 * )
 *
 * @see \Drupal\webform_postcode_austria\Element\WebformPostcodeAustria
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class WebformPostcodeAustria extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);

    $lines = [];
    $lines[] = ($value['street'] ?: '') .
      ($value['house_number'] ? ' ' . $value['house_number'] : '') .
      ($value['house_number_ext'] ? ' ' . $value['house_number_ext'] : '');
    $lines[] = ($value['zip_code'] ?: '') .
      ($value['town'] ? ' ' . $value['town'] : '');
    return $lines;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Disable element settings, since composite element configuration is
    // opinionated.
    $form['composite']['#access'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element, $webform_submission);

    if (!empty($element['#required'])) {
      $element['#zip_code__required'] = TRUE;
      $element['#house_number__required'] = TRUE;
      $element['#street__required'] = TRUE;
      $element['#town__required'] = TRUE;
      $element['#webform_composite_elements']['zip_code']['#required'] = $element['#required'];
      $element['#webform_composite_elements']['house_number']['#required'] = $element['#required'];
      $element['#webform_composite_elements']['street']['#required'] = $element['#required'];
      $element['#webform_composite_elements']['town']['#required'] = $element['#required'];
    }
  }

}
