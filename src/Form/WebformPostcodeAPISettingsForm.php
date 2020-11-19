<?php

namespace Drupal\webform_postcodeapi\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a setting form for the Webform Postcode API module.
 */
class WebformPostcodeAPISettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['webform_postcodeapi.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webform_postcodeapi_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('webform_postcodeapi.settings');

    $form['postcodenlapi_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postcode NL API URL'),
      '#default_value' => $config->get('postcodenlapi_url'),
      '#required' => TRUE,
    ];
    $form['postcodenlapi_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postcode NL API Key'),
      '#default_value' => $config->get('postcodenlapi_key'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('webform_postcodeapi.settings')
      ->set('postcodenlapi_key', $form_state->getValue('postcodenlapi_key'))
      ->set('postcodenlapi_url', $form_state->getValue('postcodenlapi_url'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
