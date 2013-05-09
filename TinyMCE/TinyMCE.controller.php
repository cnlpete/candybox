<?php

/**
 * This plugin adds javscript code to make some textbox into a tinymce instance.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.1
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class TinyMCE {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'TinyMCE';

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

    # now register some events with the pluginmanager
    #$oPlugins->registerSimplePlugin($this);
    $oPlugins->registerEditorPlugin($this);
  }

  /**
   * Show the (cached) tinymce javascript code.
   *
   * @final
   * @access public
   * @return string HTML
   * @todo remove $sCacheId
   *
   */
  public final function show() {
    $oSmarty = Smarty::getInstance();
    $oTemplate = $oSmarty->getTemplate(self::IDENTIFIER, 'show', true);
    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    $sCacheId = UNIQUE_PREFIX . '|plugins|' . self::IDENTIFIER . '|';
    return $oSmarty->fetch($oTemplate, $sCacheId);
  }

  /**
   * Return the formatted code.
   *
   * @final
   * @static
   * @access public
   * @param string $sStr
   * @return string HTML with formated code
   * @todo maybe do some code cleanup here?
   *
   */
  public final function prepareContent(&$sStr) {
    return $sStr;
  }

  /**
   * Generate an Info Array ('url' => '', 'iconurl' => '', 'description' => '')
   *
   * @final
   * @access public
   * @return array|boolean infor array or false
   *
   */
  public final function getInfo() {
    # we do not have an icon and/or info to display
    return false;
  }
}
