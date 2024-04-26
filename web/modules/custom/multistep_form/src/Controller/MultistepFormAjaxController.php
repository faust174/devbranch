<?php

namespace Drupal\multistep_form\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles AJAX requests for multistep form.
 */
class MultistepFormAjaxController extends ControllerBase {

  /**
   * Handles AJAX request to update configuration.
   */
  public function updateAjax(Request $request) {
    $step = $request->request->get('step');
    $status = $request->request->get('status');

    // Update configuration based on $step and $status.

    return new JsonResponse(['message' => 'Configuration updated']);
  }

}
