<?php

namespace Drupal\multistep_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Multistep form from product, delivery, and payment pages.
 */
class MultistepForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'custom_multistep_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
  ): array {
    $config = $this
      ->config('multistep_form.multistep_form_settings_js')
      ->get('table_steps');
    $enabled_config = [];
    foreach ($config as $value) {
      if ($value['status'] == 'enabled') {
        $enabled_config[] = $value['id'];
      }
    }
    $max_step = count($enabled_config);

    $form['link'] = [
      '#type' => 'link',
      '#title' => $this->t('Configure your form here'),
      '#url' => Url::fromRoute('multistep_form.multistep_form_settings_js'),
      '#attributes' => ['class' => ['button__configure-form']],
    ];

    if ($max_step == 0) {
      $form['text'] = [
        '#markup' => $this->t('There is no pages is available.'),
      ];

      return $form;
    }

    $form['#tree'] = TRUE;
    $form['product_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'product-container',
        'class' => ['product-container'],
      ],
    ];

    $form['product_container']['actions'] = [
      '#type' => 'actions',
    ];

    $form['product_container']['actions']['step'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'product-step-container',
      ],
    ];

    if (!$form_state->has('step')) {
      $form_state->set('step', 1);
    }
    $step = $form_state->get('step');
    switch ($enabled_config[$step - 1]) {
      case 'product':
        $this->productPage($form, $form_state);
        break;

      case 'delivery':
        $this->deliveryPage($form, $form_state);
        break;

      case 'payment':
        $this->paymentPage($form, $form_state);
        break;
    }

    if (!$form_state->has('completed')) {
      $form_state->set('completed', 1);
    }
    $completed = $form_state->get('completed');

    for ($i = 1; $i <= $max_step; $i++) {
      if ($completed >= $i && $step != $i) {
        $this->getPageButton($form, $form_state, $i, $enabled_config);
      }
    }

    if ($step != 1) {
      $form['product_container']['actions']['previous_step'] = $this->getPreviousButton($form, $form_state);
    }
    if ($step != $max_step) {
      $form['product_container']['actions']['next_step'] = $this->getNextButton($form, $form_state);
    }
    if ($step == $max_step) {
      $form['product_container']['actions']['save'] = $this->getSaveButton($form, $form_state);
    }
    $form['product_container']['progress'] = [
      '#plain_text' => 'Progress bar ' . $step . ' / ' . $max_step,
    ];

    $form['#attached']['library'][] = 'multistep_form/multistep_form_ui';

    return $form;

  }

  /**
   * Form for entering product data.
   */
  public function productPage(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $form['product_container']['product'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the product'),
      '#options' => [
        'apple' => $this->t('Apple'),
        'orange' => $this->t('Orange'),
        'banana' => $this->t('Banana'),
      ],
      '#required' => TRUE,
      '#default_value' => $form_state->get('data')['product'] ?? 'apple',
    ];

    $form['product_container']['amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Amount'),
      '#min' => 1,
      '#max' => 10,
      '#step' => 1,
      '#default_value' => $form_state->get('data')['amount'] ?? 1,
    ];

  }

  /**
   * Form for entering delivery data.
   */
  public function deliveryPage(
    array &$form,
    FormStateInterface $form_state,
  ): array {
    $form['product_container']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter your city'),
      '#required' => TRUE,
      '#default_value' => $form_state->get('data')['city'] ?? '',
    ];

    return $form;
  }

  /**
   * Form for entering payment data.
   */
  public function paymentPage(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $form['product_container']['payment'] = [
      '#type' => 'select',
      '#options' => [
        'cash' => $this->t('Cash'),
        'cashless' => $this->t('By bank transfer'),
      ],
      '#default_value' => $form_state->get('data')['payment'] ?? 'cash',
    ];
  }

  /**
   * Callback for both ajax-enabled buttons.
   */
  public function ajaxCallback(
    array $form,
    FormStateInterface $form_state,
  ): array {
    return $form['product_container'];
  }

  /**
   * A button that sends to a certain stage.
   */
  public function getPageButton(
    array &$form,
    FormStateInterface $form_state,
    $index,
    $enabled_config,
  ): void {

    switch ($enabled_config[$index - 1]) {
      case 'product':
        $label = $this->t('Product Page');
        break;

      case 'delivery':
        $label = $this->t('Delivery Page');
        break;

      case 'payment':
        $label = $this->t('Payment Page');
        break;
    }

    $form['product_container']['actions']['step'][] = [
      '#type' => 'submit',
      '#value' => $label,
      '#submit' => ['::stepPageSubmit'],
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'product-container',
      ],
      '#attributes' => [
        'class' => ['step-btn'],
        'data-index' => $index,
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Submit for saving a new value for a step.
   */
  public function stepPageSubmit(
    array $form,
    FormStateInterface $form_state
  ): void {
    $index = $form_state->getTriggeringElement()['#attributes']['data-index'];
    $form_state
      ->setValues($form_state->get('data'))
      ->set('step', $index)
      ->setRebuild();
  }

  /**
   * Button that returns to the previous step.
   */
  protected function getPreviousButton(
    array $form,
    FormStateInterface $form_state,
  ): array {
    return [
      '#type' => 'submit',
      '#value' => $this->t('Previous step'),
      '#submit' => ['::previousStepSubmit'],
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'product-container',
      ],
      '#attributes' => [
        'class' => ['previous-step'],
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Button that sends to the next step.
   */
  protected function getNextButton(
    array $form,
    FormStateInterface $form_state,
  ): array {
    return [
      '#type' => 'submit',
      '#value' => $this->t('Next step'),
      '#submit' => ['::nextStepSubmit'],
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'product-container',
      ],
      '#attributes' => [
        'class' => ['next-step'],
      ],
    ];
  }

  /**
   * Button that saving data.
   */
  protected function getSaveButton(
    array $form,
    FormStateInterface $form_state,
  ): array {
    return [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => ['::saveSubmit'],
    ];
  }

  /**
   * Ajax submit for 'Previous Step' button.
   */
  public function previousStepSubmit(
    array $form,
    FormStateInterface $form_state,
  ): void {
    $step = $form_state->get('step');
    $form_state
      ->setValues($form_state->get('data'))
      ->set('step', $step - 1)
      ->setRebuild();
  }

  /**
   * Ajax submit for 'Next Step' button.
   */
  public function nextStepSubmit(
    array $form,
    FormStateInterface $form_state,
  ): void {
    $values = $form_state->get('data');
    if (!$values) {
      $values = [];
    }
    $current_values = $form_state->getValue('product_container');
    $step = $form_state->get('step');
    unset($current_values['actions']);
    foreach ($current_values as $key => $value) {
      $values[$key] = $value;
    }

    $form_state->set('data', $values);
    $form_state->set('step', $step + 1);
    $form_state->set('completed', $step);
    $form_state->setRebuild();
  }

  /**
   * Ajax submit for 'Save' button.
   */
  public function saveSubmit(
    array $form,
    FormStateInterface $form_state,
  ): void {
    $values = $form_state->get('data');
    $current_values = $form_state->getValue('product_container');
    unset($current_values['actions']);
    foreach ($current_values as $key => $value) {
      $values[$key] = $value;
    }
    $this->messenger()->addMessage(
      $this->t("The form submitted successfully. Your data:
        Product name: @product,
        Amount: @amount,
        City: @city,
        Payment: @payment", [
        '@product' => $values['product'] ?? $this->t('Not defined'),
        '@amount' => $values['amount'] ?? $this->t('Not defined'),
        '@city' => $values['city'] ?? $this->t('Not defined'),
        '@payment' => $values['payment'] ?? $this->t('Not defined'),
      ]));
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
