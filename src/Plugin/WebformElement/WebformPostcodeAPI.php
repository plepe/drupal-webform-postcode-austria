<?php

namespace Drupal\webform_postcodeapi\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'webform_postcodeapi' element.
 *
 * @WebformElement(
 *   id = "webform_postcodeapi",
 *   label = @Translation("Webform Postcode API"),
 *   description = @Translation("Provides advanced element for upon entering postal code and house number automatically retrieve street name and city data."),
 *   category = @Translation("Composite elements"),
 *   composite = TRUE,
 *   multiline = TRUE,
 *   states_wrapper = TRUE
 * )
 *
 * @see \Drupal\webform_postcodeapi\Element\WebformPostcodeAPI
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class WebformPostcodeAPI extends WebformCompositeBase {

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
      ($value['house_number_addition'] ? ' ' . $value['house_number_addition'] : '');
    $lines[] = ($value['postal_code'] ?: '') .
      ($value['city'] ? ' ' . $value['city'] : '');
    return $lines;
  }

}
