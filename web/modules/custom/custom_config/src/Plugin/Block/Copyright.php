<?php

namespace Drupal\custom_config\Plugin\Block;

use Drupal\config_pages\ConfigPagesLoaderServiceInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
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
    $field_copyrights = $this->configPagesLoaderService->getFieldView('global_configurations', 'field_copyrights', 'default');
    $field_copyrights['#attributes'] = ['class' => ['copyright']];
    if (empty($field_copyrights['#items'])) {
      $config_pages = $this->entityTypeManager->getStorage('config_pages')->load('global_configurations');
      $cache = $config_pages->getCacheTags();
      $build = [
        '#markup' => $this->t('Copyright'),
        '#cache' => ['tags' => $cache],
        '#attributes' => ['class' => ['copyright']],
      ];
      return $build;
    }
    else {
      return $field_copyrights;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['copyright_link'] = [
      '#type' => 'link',
      '#title' => $this->t('Configure your block here'),
      '#url' => Url::fromRoute('config_pages.global_configurations'),
    ];

    return $form;
  }

}
