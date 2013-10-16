<?php

/**
 * Send newsletter to receipients or users.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

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
   * @param integer $iUserRole required user right (only for E_STRICT)
   * @return string HTML content
   *
   */
  public function create($iUserRole = 0) {
    return parent::create(0);
  }

  /**
   * Create a newsletter subscription. Send email information to mailchimp servers.
   *
   * @access public
   * @param string $sRedirectURL specify the URL to redirect to after execution (only for E_STRICT)
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
   * @param string $sTemplateName name of form template (only for E_STRICT)
   * @param string $sTitle title to show (only for E_STRICT)
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, $sTemplateName);
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->oSmarty->assign('name', isset($this->_aRequest['name']) ? (string) $this->_aRequest['name'] : '');
    $this->oSmarty->assign('surname', isset($this->_aRequest['surname']) ? (string) $this->_aRequest['surname'] : '');
    $this->oSmarty->assign('email', isset($this->_aRequest['email']) ? (string) $this->_aRequest['email'] : '');

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $this->setTitle(I18n::get('newsletters.title.subscribe'));
    $this->setDescription(I18n::get('newsletters.description.subscribe'));

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Subscribe to newsletter list.
   *
   * @static
   * @access protected
   * @param array $aData user data
   * @param boolean $bDoubleOptIn decide if we have to use double opt-in
   * @return boolean status of subscription
   *
   */
  protected static function _subscribeToNewsletter($aData, $bDoubleOptIn = false) {
    $oMailchimp = new \Mailchimp(MAILCHIMP_API_KEY);

    $aMailChimp = $oMailchimp->call('lists/subscribe', array(
            'id'            => MAILCHIMP_LIST_ID,
            'email'         => array('email' => $aData['email']),
            'merge_vars'    => array('FNAME' => $aData['name'], 'LNAME' => $aData['surname']),
            'double_optin'  => $bDoubleOptIn,
            'send_welcome'  => true)
          );

    return isset($aMailChimp['leid']) && !empty($aMailChimp['leid']);
  }
}