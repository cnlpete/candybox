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
    if(WEBSITE_MODE !== 'test')
      AdvancedException::writeLog($sMessage);

    else
      printf("\n" . $sMessage);

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

    $sModel = \CandyCMS\Core\Controllers\Main::__autoload('Mails', true);
    $oMails = new $sModel();

    return $oMails->create(array('subject' => 'Exception', 'message' => $sMessage), false);
  }

  /**
   * Write down an error message to own log.
   *
   * @static
   * @access public
   * @todo better use of $_SERVER
   *
   */
  public static function writeLog($sMessage) {
    $sIP = WEBSITE_MODE !== 'test' ? $_SERVER['REMOTE_ADDR'] : 'localhost';
    $sMessage = date('Y-m-d Hi', time()) . ' - ' . $sIP . ' - ' . $sMessage;

    if (!is_dir(PATH_STANDARD . '/app/logs'))
      mkdir(PATH_STANDARD . '/app/logs');

    $oFile = fopen(PATH_STANDARD . '/app/logs/' . WEBSITE_MODE . '.log', 'a');
    fputs($oFile, $sMessage . "\n");
    fclose($oFile);
  }
}