<?php

/**
 * Send newsletter to receipients or users.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;

class Newsletters extends Main {

  /**
   * Redirect to create method due to logic at the dispatcher.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function show() {
    return Helper::redirectTo('/' . $this->_sController . '/create');
  }

  /**
   * Override standard create method due to different user rights.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function create() {
    return parent::create(0);
  }

  /**
   * Create a newsletter subscription. Send email information to mailchimp servers.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function _create() {
    $this->_setError('email');

    if (isset($this->_aError))
      return Helper::errorMessage(I18n::get('error.standard')) .
              $this->_showFormTemplate();

    else
      return $this->_subscribeToNewsletter($this->_aRequest['newsletters'], true) === true ?
              Helper::successMessage(I18n::get('success.newsletter.create'), '/') :
              Helper::errorMessage(I18n::get('error.standard'), '/' . $this->_sController);
  }

  /**
   * Show a form for email subscription.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showFormTemplate() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, '_form');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, '_form');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $this->oSmarty->assign('name', isset($this->_aRequest['name']) ? (string) $this->_aRequest['name'] : '');
    $this->oSmarty->assign('surname', isset($this->_aRequest['surname']) ? (string) $this->_aRequest['surname'] : '');
    $this->oSmarty->assign('email', isset($this->_aRequest['email']) ? (string) $this->_aRequest['email'] : '');

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $this->setTitle(I18n::get('newsletters.title.subscribe'));
    $this->setDescription(I18n::get('newsletters.description.subscribe'));

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }
}