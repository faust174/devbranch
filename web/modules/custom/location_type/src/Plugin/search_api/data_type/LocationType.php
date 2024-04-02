<?php

namespace Drupal\location_type\Plugin\search_api\data_type;

use Drupal\search_api\DataType\DataTypePluginBase;

/**
 * Provides a custom location data type.
 *
 * @SearchApiDataType(
 *   id = "location_type",
 *   label = @Translation("Location type"),
 *   description = @Translation("Custom location type."),
 *   fallback_type = "text",
 *   prefix = "rpt",
 * )
 */
class LocationType extends DataTypePluginBase {

}
