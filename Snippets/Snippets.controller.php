<?php

/**
 * replace [gallery:%d] and [blog:%d] tags
 *
 * allows the user to link to other content types and have a little widget 
 * appear, similar to bbcode-plugin
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://haukeschade.de>
 * @license MIT
 * @since 3.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton;

final class Snippets {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Snippets';

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
   * Initialize the plugin and register all needed events.
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

    # Register some events to the plugin manager
    $oPlugins->registerContentDisplayPlugin($this);
    //$oPlugins->registerEditorPlugin($this);
  }

  private function _blogSnippet($mId) {
    $iId = intval($mId[1]);

    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, '_blog.snippet');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, '_blog.snippet');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->addTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    // hook into the appropriate cache, so it gets cleared when needed
    $sCacheId = WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|plugins|' . self::IDENTIFIER . '|blogsnippet.' . $iId . '.' .
            substr(md5($this->_aSession['user']['role']), 0 , 10);

    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $sBlogsModel = \candyCMS\Core\Models\Main::__autoload('Blogs');
      $oModel = new $sBlogsModel($this->_aRequest, $this->_aSession);

      $oSmarty->assign('blogs', $oModel->getId($iId));
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }

  private function _gallerySnippet($mId) {
    $iId = intval($mId[1]);

    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, '_gallery.snippet');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, '_gallery.snippet');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->addTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    // hook into the appropriate cache, so it gets cleared when needed
    $sCacheId = WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|plugins|' . self::IDENTIFIER . '|gallerysnippet.' . $iId . '.' .
            substr(md5($this->_aSession['user']['role']), 0 , 10);

    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $sGalleriesModel = \candyCMS\Core\Models\Main::__autoload('Galleries');
      $oModel = new $sGalleriesModel($this->_aRequest, $this->_aSession);

      $oSmarty->assign('album', $oModel->getId($iId));
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }

  /**
   * Return the formatted code.
   *
   * @final
   * @access public
   * @param string $sStr
   * @return string HTML with formated code
   * @todo caching?
   *
   */
  public final function prepareContent(&$sStr) {
    $sStr = preg_replace_callback('#\[gallery:([0-9]+)\]#Uis', "self::_gallerySnippet", $sStr);
    $sStr = preg_replace_callback('#\[blog:([0-9]+)\]#Uis', "self::_blogSnippet", $sStr);

    return $sStr;
  }

  /**
   * Show nothing, since this plugin does not need to output additional javascript.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function show() {
    return '';
  }

  /**
   * Generate an info array ('url' => '', 'iconurl' => '', 'description' => '')
   *
   * @final
   * @access public
   * @return array
   *
   */
  public final function getInfo() {
    return false;
  }
}
