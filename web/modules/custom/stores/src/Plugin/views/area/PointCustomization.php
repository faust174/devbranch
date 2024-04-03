<?php

namespace Drupal\stores\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Customize appearance of the points on the map.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("our_stores")
 */
class PointCustomization extends AreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['color'] = ['default' => '#00000'];
    $options['size'] = ['default' => '10'];
    $options['zoom'] = ['default' => '13'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['color'] = [
      '#title' => $this->t('Point color'),
      '#type' => 'color',
      '#default_value' => $this->options['color'],
    ];
    $form['size'] = [
      '#title' => $this->t('Point size'),
      '#type' => 'textfield',
      '#default_value' => $this->options['size'],
      '#element_validate' => [
        [$this, 'validateNumberField'],
      ],
    ];
    $form['zoom'] = [
      '#title' => $this->t('Map zoom'),
      '#type' => 'textfield',
      '#default_value' => $this->options['zoom'],
      '#element_validate' => [
        [$this, 'validateNumberField'],
      ],
    ];
  }

  /**
   * Validation for number fields.
   */
  public function validateNumberField($element, FormStateInterface $form_state) {
    $value = $element['#value'];
    if (!is_numeric($value) || $value <= 0) {
      $form_state->setError($element, $this->t('Please enter a valid positive number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    foreach ($this->view->result as $item) {
      $field_location_value = $item->_entity->get('field_location')->getValue();
      $field_location_values[] = $field_location_value;
    }

    $build['#attached']['library'][] = 'stores/leaflet';
    $build['#attached']['drupalSettings'] = [
      'stores' => [
        'color' => $this->options['color'],
        'size' => $this->options['size'],
        'zoom' => $this->options['zoom'],
        'locations' => $field_location_values,
      ],
    ];
    $build['#markup'] = ' <div id="map"></div>';
    return $build;
  }

}
