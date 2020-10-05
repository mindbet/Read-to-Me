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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a ReadToMeVoicesSettingsForm object.
   *
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
   * Supplies form id.
   *
   * @return formid
   *   Returns formid.
   */
  public function getFormId() {
    return 'read_to_me_voices_admin_settings';
    // End getFormId()
  }

  /**
   * Function needed to edit config settings.
   *
   * @return settings
   *   Returns key to module settings.
   */
  protected function getEditableConfigNames() {
    return ['read_to_me.settings'];
    // End getEditableConfigNames()
  }

  /**
   * The form to collect AWS credentials.
   *
   * @param array $form
   *   Custom form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The contents of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('read_to_me.settings');

    // Load AWS keys from config.
    $awsAccessKeyId = $this->config('read_to_me.settings')->get('aws_access_key_id');
    $awsSecretKey = $this->config('read_to_me.settings')->get('aws_secret_access_key');

    $credentials = new Credentials($awsAccessKeyId, $awsSecretKey);
    $client = new PollyClient([
      'version' => '2016-06-10',
      'credentials' => $credentials,
      'region' => 'us-east-1',
    ]);

    // Get current language from Drupal site.
    $language = $this->languageManager->getCurrentLanguage()->getId();

    // If not English, see if Polly has voices available.
    if ($language !== 'en') {
      $languagecrosswalk = [
        'arb' => ['code' => 'ar', 'langname' => 'Arabic'],
        'da-DK' => ['code' => 'da', 'langname' => 'Danish'],
        'nl-NL' => ['code' => 'nl', 'langname' => 'Dutch'],
        'en-US' => ['code' => 'en', 'langname' => 'English, US'],
        'en-AU' => ['code' => 'en', 'langname' => 'English, Australian'],
        'en-GB' => ['code' => 'en', 'langname' => 'English, British'],
        'en-IN' => ['code' => 'en', 'langname' => 'English, Indian'],
        'en-GB-WLS' => ['code' => 'en', 'langname' => 'English, Welsh'],
        'fr-FR' => ['code' => 'fr', 'langname' => 'French'],
        'fr-CA' => ['code' => 'fr', 'langname' => 'French, Canadian'],
        'de-DE' => ['code' => 'de', 'langname' => 'German'],
        'hi-IN' => ['code' => 'hi', 'langname' => 'Hindi'],
        'is-IS' => ['code' => 'is', 'langname' => 'Icelandic'],
        'it-IT' => ['code' => 'it', 'langname' => 'Italian'],
        'ja-JP' => ['code' => 'ja', 'langname' => 'Japanese'],
        'ko-KR' => ['code' => 'ko', 'langname' => 'Korean'],
        'nb-NO' => ['code' => 'nb', 'langname' => 'Norwegian'],
        'pl-PL' => ['code' => 'pl', 'langname' => 'Polish'],
        'pt-BR' => ['code' => 'pt-br', 'langname' => 'Portuguese, Brazilian'],
        'pt-PT' => ['code' => 'pt-pt', 'langname' => 'Portuguese, European'],
        'ro-RO' => ['code' => 'ro', 'langname' => 'Romanian'],
        'ru-RU' => ['code' => 'ru', 'langname' => 'Russian'],
        'es-US' => ['code' => 'es', 'langname' => 'Spanish, US'],
        'es-ES' => ['code' => 'es', 'langname' => 'Spanish, European'],
        'es-MX' => ['code' => 'es', 'langname' => 'Spanish, Mexican'],
        'sv-SE' => ['code' => 'sv', 'langname' => 'Swedish'],
        'tr-TR' => ['code' => 'tr', 'langname' => 'Turkish'],
        'cy-GB' => ['code' => 'cy', 'langname' => 'Welsh'],
      ];

      // Using Drupal language, look up Polly voices.
      foreach ($languagecrosswalk as $key => $value) {
        if ($value['code'] == $language) {
          $sitelanguageforpolly = $key;
          $sitelanguagenameforpolly = $value['langname'];
        }
      }

      // If Polly voices not found for this language, default to English.
      if (is_null($sitelanguageforpolly)) {
        $form['language_warning'] = [
          '#type' => 'item',
          '#title' => $this
            ->t('Polly does not have voices for this language; defaulting to US English.'),
        ];
        $sitelanguageforpolly = 'en-US';
      }
      else {
        // Otherwise display Drupal language.
        $form['language_id'] = [
          '#type' => 'item',
          '#title' => $this
            ->t('Site language detected: %sitelanguagenameforpolly', ['%sitelanguagenameforpolly' => $sitelanguagenameforpolly]),
        ];
      }

      // Use Polly API to look up voices for the site language.
      $result = $client->describeVoices([
        'Engine' => 'standard',
        'IncludeAdditionalLanguageCodes' => TRUE,
        'LanguageCode' => $sitelanguageforpolly,
      ]);

      $voiceselectlist = [];

      // Build voices select array.
      foreach ($result['Voices'] as $voicevalue) {
        $voiceselectlist[$voicevalue['Id']] = $voicevalue['Name'];
      }

      // Form element to select voice.
      $form['voice_selection'] = [
        '#type'          => 'radios',
        '#required'      => TRUE,
        '#title'         => $this->t('Voice'),
        '#description'   => $this->t('The synthetic voice to use for generation.'),
        '#options' => $voiceselectlist,
        '#default_value' => $config->get('voice_selection'),
      ];

    }

    else {

      // Voice select form if English is default language for Drupal.
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
        '#description'   => $this->t('Neural (more life-like) is $16 per million characters. Standard method is $4 per million characters. Prices as of October 2020.'),
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
    }

    return parent::buildForm($form, $form_state);

    // End buildForm()
  }

  /**
   * Validate the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // End validateForm()s
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
      ->set('voice_selection', $form_state->getValue('voice_selection'))
      ->set('voice_generation', $voice_generation_value)
      ->set('voice_style', $voice_style_value)
      ->save();

    parent::submitForm($form, $form_state);

  }//end submitForm()

}
