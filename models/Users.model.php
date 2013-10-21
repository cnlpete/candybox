<?php

/**
 * Handle all user SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Models;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\PluginManager;
use candyCMS\Core\Helpers\Pagination;
use PDO;

class Users extends Main {

  /**
   * Get user name, surname and email from user ID.
   *
   * @static
   * @access public
   * @param integer $iId ID of the user
   * @return array data with user information
   *
   */
  public static function getUserNameAndEmail($iId) {
    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                name, surname, email
                                              FROM
                                                " . SQL_PREFIX . "users
                                              WHERE
                                                id = :id
                                              LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      return $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Check, if the user already with given email address exists.
   *
   * @static
   * @access public
   * @param string $sEmail email address of user.
   * @return boolean status of user check
   *
   */
  public static function getExistingUser($sEmail) {
    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                email
                                              FROM
                                                " . SQL_PREFIX . "users
                                              WHERE
                                                email = :email
                                              LIMIT 1");

      $oQuery->bindParam('email', $sEmail, PDO::PARAM_STR);
      $oQuery->execute();

      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
      return isset($aResult['email']) && !empty($aResult['email']) ? true : false;
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
    }
  }

  /**
   * Get the verification data from an users email address.
   *
   * @static
   * @access public
   * @param string $sEmail email address to search user from.
   * @return array|boolean user data or false.
   * @see vendor/candycms/core/models/Session.model.php
   *
   */
  public static function getVerificationData($sEmail) {
    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                name,
                                                verification_code
                                              FROM
                                                " . SQL_PREFIX . "users
                                              WHERE
                                                email = :email
                                              AND
                                                verification_code != ''");

      $sEmail = Helper::formatInput($sEmail);
      $oQuery->bindParam('email', $sEmail, PDO::PARAM_STR);
      $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
      return is_array($aRow) ? $aRow : false;
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Sets a users password.
   *
   * @static
   * @access public
   * @param string $sEmail
   * @param string $sPassword
   * @param boolean $bEncrypt
   * @return boolean status of query
   *
   */
  public static function setPassword($sEmail, $sPassword, $bEncrypt = false) {
    $sPassword = $bEncrypt == true ? md5(RANDOM_HASH . $sPassword) : $sPassword;

    try {
      $oQuery = parent::$_oDbStatic->prepare("UPDATE
                                                " . SQL_PREFIX . "users
                                              SET
                                                `password` = :password
                                              WHERE
                                                `email` = :email");

      $sEmail = Helper::formatInput($sEmail);
      $oQuery->bindParam(':password', $sPassword, PDO::PARAM_STR);
      $oQuery->bindParam(':email', $sEmail, PDO::PARAM_STR);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Get user overview data.
   *
   * @access public
   * @param integer $iLimit user overview limit
   * @return array data from _setData
   *
   */
  public function getOverview($iLimit = 50) {
    try {
      $oQuery = $this->_oDb->query("SELECT COUNT(*) FROM " . SQL_PREFIX . "users");
      $iResult = $oQuery->fetchColumn();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Pagination.helper.php';
    $this->oPagination = new Pagination($this->_aRequest, $iResult, $iLimit);

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        u.id,
                                        u.name,
                                        u.email,
                                        u.surname,
                                        UNIX_TIMESTAMP(u.date) as date,
                                        u.use_gravatar,
                                        u.verification_code,
                                        u.role,
                                        (
                                          SELECT
                                            UNIX_TIMESTAMP(s.date) as last_login
                                          FROM
                                            " . SQL_PREFIX . "sessions as s
                                          WHERE
                                            s.user_id = u.id
                                          ORDER BY
                                            s.date DESC
                                          LIMIT
                                            1
                                        ) AS last_login
                                      FROM
                                        " . SQL_PREFIX . "users as u
                                      ORDER BY
                                        date ASC
                                      LIMIT
                                        :offset, :limit");

      $oQuery->bindValue('limit', $this->oPagination->getLimit(), PDO::PARAM_INT);
      $oQuery->bindValue('offset', $this->oPagination->getOffset(), PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];

      $aData[$iId] = $this->_formatForUserOutput(
              $aRow,
              array('id', 'date', 'role'),
              array('use_gravatar'));

      $this->_formatDates($aData[$iId], 'last_login');
    }

    return $aData;
 }

  /**
   * Get user entry data.
   *
   * @access public
   * @param integer $iId Id to work with
   * @param boolean $bUpdate prepare data for update?
   * @return array|boolean array on success, boolean on false
   *
   */
  public function getId($iId = '', $bUpdate = false) {
    if (empty($iId) || $iId < 1)
      return false;

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        u.id,
                                        u.name,
                                        u.email,
                                        u.surname,
                                        UNIX_TIMESTAMP(u.date) as date,
                                        u.use_gravatar,
                                        u.role,
                                        (
                                          SELECT
                                            UNIX_TIMESTAMP(s.date) as last_login
                                          FROM
                                            " . SQL_PREFIX . "sessions as s
                                          WHERE
                                            s.user_id = u.id
                                          ORDER BY
                                            s.date DESC
                                          LIMIT
                                            1
                                        ) AS last_login
                                      FROM
                                        " . SQL_PREFIX . "users as u
                                      WHERE
                                        u.id = :id
                                      LIMIT
                                        1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    if ($bUpdate)
      $aData = $this->_formatForUpdate($aRow);

    else {
      $aData = $this->_formatForUserOutput(
              $aRow,
              array('id', 'role'. 'date'),
              array('use_gravatar'));

      $oPluginManager = PluginManager::getInstance();
      $aData['content'] = $oPluginManager->runContentDisplayPlugins($aData['content']);

      $this->_formatDates($aData, 'last_login');
    }

    return $aData;
  }

  /**
   * Create a user.
   *
   * @access public
   * @param integer $iVerificationCode verification code that was sent to the user.
   * @param integer $iRole role of new User
   * @return boolean status of query
   *
   */
  public function create($aOptions) {
    $aOptions['verification_code']  = isset($aOptions['verification_code']) ? $aOptions['verification_code'] : '';
    $aOptions['role']               = isset($aOptions['role']) ? $aOptions['role'] : 1;

    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "users
                                        ( name,
                                          surname,
                                          password,
                                          email,
                                          date,
                                          role,
                                          verification_code,
                                          api_token,
                                          registration_ip)
                                      VALUES
                                        ( :name,
                                          :surname,
                                          :password,
                                          :email,
                                          NOW(),
                                          :role,
                                          :verification_code,
                                          :api_token,
                                          :registration_ip)");

      $sApiToken = md5(RANDOM_HASH . $this->_aRequest[$this->_sController]['email']);
      $sPassword = md5(RANDOM_HASH . $this->_aRequest[$this->_sController]['password']);
      $oQuery->bindParam('api_token', $sApiToken, PDO::PARAM_STR);
      $oQuery->bindParam('password', $sPassword, PDO::PARAM_STR);
      $oQuery->bindParam('role', $aOptions['role'], PDO::PARAM_INT);
      $oQuery->bindParam('verification_code', $aOptions['verification_code'], PDO::PARAM_STR);
      $oQuery->bindParam('registration_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

      foreach (array('name', 'surname', 'email') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_STR);

        unset($sValue);
      }

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = parent::$_oDbStatic->lastInsertId();

      return $bReturn;
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Update a user.
   *
   * @access public
   * @param integer $iId user ID to update
   * @return boolean status of query
   *
   */
  public function update($iId) {
    if (empty($iId) || $iId < 1)
      return false;

    $iUseGravatar = isset($this->_aRequest[$this->_sController]['use_gravatar']) ? 1 : 0;

    # Set other peoples user roles
    if ($iId !== $this->_aSession['user']['id'] && $this->_aSession['user']['role'] == 4)
      $iUserRole = isset($this->_aRequest[$this->_sController]['role']) && !empty($this->_aRequest[$this->_sController]['role']) ?
              (int) $this->_aRequest[$this->_sController]['role'] :
              1;
    else
      $iUserRole = & $this->_aSession['user']['role'];

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "users
                                      SET
                                        name = :name,
                                        surname = :surname,
                                        content = :content,
                                        use_gravatar = :use_gravatar,
                                        role = :role
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('use_gravatar', $iUseGravatar, PDO::PARAM_INT);
      $oQuery->bindParam('role', $iUserRole, PDO::PARAM_INT);
      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      foreach (array('name', 'surname', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_STR);

        unset($sValue);
      }

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Update an users password.
   *
   * @access public
   * @param integer $iId
   * @return boolean status of query
   *
   */
  public function updatePassword($iId) {
    if (empty($iId) || $iId < 1)
      return false;

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "users
                                      SET
                                        password = :password
                                      WHERE
                                        id = :id");

      $sPassword = md5(RANDOM_HASH . $this->_aRequest[$this->_sController]['password_new']);
      $oQuery->bindParam('password', $sPassword, PDO::PARAM_STR);
      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Update the Gravatar status of a user.
   *
   * This is needed if the user has chosen to use a Gravatar first and then wants to update his profile
   * with a custom avatar. If he'd upload an image and didn't save his changings to not use a Gravatar any
   * longer, the avatar wouldn't be shown. We now force the status to update if he uploads an image.
   *
   * @static
   * @access public
   * @param integer $iId user ID
   * @param integer $iUseGravatar do we want to use a Gravatar?
   * @return boolean status of query
   *
   */
  public static function updateGravatar($iId, $iUseGravatar = 0) {
    if (empty($iId) || $iId < 1)
      return false;

    try {
      $oQuery = parent::$_oDbStatic->prepare("UPDATE
                                                " . SQL_PREFIX . "users
                                              SET
                                                use_gravatar = :use_gravatar
                                              WHERE
                                                id = :id");

      $oQuery->bindParam('use_gravatar', $iUseGravatar, PDO::PARAM_INT);
      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Update a user account when verification link is clicked.
   *
   * @access public
   * @param string $sVerificationCode Code to remove.
   * @return boolean status of query
   *
   */
  public function verifyEmail($sVerificationCode) {

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        *
                                      FROM
                                        " . SQL_PREFIX . "users
                                      WHERE
                                        verification_code = :verification_code
                                      LIMIT 1");

      $sVerificationCode = Helper::formatInput($sVerificationCode);
      $oQuery->bindParam('verification_code', $sVerificationCode, PDO::PARAM_STR);
      $oQuery->execute();

      $aData = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    if ($aData['id']) {
      try {
        $oQuery = $this->_oDb->prepare("UPDATE
                                          " . SQL_PREFIX . "users
                                        SET
                                          verification_code = '',
                                          verification_date = NOW()
                                        WHERE
                                          id = :id");

        $oQuery->bindParam('id', $aData['id'], PDO::PARAM_INT);

        # Prepare for first login
        $aData['verification_code'] = '';

        $sModel = $this->__autoload('Sessions');
        $oModel = new $sModel($this->_aRequest, $this->_aSession);
        $oModel->create($aData);

        return $oQuery->execute();
      }
      catch (\PDOException $p) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
        }
      }
    }
    else
      return false;
  }

  /**
   * Return an array of user data if user exists.
   *
   * @access public
   * @return array user data of login user
   *
   */
  public function getLoginData() {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        id, verification_code
                                      FROM
                                        " . SQL_PREFIX . "users
                                      WHERE
                                        email = :email
                                      AND
                                        password = :password
                                      LIMIT
                                        1");

      $sEmail     = Helper::formatInput($this->_aRequest[$this->_sController]['email']);
      $sPassword  = md5(RANDOM_HASH . Helper::formatInput($this->_aRequest[$this->_sController]['password']));
      $oQuery->bindParam('email', $sEmail, PDO::PARAM_STR);
      $oQuery->bindParam('password', $sPassword, PDO::PARAM_STR);
      $oQuery->execute();

      return $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Get the API token of a user.
   *
   * @access public
   * @return string the token or empty string
   *
   */
  public function getToken() {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        api_token
                                      FROM
                                        " . SQL_PREFIX . "users
                                      WHERE
                                        email = :email
                                      AND
                                        password = :password
                                      LIMIT
                                        1");

      $sEmail = Helper::formatInput($this->_aRequest['email']);
      $sPassword = md5(RANDOM_HASH . Helper::formatInput($this->_aRequest['password']));
      $oQuery->bindParam('email', $sEmail, PDO::PARAM_STR);
      $oQuery->bindParam('password', $sPassword, PDO::PARAM_STR);
      $oQuery->execute();
      $aData = $oQuery->fetch(PDO::FETCH_ASSOC);

      return !empty($aData['api_token']) ? $aData['api_token'] : '';
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Fetch all user data of active token.
   *
   * @static
   * @access public
   * @param string $sApiToken API token
   * @return array $aResult user data
   * @see vendor/candycms/core/controllers/Index.controller.php
   *
   */
  public static function getUserByToken($sApiToken) {
    # This is needed for all api token use cases
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                *,
                                                UNIX_TIMESTAMP(date) as date
                                              FROM
                                                " . SQL_PREFIX . "users
                                              WHERE
                                                api_token = :api_token
                                              LIMIT
                                                1");

      $oQuery->bindParam('api_token', $sApiToken, PDO::PARAM_STR);
      $oQuery->execute();
      $aData = $oQuery->fetch(PDO::FETCH_ASSOC);

      return parent::_formatForUserOutput($aData);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }
}