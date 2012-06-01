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
   * Send the mail.
   *
   * @access protected
   * @param array $aData array with information for subject, message, name of receipient, email of receipient,
   * name of reply to, email of reply to and attachement path
   * @return boolean whether phpmailers returned true or false
   * @see vendor/phpmailer/class.phpmailer.php
   *
   */
  protected function _send($aData) {
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
    #$oMail->SetFrom(WEBSITE_MAIL, WEBSITE_NAME);

    $oMail->SetFrom(
            isset($aData['from_address']) ? $aData['from_address'] : WEBSITE_MAIL_NOREPLY,
            isset($aData['from_name']) ? $aData['from_name'] : WEBSITE_NAME);

    $oMail->AddReplyTo(
            isset($aData['from_address']) ? $aData['from_address'] : WEBSITE_MAIL_NOREPLY,
            isset($aData['from_name']) ? $aData['from_name'] : '');

    $oMail->AddAddress(
            isset($aData['to_address']) ? $aData['to_address'] : '',
            isset($aData['to_name']) ? $aData['to_name'] : '');

    $oMail->Subject = isset($aData['subject']) ? $aData['subject'] : '';
    $oMail->MsgHTML(nl2br(isset($aData['message']) ? $aData['message'] : ''));

    if (isset($aData['attachement']))
      $oMail->AddAttachment($aData['attachement']);

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
      AdvancedException::reportBoth('0119 - ' . $p->getMessage());
      exit('SQL error.');
    }

    # Not found
    if (!isset($aResult['id']))
      return false;

    # Try to resend
    try {
      if($this->_send($aResult))
        return $this->destroy ($iId);
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
   * @param array $aData array with information for subject, message, name of receipient, email of receipient,
   * name of reply to, email of reply to and attachement path
   * @param bool $bSaveMail whehter the mail queue should be used on failure
   * @return boolean the status of the action
   * @see vendor/phpmailer/class.phpmailer.php
   * @todo log entry
   *
   */
  public function create($aData, $bSaveMail = true) {
    $aData['message'] = str_replace('%NOREPLY', I18n::get('mails.body.no_reply'), $aData['message']);
    $aData['message'] = str_replace('%SIGNATURE', I18n::get('mails.body.signature'), $aData['message']);

    $aData['message'] = $this->_replaceNameAndUrl($aData['message']);
    $aData['subject'] = $this->_replaceNameAndUrl($aData['subject']);

    $sErrorMessage  = '';
    $bReturn        = false;

    try {
      $bReturn = $this->_send($aData);
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
        $oQuery->bindParam('error_message', $sErrorMessage, PDO::PARAM_STR);

        foreach ($aData as $sKey => $sValue)
          $oQuery->bindParam($sKey, isset($sValue) ? $sValue : '', PDO::PARAM_STR);

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