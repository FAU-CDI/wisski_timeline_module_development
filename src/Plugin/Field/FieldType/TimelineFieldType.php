<?php

namespace Drupal\wisski_timeline\Plugin\Field\FieldType\timelineFieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Provides a field type of timelineFieldType.
 * 
 * @FieldType(
 *   id = "timeline",
 *   label = @Translation("timeline field"),
 *   default_formatter = "timeline_formatter",
 *   default_widget = "timeline_widget",
 * )
 */

class BazItem extends FieldItemBase implements FieldItemInterface {

}

