<?php
namespace Drupal\wisski_timeline\Controller;

use Drupal\Core\Controller\ControllerBase;

class hello_controller extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
    ];
  }

}
