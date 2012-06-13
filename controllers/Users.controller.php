<?php

/**
 * CRUD action of users.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
use CandyCMS\Core\Helpers\Upload;
use CandyCMS\Plugins\Recaptcha;

class Users extends Main {

  /**
   * Route to right action.
   *
   * @access public
   * @return string HTML
   *
   */
  public function show() {
    if (!isset($this->_aRequest['action']))
       $this->_aRequest['action'] = 'show';

    switch ($this->_aRequest['action']) {

      case 'avatar':

        $this->setTitle(I18n::get('users.title.avatar'));
        return $this->updateAvatar();

        break;

      case 'password':

        $this->setTitle(I18n::get('users.title.password'));
        return $this->updatePassword();

        break;

      case 'token':

        $this->setTitle(I18n::get('global.api_token'));
        return $this->getToken();

        break;

      case 'verification':

        $this->setTitle(I18n::get('global.email.verification'));
        return $this->verifyEmail();

        break;

      default:
      case 'show':

        $this->oSmarty->setCaching(\CandyCMS\Core\Helpers\SmartySingleton::CACHING_LIFETIME_SAVED);
        return $this->_show();

        break;
    }
  }

  /**
   * Show user or user overview.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    if ($this->_iId) {
      $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'show');
      $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');
      $this->oSmarty->setTemplateDir($sTemplateDir);

      if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
        $aData = $this->_oModel->getId($this->_iId);

       if (!isset($aData) || !$aData[1]['id'])
          return Helper::redirectTo('/errors/404');

        $this->oSmarty->assign('user', $aData);

        $this->setTitle($aData[1]['full_name']);
        $this->setDescription(I18n::get('users.description.show', $aData[1]['full_name']));
      }

      return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
    }
    else {
      if ($this->_aSession['user']['role'] < 3)
        return Helper::errorMessage(I18n::get('error.missing.permission'), '/');

      else {
        $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'overview');
        $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'overview');
        $this->oSmarty->setTemplateDir($sTemplateDir);

        if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
          $this->oSmarty->assign('user', $this->_oModel->getOverview());

          $this->oSmarty->assign('_pages_',
                  $this->_oModel->oPagination->showPages('/' . $this->_sController));
        }

        $this->setTitle(I18n::get('users.title.overview'));
        return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
      }
    }
  }

  /**
   * Build form template to create or update a user.
   *
   * @access protected
   * @param boolean $bUseRequest whether the Displayed Data should be overwritten by Query Result
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($bUseRequest = false) {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, '_form');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, '_form');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    # Set user id of person to update
    $iId =  $this->_iId !== $this->_aSession['user']['id'] && $this->_aSession['user']['role'] == 4 ?
            $this->_iId :
            $this->_aSession['user']['id'];

    # Fetch data from database
    $aData = $this->_oModel->getId($iId, true);

    # Add the gravatar_urls, so the user can preview those.
    Helper::createAvatarURLs($aData, $aData['id'], $aData['email'], true,  'gravatar_');
    Helper::createAvatarURLs($aData, $aData['id'], $aData['email'], false, 'standard_');

    # Override if we want to use request
    if ($bUseRequest === true) {
      foreach ($aData as $sColumn => $sData)
        $aData[$sColumn] = isset($this->_aRequest[$this->_sController][$sColumn]) ?
                $this->_aRequest[$this->_sController][$sColumn] :
                $sData;
    }

    foreach ($aData as $sColumn => $sData)
      $this->oSmarty->assign($sColumn, $sData);

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $this->oSmarty->assign('uid', $iId);

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Upload user profile image.
   *
   * @access public
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function updateAvatar() {
    return isset($this->_aRequest[$this->_sController]) ?
            $this->_updateAvatar() :
            $this->_showFormTemplate();
  }

  /**
   * Upload user profile image.
   *
   * Check for required Fields, show Form if Fields are missing,
   * otherwise upload new Avatar, unset Gravatar on success and redirect to user Profile
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function _updateAvatar() {
    $this->_setError('terms', I18n::get('error.file.upload'));
    $this->_setError('image');

    require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Upload.helper.php';
    $oUpload = new Upload($this->_aRequest, $this->_aSession, $this->_aFile);

    try {
      if (isset($this->_aError))
        return $this->_showFormTemplate();

      elseif ($oUpload->uploadAvatarFile(false) === true) {
        $this->_oModel->updateGravatar($this->_iId);

        return Helper::successMessage(I18n::get('success.upload'), '/' .
                $this->_sController . '/' . $this->_iId);
      }

      else
        return Helper::errorMessage(I18n::get('error.file.upload'), '/' .
                $this->_sController . '/' . $this->_iId . '/update');
    }
    catch (\Exception $e) {
      return Helper::errorMessage($e->getMessage(), '/' .
                $this->_sController . '/' . $this->_iId . '/update');
    }
  }

  /**
   * Update a users password.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function updatePassword() {
    return isset($this->_aRequest[$this->_sController]) ?
            $this->_updatePassword() :
            $this->_showFormTemplate();
  }

  /**
   * Update a users password.
   *
   * Check for required Fields, show Form if Fields are missing or wrong,
   * otherwise change the password and redirect to user Profile
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _updatePassword() {
    # Check if old password is set
    $this->_setError('password_old', I18n::get('error.user.update.password.old.empty'));

    # Check if new password fields aren't empty
    $this->_setError('password_new', I18n::get('error.user.update.password.new.empty'));
    $this->_setError('password_new2', I18n::get('error.user.update.password.new.empty'));

    # Check if old password is correct, emptyness is checked by _setError
    if (md5(RANDOM_HASH . $this->_aRequest[$this->_sController]['password_old']) !== $this->_aSession['user']['password'])
      $this->_aError['password_old'] = I18n::get('error.user.update.password.old.wrong');

    # Check if new password fields match
    if ($this->_aRequest[$this->_sController]['password_new'] !== $this->_aRequest[$this->_sController]['password_new2'])
      $this->_aError['password_new'] = I18n::get('error.user.update.password.new.match');

    $sRedirectURL = '/' . $this->_sController . '/';

    if (isset($this->_aError))
      return $this->_showFormTemplate();

    elseif ($this->_oModel->updatePassword((int) $this->_iId) === true) {
      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    (int) $this->_iId,
                    $this->_aSession['user']['id']);

      return Helper::successMessage(I18n::get('success.update'), $sRedirectURL . $this->_iId);
    }
    else
      return Helper::errorMessage(I18n::get('error.sql'), $sRedirectURL . $this->_iId);
  }

  /**
   * Create user or show form template.
   *
   * This method must override the parent one because of another showTemplate method.
   *
   * @access public
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function create() {
    # Logged in users should not have a recaptcha field since we can assume that these are real humans.
    $bShowCaptcha = class_exists('\CandyCMS\Plugins\Recaptcha') ?
            $this->_aSession['user']['role'] == 0 && SHOW_CAPTCHA :
            false;

    if($this->_aSession['user']['role'] > 0 && $this->_aSession['user']['role'] < 4)
      return Helper::errorMessage(I18n::get('error.missing.permission'), '/');

    else
      return isset($this->_aRequest[$this->_sController]) ?
              $this->_create($bShowCaptcha) :
              $this->_showCreateUserTemplate($bShowCaptcha);
  }

  /**
   * Create a user.
   *
   * Check if required data is given or throw an error instead.
   * If data is given, activate the model, insert them into the database, send mail and redirect afterwards.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create($bShowCaptcha) {
    $this->_setError('name')->_setError('surname')->_setError('email')->_setError('password');

    if ($this->_oModel->getExistingUser($this->_aRequest[$this->_sController]['email']))
      $this->_aError['email'] = I18n::get('error.user.create.email');

    if ($this->_aRequest[$this->_sController]['password'] !== $this->_aRequest[$this->_sController]['password2'])
      $this->_aError['password'] = I18n::get('error.passwords');

    # Admin does not need to confirm terms
    if ($this->_aSession['user']['role'] < 4 && !isset($this->_aRequest[$this->_sController]['terms']))
      $this->_aError['terms'] = I18n::get('error.form.missing.terms');

    if ($bShowCaptcha === true && Recaptcha::getInstance()->checkCaptcha($this->_aRequest) === false)
        $this->_aError['captcha'] = I18n::get('error.captcha.incorrect');

    # Generate verification code for users (double-opt-in) when not created by admin.
    $iVerificationCode = $this->_aSession['user']['role'] < 4 ? Helper::createRandomChar(12) : '';

    if (isset($this->_aError))
      return $this->_showCreateUserTemplate();

    else {
      $bReturn = $this->_oModel->create($iVerificationCode) === true;

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    $this->_oModel->getLastInsertId('users'),
                    $this->_aSession['user']['id'],
                    '', '', $bReturn);

      if ($bReturn) {
        $this->oSmarty->clearCacheForController($this->_sController);

        # Send email if user has registered and creator is not an admin.
        if ($this->_aSession['user']['role'] < 4) {
          $sModel = $this->__autoload('Mails', true);
          $oMails = new $sModel($this->_aRequest, $this->_aSession);

          $aData['to_address']  = Helper::formatInput($this->_aRequest[$this->_sController]['email']);
          $aData['to_name']     = Helper::formatInput($this->_aRequest[$this->_sController]['name']);
          $aData['subject']     = I18n::get('users.mail.subject');
          $aData['message']     = I18n::get('users.mail.body',
                  Helper::formatInput($this->_aRequest[$this->_sController]['name']),
                  Helper::createLinkTo('users/' . $iVerificationCode . '/verification'));

          $oMails->create($aData);
        }

        return $this->_aSession['user']['role'] == 4 ?
                Helper::successMessage(I18n::get('success.create'), '/' . $this->_sController) :
                Helper::successMessage(I18n::get('success.user.create'), '/');
      }
      else
        return Helper::errorMessage(I18n::get('error.sql'), '/');
    }
  }

  /**
   * Build form template to create a user.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showCreateUserTemplate($bShowCaptcha) {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'create');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'create');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if ($this->_aSession['user']['role'] == 4) {
      $this->setTitle(I18n::get('users.title.create'));

      foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
        $this->oSmarty->assign($sInput, $sData);
    }
    else {
      $this->setTitle(I18n::get('global.registration'));
      $this->setDescription(I18n::get('users.description.create'));

      foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
        $this->oSmarty->assign($sInput, isset($sData) ?
                        Helper::formatInput($sData) :
                        $this->_aSession['user'][$sInput]);
    }

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Update an user.
   *
   * Update entry or show form template if we have enough rights.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function update() {
    if ($this->_aSession['user']['id'] == 0)
      return Helper::errorMessage(I18n::get('error.session.create_first'), '/sessions/create');

    elseif ($this->_aSession['user']['id'] !== $this->_iId && $this->_aSession['user']['role'] < 4)
      return Helper::errorMessage(I18n::get('error.missing.permission'), '/');

    else
      return isset($this->_aRequest[$this->_sController]) ?
              $this->_update() :
              $this->_showFormTemplate();
  }

  /**
   * Update a user.
   *
   * Activate model, insert data into the database and redirect afterwards.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _update() {
    $this->_setError('name');

    if (isset($this->_aError))
      return $this->_showFormTemplate();

    else {
      $bReturn = $this->_oModel->update((int) $this->_iId) === true;

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    (int) $this->_iId,
                    $this->_aSession['user']['id'],
                    '', '', $bReturn);

      if ($bReturn) {
        $this->oSmarty->clearCacheForController($this->_sController);

        # Check if user wants to unsubscribe from mailchimp
        if (!isset($this->_aRequest[$this->_sController]['receive_newsletter']))
          $this->_unsubscribeFromNewsletter(Helper::formatInput(($this->_aRequest[$this->_sController]['email'])));

        else
          $this->_subscribeToNewsletter($this->_aRequest);

        return Helper::successMessage(I18n::get('success.update'), '/' . $this->_sController . '/' . $this->_iId);
      }
      else
        return Helper::errorMessage(I18n::get('error.sql'), '/' . $this->_sController . '/' . $this->_iId);
    }
  }

  /**
   * Delete a user account.
   *
   * @access public
   * @return boolean status message
   *
   */
  public function destroy() {
    return (isset($this->_aRequest[$this->_sController]) && $this->_aSession['user']['id'] == $this->_iId) ||
            $this->_aSession['user']['role'] == 4 ?
            $this->_destroy() :
            Helper::errorMessage(I18n::get('error.missing.permission'), '/');
  }

  /**
   * Delete a user account.
   *
   * Check if the ids match or if the user is admin,
   * delete the user from database and redirect afterwards
   *
   * @access protected
   * @return boolean status message
   *
   */
  protected function _destroy() {
    require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Upload.helper.php';
    $aUser = $this->_oModel->getUserNamesAndEmail($this->_iId);

    # Do IDs match?
    if (isset($this->_aRequest[$this->_sController]) && $this->_aSession['user']['id'] == $this->_iId) {
      $bCorrectPassword = md5(RANDOM_HASH . $this->_aRequest[$this->_sController]['password']) === $this->_aSession['user']['password'];
      $sSuccessRedirectUrl = '/';
      $sFailureRedirectUrl = '/' . $this->_sController . '/' . $this->_aSession['user']['id'] . '/update#user-destroy';
    }

    # Admin can delete everybody
    elseif ($this->_aSession['user']['role'] == 4) {
      $bCorrectPassword = true;
      $sSuccessRedirectUrl = '/' . $this->_sController;
      $sFailureRedirectUrl = $sSuccessRedirectUrl;
    }

    # No admin and not the active user
    else
      return Helper::errorMessage(I18n::get('error.missing.permission'), '/');

    if ($bCorrectPassword === true) {
      $bReturn = $this->_oModel->destroy($this->_iId) === true;

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    (int) $this->_iId,
                    $this->_aSession['user']['id'],
                    '', '', $bReturn);

      if ($bReturn) {
        $this->oSmarty->clearCacheForController($this->_sController);

        # Unsubscribe from newsletter
        $this->_unsubscribeFromNewsletter($aUser['email']);

        # Destroy profile image
        Upload::destroyAvatarFiles($this->_iId);

        return Helper::successMessage(I18n::get('success.destroy'), $sSuccessRedirectUrl);
      }
      else
        return Helper::errorMessage(I18n::get('error.sql'), $sFailureRedirectUrl);
    }
    else
      return Helper::errorMessage(I18n::get('error.user.destroy.password'), $sFailureRedirectUrl);
  }

  /**
   * Verify email address.
   *
   * @access public
   * @return boolean status of message
   *
   */
  public function verifyEmail() {
    if (!isset($this->_aRequest['code']) || empty($this->_aRequest['code']))
      return Helper::errorMessage(I18n::get('error.missing.id'), '/');

    elseif ($this->_oModel->verifyEmail($this->_aRequest['code']) === true) {
      # Subscribe to MailChimp after email address is confirmed
      $this->_subscribeToNewsletter($this->_oModel->getActivationData());

      $this->oSmarty->clearCacheForController('users');

      return Helper::successMessage(I18n::get('success.user.verification'), '/');
    }

    else
      return Helper::errorMessage(I18n::get('error.user.verification'), '/');
  }

  /**
   * Get the API token of a user.
   *
   * @access public
   * @return string token or null
   *
   */
  public function getToken() {
    $this->_setError('email');
    $this->_setError('password');

    if (!$this->_aError)
      $sToken = $this->_oModel->getToken();

    return isset($sToken) && $sToken ?
            json_encode(array('success' => true, 'token' => $sToken)) :
            json_encode(array('success' => false));
  }
}