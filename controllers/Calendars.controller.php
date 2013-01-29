<?php

/**
 * CRUD action of simple calendar.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;

class Calendars extends Main {

  /**
   * Show calendar overview.
   *
   * @access protected
   * @return string HTML content or exit if action does ics output
   *
   */
  protected function _show() {
    # Show single .ics file
    return $this->_iId ?
            $this->_ics($this->_iId) :
            $this->_overview();
  }

  /**
   * Helper method to redirect archive action to show overview.
   *
   * @access public
   * @return string HTML
   *
   */
  public function archive() {
    return $this->_overview();
  }

  /**
   * Show single event as ics file.
   * This needs to be specified as ajax, since there should be no surrounding templates.
   *
   * @access protected
   * @param integer $iId ID to show
   * @return string ICS-File
   *
   */
  protected function _ics($iId) {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'ics');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'ics');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $aData = $this->_oModel->getId($iId);
      $this->oSmarty->assign('calendar', $aData);

      if (!$aData['id'])
        return Helper::redirectTo('/errors/404');
    }

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=' . $aData['title_encoded'] . '.ics');

    exit($this->oSmarty->fetch($sTemplateFile, UNIQUE_ID));
  }

  /**
   * Show the overview.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'overview');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'overview');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if ($this->_iId) {
      $this->setTitle(I18n::get('global.calendar') . ' - ' . I18n::get('global.archive') . ' ' . $this->_iId);
      $this->setDescription(I18n::get('global.calendar') . ' - ' . I18n::get('global.archive') . ' ' . $this->_iId);
    }

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('calendar', $this->_oModel->getOverview());

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show the iCalFeed.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function iCalFeed() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'icalfeed');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'icalfeed');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('calendar', $this->_oModel->getOverview());

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=' . WEBSITE_NAME . '.ics');

    exit($this->oSmarty->fetch($sTemplateFile, UNIQUE_ID));
  }

  /**
   * Create a calendar entry.
   *
   * @access protected
   * @param string $sRedirectURL specify the URL to redirect to after execution, only for E_STRICT
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create($sRedirectURL = '') {
    $this->_setError('start_date');

    return parent::_create();
  }

  /**
   * Update a calendar entry.
   *
   * @access protected
   * @param string $sRedirectURL specify the URL to redirect to after execution, only for E_STRICT
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _update($sRedirectURL = '') {
    $this->_setError('start_date');

    return parent::_update('/' . $this->_sController);
  }
}
