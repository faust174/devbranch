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
  protected function getEditableConfigNames():array {
    return ['weather_info.settings'];
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId():string {
    return 'weather_info_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    $config = $this->config('weather_info.settings');
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
  public function submitForm(array &$form, FormStateInterface $form_state):void {
    $this->config('weather_info.settings')
      ->set('api', $form_state->getValue('api'))
      ->save();
    $this->messenger()->addStatus($this->t('The configuration options have been saved.'));
  }
}
