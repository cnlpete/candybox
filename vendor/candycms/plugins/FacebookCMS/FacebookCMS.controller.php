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
use candyCMS\Core\Helpers\SmartySingleton as Smarty;
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
   * admin id.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sAdminId = PLUGIN_FACEBOOK_ADMIN_ID;

  /**
   * app id.
   *
   * @access protected
   * @var string
   * @see app/config/Plugins.inc.php
   *
   */
  protected $_sAppId = PLUGIN_FACEBOOK_APP_ID;

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
   * @return void
   *
   */
  public function __construct(&$aRequest, &$aSession, &$oPlugins) {
    parent::__construct(array(
                                'appId'   => $this->_sAppId,
                                'secret'  => $this->_sPrivateKey,
                                'cookie'  => true
                        ));

    if (!defined('PLUGIN_FACEBOOK_APP_ID') || PLUGIN_FACEBOOK_APP_ID == '')
      throw new AdvancedException('Missing config entry: PLUGIN_FACEBOOK_APP_ID');

    if (!defined('PLUGIN_FACEBOOK_SECRET') || PLUGIN_FACEBOOK_SECRET == '')
      throw new AdvancedException('Missing config entry: PLUGIN_FACEBOOK_SECRET');

    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;

    # Now register some events with the pluginmanager
    $oPlugins->registerSessionPlugin($this);

    self::$_oInstance = $this;
  }

  /**
   * Get user data.
   *
   * @final
   * @access private
   * @param string $sKey
   * @return array
   *
   */
  private final function getUserData($sKey = '') {
    try {
      $aApiCall = array(
          'method'  => 'users.getinfo',
          'uids'    => $this->getUser(),
          'fields'  => 'uid, first_name, last_name, profile_url, pic, pic_square_with_logo, locale, email, website'
      );

      # Create a longer session
      $this->setExtendedAccessToken();

      $aData = $this->api($aApiCall);
      return !empty($sKey) ? $aData[$sKey] : $aData;
    }
    catch (AdvancedException $e) {
      # Don't exit here, because exception is not critical
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage(), false);
    }
  }

  /**
   * Set user data by overriding provided user array.
   *
   * @final
   * @access public
   * @param array $aUserData insert userdata here
   * @return boolean true if we received user data
   *
   */
  public final function setUserData(&$aUserData) {
    $aFacebookData = $this->getUserData();

    # Override empty data with facebook data
    if (isset($aFacebookData) && isset($aFacebookData[0]['uid'])) {
      $aUserData = array(
              'facebook_id' => (int) $aFacebookData[0]['uid'],
              'email'       => isset($aFacebookData[0]['email']) ?
                              $aFacebookData[0]['email'] :
                              $aUserData['email'],
              'name'        => isset($aFacebookData[0]['first_name']) ?
                              $aFacebookData[0]['first_name'] :
                              $aUserData['name'],
              'surname'     => isset($aFacebookData[0]['last_name']) ?
                              $aFacebookData[0]['last_name'] :
                              $aUserData['surname']
      );

      unset($aFacebookData);
      return true;
    }
    else
      return false;
  }

  /**
   *
   * Get the Facebook avatar info for all given UIDs
   *
   * @final
   * @access private
   * @param array $aUids
   * @return array
   *
   */
  private final function _getUserAvatars($aUids) {
    try {
      $aFacebookAvatarCache = &$this->_aSession['facebookavatars'];

      # Only query for IDs we don't know
      $sUids = '';
      foreach ($aUids as $sUid)
        if (!isset($aFacebookAvatarCache[$sUid]))
          $sUids .= $sUid . ',';

      # Do the facebook call with all new $sUids
      if (strlen($sUids) > 1) {
        $aApiCall = array(
            'method'  => 'users.getinfo',
            'uids'    => substr($sUids, 0, -1),
            'fields'  => 'pic_square_with_logo, profile_url'
        );

        # We read the response and add to the cache
        foreach ($this->api($aApiCall) as $aFacebookAvatar) {
          $sUid = $aFacebookAvatar['uid'];

          $aFacebookAvatarCache[$sUid]['pic_square_with_logo'] = $aFacebookAvatar['pic_square_with_logo'];
          $aFacebookAvatarCache[$sUid]['profile_url']          = $aFacebookAvatar['profile_url'];
        }
      }

      return $aFacebookAvatarCache;
    }
    catch (AdvancedException $e) {
      # Don't exit here, because exception is not critical
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage(), false);
    }
  }

  /**
   * Get user profile images and write to $aResult
   *
   * @access public
   * @param array $aIds the ids to get avatars for (array('entryid' => 'facebook_id'))
   * @param array $aData data array to insert avatarurl and user url into
   *
   */
  public function setAvatars(&$aIds, &$aData) {
    # Create a new facebook array with avatar urls
    $aFacebookAvatarCache = $this->_getUserAvatars($aIds);

    # Finally, we need to rebuild avatar data in main data array
    foreach ($aIds as $iId => $sFacebookId) {
      if (isset($aFacebookAvatarCache[$sFacebookId])) {
        $aData[$iId]['author']['avatar_64'] = $aFacebookAvatarCache[$sFacebookId]['pic_square_with_logo'];
        $aData[$iId]['author']['url'] = $aFacebookAvatarCache[$sFacebookId]['profile_url'];
      }
    }
  }

  /**
   * Get the FB logout url
   *
   * @access public
   * @param string $sTargetUrl the url to redirect to afterwards
   * @return string HTML
   *
   */
  public function logoutUrl($sTargetUrl) {
    return $this->getLogoutUrl(array('next' => $sTargetUrl . '?reload=1'));
  }

  /**
   * Get FB JavaScript code.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function showJavascript() {
    $oSmarty = Smarty::getInstance();
    $oTemplate = $oSmarty->getTemplate(self::IDENTIFIER, 'show', true);

    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    $sCacheId = UNIQUE_PREFIX . '|plugins|' . self::IDENTIFIER . '|' . $this->_sAppId;

    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {
      $oSmarty->assign('PLUGIN_FACEBOOK_APP_ID', $this->_sAppId);
      $oSmarty->assign('WEBSITE_LOCALE', WEBSITE_LOCALE);
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }

  /**
   * Get FB Meta tags.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function showMeta() {
    return '<meta property="fb:admins" content="' . $this->_sAdminId . '"/>' .
           '<meta property="fb:app_id" content="' . $this->_sAppId . '"/>';
  }

  /**
   * Get FB Button tag.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function showButton() {
    return '<fb:login-button scope="email" onlogin="window.location=\'' . CURRENT_URL .'\'"></fb:login-button>';
  }
}
