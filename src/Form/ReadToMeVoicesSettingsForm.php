<?php

namespace Drupal\read_to_me\Form;

use Aws\Polly\PollyClient;
use Aws\Credentials\Credentials;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Configure example settings for this site.
 */
class ReadToMeVoicesSettingsForm extends ConfigFormBase {

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;


  /**
   * ReadToMeVoicesSettingsForm constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager')
      );
  }


  /**
   *
   * @return formid
   * Returns formid.
   */
  public function getFormId() {
    return 'read_to_me_voices_admin_settings';
  //end getFormId()
  }

  /**
   *
   * @return settings
   * Returns key to module settings.
   */
  protected function getEditableConfigNames() {
    return ['read_to_me.settings'];
  //end getEditableConfigNames()
  }

  /**
   * Custom form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('read_to_me.settings');

    $awsAccessKeyId = $this->config('read_to_me.settings')->get('aws_access_key_id');
    $awsSecretKey = $this->config('read_to_me.settings')->get('aws_secret_access_key');

    $credentials = new Credentials($awsAccessKeyId, $awsSecretKey);
    $client = new PollyClient([
      'version' => '2016-06-10',
      'credentials' => $credentials,
      'region' => 'us-east-1',
    ]);


//    $langcodes = \Drupal::languageManager()->getLanguages();
    $langcodes = $this->languageManager->getLanguages();

    $langcodesList = array_keys($langcodes);

//    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $language = $this->languageManager->getCurrentLanguage()->getId();


    $languagecrosswalk = [
      'ar' => 'arb',
      '' => 'cmn-CN',
      'da' => 'da-DK',
      'nl' => 'nl-NL',
      'en' => 'en-AU',
      'en' => 'en-GB',
      'en' => 'en-IN',
      'en' => 'en-US',
      'en' => 'en-GB-WLS',
      'fr' => 'fr-CA',
      'fr' => 'fr-FR',
      'de' => 'de-DE',
      'hi' => 'hi-IN',
      'is' => 'is-IS',
      'es' => 'es-ES',
      'es' => 'es-MX',
      'es' => 'es-US',
      'it' => 'it-IT',
      'ja' => 'ja-JP',
      'ko' => 'ko-KR',
      'nb' => 'nb-NO',
      'pl' => 'pl-PL',
      'pt' => 'pt-BR',
      'pt' => 'pt-PT',
      'ro' => 'ro-RO',
      'ru' => 'ru-RU',
      'sv' => 'sv-SE',
      'tr' => 'tr-TR',
      'zh-hans' => 'tr-TR',
      'zh-hant' => 'tr-TR',
      '' => '',
    ];

    dpm($langcodesList);

    dpm($language);



    $result = $client->describeVoices([
      'Engine' => 'standard',
      'IncludeAdditionalLanguageCodes' => TRUE,
      'LanguageCode' => 'da-DK',
    ]);

    dpm($result);

    $form['voice_selection'] = [
      '#type'          => 'radios',
      '#required'      => TRUE,
      '#title'         => $this->t('Voice'),
      '#description'   => $this->t('The synthetic voice to use for generation.'),
      '#options' => [
        'Ivy' => $this->t('Ivy <span>[Female, child, standard]</span>'),
        'Joanna' => $this->t('Joanna <span>[Female, neural or standard, conversational or newscaster available]</span>'),
        'Kendra' => $this->t('Kendra <span>[Female, standard]</span>'),
        'Kimberly' => $this->t('Kimberly <span>[Female, standard]</span>'),
        'Salli' => $this->t('Salli <span>[Female, standard]</span>'),
        'Joey' => $this
          ->t('Joey <span>[Male, standard]</span>'),
        'Justin' => $this
          ->t('Justin <span>[Male, child]</span>'),
        'Kevin' => $this
          ->t('Kevin <span>[Male, child, neural only]</span>'),
        'Matthew' => $this
          ->t('Matthew <span>[Male, neural or standard, conversational or newscaster available]</span>'),
      ],
      '#default_value' => $config->get('voice_selection'),

    ];

    $form['voice_generation'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Generation method'),
      '#description'   => $this->t('Neural (more life-like) is $16 per million characters. Standard method is $4 per million characters.'),
      '#options' => [
        'neural' => $this->t('Neural'),
        'standard' => $this->t('Standard'),
      ],
      '#default_value' => $config->get('voice_generation'),
      '#states' => [
        'visible' => [
          [':input[name="voice_selection"]' => ['value' => 'Joanna']],
          [':input[name="voice_selection"]' => ['value' => 'Matthew']],
        ],
      ],
    ];

    $form['voice_style'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Voice style'),
      '#options' => [
        'newscaster' => $this
          ->t('Newscaster'),
        'conversational' => $this
          ->t('Conversational'),
        'nostyle' => $this
          ->t('No applied style'),
      ],
      '#default_value' => $config->get('voice_style'),
      '#states' => [
        'visible' => [
          [
            ':input[name="voice_selection"]' => ['value' => 'Joanna'],
            ':input[name="voice_generation"]' => ['value' => 'neural'],
          ],
          [
            ':input[name="voice_selection"]' => ['value' => 'Matthew'],
            ':input[name="voice_generation"]' => ['value' => 'neural'],
          ],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);

  //end buildForm()
  }

  /**
   * Validate the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  //end validateForm()s
  }

  /**
   * Submits the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve and set the configuration.
    $voice_generation_value = ($form_state->getValue('voice_generation'));
    $voice_style_value = ($form_state->getValue('voice_style'));
    if ((!$form_state->getValue('voice_selection') === 'Joanna') and
      (!$form_state->getValue('voice_selection') === 'Matthew')) {
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
}
