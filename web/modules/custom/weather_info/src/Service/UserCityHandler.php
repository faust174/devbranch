<?php

namespace Drupal\weather_info\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the name of the city selected by the user.
 */
class UserCityHandler {

  public function __construct(
    protected AccountProxyInterface $currentUser,
    protected Connection $database) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container):static {
    return new static(
      $container->get('current_user'),
      $container->get('database'),
    );
  }

  /**
   * Receives the data from database.
   */
  public function getUserSelectedCity() {
    $uid = $this->currentUser->id();
    $query = $this->database->select('user_city_preferences', 'ucp')
      ->fields('ucp', ['city'])
      ->condition('uid', $uid)
      ->execute();
    return $query->fetchField();
  }

  /**
   * Sets the user's selected city.
   */
  public function setUserSelectedCity($city) {
    $uid = $this->currentUser->id();

    $query = $this->database->upsert('user_city_preferences')
      ->key('uid')
      ->fields([
        'uid' => $uid,
        'city' => $city,
      ]);

    $query->execute();
  }

}
