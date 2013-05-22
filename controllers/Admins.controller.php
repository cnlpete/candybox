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
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

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

    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);
    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Clear the cache.
   *
   * @access public
   * @return
   * @todo test and doc 
   *
   */
  public function clearCache() {
    if ($this->_aSession['user']['role'] < 4)
      return Helper::redirectTo('/errors/401');

    # clear the smarty cache and compile directories
    $this->oSmarty->clearCompiledTemplate();
    $this->oSmarty->clearCache(null, WEBSITE_MODE);

    # clear all the translation directories
    # but save the success message first
    $sSuccessMessage = I18n::get('success.update');
    I18n::unsetLanguage();

    return Helper::successMessage($sSuccessMessage, '/admins/');
  }

  /**
   * Recompile the stylesheets.
   *
   * @access public
   * @return
   * @todo test and doc
   *
   */
  public function recompilestylesheets() {
    if ($this->_aSession['user']['role'] < 4)
      return Helper::redirectTo('/errors/401');

    $sStylesheetPath  = WEBSITE_CDN !== '' ? WEBSITE_CDN : '/public';
    $sStylesheetPath .= '/stylesheets';

    Cache::clear(Helper::removeSlash($sStylesheetPath . '/mobile.css'));
    Helper::compileStylesheet(
          Helper::removeSlash('/app/assets/stylesheets/mobile/application.less'),
          Helper::removeSlash($sStylesheetPath . '/mobile.css'),
          false);

    Cache::clear(Helper::removeSlash($sStylesheetPath . '/mobile.min.css'));
    Helper::compileStylesheet(
          Helper::removeSlash('/app/assets/stylesheets/mobile/application.less'),
          Helper::removeSlash($sStylesheetPath . '/mobile.min.css'));

    Cache::clear(Helper::removeSlash($sStylesheetPath . '/core.css'));
    Helper::compileStylesheet(
          Helper::removeSlash('/app/assets/stylesheets/core/application.less'),
          Helper::removeSlash($sStylesheetPath . '/core.css'),
          false);

    Cache::clear(Helper::removeSlash($sStylesheetPath . '/core.min.css'));
    Helper::compileStylesheet(
          Helper::removeSlash('/app/assets/stylesheets/core/application.less'),
          Helper::removeSlash($sStylesheetPath . '/core.min.css'));

    return Helper::successMessage(I18n::get('success.update'), '/admins/');
  }
}