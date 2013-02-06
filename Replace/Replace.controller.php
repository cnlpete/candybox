<?php

/**
 * Replaces text. This can be either used to avoid bad words, SEO optimization or to display ads.
 * The config file must be placed under "app/config/Replace.yml".
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.1.5
 *
 */

namespace candyCMS\Plugins;

final class Replace {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Replace';

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

    # Check for config file
    if (!isset($this->_aSession['replace'])) {
      $sReplaceDataFile = PATH_STANDARD . '/app/config/Replace.yml';

      if (file_exists($sReplaceDataFile))
        $this->_aSession['replace'] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sReplaceDataFile));

      else
        die('No replace config file.');
    }

    # now register some events with the pluginmanager
    #$oPlugins->registerSimplePlugin($this);
    # @todo do we want this plugin to replace globally or only in content fields?
    #$oPlugins->registerContentDisplayPlugin($this);
    $oPlugins->registerGlobalDisplayPlugin($this);
  }

  /**
   * Replace the words.
   *
   * @final
   * @access public
   * @param string $sHtml
   * @return string HTML
   *
   */
  public final function prepareContent(&$sHtml) {
    foreach ($this->_aSession['replace'] as $sKey => $sValue)
      $sHtml = str_replace($sKey, $sValue, $sHtml);

    return $sHtml;
  }
}
