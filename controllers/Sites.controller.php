<?php

/**
 * Show a static page.
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
use candyCMS\Core\Helpers\I18n;

class Sites extends Main {

  /**
   * Print out a static page.
   *
   * An example would be an URL linking to "/sites/welcome" when there is a template named
   * "welcome.tpl" in the static folder defined in the "app/config/Candy.inc.php" (PATH_STATIC -
   * normally located at "/public/_static/").
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sSite = isset($this->_aRequest['site']) ?
            strtolower((string) $this->_aRequest['site']) :
            '';

    if (!file_exists(PATH_STANDARD . '/' . PATH_STATIC_TEMPLATES . '/' . $sSite . '.tpl'))
      return Helper::redirectTo('/errors/404');

    $this->setTitle(ucfirst($sSite));
    return $this->oSmarty->fetch(PATH_STANDARD . '/' . PATH_STATIC_TEMPLATES . '/' . $sSite . '.tpl');
  }

  /**
   * Return an overview over all static pages.
   *
   * @access protected
   * @return string HTML content
   * @todo needs test
   *
   */
  protected function _overview() {
    # Set caching false for now
    $this->oSmarty->setCaching(false);

    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'overview');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'overview');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $aSites = array();
      $oPathDir = opendir(PATH_STANDARD . '/' . PATH_STATIC_TEMPLATES);

      while ($sSite = readdir($oPathDir)) {
        if (substr($sSite, 0, 1) == '.')
          continue;

        $iLen = strlen($sSite);
        $aSites[$sSite]['title']  = ucfirst(substr($sSite, 0, $iLen - 4));
        $aSites[$sSite]['url']    = '/sites/' . $aSites[$sSite]['title'];
      }

      closedir($oPathDir);
      $this->oSmarty->assign('sites', $aSites);
    }

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }
}