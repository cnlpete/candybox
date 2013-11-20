<?php

/**
 * Show modified exceptions.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Helpers;

class AdvancedException extends \Exception {

  /**
   * Report errors to our log and send a mail to the admin.
   *
   * @static
   * @access public
   * @param string $sMessage
   * @param boolean $bExit
   *
   */
  public static function reportBoth($sMessage, $bExit = true) {
    !ACTIVE_TEST ? AdvancedException::writeLog($sMessage) : '';

    if (WEBSITE_MODE == 'production' || WEBSITE_MODE == 'staging')
      AdvancedException::sendAdminMail($sMessage);

    if ($bExit && !ACTIVE_TEST)
      exit(I18n::get('error.standard'));
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
        'to_address'  => WEBSITE_MAIL_EXCEPTION,
        'subject'     => 'Exception',
        'message'     => $sMessage), false);
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