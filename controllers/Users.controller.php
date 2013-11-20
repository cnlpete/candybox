<?php

/**
 * CRUD action of users.
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
use candyCMS\Core\Helpers\Upload;
use candyCMS\Core\Helpers\PluginManager;

class Users extends Main {

  /**
   * Show user.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'show');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $aData = $this->_oModel->getId($this->_iId);

      if (!isset($aData) || !$aData['id'])
        return Helper::redirectTo('/errors/404');

      $this->oSmarty->assign('user', $aData);

      $this->setTitle($aData['full_name']);
      $this->setDescription(I18n::get('users.description.show', $aData['full_name']));
    }

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Show user overview.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    else {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

      if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
        $this->oSmarty->assign('user', $this->_oModel->getOverview());

        $this->oSmarty->assign('_pagination_',
                $this->_oModel->oPagination->showPages('/' . $this->_sController));
      }

      $this->setTitle(I18n::get('users.title.overview'));
      return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
    }
  }

  /**
   * Adjust the rights, only administrators and moderators should be able to see the list
   *
   * @access protected
   * @return string json
   *
   */
  protected function _overviewJSON() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::errorMessage(I18n::get('error.401.info'), '/errors/401', array('type' => 'json'));

    return parent::_overviewJSON();
  }

  /**
   * Build form template to create or update a user.
   *
   * @access protected
   * @param boolean $bUseRequest whether the displayed data should be overwritten by query result.
   * This is also not the same type as in parents method (boolean vs string), but since it's
   * overwritten, it doesn't matter.
   * @param string $sTitle title to show (only for E_STRICT)
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($bUseRequest = false, $sTitle = '') {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, '_form');
    $this->oSmarty->setTemplateDir($oTemplate);

    # Set user id of person to update
    $iId =  $this->_iId !== $this->_aSession['user']['id'] && $this->_aSession['user']['role'] == 4 ?
            $this->_iId :
            $this->_aSession['user']['id'];

    # Fetch data from database
    $aData = $this->_oModel->getId($iId, true);

    # Set title
    $this->setTitle(vsprintf(I18n::get('users.title.update'), $aData['full_name']));

    # Add the gravatar_urls, so the user can preview those.
    Helper::createAvatarURLs($aData, $aData['id'], $aData['email'], true,  'gravatar_');
    Helper::createAvatarURLs($aData, $aData['id'], $aData['email'], false, 'standard_');

    # Override if we want to use request
    if ($bUseRequest) {
      foreach ($aData as $sColumn => $sData)
        $aData[$sColumn] = isset($this->_aRequest[$this->_sController][$sColumn]) ?
                $this->_aRequest[$this->_sController][$sColumn] :
                $sData;
    }

    foreach ($aData as $sColumn => $sData)
      $this->oSmarty->assign($sColumn, $sData);

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $oPluginManager = PluginManager::getInstance();
    $this->oSmarty->assign('editorinfo', $oPluginManager->getEditorInfo());

    $this->oSmarty->assign('uid', $iId);

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Upload user profile image.
   *
   * @access public
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function avatar() {
    if ($this->_aSession['user']['id'] == 0)
      return Helper::errorMessage(I18n::get('error.session.create_first'), '/sessions/create');

    elseif ($this->_aSession['user']['id'] !== $this->_iId && $this->_aSession['user']['role'] < 4)
      return Helper::redirectTo('/errors/401');

    else
      return isset($this->_aRequest[$this->_sController]) ||
              isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
              $this->_updateAvatar() :
              $this->_showFormTemplate();
  }

  /**
   * Upload user profile image.
   *
   * Check for required fields, show form if fields are missing,
   * otherwise upload new avatar, unset Gravatar on success and redirect to user profile
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function _updateAvatar() {
    $this->_setError('terms', I18n::get('error.form.missing.terms'));
    $this->_setError('image');

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Upload.helper.php';
    $oUpload = new Upload($this->_aRequest, $this->_aSession, $this->_aFile);

    try {
      if ($oUpload->uploadAvatarFile(false) === true) {
        $this->_oModel->updateGravatar($this->_iId);

        $aFileName = $oUpload->getIds();
        $sFileMime = $oUpload->getFileMimeType();

        $this->_aRequest['fileData'] = array(
            'popup'     => 'data:' . $sFileMime . ';base64,' .
            base64_encode(file_get_contents(Helper::removeSlash(PATH_UPLOAD) . '/users/popup/' . $aFileName[0])),
            'thumbnail' => 'data:' . $sFileMime . ';base64,' .
            base64_encode(file_get_contents(Helper::removeSlash(PATH_UPLOAD) . '/users/64/' . $aFileName[0]))
        );

        return Helper::successMessage(I18n::get('success.upload'),
                '/' . $this->_sController . '/' . $this->_iId,
                $this->_aRequest);
      }

      else
        return Helper::errorMessage(I18n::get('error.file.upload'),
                '/' . $this->_sController . '/' . $this->_iId . '/update',
                $this->_aRequest);
    }
    catch (\AdvancedException $e) {
      return Helper::errorMessage($e->getMessage(),
              '/' . $this->_sController . '/' . $this->_iId . '/update',
                $this->_aRequest);
    }
  }

  /**
   * Update a users password.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function password() {
    if ($this->_aSession['user']['id'] == 0)
      return Helper::errorMessage(I18n::get('error.session.create_first'), '/sessions/create');

    elseif ($this->_aSession['user']['id'] !== $this->_iId && $this->_aSession['user']['role'] < 4)
      return Helper::redirectTo('/errors/401');

    else
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
   * @param integer $iUserRole required user right, only for E_STRICT
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function create( $iUserRole = 3 ) {
    if($this->_aSession['user']['role'] > 0 && $this->_aSession['user']['role'] < 4)
      return Helper::redirectTo('/errors/401');

    else
      return isset($this->_aRequest[$this->_sController]) ?
              $this->_create() :
              $this->_showCreateUserTemplate();
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
  protected function _create() {
    $this->_setError('name')->_setError('surname')->_setError('email')->_setError('password');

    if ($this->_oModel->getExistingUser($this->_aRequest[$this->_sController]['email']))
      $this->_aError['email'] = I18n::get('error.user.create.email');

    if ($this->_aRequest[$this->_sController]['password'] !== $this->_aRequest[$this->_sController]['password2'])
      $this->_aError['password'] = I18n::get('error.passwords');

    # Admin does not need to confirm terms
    if ($this->_aSession['user']['role'] < 4 && !isset($this->_aRequest[$this->_sController]['terms']))
      $this->_aError['terms'] = I18n::get('error.form.missing.terms');

    # Do the captchaCheck for for not logged in users
    if ($this->_aSession['user']['role'] == 0) {
      $oPluginManager = PluginManager::getInstance();
      $oPluginManager->checkCaptcha($this->_aError);
    }

    # Generate verification code for users (double-opt-in) when not created by admin.
    $aOptions['verification_code'] = $this->_aSession['user']['role'] < 4 ? Helper::createRandomChar(16) : '';

    if (isset($this->_aError))
      return $this->_showCreateUserTemplate();

    else {
      $bReturn = $this->_oModel->create( $aOptions ) === true;

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    $this->_oModel->getLastInsertId('users'),
                    $this->_aSession['user']['id'],
                    '', '', $bReturn);

      if ($bReturn) {
        $this->oSmarty->clearControllerCache($this->_sController);

        # Send email if user has registered and creator is not an admin.
        if ($this->_aSession['user']['role'] < 4) {
          $sModel = $this->__autoload('Mails', true);
          $oMails = new $sModel($this->_aRequest, $this->_aSession);

          $aMail['to_address']  = Helper::formatInput($this->_aRequest[$this->_sController]['email']);
          $aMail['to_name']     = Helper::formatInput($this->_aRequest[$this->_sController]['name']);
          $aMail['subject']     = I18n::get('users.mail.subject');
          $aMail['message']     = I18n::get('users.mail.body',
                  Helper::formatInput($this->_aRequest[$this->_sController]['name']),
                  Helper::createLinkTo('users/' . $iVerificationCode . '/verification'));

          $oMails->create($aMail);
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
  protected function _showCreateUserTemplate() {
    # We don't support JSON for this template
    if (isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'])
      return $this->_showFormTemplateJSON();

    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'create');
    $this->oSmarty->setTemplateDir($oTemplate);

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

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Update an user.
   *
   * Update entry or show form template if we have enough rights.
   *
   * @access public
   * @param integer $iUserRole required user right, only for E_STRICT
   * @return string HTML content
   *
   */
  public function update($iUserRole = 4) {
    if ($this->_aSession['user']['id'] == 0)
      return Helper::errorMessage(I18n::get('error.session.create_first'), '/sessions/create');

    elseif ($this->_aSession['user']['id'] !== $this->_iId && $this->_aSession['user']['role'] < $iUserRole)
      return Helper::redirectTo('/errors/401');

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
        $this->oSmarty->clearControllerCache($this->_sController);

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
   * @param integer $iUserRole required user right, only for E_STRICT
   * @return boolean status message
   *
   */
  public function destroy($iUserRole = 4) {
    if (!$this->_iId)
      return Helper::redirectTo('/errors/403');

    else
      return ( (isset($this->_aRequest[$this->_sController]) && $this->_aSession['user']['id'] == $this->_iId)) ||
              $this->_aSession['user']['role'] == $iUserRole ?
              $this->_destroy() :
              Helper::errorMessage(I18n::get('error.401.title'), '/', $this->_aRequest);
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
    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Upload.helper.php';
    $aUser = $this->_oModel->getUserNameAndEmail($this->_iId);

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
      return Helper::errorMessage(I18n::get('error.401.title'), '/', $this->_aRequest);

    if ($bCorrectPassword === true) {
      $bReturn = $this->_oModel->destroy($this->_iId) === true;

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    (int) $this->_iId,
                    $this->_aSession['user']['id'],
                    '', '', $bReturn);

      if ($bReturn) {
        $this->oSmarty->clearControllerCache($this->_sController);

        # Unsubscribe from newsletter
        $this->_unsubscribeFromNewsletter($aUser['email']);

        # Destroy profile image
        Upload::destroyAvatarFiles($this->_iId);

        return Helper::successMessage(I18n::get('success.destroy'), $sSuccessRedirectUrl, $this->_aRequest);
      }
      else
        return Helper::errorMessage(I18n::get('error.sql'), $sFailureRedirectUrl, $this->_aRequest);
    }
    else
      return Helper::errorMessage(I18n::get('error.user.destroy.password'), $sFailureRedirectUrl, $this->_aRequest);
  }

  /**
   * Verify email address.
   *
   * @access public
   * @return string message
   * @todo remove bug: When a user is registered to the list BEFORE registering
   * (only if newsletter is re-enabled)
   * to the CMS an exception is thrown
   *
   */
  public function verification() {
    if (!isset($this->_aRequest['code']) || empty($this->_aRequest['code']))
      return Helper::errorMessage(I18n::get('error.missing.id'), '/');

    elseif ($this->_oModel->verifyEmail($this->_aRequest['code']) === true) {
      # Subscribe to MailChimp after email address is confirmed
      # We need to fetch user data by SESSION since we don't grab that from cache
      # any longer.
      #
      # THIS FUNCTION IS DISABLED TO TO BUG DESCRIBED AVOBE
      #$sModel   = $this->__autoload('Sessions', true);
      #$oSession = new $sModel($this->_aRequest, $this->_aSession);

      #$this->__autoload('Newsletters', true);
      #Newsletters::_subscribeToNewsletter($oSession->getUserBySession());

      $this->oSmarty->clearControllerCache($this->_sController);
      return Helper::successMessage(I18n::get('success.user.verification'), '/');
    }

    else
      return Helper::errorMessage(I18n::get('error.user.verification'), '/');
  }

  /**
   * Get the API token of a user.
   *
   * USE THAT METHOD WITH SSL ONLY!
   *
   * @access public
   * @return string JSON token or null
   *
   */
  public function token() {
    if (isset($this->_aRequest['email']) && isset($this->_aRequest['password']))
      $sToken = $this->_oModel->getToken();

    header('Content-Type: application/json');
    return isset($sToken) && $sToken ?
              json_encode(array('success' => true,  'error' => '', 'token' => $sToken)) :
              json_encode(array('success' => false, 'error' => 'No matching results.', 'token' => ''));
  }
}