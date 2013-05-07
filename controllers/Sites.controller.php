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
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

class Sites extends Main {

  /**
   * Print out a static page.
   *
   * An example would be an URL linking to "/sites/welcome" when there is a template named
   * "welcome.tpl" located at app/sites/welcome.tpl.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sSite = isset($this->_aRequest['site']) ?
            strtolower((string) $this->_aRequest['site']) :
            '';

    if (!file_exists(PATH_STANDARD . '/app/sites/' . $sSite . '.tpl'))
      return Helper::redirectTo('/errors/404');

    $this->setTitle(ucfirst($sSite));
    $this->oSmarty->setTemplateDir(array('dir' => PATH_STANDARD . '/app/sites'));
    return $this->oSmarty->fetch(array('file' => $sSite . '.tpl'));
  }

  /**
   * Return an overview over all static pages.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    # Set caching false for now
    $this->oSmarty->setCaching(false);

    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $aSites = array();
      $oPathDir = opendir(PATH_STANDARD . '/app/sites');

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

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }
}
