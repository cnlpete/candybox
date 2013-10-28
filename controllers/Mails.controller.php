<?php

/**
 * Handle all mail stuff.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\PluginManager;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

class Mails extends Main {

  /**
   * Redirect to create method due to logic at the dispatcher.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function show() {
    if ($this->_aSession['user']['role'] < 4) {
      return !empty($this->_iId) ?
              Helper::redirectTo('/' . $this->_aRequest['controller'] . '/' . $this->_iId . '/create') :
              Helper::redirectTo('/' . $this->_aRequest['controller'] . '/create');
    }
    else
      return $this->_overview();
  }

  /**
   * Show log overview if we have admin rights.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->oSmarty->assign('mails', $this->_oModel->getOverview());

    $this->setTitle(I18n::get('global.mails'));
    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Show mail overview if we have admin rights.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function resend() {
    header('Content-Type: application/json');

    if ($this->_aSession['user']['role'] == 4)
      return json_encode(array(
                  'success' => $this->_oModel->resend($this->_iId),
                  'errors'  => ''
              ));

    else
      return json_encode(array(
                  'success' => false,
                  'errors'  => I18n::get('error.401.title')
              ));
  }

  /**
   * Show a mail form or direct it to the user.
   *
   * Create entry or show form template if we have enough rights. Due to spam bots we provide
   * a captcha and need to override the original method.
   * We must override the main method due to a diffent required user role and a captcha.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function create() {
    return isset($this->_aRequest[$this->_sController]) ?
            $this->_create() :
            $this->_showCreateTemplate();
  }

  /**
   * Create a mail template.
   *
   * Show the create mail form and check data for correct information.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showCreateTemplate() {
    # We don't support JSON
    # @todo put this into a seperated method
    if (isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'])
      return json_encode(array(
                  'success' => false,
                  'error'   => 'There is no JSON handling method called ' . __FUNCTION__ . ' for this controller.'
              ));

    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'create');
    $this->oSmarty->setTemplateDir($oTemplate);

    $sUser = $this->__autoload('Users', true);
    $aUser = $sUser::getUserNameAndEmail($this->_iId);

    if (!$aUser && $this->_iId)
      return Helper::redirectTo('/errors/404');

    $this->oSmarty->assign('user', $aUser);

    # Set own email when logged in
    if ($this->_aSession['user']['email'] && !isset($this->_aRequest[$this->_sController]['email']))
      $this->_aRequest[$this->_sController]['email'] = $this->_aSession['user']['email'];

    foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
      $this->oSmarty->assign($sInput, $sData);

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $oPluginManager = PluginManager::getInstance();
    $this->oSmarty->assign('editorinfo', $oPluginManager->getEditorInfo());

    $sFullname = trim($aUser['name'] . ' ' . $aUser['surname']);
    $sFullname = empty($sFullname) ? WEBSITE_NAME : $sFullname;

    $this->setTitle($sFullname . ' - ' . I18n::get('global.contact'));
    $this->setDescription(I18n::get('mails.description.show', $sFullname));

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Check if required data is given or throw an error instead.
   * If data is correct, send mail.
   *
   * @access protected
   * @param boolean $bShowCaptcha Show the captcha?
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   * @todo make E_STRICT compilant
   *
   */
  protected function _create($bShowCaptcha = true) {
    $this->_setError('content')->_setError('email');

    # do the captchaCheck for for not logged in users
    if ($this->_aSession['user']['role'] == 0) {
      $oPluginManager = PluginManager::getInstance();
      $oPluginManager->checkCaptcha($this->_aError);
    }

    if (isset($this->_aError))
      return $this->_showCreateTemplate();

    else {
      # Select user name and surname
      $sModel = $this->__autoload('Users', true);
      $oClass = new $sModel($this->_aRequest, $this->_aSession);
      $aRow   = $oClass::getUserNameAndEmail($this->_iId);

      # If ID is specified and user not found => 404
      if (!$aRow && $this->_iId)
        return Helper::redirectTo('/errors/404');

      $aMail['from_name']   = isset($this->_aSession['user']['name']) ?
              $this->_aSession['user']['name'] :
              I18n::get('global.system');

      $aMail['subject']     = isset($this->_aRequest[$this->_sController]['subject']) &&
              $this->_aRequest[$this->_sController]['subject'] ?
              Helper::formatInput($this->_aRequest[$this->_sController]['subject']) :
              I18n::get('mails.subject.by', $aMail['from_name']);

      $aMail['message']     = Helper::formatInput($this->_aRequest[$this->_sController]['content']);
      $aMail['to_name']     = isset($aRow['name']) ? $aRow['name'] : '';
      $aMail['to_address']  = isset($aRow['email']) ? $aRow['email'] : WEBSITE_MAIL;
      $aMail['from_name']   = isset($this->_aSession['user']['name']) ? $this->_aSession['user']['name'] : '';
      $aMail['from_address']= Helper::formatInput($this->_aRequest[$this->_sController]['email']);

      $bStatus = $this->_oModel->create($aMail);

      Logs::insert( $this->_aRequest['controller'],
                    'create',
                    (int) $this->_iId,
                    $this->_aSession['user']['id'],
                    '', '', $bStatus);

      return $bStatus ?
              $this->_showSuccessPage() :
              Helper::errorMessage(I18n::get('error.mail.create'), '/users/' . $this->_iId);
    }
  }

  /**
   * Show success message after mail is sent.
   *
   * @access protected
   * @return string HTML success page.
   *
   */
  protected function _showSuccessPage() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'success');
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->setTitle(I18n::get('mails.success_page.title'));

    $this->oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);
    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }
}