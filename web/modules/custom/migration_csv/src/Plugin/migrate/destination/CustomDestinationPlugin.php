<?php

namespace Drupal\migration_csv\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Row;

/**
 * Provides a destination plugin for migrating data to a user_data table.
 *
 * @MigrateDestination(
 *   id = "custom_destination_plugin"
 * )
 */
class CustomDestinationPlugin extends DestinationBase {

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'uid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    $data = [
      'uid' => $row->getSourceProperty('uid'),
      'city' => $row->getSourceProperty('city'),
      'country' => $row->getSourceProperty('country'),
    ];

    \Drupal::database()->insert('user_data')
      ->fields($data)
      ->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function fields() {
  }

}
