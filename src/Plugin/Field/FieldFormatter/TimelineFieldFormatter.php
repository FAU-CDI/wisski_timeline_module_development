<?php

namespace Drupal\random\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'Random_default' formatter.
 *
 * @FieldFormatter(
 *   id = "TimelineFieldFormatter",
 *   label = @Translation("Timeline Field Formatter"),
 *   field_types = {
 *     "Timeline FieldFormatter"
 *   }
 * )
 */
class TimelineFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the random string.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = ['#markup' => $item->value];
    }
    print("TESEEEEEEET");
    print($items);
    $element[test] = ['#markup' => $this_t("TRALALALA")];
    return $element;
  }

public function load_my_html_first_test($html) {
   $document = <<<EOD
<!DOCTYPE html>
<html>
<h1>My First Heading</h1>
<p>My first paragraph.</p>
</html>
EOD;

   // PHP's \DOMDocument serialization adds extra whitespace when the markup
   // of the wrapping document contains newlines, so ensure we remove all
   // newlines before injecting the actual HTML body to be processed.
   $document = strtr($document, array(
     "\n" => '',
     '!html' => $html,
   ));
   $dom = new \DOMDocument();

   // Ignore warnings during HTML soup loading.
   @$dom->loadHTML($document);
   return $dom;
 }

}
