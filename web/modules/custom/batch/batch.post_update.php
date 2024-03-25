<?php

/**
 * Process batch items.
 */
function batch_post_update_paragraph_field_type_update9(&$context) {
  $entity = \Drupal::entityQuery('paragraph');
  $entity->accessCheck(FALSE)
    ->exists('field_quote');
  $paragraphs = $entity->execute();
  $limit = 10;
  $format = 'limited_html';

  if (empty($context['progress'])) {
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

    foreach ($context['sandbox']['items'] as $item) {
      if ($counter != $limit) {
        process_item($item, $format);

        $counter++;
        $context['sandbox']['progress']++;
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
function process_item($paragraph_id, &$format) {
  $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
  $paragraph = $paragraph_storage->load($paragraph_id);
  $paragraph->get('field_quote')->format = $format;
  $paragraph->save();
}
