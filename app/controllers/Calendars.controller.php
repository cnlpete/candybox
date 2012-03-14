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

namespace CandyCMS\Controller;

use CandyCMS\Helper\Helper as Helper;
use CandyCMS\Helper\I18n as I18n;
use Smarty;

class Calendars extends Main {

	/**
	 * Show calendar overview.
	 *
	 * @access protected
	 * @return string HTML content
	 *
	 */
	protected function _show() {
    # Show .ics
    if (!empty($this->_iId) && !isset($this->_aRequest['action'])) {
			$this->oSmarty->assign('calendar', $this->_oModel->getData($this->_iId));

      header('Content-type: text/calendar; charset=utf-8');
      header('Content-Disposition: inline; filename=' . I18n::get('global.event') . '.ics');

      $this->oSmarty->setTemplateDir(Helper::getTemplateDir($this->_aRequest['controller'], 'ics'));
      $this->oSmarty->display('ics.tpl', UNIQUE_ID);
			exit();
    }

    # Show overview
    else {
      $sTemplateDir		= Helper::getTemplateDir($this->_aRequest['controller'], 'show');
      $sTemplateFile	= Helper::getTemplateType($sTemplateDir, 'show');

			$this->oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
      $this->oSmarty->setTemplateDir($sTemplateDir);

			if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
				$this->oSmarty->assign('calendar', $this->_oModel->getData($this->_iId));

      return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
    }
	}

  /**
	 * Build form template to create or update a calendar entry.
	 *
	 * @access protected
	 * @return string HTML content
	 *
	 */
	protected function _showFormTemplate() {
		# Update
		if (!empty($this->_iId))
			$aData = & $this->_oModel->getData($this->_iId, true);

		# Create
		else {
			$aData['content']			= isset($this->_aRequest['content']) ? $this->_aRequest['content'] : '';
			$aData['end_date']		= isset($this->_aRequest['end_date']) ? $this->_aRequest['end_date'] : '';
			$aData['start_date']	= isset($this->_aRequest['start_date']) ? $this->_aRequest['start_date'] : '';
			$aData['title']				= isset($this->_aRequest['title']) ? $this->_aRequest['title'] : '';
		}

		foreach ($aData as $sColumn => $sData)
			$this->oSmarty->assign($sColumn, $sData);

		if (!empty($this->_aError))
			$this->oSmarty->assign('error', $this->_aError);

    $sTemplateDir		= Helper::getTemplateDir($this->_aRequest['controller'], '_form');
    $sTemplateFile	= Helper::getTemplateType($sTemplateDir, '_form');

    $this->oSmarty->setTemplateDir($sTemplateDir);
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
	}

  /**
	 * Create a download entry.
	 *
	 * Check if required data is given or throw an error instead.
	 * If data is given, activate the model, insert them into the database and redirect afterwards.
	 *
	 * @access protected
	 * @return string|boolean HTML content (string) or returned status of model action (boolean).
   * @todo remove when start date is set
	 *
	 */
	protected function _create() {
		$this->_setError('title');
		$this->_setError('start_date');

		if (isset($this->_aError))
			return $this->_showFormTemplate();

		elseif ($this->_oModel->create() === true) {
			$this->oSmarty->clearCache(null, $this->_aRequest['controller']);

			Logs::insert($this->_aRequest['controller'],
									$this->_aRequest['action'],
									$this->_oModel->getLastInsertId('calendars'),
									$this->_aSession['user']['id']);

			return Helper::successMessage(I18n::get('success.create'), '/calendars');
		}
		else
			return Helper::errorMessage(I18n::get('error.sql'), '/calendars');
	}

	/**
	 * Update a calendar entry.
	 *
	 * Activate model, insert data into the database and redirect afterwards.
	 *
	 * @access protected
	 * @return boolean status of model action
   * @todo remove when start_date is set
	 *
	 */
	protected function _update() {
		$this->_setError('title');
		$this->_setError('start_date');

		if (isset($this->_aError))
			return $this->_showFormTemplate();

		elseif ($this->_oModel->update((int) $this->_aRequest['id']) === true) {
			$this->oSmarty->clearCache(null, $this->_aRequest['controller']);

			Logs::insert($this->_aRequest['controller'],
									$this->_aRequest['action'],
									(int) $this->_aRequest['id'],
									$this->_aSession['user']['id']);

			return Helper::successMessage(I18n::get('success.update'), '/calendars');
		}
		else
			return Helper::errorMessage(I18n::get('error.sql'), '/calendars');
	}
}