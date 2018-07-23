<?php

namespace Drupal\wisski_timeline\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "hello_block",
 *   admin_label = @Translation("Hello block"),
 *   category = @Translation("Hello World"),
 * )
 */
class hello_block extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $result = array(
      '#markup' => $this->t('Hello, World!'),
      '#attached' => array(
        'library' => array(
          'wisski_timeline/example_timeline',
        ),
      ),
    );
    $result['#hello_block']['#attached']['library'] = 'wisski_timeline/example_timeline';
    //$result['#attached']['library'][] = 'wisski_timeline/example_timeline';
    return $result;
  }

}
