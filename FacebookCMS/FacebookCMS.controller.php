<?php

/**
 * This plugin gives users the opportunity to comment without registration.
 *
 * NOTE: This plugin slows down your page rapidly by sending a request to facebook each load!
 * If you don't need it, keep it disabled.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;
use Facebook;

final class FacebookCMS extends Facebook {

  /**
   * Identifier for template replacements.
   *
   * @var contant
   *
   */
  const IDENTIFIER = 'FacebookCMS';

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
   * public key.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sPublicKey = PLUGIN_FACEBOOK_APP_ID;

  /**
   * private key.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sPrivateKey = PLUGIN_FACEBOOK_SECRET;

  /**
   *
   * @var static
   * @access private
   *
   */
  private static $_oInstance = null;

  /**
   * Get the instance
   *
   * @static
   * @access public
   * @return object self::$_oInstance instance that was found or generated
   *
   */
  public static function getInstance() {
    return self::$_oInstance;
  }

  /**
   * Initialize the plugin and register all needed events.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param object $oPlugins the PluginManager
   *
   */
  public function __construct(&$aRequest, &$aSession, &$oPlugins) {
    parent::__construct(array(
        'appId'   => $this->_sPublicKey,
        'secret'  => $this->_sPrivateKey,
        'cookie'  => true
        ));

    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;

    # now register some events with the pluginmanager
    $oPlugins->registerSimplePlugin($this);
    #$oPlugins->registerContentDisplayPlugin($this);
    # @todo there is no pluginmanager event for this yet

    self::$_oInstance = $this;
  }

  /**
   * Get user data.
   *
   * @final
   * @access public
   * @param string $sKey
   * @return array
   *
   */
  public final function getUserData($sKey = '') {
    try {
      $aApiCall = array(
          'method'  => 'users.getinfo',
          'uids'    => $this->getUser(),
          'fields'  => 'uid, first_name, last_name, profile_url, pic, pic_square_with_logo, locale, email, website'
      );

      $aData = $this->api($aApiCall);
      return !empty($sKey) ? $aData[$sKey] : $aData;
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
      exit('Error: Cannot use Facebook API.');
    }
  }

  /**
   *
   * Get the Facebook avatar info for all given UIDs, load from cache, if cache is specified
   *
   * @final
   * @access public
   * @param array $aUids
   * @return array
   *
   */
  public final function getUserAvatars($aUids) {
    try {
      $aFacebookAvatarCache = &$aSession['facebookavatars'];

      # only query for ids we don't know
      $sUids = '';
      foreach ($aUids as $sUid)
        if (!isset($aFacebookAvatarCache[$sUid]))
          $sUids .= $sUid . ',';

      # do the facebook call with all new $sUids
      if (strlen($sUids) > 1) {
        $aApiCall = array(
            'method' => 'users.getinfo',
            'uids' => substr($sUids, 0, -1),
            'fields' => 'pic_square_with_logo, profile_url'
        );

        # we read the response and add to the cache
        foreach ($this->api($aApiCall) as $aFacebookAvatar) {
          $sUid = $aFacebookAvatar['uid'];

          $aFacebookAvatarCache[$sUid]['pic_square_with_logo'] = $aFacebookAvatar['pic_square_with_logo'];
          $aFacebookAvatarCache[$sUid]['profile_url']          = $aFacebookAvatar['profile_url'];
        }
      }

      return $aFacebookAvatarCache;
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
      exit('Error: Cannot create Facebook avatar images.');
    }
  }

  /**
   * Show FB JavaScript code.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function show() {
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|plugins|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $oSmarty->assign('PLUGIN_FACEBOOK_APP_ID', defined('PLUGIN_FACEBOOK_APP_ID') ? PLUGIN_FACEBOOK_APP_ID : '');
      $oSmarty->assign('WEBSITE_LOCALE', WEBSITE_LOCALE);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
