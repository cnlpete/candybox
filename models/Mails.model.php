<?php

/**
 * Handle all medias model requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade
 * @license MIT
 * @since 2.1
 *
 */

namespace candyCMS\Core\Models;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use PDO;

class Mails extends Main {

  /**
   * Get the current mail queue.
   *
   * @access public
   * @return array $this->_aData
   *
   */
  public function getOverview() {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        m.*,
                                        UNIX_TIMESTAMP(m.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "mails m
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        m.user_id=u.id
                                      ORDER BY
                                        m.date ASC");

      $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];

      $this->_aData[$iId] = $this->_formatForOutput(
              $aRow,
              array('id', 'user_id'),
              array('result'));
    }

    return $this->_aData;
  }

  /**
   * Replace the WEBSITE_NAME and WEBSITE_URL placeholders with its according constants
   *
   * @static
   * @access private
   * @param string $sText the text in which to replace the placeholders
   * @return string the text with all placeholders replaced
   * @todo check if we need both variable types
   *
   */
  private static function _replaceNameAndUrl($sText) {
    $sText = str_replace('%%WEBSITE_NAME',  WEBSITE_NAME, $sText);
    $sText = str_replace('%%WEBSITE_URL',   WEBSITE_URL,  $sText);
    $sText = str_replace('%WEBSITE_NAME',   WEBSITE_NAME, $sText);
    $sText = str_replace('%WEBSITE_URL',    WEBSITE_URL,  $sText);

    return $sText;
  }

  /**
   * Send the mail.
   *
   * @access protected
   * @param array $aMail array with information for subject, message, name of receipient, email of receipient,
   * name of reply to, email of reply to and attachment path
   * @return boolean whether phpmailers returned true or false
   * @see vendor/phpmailer/phpmailer/class.phpmailer.php
   *
   */
  protected function _send($aMail) {
    require_once PATH_STANDARD . '/vendor/phpmailer/phpmailer/class.phpmailer.php';
    $oMail = new \PHPMailer(true);

    if (SMTP_ENABLE === true || ACTIVE_TEST) {
      $oMail->IsSMTP();

      $oMail->SMTPAuth  = defined('SMTP_USE_AUTH') ? SMTP_USE_AUTH === true : true;
      $oMail->SMTPDebug = WEBSITE_MODE == 'development' || ACTIVE_TEST ? 1 : 0;

      $oMail->Host      = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
      $oMail->Port      = defined('SMTP_POST') ? SMTP_PORT : '1025';
      $oMail->Username  = defined('SMTP_USER') ? SMTP_USER : '';
      $oMail->Password  = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
    }
    else
      $oMail->IsMail();

    $oMail->CharSet = 'utf-8';

    $oMail->SetFrom(
            isset($aMail['from_address']) ? $aMail['from_address'] : WEBSITE_MAIL_NOREPLY,
            isset($aMail['from_name']) ? $aMail['from_name'] : WEBSITE_NAME);

    $oMail->AddReplyTo(
            isset($aMail['from_address']) ? $aMail['from_address'] : WEBSITE_MAIL_NOREPLY,
            isset($aMail['from_name']) ? $aMail['from_name'] : '');

    $oMail->AddAddress(
            isset($aMail['to_address']) ? $aMail['to_address'] : WEBSITE_MAIL,
            isset($aMail['to_name']) ? $aMail['to_name'] : WEBSITE_NAME);

    $oMail->Subject = isset($aMail['subject']) ? $aMail['subject'] : I18n::get('mails.subject.by', 'System');
    $oMail->MsgHTML(nl2br(isset($aMail['message']) ? $aMail['message'] : ''));

    if (!empty($aMail['attachment']))
      $oMail->AddAttachment($aMail['attachment']);

    return $oMail->Send();
  }

  /**
   * Try to resend the mail, given by ID.
   *
   * @access public
   * @param integer $iId the id of the mail, we are trying to send
   * @return $bReturn boolean status
   *
   */
  public function resend($iId) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        *
                                      FROM
                                        " . SQL_PREFIX . "mails
                                      WHERE
                                        id = :id
                                      LIMIT
                                        1");

      $oQuery->bindValue(':id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }

    # Not found
    if (!isset($aResult['id']))
      return false;

    # Try to resend
    try {
      if ($this->_send($aResult))
        return $this->destroy($iId);
    }
    catch (\phpmailerException $e) {
      AdvancedException::writeLog($e->errorMessage());
      return false;
    }
  }

  /**
   * Create a new mail, store it to database on failure
   *
   * @access public
   * @param array $aMail array with information for subject, message, name of receipient, email of receipient,
   * name of reply to, email of reply to and attachment path
   * @param bool $bSaveMail whehter the mail queue should be used on failure
   * @return boolean the status of the action
   * @see vendor/phpmailer/phpmailer/class.phpmailer.php
   * @todo log entry
   * @todo make compilant to main::create, since in strict-mode inheritance also inherits parameters
   *
   */
  public function create($aMail, $bSaveMail = true) {
    $aMail['message'] = str_replace('%NOREPLY', I18n::get('mails.body.no_reply'), $aMail['message']);
    $aMail['message'] = str_replace('%SIGNATURE', I18n::get('mails.body.signature'), $aMail['message']);

    $aMail['message'] = $this->_replaceNameAndUrl($aMail['message']);
    $aMail['subject'] = $this->_replaceNameAndUrl($aMail['subject']);

    # Bugfix: Fix all the missing email parts to avoid SQL errors
    $aMail['attachment']   = isset($aMail['attachment']) ? $aMail['attachment'] : '';
    $aMail['from_address']  = isset($aMail['from_address']) ? $aMail['from_address'] : WEBSITE_MAIL_NOREPLY;
    $aMail['from_name']     = isset($aMail['from_name']) ? $aMail['from_name'] : WEBSITE_NAME;
    $aMail['to_name']       = isset($aMail['to_name']) ? $aMail['to_name'] : '';

    $sErrorMessage  = '';
    $bReturn        = false;

    try {
      $bReturn = $this->_send($aMail);
    }
    catch (\phpmailerException $e) {
      AdvancedException::writeLog(__METHOD__ . ' - '. $e->errorMessage());
      $sErrorMessage = $e->errorMessage();
    }

    if ((!$bReturn && $bSaveMail && defined('USE_MAIL_QUEUE') && USE_MAIL_QUEUE == true) || ACTIVE_TEST) {
      try {
        $oQuery = $this->_oDb->prepare("INSERT INTO
                                          " . SQL_PREFIX . "mails
                                          ( user_id,
                                            date,
                                            ip,
                                            from_address,
                                            from_name,
                                            to_address,
                                            to_name,
                                            subject,
                                            message,
                                            attachment,
                                            error_message)
                                        VALUES
                                          ( :user_id,
                                            NOW(),
                                            :ip,
                                            :from_address,
                                            :from_name,
                                            :to_address,
                                            :to_name,
                                            :subject,
                                            :message,
                                            :attachment,
                                            :error_message)");

        $iUserId = (int) (isset($this->_aSession['user']['id']) ? $this->_aSession['user']['id'] : 0);
        $oQuery->bindParam('user_id', $iUserId, PDO::PARAM_INT);
        $oQuery->bindParam('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $oQuery->bindParam('error_message', $sErrorMessage, PDO::PARAM_STR);

        foreach ($aMail as $sKey => $sValue) {
          $sValue = isset($sValue) ? $sValue : '';
          $oQuery->bindParam($sKey, $sValue, PDO::PARAM_STR);
        }

        $oQuery->execute();
        parent::$iLastInsertId = parent::$_oDbStatic->lastInsertId();
      }
      catch (\PDOException $p) {
        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
        }

        AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
        exit('SQL error.');
      }
    }

    return $bReturn;
  }
}
