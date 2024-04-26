<?php

namespace Drupal\multistep_form\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for configuring multistep form steps.
 */
class MultistepFormConfiguration extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multistep_form_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['multistep_form.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'] = 'multistep_form/config';
    $config = $this->config('multistep_form.settings');
    $form['#tree'] = TRUE;
    // $form['steps'] = [
    //      '#type' => 'fieldset',
    //      '#title' => $this->t('Configure Multistep Form Steps'),
    //    ];
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'wrapper'],
    ];
    $header = [
      'step' => $this->t('Step'),
      'weight' => $this->t('Weight'),
      'disable' => $this->t('Disable'),
    ];

    $form['wrapper']['steps'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => $this->t('No steps configured yet.'),
    ];
    $form_state->setRebuild(TRUE);
    $step_order = $config->get('step_order');

    foreach ($step_order as $step) {
      $disable_key = 'disable_' . strtolower($step);
      $weight_key = 'weight_' . strtolower($step);

      $form['wrapper']['steps'][$step]['step'] = [
        '#markup' => $step,
      ];
      $form['wrapper']['steps'][$step]['weight'] = [
        '#type' => 'select',
        '#options' => range(0, count($step_order)),
        '#default_value' => $config->get($weight_key),
//        '#disabled' => $config->get($disable_key),
        '#attributes' => [
          'class' => ['weight-select'],
          'data-step' => $step,
          'data-options' => count($step_order),
          'data-initial-weight' => $config->get($weight_key),
          'data-disabled' => $config->get($disable_key) ? 'true' : 'false',
        ],
      ];
      $form['wrapper']['steps'][$step]['disable'] = [
        '#type' => 'checkbox',
        '#default_value' => $config->get($disable_key),
        '#attributes' => [
          'class' => ['multistep-checkbox'],
          'data-step' => $step,
        ],
      ];
    }

    $form['wrapper']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
    ];

    return $form;
  }

  /**
   * Handles the Ajax callback for updating the form.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    // $triggering_element = $form_state->getTriggeringElement();
    //    $step = $triggering_element['#parents'][2];
    $config = $this->config('multistep_form.settings');
    $status = $form_state->getValue('wrapper')['steps']['Product']['disable'];
    $config->set('disable_product', $status);
    $config->save();
    $form_state->setRebuild(TRUE);
    return $form['wrapper'];
  }

  /**
   * Handles the AJAX submission for updating the form.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $config = $this->configFactory()->getEditable('multistep_form.settings');


    $step_order = [];

    foreach ($values['wrapper']['steps'] as $step_name => $step_data) {
      if (!$step_data['disable']) {
        $step_order[] = strtolower($step_name);
      }
    }

    $config->set('step_order', $step_order);
    foreach ($step_order as $step) {
      $disable_key = 'disable_' . strtolower($step);
      $weight_key = 'weight_' . strtolower($step);

      $config->set($disable_key, $values['wrapper']['steps'][$step]['disable']);
      $config->set($weight_key, $values['wrapper']['steps'][$step]['weight']);
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
// Foreach ($step_order as $step) {
//      $disable_key = 'disable_' . strtolower($step);
//      $weight_key = 'weight_' . strtolower($step);
//
//      $form['wrapper']['steps'][$step]['step'] = [
//        '#markup' => $step,
//      ];
//      $form['wrapper']['steps'][$step]['weight'] = [
//        '#type' => 'select',
//        '#options' => range(1, count($step_order)),
//        '#default_value' => $config->get($weight_key),
//        '#disabled' => $config->get($disable_key),
//        '#ajax' => [
//          'callback' => '::ajaxCallback',
//          'wrapper' => 'wrapper',
//        ],
//      ];
//      $form['wrapper']['steps'][$step]['disable'] = [
//        '#type' => 'checkbox',
//        '#default_value' => $config->get($disable_key),
//        '#ajax' => [
//          'callback' => '::ajaxCallback',
//          'wrapper' => 'wrapper',
//        ],
//        '#submit' => ['::ajaxSubmit'],
//      ];
//    }.
