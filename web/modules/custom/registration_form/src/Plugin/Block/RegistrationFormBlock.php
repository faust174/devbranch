<?php

namespace Drupal\registration_form\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Registration form' block.
 */
#[Block(
  id: "Registration form block",
  admin_label: new TranslatableMarkup("Registration form block"),
)]
class RegistrationFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    protected AccountProxyInterface $currentUser) {
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
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $isAnonymous = $this->currentUser->isAnonymous();
    if (!$isAnonymous) {
      return [];
    }
    $build = [
      '#type' => 'link',
      '#title' => $this->t('Register'),
      '#url' => Url::fromRoute('user.register'),
      '#options' => [
        'attributes' => [
          'class' => ['use-ajax', 'register-button'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 700,
          ]),
        ],
      ],
      '#attached' => ['library' => ['core/drupal.dialog.ajax']],
    ];

    return $build;
  }

}
