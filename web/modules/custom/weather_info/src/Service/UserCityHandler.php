<?php

namespace Drupal\weather_info\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the name of the city selected by the user.
 */
class UserCityHandler {

  public function __construct(
    protected AccountProxyInterface $currentUser,
    protected Connection $database,
    protected EntityTypeManagerInterface $entityTypeManager,
    ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container):static {
    return new static(
      $container->get('current_user'),
      $container->get('database'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Receives the data from database.
   */
  public function getUserSelectedCity() {
    $uid = $this->currentUser->id();
    $query = $this->database->select('user_data', 'ud')
      ->fields('ud', ['city'])
      ->condition('uid', $uid)
      ->execute();
    return $query->fetchField();
  }
  /**
   * Sets the user's selected city.
   */
  public function setUserSelectedCity($city):void {
    $uid = $this->currentUser->id();
    if ($this->getUserSelectedCity()) {
      $this->database->update('user_data')
        ->fields(['city' => $city])
        ->condition('uid', $uid)
        ->execute();
    }
    else {
      $this->database->insert('user_data')
        ->fields(['uid' => $uid, 'city' => $city])
        ->execute();
    }
  }
  /**
   * Returns list of cities available.
   */
  public function getCities():array {
    return [
      'Lutsk' => 'Lutsk',
      'London' => 'London',
      'Luxembourg' => 'Luxembourg',
    ];
  }

}
