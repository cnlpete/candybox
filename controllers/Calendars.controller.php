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
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

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
   * @todo remove exit
   *
   */
  protected function _ics($iId) {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'ics');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $aData = $this->_oModel->getId($iId);
      $this->oSmarty->assign('calendar', $aData);

      if (!$aData['id'])
        return Helper::redirectTo('/errors/404');
    }

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=' . $aData['title_encoded'] . '.ics');

    exit($this->oSmarty->fetch($oTemplate, UNIQUE_ID));
  }

  /**
   * Show the overview.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    if ($this->_iId) {
      $this->setTitle(I18n::get('global.calendar') . ' - ' . I18n::get('global.archive') . ' ' . $this->_iId);
      $this->setDescription(I18n::get('global.calendar') . ' - ' . I18n::get('global.archive') . ' ' . $this->_iId);
    }

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->oSmarty->assign('calendar', $this->_oModel->getOverview());

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Show the iCalFeed.
   *
   * @access public
   * @return string HTML content
   * @todo remove exit
   *
   */
  public function iCalFeed() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'icalfeed');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->oSmarty->assign('calendar', $this->_oModel->getOverview());

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=' . WEBSITE_NAME . '.ics');

    exit($this->oSmarty->fetch($oTemplate, UNIQUE_ID));
  }

  /**
   * Create a calendar entry.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('start_date');

    # Set a custom redirect url, because otherwise it would prompt to download.
    $this->_sRedirectURL = '/' . $this->_sController;
    return parent::_create();
  }

  /**
   * Update a calendar entry.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _update() {
    $this->_setError('start_date');

    # Set a custom redirect url, because otherwise it would prompt to download.
    $this->_sRedirectURL = '/' . $this->_sController;
    return parent::_update();
  }
}
