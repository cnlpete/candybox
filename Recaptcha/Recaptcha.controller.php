<?php

/**
 * Recaptcha Plugin.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;
use candyCMS\Core\Helpers\I18n;

if (!defined('SHOW_CAPTCHA'))
  define('SHOW_CAPTCHA', MOBILE === false && WEBSITE_MODE !== 'test');

final class Recaptcha {

  /**
   * ReCaptcha public key.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sPublicKey = PLUGIN_RECAPTCHA_PUBLIC_KEY;

  /**
   * ReCaptcha private key.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sPrivateKey = PLUGIN_RECAPTCHA_PRIVATE_KEY;

  /**
   * ReCaptcha object.
   *
   * @var object
   * @access protected
   *
   */
  protected $_oResponse = '';

  /**
   * Provided ReCaptcha error message.
   *
   * @var string
   * @access protected
   *
   */
  protected $_sError = '';

  /**
   * Error Message of last captcha check
   *
   * @var string
   * @access private
   */
  private $_sErrorMessage = '';

  /**
   * Identifier for template replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Recaptcha';

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aRequest;

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aSession;

  /**
   * Initialize the software by adding input params.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param object $oPlugins the PluginManager
   *
   */
  public function __construct(&$aRequest, &$aSession, &$oPlugins) {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;

    require_once PATH_STANDARD . '/vendor/recaptcha/recaptcha/recaptchalib.php';

    # now register some events with the pluginmanager
    $oPlugins->registerCaptchaPlugin($this);
  }

  /**
   * Get the HTML-Code for the Recaptcha form.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function show() {
    if ($this->_aSession['user']['role'] == 0) {
      $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'recaptcha');
      $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'recaptcha');

      $oSmarty = SmartySingleton::getInstance();
      $oSmarty->setTemplateDir($sTemplateDir);

      # No caching for this very dynamic form
      $oSmarty->setCaching(SmartySingleton::CACHING_OFF);

      $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
      $oSmarty->assign('MOBILE', MOBILE);
      $oSmarty->assign('_captcha_', recaptcha_get_html($this->_sPublicKey, $this->_sError));

      if ($this->_sErrorMessage)
        $oSmarty->assign('_error_', $this->_sErrorMessage);

      return $oSmarty->fetch($sTemplateFile);
    }
  }

  /**
   * Check if the entered captcha is correct.
   *
   * @final
   * @access public
   * @param array $aError
   * @return array|boolean
   * @todo doc; test: captcha might be broken before
   *
   */
  public final function check(&$aError) {
    $this->_sErrorMessage = '';
    if (isset($this->_aRequest['recaptcha_response_field'])) {
      $this->_oRecaptchaResponse = recaptcha_check_answer (
              $this->_sPrivateKey,
              $_SERVER['REMOTE_ADDR'],
              $this->_aRequest['recaptcha_challenge_field'],
              $this->_aRequest['recaptcha_response_field']);

      if (!$this->_oRecaptchaResponse->is_valid) {
        $this->_sErrorMessage = I18n::get('error.captcha.incorrect');
        return false;
      }
    }
    else
      $this->_sErrorMessage = I18n::get('error.captcha.loading');

    $aError['captcha'] = $this->_sErrorMessage;
    return $aError;
  }
}