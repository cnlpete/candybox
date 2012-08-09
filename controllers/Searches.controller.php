<?php

/**
 * Start a search.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.5
 *
 */

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;

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
  protected function _show() {
    if (!( (isset($this->_aRequest['search']) && $this->_aRequest['search']) ||
            (isset($this->_aRequest[$this->_sController]) && $this->_aRequest[$this->_sController]['search']) ))
      return $this->_create();

    else {
      if (substr(CURRENT_URL, -strlen($this->_sController)) == $this->_sController)
        return Helper::redirectTo ('/' . $this->_sController . '/' .
                urlencode($this->_aRequest[$this->_sController]['search']));

      $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'show');
      $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');
      $this->oSmarty->setTemplateDir($sTemplateDir);

      $sString = Helper::formatInput(urldecode($this->_aRequest['search']));

      if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
        $this->oSmarty->assign('string', urlencode($sString));
        $this->oSmarty->assign('tables', $this->_oModel->getData($sString,
                    array('blogs', 'contents', 'downloads', 'gallery_albums')));

        $this->setTitle(I18n::get('searches.title.show', $sString));
        $this->setDescription(I18n::get('searches.description.show', $sString));
      }

      return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
    }
  }

  /**
   * show the search form, this is a create action since it creates a new search.
   *
   * @access protected
   * @return string HTML content.
   *
   */
  protected function _create() {
    $this->oSmarty->setCaching(\CandyCMS\Core\Helpers\SmartySingleton::CACHING_LIFETIME_SAVED);
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
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, '_form');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, '_form');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $this->setTitle(I18n::get('global.search'));

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }
}