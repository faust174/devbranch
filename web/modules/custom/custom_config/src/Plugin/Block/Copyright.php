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
    $loader = $this->configPagesLoaderService;
    if ($entity = $loader->load('global_configurations')) {
      $copyright = $entity->get('field_copyrights')->view('full');
      $copyright['#attributes']['class'][] = 'copyright';
    }
    else {
      $entityTypeManager = $this->entityTypeManager;
      $cache = $entityTypeManager->getDefinition('config_pages')->getListCacheTags();
      $copyright = [
        '#markup' => $this->t('Copyright'),
        '#cache' => [
          'tags' => $cache,
        ],
      ];
    }
    return $copyright;
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
