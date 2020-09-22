<?php

namespace Drupal\read_to_me\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManager;

/**
 * Configure example settings for this site.
 */
class ReadToMeSettingsForm extends ConfigFormBase {

  /**
   * Returns formid.
   *
   * @return formid
   */
  public function getFormId() {
    return 'read_to_me_admin_settings';

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
    $config             = $this->config('read_to_me.settings');


    $form['aws_access_key_id']        = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('AWS Access Key ID'),
      '#description'   => $this->t('Your AWS access key ID.'),
      '#default_value' => $config->get('aws_access_key_id'),
    ];

    $form['aws_secret_access_key']        = [
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#title'         => $this->t('AWS Secret Access Key'),
      '#description'   => $this->t('Your AWS secret access key.'),
      '#default_value' => $config->get('aws_secret_access_key'),
    ];



    $form['voice_selection']        = [
      '#type'          => 'radios',
      '#required'      => TRUE,
      '#title'         => $this->t('Voice'),
      '#description'   => $this->t('The synthetic voice to use for generation.'),
      '#options' => array(
        'Ivy' => $this
          ->t('Ivy <span>[Female, child, standard]</span>'),
        'Joanna' => $this
          ->t('Joanna <span>[Female, neural or standard, conversational or newscaster available]</span>'),
        'Kendra' => $this
          ->t('Kendra <span>[Female, standard]</span>'),
        'Kimberly' => $this
          ->t('Kimberly <span>[Female, standard]</span>'),
        'Salli' => $this
          ->t('Salli <span>[Female, standard]</span>'),
        'Joey' => $this
          ->t('Joey <span>[Male, standard]</span>'),
        'Justin' => $this
          ->t('Justin <span>[Male, child]</span>'),
        'Kevin' => $this
          ->t('Kevin <span>[Male, child, neural only]</span>'),
        'Matthew' => $this
          ->t('Matthew <span>[Male, neural or standard, conversational or newscaster available]</span>'),
      ),
      '#default_value' => $config->get('voice_selection'),

    ];

    $form['voice_generation']        = [
      '#type'          => 'radios',
      '#title'         => $this->t('Generation method'),
      '#description'   => $this->t('Neural (more life-like) is $ per __ characters. Standard method is $ per __ characters. '),
      '#options' => array(
        'neural' => $this
          ->t('Neural'),
        'standard' => $this
          ->t('Standard'),
      ),
      '#default_value' => $config->get('voice_generation'),
      '#states' => array(
        'visible' => array(
          array(':input[name="voice_selection"]' => array('value' => 'Joanna')),
          array(':input[name="voice_selection"]' => array('value' => 'Matthew')),
        ),
      )
    ];

    $form['voice_style']        = [
      '#type'          => 'radios',
      '#title'         => $this->t('Voice style'),
      '#description'   => $this->t(''),
      '#options' => array(
        'newscaster' => $this
          ->t('Newscaster'),
        'conversational' => $this
          ->t('Conversational'),
        'nostyle' => $this
          ->t('No applied style'),
      ),
      '#default_value' => $config->get('voice_style'),
      '#states' => array(
        'visible' => array(
          array(
            ':input[name="voice_selection"]' => array('value' => 'Joanna'),
            ':input[name="voice_generation"]' => array('value' => 'neural'),
          ),
          array(
            ':input[name="voice_selection"]' => array('value' => 'Matthew'),
            ':input[name="voice_generation"]' => array('value' => 'neural'),
          ),
        ),
      )
    ];


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
      ->set('aws_access_key_id', $form_state->getValue('aws_access_key_id'))
      ->set('aws_secret_access_key', $form_state->getValue('aws_secret_access_key'))
      ->set('voice_selection', $form_state->getValue('voice_selection'))
      ->set('voice_generation', $voice_generation_value)
      ->set('voice_style', $voice_style_value)
      ->save();

    parent::submitForm($form, $form_state);

  }//end submitForm()

}//end class
