<?php

/**
 * Handle all mail stuff.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
use CandyCMS\Plugins\Recaptcha;

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
      return $this->_show();
  }

  /**
   * Show log overview if we have admin rights.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('mails', $this->_oModel->getOverview());

    $this->setTitle(I18n::get('global.mails'));
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show mail overview if we have admin rights.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function resend() {
    exit(json_encode($this->_oModel->resend($this->_iId) == true));
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
    $bShowCaptcha = class_exists('\CandyCMS\Plugins\Recaptcha') ?
            $this->_aSession['user']['role'] == 0 && SHOW_CAPTCHA :
            false;

    return isset($this->_aRequest[$this->_sController]) ?
            $this->_create($bShowCaptcha) :
            $this->_showCreateMailTemplate();
  }

  /**
   * Create a mail template.
   *
   * Show the create mail form and check data for correct information.
   *
   * @access protected
   * @return string HTML content
   * @todo rename to _show?
   *
   */
  protected function _showCreateMailTemplate() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'create');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'create');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $sUser = $this->__autoload('Users', true);
    $aUser = $sUser::getUserNamesAndEmail($this->_iId);

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

    $sFullname = trim($aUser['name'] . ' ' . $aUser['surname']);
		$sFullname = empty($sFullname) ? WEBSITE_NAME : $sFullname;

    $this->setTitle($sFullname . ' - ' . I18n::get('global.contact'));
    $this->setDescription(I18n::get('mails.description.show', $sFullname));

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Check if required data is given or throw an error instead.
   * If data is correct, send mail.
   *
   * @access protected
   * @param boolean $bShowCaptcha Show the captcha?
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create($bShowCaptcha = true) {
    $this->_setError('content')->_setError('email');

    if ($bShowCaptcha === true && Recaptcha::getInstance()->checkCaptcha($this->_aRequest) === false)
        $this->_aError['captcha'] = I18n::get('error.captcha.incorrect');

    if (isset($this->_aError))
      return $this->_showCreateMailTemplate();

    else {
      # Select user name and surname
      $sModel = $this->__autoload('Users', true);
      $oClass = new $sModel($this->_aRequest, $this->_aSession);
      $aRow   = $oClass::getUserNamesAndEmail($this->_iId);

      # If ID is specified and user not found => 404
      if (!$aRow && $this->_iId)
        return Helper::redirectTo('/errors/404');

      $aData['from_name']   = isset($this->_aSession['user']['name']) ?
              $this->_aSession['user']['name'] :
              I18n::get('global.system');

      $aData['subject']     = isset($this->_aRequest[$this->_sController]['subject']) &&
              $this->_aRequest[$this->_sController]['subject'] ?
              Helper::formatInput($this->_aRequest[$this->_sController]['subject']) :
              I18n::get('mails.subject.by', $aData['from_name']);

      $aData['message']     = Helper::formatInput($this->_aRequest[$this->_sController]['content']);
      $aData['to_name']     = isset($aRow['name']) ? $aRow['name'] : '';
      $aData['to_address']  = isset($aRow['email']) ? $aRow['email'] : WEBSITE_MAIL;
      $aData['from_name']   = isset($this->_aSession['user']['name']) ? $this->_aSession['user']['name'] : '';
      $aData['from_address']= Helper::formatInput($this->_aRequest[$this->_sController]['email']);

      $bStatus = $this->_oModel->create($aData);

      Logs::insert( $this->_aRequest['controller'],
                    'create',
                    (int) $this->_iId,
                    $this->_aSession['user']['id'],
                    '', '', $bStatus);

      return $bStatus === true ?
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
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'success');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'success');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $this->setTitle(I18n::get('mails.success_page.title'));

    $this->oSmarty->setCaching(\CandyCMS\Core\Helpers\SmartySingleton::CACHING_LIFETIME_SAVED);
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * There is no update Action for the mails Controller
   *
   * @access public
   *
   */
  public function update() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->update()');
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no destroy Action for the mails Controller
   *
   * @access public
   *
   */
  public function destroy() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->destroy()');
    return Helper::redirectTo('/errors/404');
  }
}