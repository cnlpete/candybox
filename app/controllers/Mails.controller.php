<?php

/**
 * This is an example for extending a standard class.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Controllers;

use candyCMS\Core\Helpers\Helper;

require_once PATH_STANDARD . '/vendor/candycms/core/controllers/Mails.controller.php';

class Mails extends \candyCMS\Core\Controllers\Mails {

  /**
   * This method overrides the standard update method and is used for tests.
   *
   * @access public
   * @return string
   *
   */
  public function update() {
    return 'This is an example!';
  }
}