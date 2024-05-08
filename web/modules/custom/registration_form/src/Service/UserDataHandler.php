<?php

namespace Drupal\registration_form\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides functionality that handles user data.
 */
class UserDataHandler implements ContainerFactoryPluginInterface {

  public function __construct(protected Connection $database) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * Saves user data to the database.
   */
  public function saveUserData($uid, $city, $country, $selected_interests): void {
    $this->database->insert('user_data')
      ->fields([
        'uid' => $uid,
        'city' => $city,
        'country' => $country,
      ])
      ->execute();
    foreach ($selected_interests as $tid) {
      $this->database->insert('user_interests')
        ->fields([
          'uid' => $uid,
          'tid' => $tid,
        ])
        ->execute();
    }
  }

}
