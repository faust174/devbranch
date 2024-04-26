<?php

namespace Drupal\multistep_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManager;

/**
 * Provides a multistep checkout form.
 */
class CustomMultistepForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'custom_multistep_form';
  }

  /**
   * Builds the multistep form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $page_num = $form_state->get('page_num') ?: 1;

    $steps = $this->getStepOrder();

    $page_values = $form_state->get('page_values')['product'];
    $current_step_index = $page_num - 1;
    $current_step = array_keys($steps)[$current_step_index] ?? NULL;

    if ($current_step && !$steps[$current_step]['disabled']) {
      switch ($current_step) {
        case 'product':
          $this->buildProductSelectionStep($form, $form_state);
          break;

        case 'address':
          $this->buildDeliveryOptionsStep($form, $form_state);
          break;

        case 'payment':
          $this->buildPaymentStep($form, $form_state);
          break;
      }
    }

    if ($page_num <= count($steps)) {
      $progress_bar = [
        '#type' => 'markup',
        '#markup' => $this->t('Progress: @current/@total', [
          '@current' => $page_num,
          '@total' => count($steps),
        ]),
      ];
      $form['wrapper']['progress_bar'] = $progress_bar;
    }

    if (!isset($form['wrapper']['navigation'])) {
      $form['wrapper']['navigation'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['navigation-buttons']],
      ];



      $finished_steps = $form_state->get('completed_steps') ?? [];
      foreach ($finished_steps as $step) {
        $form['wrapper']['navigation'][$step] = [
          '#type' => 'submit',
          '#value' => $this->t('Go to @step', ['@step' => ucfirst($step)]),
          '#submit' => [[$this, 'goToStepSubmit']],
          '#attributes' => ['data-step' => $step],
          '#ajax' => [
            'callback' => '::ajaxCallback',
            'wrapper' => 'wrapper',
          ],
          '#limit_validation_errors' => [],
        ];
      }

      if ($page_num > 1) {
        $form['wrapper']['navigation']['previous'] = [
          '#type' => 'submit',
          '#value' => $this->t('Previous'),
          '#submit' => ['::previousSubmit'],
          '#ajax' => [
            'callback' => '::ajaxCallback',
            'wrapper' => 'wrapper',
          ],
          '#limit_validation_errors' => [],
        ];
      }

      if ($page_num === count($steps) + 1) {
        $this->buildFinishStep($form, $form_state);
      }
      else {
        $form['wrapper']['navigation']['next'] = [
          '#type' => 'submit',
          '#value' => t('Next'),
          '#submit' => ['::nextSubmit'],
          '#ajax' => [
            'callback' => '::ajaxCallback',
            'wrapper' => 'wrapper',
          ],
        ];
      }
    }

    return $form;
  }

//  /**
//   * Builds the multistep form.
//   */
//  public function buildForm(array $form, FormStateInterface $form_state) {
//    $form['#tree'] = TRUE;
//    $page_num = $form_state->get('page_num') ?: 1;
//
//    $steps = $this->getStepOrder();
//    $visited_steps = $form_state->get('visited_steps') ?: [];
//
//    $current_step_index = $page_num - 1;
//    $current_step = array_keys($steps)[$current_step_index] ?? NULL;
//
//    if ($current_step && !$steps[$current_step]['disabled']) {
//      switch ($current_step) {
//        case 'product':
//          $this->buildProductSelectionStep($form, $form_state);
//          break;
//
//        case 'address':
//          $this->buildDeliveryOptionsStep($form, $form_state);
//          break;
//
//        case 'payment':
//          $this->buildPaymentStep($form, $form_state);
//          break;
//      }
//    }
//
//    if ($page_num <= count($steps)) {
//      $progress_bar = [
//        '#type' => 'markup',
//        '#markup' => $this->t('Progress: @current/@total', [
//          '@current' => $page_num,
//          '@total' => count($steps),
//        ]),
//      ];
//      $form['wrapper']['progress_bar'] = $progress_bar;
//    }
//
//    if (!isset($form['wrapper']['navigation'])) {
//      $form['wrapper']['navigation'] = [
//        '#type' => 'container',
//        '#attributes' => ['class' => ['navigation-buttons']],
//      ];
//
//      foreach ($visited_steps as $step) {
//        $form['wrapper']['navigation'][$step] = [
//          '#type' => 'submit',
//          '#value' => $this->t('Go to @step', ['@step' => ucfirst($step)]),
//          '#submit' => [[$this, 'goToStepSubmit']],
//          '#attributes' => ['data-step' => $step],
//          '#ajax' => [
//            'callback' => '::ajaxCallback',
//            'wrapper' => 'wrapper',
//          ],
//          '#limit_validation_errors' => [],
//        ];
//      }
//
//      if ($page_num === count($steps) + 1) {
//        $this->buildFinishStep($form, $form_state);
//      }
//      else {
//        $form['wrapper']['navigation']['next'] = [
//          '#type' => 'submit',
//          '#value' => t('Next'),
//          '#submit' => ['::nextSubmit'],
//          '#ajax' => [
//            'callback' => '::ajaxCallback',
//            'wrapper' => 'wrapper',
//          ],
//        ];
//      }
//
//      if ($page_num > 1) {
//        $form['wrapper']['navigation']['previous'] = [
//          '#type' => 'submit',
//          '#value' => $this->t('Previous'),
//          '#submit' => ['::previousSubmit'],
//          '#ajax' => [
//            'callback' => '::ajaxCallback',
//            'wrapper' => 'wrapper',
//          ],
//          '#limit_validation_errors' => [],
//        ];
//      }
//    }
//
//    return $form;
//  }


  /**
   * Submit handler for the "Go to Step" buttons.
   */
  public function goToStepSubmit(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $step = $triggering_element['#attributes']['data-step'];

      $visited_steps[] = $step;
      $form_state->set('visited_steps', $visited_steps);


    $steps = $this->getStepOrder();
    $page_num = array_search($step, array_keys($steps)) + 1;

    $finished_steps = array_slice(array_keys($steps), 0, $page_num - 1);
    $form_state->set('finished_steps', $finished_steps);

    $form_state->set('page_num', $page_num);
    $form_state->setRebuild(TRUE);

  }




  /**
   * Get the step order based on configuration.
   */
  private function getStepOrder() {
    $config = \Drupal::config('multistep_form.settings');
    $step_order = $config->get('step_order');
    $steps = [];

    foreach ($step_order as $step_name) {
      $step_disabled = $config->get('disable_' . $step_name);
      if (!$step_disabled) {
        $steps[$step_name] = [
          'weight' => $config->get('weight_' . $step_name),
          'disabled' => $step_disabled,
        ];
      }
    }

    uasort($steps, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
    });

    return $steps;
  }

  /**
   *
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['wrapper'];
  }

//
//  public function validateForm(array &$form, FormStateInterface $form_state) {
//    $page_num = $form_state->get('page_num') ?: 1;
//    $config = \Drupal::config('multistep_form.settings');
//    $steps = $config->get('step_settings');
//
//    if (isset($steps[$page_num])) {
//      $step_settings = $steps[$page_num];
//      foreach ($step_settings as $field_name => $field_settings) {
//        if ($field_settings['required'] && empty($form_state->getValue($field_name))) {
//          $form_state->setErrorByName($field_name, $this->t('@field is required.', ['@field' => $field_settings['label']]));
//        }
//        elseif (!empty($field_settings['not_empty']) && empty(trim($form_state->getValue($field_name)))) {
//          $form_state->setErrorByName($field_name, $this->t('@field cannot be empty.', ['@field' => $field_settings['label']]));
//        }
//      }
//    }
//  }

  /**
   * Builds the product selection step.
   */
  private function buildProductSelectionStep(array &$form, $form_state) {
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'wrapper'],
    ];
    $form['wrapper']['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Select product'),
    ];
    $form['wrapper']['product'] = [
      '#type' => 'select',
      '#title' => $this->t('Product'),
      '#options' => [
        't-shirt' => $this->t('T-Shirt'),
        'hoodie' => $this->t('Hoodie'),
        'mug' => $this->t('Mug'),
        'poster' => $this->t('Poster'),
      ],
      '#default_value' => $form_state->get('page_values')['product'] ?? 't-shirt',
      '#required' => TRUE,
    ];

    $form['wrapper']['color'] = [
      '#type' => 'color',
      '#title' => $this->t('Color'),
      '#default_value' => $form_state->get('page_values')['color'] ?? '#000000',
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * Builds the delivery options step.
   */
  private function buildDeliveryOptionsStep(array &$form, $form_state) {

    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'wrapper'],
    ];
    $cities = [
      'Lutsk' => $this->t('Lutsk'),
      'London' => $this->t('London'),
      'Luxembourg' => $this->t('Luxembourg'),
    ];
    $form['wrapper']['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Delivery address'),
    ];
    $form['wrapper']['country'] = [
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#options' => CountryManager::getStandardList(),
      '#default_value' => $form_state->get('page_values')['country'] ?? 'UA',
      '#required' => TRUE,
    ];
    $form['wrapper']['city'] = [
      '#type' => 'select',
      '#title' => $this->t('City'),
      '#options' => $cities,
      '#default_value' => $form_state->get('page_values')['city'] ?? 'Lutsk',
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * Builds the payment step.
   */
  private function buildPaymentStep(array &$form, $form_state) {
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'wrapper'],
    ];
    $form['wrapper']['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Payment Information'),
    ];
    $form['wrapper']['card_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Card Number'),
      '#required' => TRUE,
    ];
    $form['wrapper']['card_holder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cardholder Name'),
      '#required' => TRUE,
    ];

    return $form;
  }
  /**
   * Builds the finish step.
   */
  private function buildFinishStep(array &$form, FormStateInterface $form_state) {
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'wrapper'],
    ];
    $form['wrapper']['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Thank you for your order!'),
    ];

    $page_values = $form_state->get('page_values');
    if (!empty($page_values)) {
      $form['wrapper']['summary'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Order Summary'),
      ];

      foreach ($page_values as $key => $value) {
        $form['wrapper']['summary'][$key] = [
          '#type' => 'item',
          '#markup' => $this->t('<strong>@label:</strong> <span>@data</span>', [
            '@label' => ucfirst($key),
            '@data' => is_array($value) ? implode(', ', $value) : $value,
          ]),
        ];
      }

      $form['wrapper']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Place Order'),
      ];
    }

    $form['wrapper']['navigation']['previous'] = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::previousSubmit'],
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    if (isset($form['wrapper']['summary']['navigation'])) {
      unset($form['wrapper']['summary']['navigation']);
    }

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Submit handler for the previous button.
   */
  public function previousSubmit(array &$form, FormStateInterface $form_state) {
    $page_num = $form_state->get('page_num') ?: 1;
    if ($page_num > 1) {
      $form_state->set('page_num', $page_num - 1);
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * Submit handler for the next button.
   */
  public function nextSubmit(array &$form, FormStateInterface $form_state) {
    $page_num = $form_state->get('page_num') ?: 1;

    $page_values = $form_state->get('page_values') ?: [];

    // Get the current step name based on the step order and current step number
    $steps = $this->getStepOrder();
    $current_step_index = $page_num - 1;
    $step_names = array_keys($steps);
    $current_step_name = $step_names[$current_step_index];
    $form_state->set('completed_steps', $current_step_name);


    $current_values = $form_state->getValue('wrapper');
    unset($current_values['next']);
    unset($current_values['description']);
    unset($current_values['previous']);
    foreach ($current_values as $key => $value) {
      $page_values[$key] = $value;
    }
    $form_state->set('page_values', $page_values);
    $form_state->set('page_num', $page_num + 1);
    $form_state->setRebuild(TRUE);
  }

}
