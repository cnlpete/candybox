<?php

/**
 * Show modified exceptions.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace CandyCMS\Core\Helpers;

use CandyCMS\Core\Controllers\Mails;

class AdvancedException extends \Exception {

  /**
   * Report errors to our log and send a mail to the admin.
   *
   * @static
   * @access public
   * @param type $sMessage
   *
   */
  public static function reportBoth($sMessage) {
    AdvancedException::writeLog($sMessage);

    if (WEBSITE_MODE == 'production' || WEBSITE_MODE == 'staging')
      AdvancedException::sendAdminMail($sMessage);
  }

  /**
   * Send an email to an administrator when an error occurs.
   *
   * @static
   * @access public
   * @return boolean mail status
   *
   */
  public static function sendAdminMail($sMessage) {
    $sMessage = date('Y-m-d Hi', time()) . ' - ' . $sMessage;

    $sClass = \CandyCMS\Core\Controllers\Main::__autoload('Mails', true);
    $oMails = new $sClass(null, null);

    return $oMails->create('Exception',
            $sMessage,
            '',
            WEBSITE_MAIL,
            '',
            WEBSITE_MAIL_NOREPLY,
            '',
            false);
  }

  /**
   * Write down an error message to own log.
   *
   * @static
   * @access public
   *
   */
  public static function writeLog($sMessage) {
    $sMessage = date('Y-m-d Hi', time()) . ' - ' . $sMessage;

    if (!is_dir(PATH_STANDARD . '/app/logs'))
      mkdir(PATH_STANDARD . '/app/logs');

    $sFileName = PATH_STANDARD . '/app/logs/' . WEBSITE_MODE . '.log';
    $oFile = fopen($sFileName, 'a');
    fputs($oFile, $sMessage . "\n");
    fclose($oFile);
  }
}