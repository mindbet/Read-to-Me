<?php

namespace Drupal\read_to_me\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class ReadToMeSettingsForm extends ConfigFormBase {

  /**
   * Returns formid.
   *
   * @return formid
   *   Returns formid.
   */
  public function getFormId() {
    return 'read_to_me_admin_settings';

    // End getFormId()
  }

  /**
   * Returns key to module settings.
   *
   * @return settings
   *   Returns key to module settings.
   */
  protected function getEditableConfigNames() {
    return ['read_to_me.settings'];

    // End getEditableConfigNames()
  }

  /**
   * Custom form.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   State of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('read_to_me.settings');

    $form['aws_access_key_id'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('AWS Access Key ID'),
      '#description'   => $this->t('Your AWS access key ID.'),
      '#default_value' => $config->get('aws_access_key_id'),
    ];

    $form['aws_secret_access_key'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('AWS Secret Access Key'),
      '#description'   => $this->t('Your AWS secret access key.'),
      '#default_value' => $config->get('aws_secret_access_key'),
    ];

    $form['s3bucket'] = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('S3 Bucket'),
      '#description'   => $this->t('Cloud storage for generated files.'),
      '#default_value' => $config->get('s3bucket'),
    ];

    return parent::buildForm($form, $form_state);
    // End buildForm()
  }

  /**
   * Validate the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // End validateForm()
  }

  /**
   * Submits the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve and set the configuration.
    $this->configFactory->getEditable('read_to_me.settings')
      ->set('aws_access_key_id', $form_state->getValue('aws_access_key_id'))
      ->set('aws_secret_access_key', $form_state->getValue('aws_secret_access_key'))
      ->set('s3bucket', $form_state->getValue('s3bucket'))
      ->save();

    parent::submitForm($form, $form_state);

    // End submitForm()
  }

  // End class.
}
