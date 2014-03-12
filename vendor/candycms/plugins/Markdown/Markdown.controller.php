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

    # Register some events to the plugin manager
    $oPlugins->registerEditorPlugin($this);
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
   * Generate an info array ('url' => '', 'iconurl' => '', 'description' => '')
   *
   * @final
   * @access public
   * @return array
   *
   */
  public final function getInfo() {
    return array( 'url'         => 'http://daringfireball.net/projects/markdown/syntax',
                  'description' => 'Markdown',
                  'iconurl'     => '/vendor/candycms/plugins/Markdown/assets/icon.png');
  }
}
