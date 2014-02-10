<?php

/**
 * Execute the plugin logic
 *
 * This is basically an observer patter with some predefined events.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 3.0
 *
 */

namespace candyCMS\Core\Helpers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;

/**
 * Class PluginManager
 * @package candyCMS\Core\Helpers
 *
 */
class PluginManager {

  /**
   *
   * @access private
   *
   */
  private $_aRequest;

  /**
   *
   * @access private
   *
   */
  private $_aSession;

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
    if (self::$_oInstance === null) {
      self::$_oInstance = new self();
    }

    return self::$_oInstance;
  }

  /**
   * Assign the Session and the Request Object to Smarty
   *
   * @access public
   * @param array $aRequest the $_REQUEST array
   * @param array $aSession the $_SESSION array
   *
   */
  public function setRequestAndSession(&$aRequest = null, &$aSession = null) {
    $this->_aRequest = &$aRequest;
    $this->_aSession = &$aSession;
  }

  /**
   * Saves all loaded Plugins
   *
   * @var array
   * @access protected
   *
   */
  protected $_aPlugins = array();

  /**
   * Saves simple Plugins
   *
   * @var array
   * @access protected
   *
   */
  protected $_aSimplePluginNames = array();
  protected $_aContentDisplayPluginNames = array();
  protected $_aGlobalDisplayPluginNames = array();
  protected $_aRepetitivePluginNames = array();
  protected $_aCaptchaPluginNames = array();
  protected $_aEditorPluginNames = array();
  protected $_sSessionPluginName = ''; # @todo doc

  /**
   * Load all defined plugins.
   *
   * @access public
   * @param string $sAllowedPlugins comma separated plugin names
   * @see app/config/Candy.inc.php
   * @return array of plugins
   *
   */
  public function load($sAllowedPlugins) {
    if (!empty($sAllowedPlugins) && !ACTIVE_TEST) {
      $aPlugins = explode(',', $sAllowedPlugins);

      foreach ($aPlugins as $sPluginName) {
        $oPlugin = self::_load($sPluginName);

        if ($oPlugin !== null) {
          $sLowerPluginName = strtolower($oPlugin::IDENTIFIER);
          $this->_aPlugins[$sLowerPluginName] = $oPlugin;
        }
      }
    }
  }

  /**
   * Load a plugin.
   *
   * @access private
   * @param string $sPlugin plugin name
   * @see app/config/Candy.inc.php
   * @return object the plugin object or null
   *
   */
  private function _load($sPlugin) {
    $sPlugin = (string) ucfirst($sPlugin);
    $sPluginNamespace = '\candyCMS\Plugins\\' . $sPlugin;

    try {
      if (!file_exists(PATH_STANDARD . '/vendor/candycms/plugins/' . $sPlugin . '/' . $sPlugin . '.controller.php'))
        throw new AdvancedException('Missing plugin: ' . ucfirst($sPlugin));

      else {
        require_once PATH_STANDARD . '/vendor/candycms/plugins/' . $sPlugin . '/' . $sPlugin . '.controller.php';
        return new $sPluginNamespace($this->_aRequest, $this->_aSession, $this);
      }
    }
    catch (AdvancedException $e) {
      die($e->getMessage());
    }

    return null;
  }

  /**
   *
   * @access public
   * @return type
   * @todo documentation
   *
   */
  public function getLoadedPluginNames() {
    foreach ($this->_aPlugins as $sKey => &$oPlugin)
      $aReturnArray[] = $oPlugin::IDENTIFIER;

    return isset($aReturnArray) ? $aReturnArray : array();
  }

  /** ----------------------- THE API'S ----------------------- **/

  /**
   * Register as oldschool plugin (simple <!-- plugin:name_of_plugin --> replacement).
   *
   * Plugin MUST provide a show() function.
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerSimplePlugin(&$oPlugin) {
    $this->_aSimplePluginNames[] = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   * Run all oldschool plugins (simple <!-- plugin:name_of_plugin --> replacement).
   *
   * @access public
   * @param string $sHtml the content, the plugins want to change
   *
   */
  public function runSimplePlugins(&$sHtml) {
    foreach ($this->_aSimplePluginNames as $sPluginName) {
      $oPlugin  = $this->_aPlugins[$sPluginName];
      $sHtml    = str_replace('<!-- plugin:' . strtolower($oPlugin::IDENTIFIER) . ' -->', $oPlugin->show(), $sHtml);
    }

    $sHtml = $this->runCaptchaPlugins($sHtml);
    $sHtml = $this->runEditorPlugins($sHtml);

    return $sHtml;
  }

  /**
   * Register as oldschool plugin (simple <!--Name--> replacement).
   *
   * Plugin MUST provide a show() function for additional content to load,
   * a prepareContent() function for displaying content and a getInfo()
   * function for displaying an icon and/or link to some help page.
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerEditorPlugin(&$oPlugin) {
    $this->_aEditorPluginNames[] = strtolower($oPlugin::IDENTIFIER);

    # Make editor plugins use the prepareContent event
    $this->registerContentDisplayPlugin($oPlugin);
  }

  /**
   * Get information regarding current editors from plugins.
   *
   * @access public
   * @return array $aReturnValue all the info data in one big array
   *
   */
  public function getEditorInfo() {
    foreach ($this->_aEditorPluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];
      $aTmpReturnValue = $oPlugin->getInfo();

      if ($aTmpReturnValue !== false)
        $aReturnValue[] = $aTmpReturnValue;
    }

    return isset($aReturnValue) ? $aReturnValue : array();
  }

  /**
   * Output all editor information.
   *
   * @access public
   * @param string $sHtml the content, the plugins want to change
   * @return string replaced HTML
   *
   */
  public function runEditorPlugins(&$sHtml) {
    foreach ($this->_aEditorPluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];
      $sHtml = str_replace('<!-- pluginmanager:editor -->', $oPlugin->show() . '<!-- pluginmanager:editor -->', $sHtml);
    }

    return $sHtml;
  }

  /**
   * Register as content display plugin.
   *
   * Plugin MUST provide a prepareContent() function
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerContentDisplayPlugin(&$oPlugin) {
    $this->_aContentDisplayPluginNames[] = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   * Run all content display plugins (that wil lalter given content).
   *
   * @access public
   * @param string $sHtml the content, the plugins want to change
   *
   */
  public function runContentDisplayPlugins(&$sHtml) {
    foreach ($this->_aContentDisplayPluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];
      $sHtml = $oPlugin->prepareContent($sHtml);
    }

    return $sHtml;
  }

  /**
   * Register as global display plugin.
   *
   * Plugin MUST provide a prepareContent() function
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerGlobalDisplayPlugin(&$oPlugin) {
    $this->_aGlobalDisplayPluginNames[] = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   * Run all content display plugins (who will alter given content).
   *
   * @access public
   * @param string $sHtml the content, the plugins want to change
   *
   */
  public function runGlobalDisplayPlugins(&$sHtml) {
    foreach ($this->_aGlobalDisplayPluginNames as $sPluginName) {
      $oPlugin  = $this->_aPlugins[$sPluginName];
      $sHtml    = $oPlugin->prepareContent($sHtml);
    }

    return $sHtml;
  }

  /**
   * Register as repetitive plugin.
   *
   * Plugin MUST provide a needsExecution() and an execute() function.
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerRepetitivePlugin(&$oPlugin) {
    $this->_aRepetitivePluginNames[] = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   * Run all repetitive plugins (who will execute every x seconds).
   *
   * @access public
   * @param boolean $bForceExecution whether to force the execution
   *
   */
  public function runRepetitivePlugins($bForceExecution = false) {
    foreach ($this->_aRepetitivePluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];

      if ($oPlugin->needsExecution($bForceExecution))
        $oPlugin->execute();
    }
  }

  /**
   * Register as Captcha plugin.
   *
   * Plugin MUST provide a show() and a check() function.
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerCaptchaPlugin(&$oPlugin) {
    $this->_aCaptchaPluginNames[] = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   * Check all captcha plugins.
   *
   * @access public
   * @param array $aError an array to append errors to
   *
   */
  public function checkCaptcha(&$aError) {
    foreach ($this->_aCaptchaPluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];
      $oPlugin->check($aError);
    }
  }

  /**
   * Output all captcha information.
   *
   * @access public
   * @param string $sHtml the content, the plugins want to change
   * @return string HTML
   *
   */
  public function runCaptchaPlugins(&$sHtml) {
    foreach ($this->_aCaptchaPluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];
      $sHtml = str_replace('<!-- pluginmanager:captcha -->', $oPlugin->show() . '<!-- pluginmanager:captcha -->', $sHtml);
    }

    return $sHtml;
  }

  /**
   * Register as session plugin.
   * There can only be ONE session plugin.
   *
   * It MUST provide setUserData, setAvatars, logoutUrl, showJavascript, showMeta and showButton functions
   * to see how these functions work, have a look at the official facebook plugin.
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerSessionPlugin(&$oPlugin) {
    if (!empty($this->_sSessionPluginName))
      throw new AdvancedException('Duplicate Session plugin: ' . ucfirst($this->_sSessionPluginName) . ' and ' . $oPlugin::IDENTIFIER);

    $this->_sSessionPluginName = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   *
   * @return type
   * @todo documentation
   *
   */
  public function hasSessionPlugin() {
    return !empty($this->_sSessionPluginName);
  }

  /**
   *
   * @return type
   * @todo documentation
   *
   */
  public function getSessionPlugin() {
    return $this->_aPlugins[$this->_sSessionPluginName];
  }

  /**
   * Output all session plugin information.
   *
   * @access public
   * @param string $sHtml the content, the plugin wants to change
   * @return string HTML
   *
   */
  public function runSessionPlugin(&$sHtml) {
    if ($this->hasSessionPlugin()) {
      $oPlugin = $this->_aPlugins[$this->_sSessionPluginName];
      $sHtml = str_replace('<!-- pluginmanager:sessionplugin::meta -->', $oPlugin->showMeta(), $sHtml);
      $sHtml = str_replace('<!-- pluginmanager:sessionplugin::javascript -->', $oPlugin->showJavascript(), $sHtml);
      $sHtml = str_replace('<!-- pluginmanager:sessionplugin::button -->', $oPlugin->showButton(), $sHtml);
    }

    return $sHtml;
  }
}