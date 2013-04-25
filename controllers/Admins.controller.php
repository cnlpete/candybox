<?php

/**
 * Provide a few tools for Administrators, such as clearing the cache.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 3.1
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\Cache;
use candyCMS\Core\Helpers\I18n;

class Admins extends Main {

  /**
   * Show all possible actions.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    if ($this->_aSession['user']['role'] < 4)
      return Helper::redirectTo('/errors/401');

    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'overview');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'overview');
    $this->oSmarty->setTemplateDir($sTemplateDir);
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Clear the cache
   *
   * @access protected
   *
   */
  public function clearcache() {
    # clear the smarty cache and compile directories
    $this->oSmarty->clearCompiledTemplate();
    $this->oSmarty->clearCache(null, WEBSITE_MODE);

    # clear all the translation directories
    # but save the success message first
    $sSuccessMessage = I18n::get('success.update');
    I18n::unsetLanguage();

    return Helper::successMessage($sSuccessMessage, '/admins/');
  }
}
