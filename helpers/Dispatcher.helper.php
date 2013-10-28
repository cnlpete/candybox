<?php

/**
 * Route the application to the given controller and action.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Helpers;

class Dispatcher {

  /**
   * @var object
   * @access public
   *
   */
  public $oController;

  /**
   * Initialize the controller by adding input params.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param array $aFile alias for $_FILE
   * @param array $aCookie alias for $_COOKIE
   *
   */
  public function __construct(&$aRequest, &$aSession, &$aFile = '', &$aCookie = '') {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;
    $this->_aFile     = & $aFile;
    $this->_aCookie   = & $aCookie;
  }

  /**
   * Get the controller object.
   *
   * @access public
   * @return object $this->oController controller
   * @see vendor/candycms/core/controllers/Main.controller.php -> __autoload()
   *
   */
  public function getController() {
    $sController = ucfirst(strtolower((string) $this->_aRequest['controller']));

    try {
      # Are extensions for existing controllers available? If yes, use them.
      if (EXTENSION_CHECK && file_exists(PATH_STANDARD . '/app/controllers/' . $sController . '.controller.php') && !ACTIVE_TEST) {
        require_once PATH_STANDARD . '/app/controllers/' . $sController . '.controller.php';

        $sClassName = '\candyCMS\Controllers\\' . $sController;
        $this->oController = new $sClassName($this->_aRequest, $this->_aSession, $this->_aFile, $this->_aCookie);
      }

      # There are no extensions, so we use the default controllers
      elseif (file_exists(PATH_STANDARD . '/vendor/candycms/core/controllers/' . $sController . '.controller.php')) {
        require_once PATH_STANDARD . '/vendor/candycms/core/controllers/' . $sController . '.controller.php';

        $sClassName = '\candyCMS\Core\Controllers\\' . $sController;
        $this->oController = new $sClassName($this->_aRequest, $this->_aSession, $this->_aFile, $this->_aCookie);
      }
      else {
        # Bugfix: Fix exceptions when upload file is missing
        if ($sController && substr(strtolower($sController), 0, 6) !== 'upload')
          throw new AdvancedException('Controller not found:' . PATH_STANDARD .
                  '/vendor/candycms/core/controllers/' . $sController . '.controller.php');
      }
    }
    catch (AdvancedException $e) {
      # Check if site should be compatible to candyCMS version 1.x and send headers to browser.
      if (defined('CHECK_DEPRECATED_LINKS') && CHECK_DEPRECATED_LINKS === true &&
              Helper::pluralize($sController) !== $sController &&
              file_exists(PATH_STANDARD . '/vendor/candycms/core/controllers/' . Helper::pluralize($sController) . '.controller.php')) {
        $sUrl = str_replace(strtolower($sController), strtolower(Helper::pluralize($sController)), $_SERVER['REQUEST_URI']);

        AdvancedException::writeLog(__METHOD__ . ' - ' . $e->getMessage());
        return Helper::warningMessage(I18n::get('error.302.info', $sUrl), $sUrl);
      }

      # Redirect RSS
      elseif (defined('CHECK_DEPRECATED_LINKS') && CHECK_DEPRECATED_LINKS === true && 'Rss' == $sController) {
        AdvancedException::writeLog(__METHOD__ . ' - ' . $e->getMessage());
        return Helper::redirectTo('/blogs.rss');
      }

      else {
        AdvancedException::writeLog(__METHOD__ . ' - ' . $e->getMessage());
        return Helper::redirectTo('/errors/404');
      }
    }

    $this->oController->__init();
    return $this->oController;
  }

  /**
   * Handle the pre-defined actions.
   *
   * @access public
   * @return string HTML
   *
   */
  public function getAction() {
    # Bugfix for all versions
    if (!defined('REQUEST_METHOD'))
      define('REQUEST_METHOD', $this->_aRequest['method']);

    switch (REQUEST_METHOD) {
      case 'POST':

        $sAction = isset($this->_aRequest['action']) ?
                strtolower((string) $this->_aRequest['action']) :
                'create';

        break;
      case 'PUT':

        $sAction = isset($this->_aRequest['action']) ?
                strtolower((string) $this->_aRequest['action']) :
                'update';

        break;
      case 'DELETE':

        $sAction = isset($this->_aRequest['action']) ?
                strtolower((string) $this->_aRequest['action']) :
                'destroy';

        break;
      default:
      case 'GET':

        $sAction = isset($this->_aRequest['action']) ?
                strtolower((string) $this->_aRequest['action']) :
                'show';

        break;
    }

    return method_exists($this->oController, $sAction) ?
            $this->oController->setContent($this->oController->$sAction()) :
            $this->oController->setContent($this->oController->show());
  }
}