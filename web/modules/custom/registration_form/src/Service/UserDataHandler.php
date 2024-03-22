<?php

namespace Drupal\registration_form\Service;

use Drupal\Core\Database\Connection;

/**
 * Provides functionality that handles user data.
 */
class UserDataHandler {

  public function __construct(protected Connection $database) {
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
