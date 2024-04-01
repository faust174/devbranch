<?php

/**
 * @file
 */

/**
 * Process batch items.
 */
function batch_post_update_paragraph_field_type_update9(&$context) {
  $limit = 10;
  $format = 'limited_html';

  $entity = \Drupal::entityQuery('paragraph')
    ->accessCheck(FALSE)
    ->condition('field_quote.format', $format, '<>')
    ->range(0, $limit);
  $paragraphs = $entity->execute();

  if (empty($paragraphs)) {
    $context['finished'] = 1;
    return;
  }
  $multipleParagraphs = \Drupal::entityTypeManager()
    ->getStorage('paragraph')
    ->loadMultiple($paragraphs);
  foreach ($multipleParagraphs as $paragraph) {
    process_item($paragraph, $format);
  }
  $context['finished'] = 0;
}

/**
 * Update the text format of a paragraph.
 */
function process_item($paragraph, &$format) {
  $paragraph->get('field_quote')->format = $format;
  $paragraph->save();
}
