<?php

namespace Drupal\batch\Commands;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Defines Drush command for batch operations.
 */
class BatchCommands extends DrushCommands {

  use DependencySerializationTrait;

  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * Updates paragraph text format.
   *
   * @command batch:updateParagraphTextformat
   *
   * @aliases upd_format
   */
  public function updateParagraphTextformat() {
    $query = $this->entityTypeManager->getStorage('paragraph')
      ->getQuery()
      ->accessCheck(FALSE)
      ->exists('field_quote');
    $paragraphs = $query->execute();
    $format = 'limited_html';

    $batch = [
      'title' => t('Bulk updating text format'),
      'operations' => [
        [[$this, 'processItems'], [$paragraphs, $format]],
      ],
    ];

    batch_set($batch);
    drush_backend_batch_process();
    $this->output()->writeln('Format updated successfully');
  }

  /**
   * Process batch items.
   */
  public function processItems($paragraphs, $format, &$context): void {
    $limit = 11;
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($paragraphs);
    }

    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['items'] = $paragraphs;
    }

    $counter = 0;
    if (!empty($context['sandbox']['items'])) {

      if ($context['sandbox']['progress'] != 0) {
        array_splice($context['sandbox']['items'], 0, $limit);
      }

      $entity = \Drupal::entityTypeManager();
      $multipleParagraphs = $entity
        ->getStorage('paragraph')
        ->loadMultiple($paragraphs);

      foreach ($multipleParagraphs as $paragraph) {
        if ($counter != $limit) {
          $this->updateFormat($paragraph, $format);

          $context['sandbox']['progress']++;
          $counter++;
        }
      }
    }

    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Update the text format of a paragraph.
   */
  public function updateFormat($paragraph, &$format):void {
    $paragraph->get('field_quote')->format = $format;
    $paragraph->save();
  }

}
