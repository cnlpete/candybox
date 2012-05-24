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
      if (!empty($this->_iId))
        return Helper::redirectTo('/' . $this->_aRequest['controller'] . '/' . $this->_iId . '/create');
      else
        return Helper::redirectTo('/' . $this->_aRequest['controller'] . '/create');
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

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $this->oSmarty->assign('mails', $this->_oModel->getOverview());
    }

    $this->setTitle(I18n::get('global.mails'));
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
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
            $this->_showCreateMailTemplate($bShowCaptcha);
  }

  /**
   * Create a mail template.
   *
   * Show the create mail form and check data for correct information.
   *
   * @access protected
   * @param boolean $bShowCaptcha show captcha or not.
   * @return string HTML content
   * @todo rename to _show?
   *
   */
  protected function _showCreateMailTemplate($bShowCaptcha) {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'create');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'create');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $sUser = $this->__autoload('Users', true);
    $aUser = $sUser::getUserNamesAndEmail($this->_iId);

    if (!$aUser) {
      if ($this->_iId)
        return Helper::redirectTo('/errors/404');

      else
        $aUser['name'] = I18n::get('global.system');
    }

    $this->oSmarty->assign('user', $aUser);

    foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
      $this->oSmarty->assign($sInput, $sData);

    if ($bShowCaptcha === true)
      $this->oSmarty->assign('_captcha_', Recaptcha::getInstance()->show());

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $sFullname = $aUser['name'] . ' ' . $aUser['surname'];
    $this->setTitle(I18n::get('global.contact') . ' ' . $sFullname);
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
      return $this->_showCreateMailTemplate($bShowCaptcha);

    else {
      # Select user name and surname
      $oClass = $this->__autoload('Users', true);
      $sModel = new $oClass($this->_aRequest, $this->_aSession);
      $aRow   = $sModel::getUserNamesAndEmail($this->_iId);

      # if id is specified, but user not found => 404
      if (!$aRow && $this->_iId)
        return Helper::redirectTo('/errors/404');

      $sSendersName = isset($this->_aSession['user']['name']) ?
              $this->_aSession['user']['name'] :
              I18n::get('global.system');

      $sSubject = isset($this->_aRequest[$this->_sController]['subject']) && $this->_aRequest[$this->_sController]['subject'] ?
              Helper::formatInput($this->_aRequest[$this->_sController]['subject']) :
              I18n::get('mails.subject.by', $sSendersName);

      $bStatus = $this->_oModel->create($sSubject,
              Helper::formatInput($this->_aRequest[$this->_sController]['content']),
              isset($aRow['name']) ? $aRow['name'] : '',
              isset($aRow['email']) ? $aRow['email'] : WEBSITE_MAIL,
              isset($this->_aSession['user']['name']) ? $this->_aSession['user']['name'] : '',
              Helper::formatInput($this->_aRequest[$this->_sController]['email']));


      Logs::insert($this->_aRequest['controller'], 'create', (int) $this->_iId, 0, '', '', $bStatus);

      if ($bStatus == true)
        return $this->_showSuccessPage();

      else
        Helper::errorMessage(I18n::get('error.mail.create'), '/users/' . $this->_iId);
    }
  }

  /**
   * Show success message after mail is sent.
   *
   * @access private
   * @return string HTML success page.
   *
   */
  private function _showSuccessPage() {
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
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no destroy Action for the mails Controller
   *
   * @access public
   *
   */
  public function destroy() {
    return Helper::redirectTo('/errors/404');
  }
}
