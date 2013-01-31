<?php

/**
 * Execute the plugin logic
 *
 * This is basically an observer patter with some predefined events
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://haukeschade.de>
 * @license MIT
 * @since 3.0
 *
 */

namespace candyCMS\Core\Helpers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;

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
    if (!ACTIVE_TEST) {
      if (!empty($sAllowedPlugins)) {
        $aPlugins = explode(',', $sAllowedPlugins);

        foreach ($aPlugins as $sPluginName) {
          $oPlugin = self::_load($sPluginName);
          if ($oPlugin !== null) {
            $sLowerPluginName = strtolower($oPlugin::IDENTIFIER);
            $this->_aPlugins[$sLowerPluginName] = $oPlugin;
          }
        }
      };
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
      if (!file_exists(PATH_STANDARD . '/vendor/candyCMS/plugins/' . $sPlugin . '/' . $sPlugin . '.controller.php'))
        throw new AdvancedException('Missing plugin: ' . ucfirst($sPlugin));

      else {
        require_once PATH_STANDARD . '/vendor/candyCMS/plugins/' . $sPlugin . '/' . $sPlugin . '.controller.php';
        return new $sPluginNamespace($this->_aRequest, $this->_aSession, $this);
      }
    }
    catch (AdvancedException $e) {
      die($e->getMessage());
    }
    return null;
  }

  /**
   * register as oldschool plugin (simple <!--Name--> replacement)
   *
   * Plugin MUST provide a show() function
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerSimplePlugin(&$oPlugin) {
    $this->_aSimplePluginNames[] = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   * run all oldschool plugins (simple <!--Name--> replacement)
   *
   * @access public
   * @param string $sHtml the content, the plugins want to change
   *
   */
  public function runSimplePlugins(&$sHtml) {
    foreach ($this->_aSimplePluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];
      $sHtml = str_replace('<!-- plugin:' . strtolower($oPlugin::IDENTIFIER) . ' -->', $oPlugin->show(), $sHtml);
    }
    return $sHtml;
  }

  /**
   * register as content display plugin
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
   * run all content display plugins (that wil lalter given content)
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
   * register as global display plugin
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
   * run all content display plugins (who will alter given content)
   *
   * @access public
   * @param string $sHtml the content, the plugins want to change
   *
   */
  public function runGlobalDisplayPlugins(&$sHtml) {
    foreach ($this->_aGlobalDisplayPluginNames as $sPluginName) {
      $oPlugin = $this->_aPlugins[$sPluginName];
      $sHtml = $oPlugin->prepareContent($sHtml);
    }
    return $sHtml;
  }

  /**
   * register as repetitive plugin
   *
   * Plugin MUST provide a needsExecution() and an execute() function
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerRepetitivePlugin(&$oPlugin) {
    $this->_aRepetitivePluginNames[] = strtolower($oPlugin::IDENTIFIER);
  }

  /**
   * run all repetitive plugins (who will execute every x seconds)
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
   * register as Captcha plugin
   *
   * Plugin MUST provide a show() and a check() function
   *
   * @access public
   * @param object $oPlugin the plugin to be added to this event
   *
   */
  public function registerCaptchaPlugin(&$oPlugin) {
    $this->_aCaptchaPluginNames[] = strtolower($oPlugin::IDENTIFIER);
    # forms will provide all nessecary <!--captchaPluginName--> placeholders
    # @todo make this more elegant by having forms provide a global <!--captchas--> Placeholder and and/or run show on all Captchaplugins
    $this->registerSimplePlugin($oPlugin);
  }

  /**
   * check all captcha plugins
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

}
