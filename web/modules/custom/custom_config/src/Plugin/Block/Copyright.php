<?php

namespace Drupal\custom_config\Plugin\Block;

use Drupal\config_pages\ConfigPagesLoaderServiceInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Provides a 'Copyright' block.
*
* @package Drupal\custom_config\Plugin\Block
*/
#[Block(
  id: "copyright_block",
  admin_label: new TranslatableMarkup("Copyright Block"),
  category : new TranslatableMarkup("Custom"),

)]
class Copyright extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  protected EntityTypeManagerInterface $entityTypeManager,
  protected ConfigPagesLoaderServiceInterface $configPagesLoaderService,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config_pages.loader'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $field_copyrights = $this->configPagesLoaderService->getFieldView('global_configurations', 'field_copyrights');
    unset($field_copyrights['#title']);
    $field_copyrights['#cache']['tags'] = ['config_pages:1'];
    $field_copyrights['#attributes'] = ['class' => ['copyright']];
    return $field_copyrights;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['copyright_link'] = [
      '#markup' => $this->t('Copyrights text can be edited <a href="/admin/custom_config/">here</a>.'),
    ];
    return $form;
  }

}
