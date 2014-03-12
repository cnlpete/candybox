<?php

/**
 * Show customized error message when page is not found.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;

/**
 * Class Errors
 * @package candyCMS\Core\Controllers
 *
 */
class Errors extends Main {

  /**
   * Show a 404 error when a page is not available or found.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    if ($this->_iId == '401')
      header('HTTP/1.0 401 Authorization Required');

    elseif ($this->_iId == '403')
      header('HTTP/1.0 403 Forbidden');

    elseif ($this->_iId == '404') {
      header('Status: 404 Not Found');
      header('HTTP/1.0 404 Not Found');
    }

    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, $this->_iId);
    $this->oSmarty->setTemplateDir($oTemplate);
    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * There is no overview available
   *
   * @access protected
   * @return string HTML 403
   *
   */
  protected function _overview() {
    return Helper::redirectTo('/errors/403');
  }
}