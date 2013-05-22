<?php

/**
 * Start a search.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.5
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

class Searches extends Main {

  /**
   * Search string.
   *
   * @var string
   * @access protected
   *
   */
  protected $_sSearch;

  /**
   * Show search results.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    # Bugfix: Use search via form or via URL
    if (isset($this->_aRequest['search']))
      $this->_aRequest[$this->_sController]['search'] =& $this->_aRequest['search'];

    if (!isset($this->_aRequest[$this->_sController]['search']))
      return $this->_create();

    else {
      $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
      $this->oSmarty->setTemplateDir($oTemplate);

      $sString = Helper::formatInput(urldecode($this->_aRequest[$this->_sController]['search']));
      $this->oSmarty->assign('string', urlencode($sString));

      if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
        $aSitemapModels = array_filter( array_map('trim', explode(',', DATA_SEARCHES)));
        $aResults = array();
        foreach ($aSitemapModels as $sSitemapModel) {
          $sModel = $this->__autoload($sSitemapModel, true);
          $oModel = new $sModel($this->_aRequest, $this->_aSession);
          $aResults[strtolower($sSitemapModel)] = $oModel->search($sString, strtolower($sSitemapModel));
        }
        $this->oSmarty->assign('tables', $aResults);

        $this->setTitle(I18n::get('searches.title.show', $sString));
        $this->setDescription(I18n::get('searches.description.show', $sString));
      }

      return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
    }
  }

  /**
   * Show the search form, this is a create action since it creates a new search.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _create() {
    $this->oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
    return $this->_formTemplate();
  }

  /**
   * Provide a search form template.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _formTemplate() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, '_form');
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->setTitle(I18n::get('global.search'));

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }
}
