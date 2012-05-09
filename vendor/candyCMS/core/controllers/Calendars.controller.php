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

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;

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
    if ($this->_iId && !isset($this->_aRequest['action']))
      exit($this->_showEntry());

    # Show overview
    elseif (isset($this->_aRequest['action']) && $this->_aRequest['action'] == 'icalfeed')
      exit($this->_showIcalFeed());

    else
      return $this->_showOverview();
  }

  /**
   * show single event as ics file
   * this needs to be specified as ajax, since there should be no surrounding templates
   *
   * @return string ICS-File
   */
  private function _showEntry() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'ics');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'ics');

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $aData = $this->_oModel->getData($this->_iId);
      $this->oSmarty->assign('calendar', $aData);

      if (!$aData['id'])
        return Helper::redirectTo('/errors/404');
    }

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=' . $aData['title_encoded'] . '.ics');

    $this->oSmarty->setTemplateDir($sTemplateDir);
    return ($this->oSmarty->fetch($sTemplateFile, UNIQUE_ID));
  }

  /**
   * show the overview
   *
   * @return string HTML content
   */
  private function _showOverview() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('calendar', $this->_oModel->getData());

    # Add the current year when in archive mode
    #if (isset($this->_aRequest['action']) && $this->_aRequest['action'] == 'archive')
    #  $this->_aRequest['id'] = $this->_aRequest['id'] ? $this->_aRequest['id'] : date('Y');

    $this->oSmarty->setTemplateDir($sTemplateDir);
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * show the overview
   *
   * @access private
   * @return string HTML content
   *
   */
  private function _showIcalFeed() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'icalfeed');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'icalfeed');

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('calendar', $this->_oModel->getData());

    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=' . WEBSITE_NAME . '.ics');

    $this->oSmarty->setTemplateDir($sTemplateDir);
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
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

    return parent::_update();
  }
}
