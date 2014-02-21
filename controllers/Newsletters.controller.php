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

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;

/**
 * Class Newsletters
 * @package candyCMS\Core\Controllers
 *
 */
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

    # User might have used FB connect, so we might already have some information
    if ($this->_aSession['user']['role'] === 2) {
      $sName = isset($this->_aSession['user']['name']) ?
          (string) $this->_aSession['user']['name'] :
          '';

      $sSurname = isset($this->_aSession['user']['surname']) ?
          (string) $this->_aSession['user']['surname'] :
          '';

      $sEmail = isset($this->_aSession['user']['email']) ?
          (string) $this->_aSession['user']['email'] :
          '';
    }

    # if there is a request, overwrite session data with request data
    $sName = isset($this->_aRequest['newsletters']['name']) ?
        (string) $this->_aRequest['newsletters']['name'] :
        $sName;

    $sSurname = isset($this->_aRequest['newsletters']['surname']) ?
        (string) $this->_aRequest['newsletters']['surname'] :
        $sSurname;

    $sEmail = isset($this->_aRequest['newsletters']['email']) ?
        (string) $this->_aRequest['newsletters']['email'] :
        $sEmail;

    $this->oSmarty->assign('name', $sName);
    $this->oSmarty->assign('surname', $sSurname);
    $this->oSmarty->assign('email', $sEmail);

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
   * @access private
   * @param array $aData user data
   * @param boolean $bDoubleOptIn decide if we have to use double opt-in
   * @return boolean status of subscription
   *
   */
  private static function _subscribeToNewsletter($aData, $bDoubleOptIn = false) {
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