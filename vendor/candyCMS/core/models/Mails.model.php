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

namespace CandyCMS\Core\Models;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
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
      AdvancedException::reportBoth('0118 - ' . $p->getMessage());
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
   * Send the mail and return phpmailers exit-status,
   * will also throw phpmailers exceptions.
   *
   * @access private
   * @param string $sSubject mail subject
   * @param string $sMessage mail message
   * @param string $sToName name of the user to send the mail to
   * @param string $sToMail email address to send mail to
   * @param string $sReplyToName the name of the sender, can be empty
   * @param string $sReplyToMail email address the user can reply to
   * @param string $sAttachement path to the attachment
   * @return boolean whether phpmailers returned true or false
   * @see vendor/phpmailer/class.phpmailer.php
   *
   */
  private function _send($sSubject, $sMessage, $sToName, $sToMail, $sReplyToName, $sReplyToMail, $sAttachement = '') {
    require_once 'vendor/phpmailer/class.phpmailer.php';
    $oMail = new \PHPMailer(true);

    if (SMTP_ENABLE === true) {
      $oMail->IsSMTP();

      $oMail->SMTPAuth  = defined('SMTP_USE_AUTH') ? SMTP_USE_AUTH === true : true;
      $oMail->SMTPDebug = WEBSITE_MODE == 'development' ? 1 : 0;

      $oMail->Host      = SMTP_HOST;
      $oMail->Port      = SMTP_PORT;
      $oMail->Username  = SMTP_USER;
      $oMail->Password  = SMTP_PASSWORD;
    }
    else
      $oMail->IsMail();

    $oMail->CharSet = 'utf-8';
    $oMail->SetFrom(WEBSITE_MAIL, WEBSITE_NAME);
    $oMail->AddReplyTo($sReplyToMail, $sReplyToName ? $sReplyToName : '');
    $oMail->AddAddress($sToMail, $sToName ? $sToName : '');
    $oMail->Subject = $sSubject;
    $oMail->MsgHTML(nl2br($sMessage));

    if ($sAttachement)
      $oMail->AddAttachment($sAttachement);

    return $oMail->Send();
  }

  /**
   * Try to resend the mail, given by iId.
   *
   * @access public
   * @param int $iId the id of the mail, we are trying to send
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
      AdvancedException::reportBoth('0119 - ' . $p->getMessage());
      exit('SQL error.');
    }

    # Not found
    if (!isset($aResult['id']))
      return false;

    # Try to resend
    try {
      $bReturn = $this->_send($aResult['subject'],
                              $aResult['content'],
                              $aResult['to_name'],
                              $aResult['to_address'],
                              $aResult['from_name'],
                              $aResult['from_address']);

      if ($bReturn)
        $this->destroy($iId);

      return $bReturn;
    }
    catch (\phpmailerException $e) {
      AdvancedException::writeLog($e->errorMessage());
      return false;
    }
  }

  /**
   * Create a new mail, store it to database on failure
   *
   * @access private
   * @param string $sSubject mail subject
   * @param string $sMessage mail message
   * @param string $sToName name of the user to send the mail to
   * @param string $sToMail email address to send mail to
   * @param string $sReplyToName the name of the sender, can be empty
   * @param string $sReplyToMail email address the user can reply to
   * @param string $sAttachement path to the attachment
   * @param bool $bSaveMail whehter the mail queue should be used on failure
   * @return boolean the status of the action
   * @see vendor/phpmailer/class.phpmailer.php
   * @todo exception handling
   * @todo log entry
   *
   */
  public function create($sSubject, $sMessage, $sToName, $sToMail, $sReplyToName, $sReplyToMail, $sAttachement = '', $bSaveMail = true) {
    $sMessage = str_replace('%NOREPLY', I18n::get('mails.body.no_reply'), $sMessage);
    $sMessage = str_replace('%SIGNATURE', I18n::get('mails.body.signature'), $sMessage);

    $sMessage = $this->_replaceNameAndUrl($sMessage);
    $sSubject = $this->_replaceNameAndUrl($sSubject);

    $sErrorMessage  = '';
    $bReturn        = false;

    try {
      $bReturn = $this->_send($sSubject, $sMessage, $sToName, $sToMail, $sReplyToName, $sReplyToMail, $sAttachement);
    }
    catch (\phpmailerException $e) {
      AdvancedException::writeLog($e->errorMessage());
      $sErrorMessage = $e->errorMessage();
    }

    if (!$bReturn && $bSaveMail && defined('USE_MAIL_QUEUE') && USE_MAIL_QUEUE == true) {
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
                                            content,
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
                                            :content,
                                            :error_message)");

        $iUserId = (int) (isset($this->_aSession['user']['id']) ? $this->_aSession['user']['id'] : 0);
        $oQuery->bindParam('user_id', $iUserId, PDO::PARAM_INT);
        $oQuery->bindParam('ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $oQuery->bindParam('from_address', $sReplyToMail, PDO::PARAM_STR);
        $oQuery->bindParam('from_name', $sReplyToName, PDO::PARAM_STR);
        $oQuery->bindParam('to_address', $sToMail, PDO::PARAM_STR);
        $oQuery->bindParam('to_name', $sToName, PDO::PARAM_STR);
        $oQuery->bindParam('subject', $sSubject, PDO::PARAM_STR);
        $oQuery->bindParam('content', $sMessage, PDO::PARAM_STR);
        $oQuery->bindParam('error_message', $sErrorMessage, PDO::PARAM_STR);

        $oQuery->execute();
        parent::$iLastInsertId = Helper::getLastEntry('mails');
      }
      catch (\PDOException $p) {
        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth('0116 - ' . $e->getMessage());
        }

        AdvancedException::reportBoth('0117 - ' . $p->getMessage());
        exit('SQL error.');
      }
    }

    return $bReturn;
  }
}