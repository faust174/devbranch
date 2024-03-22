<?php

namespace Drupal\weather_info\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\weather_info\Service\UserCityHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Weather Info form for users.
 */
class UserWeatherInfoForm extends FormBase {

  /**
   * Constructs a new UserWeatherInfoForm object.
   */
  public function __construct(protected ConfigFactoryInterface $config, protected Connection $database, protected AccountProxyInterface $currentUser, protected UserCityHandler $userCityHandler) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container):static {
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
  public function getFormId(): string {
    return 'user_weather_info_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    $selected_city = $this->userCityHandler->getUserSelectedCity();

    $cities = $this->userCityHandler->getCities();
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
  public function submitForm(array &$form, FormStateInterface $form_state):void {
    $city = $form_state->getValue('city');
    $this->userCityHandler->setUserSelectedCity($city);
  }

}
