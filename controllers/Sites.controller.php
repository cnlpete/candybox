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

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;

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

  /**
   * There is no create action for the Sites controller.
   *
   * @access public
   *
   */
  public function create() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->create()');
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no update action for the Sites controller.
   *
   * @access public
   *
   */
  public function update() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->update()');
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no destroy action for the Sites controller.
   *
   * @access public
   *
   */
  public function destroy() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->destroy()');
    return Helper::redirectTo('/errors/404');
  }
}