<?php

declare(strict_types=1);

namespace Drupal\ckeditor_plugin\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Link Plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class LinkPlugin extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface {

  use CKEditor5PluginConfigurableTrait;

  const DEFAULT_CONFIGURATION = [
    'default_color' => '#000000',
  ];

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return static::DEFAULT_CONFIGURATION;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['default_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Link Color'),
      '#description' => $this->t('Enter the default color for links in hex format (e.g., #000000 for black).'),
      '#default_value' => $this->configuration['default_color'],
      '#maxlength' => 7,
      '#size' => 7,
      '#attributes' => ['type' => 'color'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['default_color'] = $form_state->getValue('default_color');
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $default_color = $this->configuration['default_color'];

    $dynamic_config = [
      'default_color' => $default_color,
    ];

    return $dynamic_config;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
