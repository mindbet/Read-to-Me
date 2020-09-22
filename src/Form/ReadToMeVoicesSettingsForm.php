<?php

namespace Drupal\read_to_me\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManager;

/**
 * Configure example settings for this site.
 */
class ReadToMeVoicesSettingsForm extends ConfigFormBase {

  /**
   * Returns formid.
   *
   * @return formid
   */
  public function getFormId() {
    return 'read_to_me_voices_admin_settings';

  }//end getFormId()

  /**
   * Returns key to module settings.
   *
   * @return settings
   */
  protected function getEditableConfigNames() {
    return ['read_to_me.settings'];

  }//end getEditableConfigNames()

  /**
   * Custom form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

  $awsAccessKeyId = \Drupal::config('read_to_me.settings')->get('aws_access_key_id');
  $awsSecretKey = \Drupal::config('read_to_me.settings')->get('aws_secret_access_key');

  $credentials    = new \Aws\Credentials\Credentials($awsAccessKeyId, $awsSecretKey);
  $client         = new \Aws\Polly\PollyClient([
    'version'     => '2016-06-10',
    'credentials' => $credentials,
    'region'      => 'us-east-1',
  ]);


  $langcodes = \Drupal::languageManager()->getLanguages();
  $langcodesList = array_keys($langcodes);

  dpm($langcodesList);

  $result = $client->describeVoices([
    'Engine' => 'standard',
    'IncludeAdditionalLanguageCodes' => true,
    'LanguageCode' => 'en-US',
  ]);

  dpm($result);

    return parent::buildForm($form, $form_state);

  }//end buildForm()

  /**
   * Validate the form.
   *
   * @return errors
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }//end validateForm()

  /**
   * Submits the form.
   *
   * @return form
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve and set the configuration.
    $voice_generation_value = ($form_state->getValue('voice_generation'));
    $voice_style_value = ($form_state->getValue('voice_style'));
    if ((!$form_state->getValue('voice_selection') === 'Joanna') and
      (!$form_state->getValue('voice_selection') === 'Matthew'))
    {
      unset($voice_generation_value);
      unset($voice_style_value);
    }



    $this->configFactory->getEditable('read_to_me.settings')
      ->set('key', $form_state->getValue('key'))
      ->set('aws_access_key_id', $form_state->getValue('aws_access_key_id'))
      ->set('aws_secret_access_key', $form_state->getValue('aws_secret_access_key'))
      ->set('voice_selection', $form_state->getValue('voice_selection'))
      ->set('voice_generation', $voice_generation_value)
      ->set('voice_style', $voice_style_value)
      ->save();

    parent::submitForm($form, $form_state);

  }//end submitForm()
