<?php

/**
 * Show customized error message when page is not found.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;

class Errors extends Main {

  /**
   * Show a 404 error when a page is not available or found.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, $this->_iId);
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, $this->_iId);

    if ($this->_iId == '401') {
      header('HTTP/1.0 401 Authorization Required');
    }
    elseif ($this->_iId == '403') {
      header('HTTP/1.0 403 Forbidden');
    }
    elseif ($this->_iId == '404') {
      header('Status: 404 Not Found');
      header('HTTP/1.0 404 Not Found');
    }

    $this->oSmarty->setTemplateDir($sTemplateDir);
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * There is no overview avaiable
   *
   * @access protected
   * @return HTML 404
   *
   */
  protected function _overview() {
    return Helper::redirectTo('/errors/403');
  }
}