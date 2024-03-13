<?php

namespace Drupal\weather_info\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Weather Info form.
 */
class WeatherInfoForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['weather_info.settings'];
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'weather_info_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $cities = [
      'Lutsk' => $this->t('Lutsk'),
      'London' => $this->t('London'),
      'Luxembourg' => $this->t('Luxembourg'),
    ];

    $config = $this->config('weather_info.settings');
    $form['city'] = [
      '#type' => 'select',
      '#title' => $this->t('City'),
      '#default_value' => $config->get('city'),
      '#options' => $cities,
    ];
    $form['api'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api') ?? 'Enter your API Key',
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('weather_info.settings')
      ->set('city', $form_state->getValue('city'))
      ->set('api', $form_state->getValue('api'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
