<?php

namespace Drupal\weather_info\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\weather_info\Service\UserSelectedCity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Weather Info form for users.
 */
class UserWeatherInfoForm extends FormBase {

  /**
   * Constructs a new UserWeatherInfoForm object.
   */
  public function __construct(protected ConfigFactoryInterface $config, protected Connection $database, protected AccountProxyInterface $currentUser, protected UserSelectedCity $userSelectedCity) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('database'),
      $container->get('current_user'),
      $container->get('weather_info.user_selected_city'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['weather_info.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'user_weather_info_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $selected_city = $this->userSelectedCity->getUserSelectedCity();
    $cities = [
      'Lutsk' => $this->t('Lutsk'),
      'London' => $this->t('London'),
      'Luxembourg' => $this->t('Luxembourg'),
    ];
    $form['city'] = [
      '#type' => 'select',
      '#options' => $cities,
      '#title' => $this->t('City'),
      '#default_value' => $selected_city,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $selected_city = $form_state->getValue('city');
    $this->config->getEditable('weather_info.settings')
      ->set('city', $selected_city)
      ->save();
    $city = $form_state->getValue('city');
    $uid = $this->currentUser->id();

    $record = $this->database->select('user_city_preferences', 'ucp')
      ->fields('ucp', ['city'])
      ->condition('uid', $uid)
      ->execute()
      ->fetchAssoc();

    if ($record) {
      $this->database->update('user_city_preferences')
        ->fields(['city' => $city])
        ->condition('uid', $uid)
        ->execute();
    }
    else {
      $this->database->insert('user_city_preferences')
        ->fields(['uid' => $uid, 'city' => $city])
        ->execute();
    }
  }

}
