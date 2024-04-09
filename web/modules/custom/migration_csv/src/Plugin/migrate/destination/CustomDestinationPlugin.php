<?php

namespace Drupal\migration_csv\Plugin\migrate\destination;

use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a destination plugin for migrating data to a user_data table.
 *
 * @MigrateDestination(
 *   id = "custom_destination_plugin"
 * )
 */
class CustomDestinationPlugin extends DestinationBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, protected Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
    $configuration,
    $plugin_id,
    $plugin_definition,
    $migration,
    $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getIds():array {
    return [
      'uid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function import(Row $row, array $old_destination_id_values = []):void {
    $data = [
      'uid' => $row->getDestinationProperty('uid'),
      'city' => $row->getDestinationProperty('city'),
      'country' => $row->getDestinationProperty('country'),
    ];

    $this->database->insert('user_data')
      ->fields($data)
      ->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function fields() {
  }

}
