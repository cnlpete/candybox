<?php

/**
 * Format text using Markdown.
 *
 * This plugin is the most powerful plugin, if you don't want to write every
 * text in HTML. It enables users to use markdown formatting in their content
 * texts.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://haukeschade.de>
 * @license MIT
 * @since 3.0
 *
 */

namespace candyCMS\Plugins;

final class Markdown {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Markdown';

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

    # now register some events with the pluginmanager
    #$oPlugins->registerSimplePlugin($this);
    #$oPlugins->registerContentDisplayPlugin($this);
    $oPlugins->registerEditorPlugin($this);
  }

  /**
   * Return the formatted code.
   *
   * @final
   * @static
   * @access public
   * @param string $sStr
   * @return string HTML with formated code
   * @todo caching?
   *
   */
  public final function prepareContent(&$sStr) {
    $oMarkdown = new \dflydev\markdown\MarkdownParser();
    return $oMarkdown->transformMarkdown($sStr);
  }

  /**
   * Show nothing, since this plugin does not need to output additional javascript.
   *
   * @final
   * @access public
   * @return string HTML
   * @todo add markdowneditor: https://github.com/samwillis/pagedown-bootstrap ??
   *
   */
  public final function show() {
    return '';
  }

  /**
   * Generate an Info Array ('url' => '', 'iconurl' => '', 'description' => '')
   *
   * @final
   * @access public
   * @return array|boolean infor array or false
   * @todo return array with markdown logo and link to some markup info page
   * @todo markdown logo: https://github.com/dcurtis/markdown-mark
   *
   */
  public final function getInfo() {
    return false;
  }
}
