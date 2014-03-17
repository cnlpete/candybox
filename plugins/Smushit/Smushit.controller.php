<?php

/**
 * use SmushIt Service to shrink images
 *
 * Do not allow if you want maximum quality
 * for your images or uploading causes timeouts.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://haukeschade.de>
 * @license MIT
 * @since 4.1
 *
 */

namespace candybox\Plugins;

use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class Smushit {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Smushit';

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
    $oPlugins->registerImageCreationPlugin($this);
  }

  public function alterImage($sPath) {
    if (ALLOW_SMUSHIT) {
      try {
        require_once PATH_STANDARD . '/vendor/tylerhall/smushit-php/class.smushit.php';
      }
      catch (AdvancedException $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
        return;
      }

      # Send information of our created image to the server.
      $oSmushIt = new \SmushIt(WEBSITE_URL . '/' . $sPath);

      # Download new image from Smush.it
      if (empty($oSmushIt->error)) {
        unlink($sPath);
        file_put_contents($sPath, file_get_contents($oSmushIt->compressedUrl));
      }
    }
  }
}
