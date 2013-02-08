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

namespace candyCMS\Core\Helpers;

use candyCMS\Core\Controllers\Mails;

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
    !ACTIVE_TEST ? AdvancedException::writeLog($sMessage) : printf("\n" . $sMessage);

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

    $sModel = \candyCMS\Core\Controllers\Main::__autoload('Mails', true);
    $oMails = new $sModel();

    return $oMails->create(array(
        'subject' => 'Exception',
        'message' => $sMessage), false);
  }

  /**
   * Write down an error message to own log.
   *
   * @static
   * @access public
   *
   */
  public static function writeLog($sMessage) {
    $sIP = !ACTIVE_TEST ? SERVER_IP : '127.0.0.1';
    $sMessage = date('Y-m-d Hi', time()) . ' - ' . $sIP . ' - ' . $sMessage;

    $oFile = fopen(PATH_STANDARD . '/app/logs/' . WEBSITE_MODE . '.log', 'a');
    fputs($oFile, $sMessage . "\n");
    fclose($oFile);
  }
}