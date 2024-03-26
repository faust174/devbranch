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
    $batch = [
      'title' => t('Bulk updating text format'),
      'operations' => [
        [[$this, 'processItems'], []],
      ],
    ];

    batch_set($batch);
    drush_backend_batch_process();
    $this->output()->writeln('Format updated successfully');
  }

  /**
   * Process batch items.
   */
  public function processItems(&$context): void {
    $limit = 11;
    $format = 'limited_html';
    $query = $this->entityTypeManager->getStorage('paragraph')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('field_quote.format', $format, '<>')
      ->range(0, $limit);
    $paragraphs = $query->execute();
    if (empty($paragraphs)) {
      $context['finished'] = 1;
      return;
    }
    $multipleParagraphs = $this->entityTypeManager
      ->getStorage('paragraph')
      ->loadMultiple($paragraphs);
    foreach ($multipleParagraphs as $paragraph) {
      $this->updateFormat($paragraph, $format);
    }
    $context['finished'] = 0;
  }

  /**
   * Update the text format of a paragraph.
   */
  public function updateFormat($paragraph, &$format):void {
    $paragraph->get('field_quote')->format = $format;
    $paragraph->save();
  }

}
