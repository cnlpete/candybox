<?php

/**
 * Handle all blog SQL requests.
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
use candyCMS\Core\Helpers\Pagination;
use PDO;

class Sessions extends Main {

  /**
   * Fetch all user data of active session.
   *
   * @static
   * @access public
   * @return array | boolean $aData with user data or false
   * @see vendor/candycms/core/controllers/Index.controller.php
   *
   */
  public static function getUserBySession() {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                u.*,
                                                UNIX_TIMESTAMP(u.date) as date
                                              FROM
                                                " . SQL_PREFIX . "users AS u
                                              LEFT JOIN
                                                " . SQL_PREFIX . "sessions AS s
                                              ON
                                                u.id = s.user_id
                                              WHERE
                                                s.session = :session_id
                                              AND
                                                s.ip = :ip
                                              LIMIT
                                                1");

      $oQuery->bindValue('session_id', session_id(), PDO::PARAM_STR);
      $oQuery->bindParam('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
      $bReturn = $oQuery->execute();

      if ($bReturn == false)
        self::destroy();

      $aData = $oQuery->fetch(PDO::FETCH_ASSOC);
      return $aData ? parent::_formatForUserOutput($aData) : false;
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Create a user session.
   *
   * @access public
   * @param array $aUser optional user data.
   * @return boolean status of login
   * @todo put logic into controller
   *
   */
  public function create($aUser = '') {
    if (empty($aUser)) {
      $sModel = $this->__autoload('Users', true);
      $oModel = new $sModel($this->_aRequest, $this->_aSession);
      $aUser  = $oModel->getLoginData();
    }

    # User did verify and has id, so log in!
    if (isset($aUser['id']) && !empty($aUser['id']) && empty($aUser['verification_code'])) {
      try {
        $oQuery = $this->_oDb->prepare("INSERT INTO
                                          " . SQL_PREFIX . "sessions
                                          ( user_id,
                                            session,
                                            ip,
                                            date)
                                        VALUES
                                          ( :user_id,
                                            :session,
                                            :ip,
                                            NOW())");

        $sSessionId = session_id();
        $oQuery->bindParam('user_id', $aUser['id'], PDO::PARAM_INT);
        $oQuery->bindParam('session', $sSessionId, PDO::PARAM_STR);
        $oQuery->bindParam('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

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
   * Resend password.
   *
   * @access public
   * @param string $sPassword new password if we want to resend it
   * @return boolean status of query
   *
   */
  public function password($sPassword) {
    $sModel = $this->__autoload('Users');
    return $sModel::setPassword($this->_aRequest[$this->_sController]['email'], $sPassword);
  }

  /**
   * Resend verification.
   *
   * @access public
   * @return boolean|array status of query or user array
   *
   */
  public function verification() {
    $sModel = $this->__autoload('Users');
    $mData  = $sModel::getVerificationData($this->_aRequest[$this->_sController]['email']);

    return is_array($mData) ? $mData : false;
  }

  /**
   * Destroy a user session and logout.
   *
   * @access public
   * @param integer $iId the session id
   * @param string $sController controller to use, obsolete and only for not giving E_STRICT warnings
   * @return boolean status of query
   *
   */
  public function destroy($iId, $sController = '') {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("UPDATE
                                                " . SQL_PREFIX . "sessions
                                              SET
                                                session = NULL
                                              WHERE
                                                session = :session_id");

      $oQuery->bindParam('session_id', $iId, PDO::PARAM_STR);
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
}