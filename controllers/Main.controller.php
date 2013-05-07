<?php

/**
 * Parent class for most other controllers and provides most language variables.
 *
 * @abstract
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\PluginManager;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;
use MCAPI;

abstract class Main {

  /**
   * Alias for $_REQUEST
   *
   * @var array
   * @access protected
   */
  protected $_aRequest = array();

  /**
   * Alias for $_SESSION
   *
   * @var array
   * @access protected
   */
  protected $_aSession = array();

  /**
   * Alias for $_FILE
   *
   * @var array
   * @access protected
   */
  protected $_aFile;

  /**
   * Alias for $_COOKIE
   *
   * @var array
   * @access protected
   */
  protected $_aCookie;

  /**
   * ID to process.
   *
   * @var integer
   * @access protected
   */
  protected $_iId;

  /**
   * Fetches all error messages in an array.
   *
   * @var array
   * @access protected
   */
  protected $_aError;

  /**
   * The controller claimed model.
   *
   * @var object
   * @access protected
   */
  protected $_oModel;

  /**
   * Returned data from models.
   *
   * @var array
   * @access protected
   */
  protected $_aData = array();

  /**
   * Final HTML-Output.
   *
   * @var string
   * @access private
   */
  private $_sContent;

  /**
   * Name of the current controller.
   *
   * @var string
   * @access protected
   */
  protected $_sController;

  /**
   * Meta description.
   *
   * @var string
   * @access private
   */
  private $_sDescription;

  /**
   * Meta keywords.
   *
   * @var string
   * @access private
   */
  private $_sKeywords;

  /**
   * Page title.
   *
   * @var string
   * @access private
   */
  private $_sTitle;

  /**
   * Name of the templates folder.
   *
   * @var string
   * @access protected
   *
   */
  protected $_sTemplateFolder;

  /**
   * Smarty object.
   *
   * @var object
   * @access public
   */
  public $oSmarty;

  /**
   * All the caches, the controller has to clear on change.
   *
   * @var array
   * @access protected
   */
  protected $_aDependentCaches = array('searches', 'sitemaps');

  /**
   * the current redirect URL, if set, the controller 'should' redirect there after completion of _create, _update or _destroy
   *
   * @var array
   * @access protected
   */
  protected $_sRedirectURL = '';

  /**
   * Initialize the controller by adding input params, set default id and start template engine.
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

    # Load config files if not already done (important for unit testing)
    # @todo this should be done in the testing scenarios instead, i.e. the respective initialization should be included
    if (!defined('WEBSITE_URL'))
      require PATH_STANDARD . '/config/Candy.inc.php';

    if (!defined('WEBSITE_LOCALE'))
      define('WEBSITE_LOCALE', 'en_US');

    $this->_iId = isset($this->_aRequest['id']) ? (int) $this->_aRequest['id'] : '';
    $this->_sController = $this->_aRequest['controller'];

    $this->_setSmarty();
  }

  /**
   * Destructor.
   *
   * @access public
   *
   */
  public function __destruct() {}

  /**
   * Dynamically load classes.
   *
   * @static
   * @param string $sClass name of class to load
   * @param boolean $bModel load a model file
   * @return string class name
   *
   */
  public static function __autoload($sClass, $bModel = false) {
    $sClass = (string) ucfirst(strtolower($sClass));

    if ($bModel === true)
      return \candyCMS\Core\Models\Main::__autoload($sClass);

    else {
      if (EXTENSION_CHECK && file_exists(PATH_STANDARD . '/app/controllers/' . $sClass . '.controller.php')) {
        require_once PATH_STANDARD . '/app/controllers/' . $sClass . '.controller.php';
        return '\candyCMS\Controllers\\' . $sClass;
      }
      else {
        require_once PATH_STANDARD . '/vendor/candycms/core/controllers/' . $sClass . '.controller.php';
        return '\candyCMS\Core\Controllers\\' . $sClass;
      }
    }
  }

  /**
   * Method to include the model files.
   *
   * @access public
   * @return object $this->_oModel
   *
   */
  public function __init() {
    $sModel = $this->__autoload($sController ? $sController : $this->_sController, true);

    if ($sModel)
      $this->_oModel = new $sModel($this->_aRequest, $this->_aSession, $this->_aFile);

    return $this->_oModel;
  }

  /**
   * Set up Smarty.
   *
   * @access proteced
   * @return object $this->oSmarty
   *
   */
  protected function _setSmarty() {
    # Initialize smarty
    $this->oSmarty = Smarty::getInstance();

    # Clear cache on development mode or when we force it via a request.
    if (isset($this->_aRequest['clearcache']) || WEBSITE_MODE == 'development' || ACTIVE_TEST) {
      $this->oSmarty->clearAllCache();
      $this->oSmarty->clearCompiledTemplate();
    }

    return $this->oSmarty;
  }

  /**
   * Set meta description.
   *
   * @access public
   * @param string $sDescription description to be set.
   *
   */
  public function setDescription($sDescription) {
    if (!empty($sDescription))
      $this->_sDescription = & $sDescription;
  }

  /**
   * Give back the meta description.
   *
   * @access public
   * @return string meta description
   *
   */
  public function getDescription() {
    # Show default description if this is our landing page or we got no descrption.
    if (CURRENT_URL == (WEBSITE_URL . '/'))
      $this->setDescription(I18n::get('website.description'));

    elseif (!$this->_sDescription)
      $this->setDescription($this->getTitle());

    return $this->_sDescription;
  }

  /**
   * Set meta keywords.
   *
   * @access public
   * @param string $sKeywords keywords to be set.
   *
   */
  public function setKeywords($sKeywords) {
    if (!empty($sKeywords))
      $this->_sKeywords = & $sKeywords;
  }

  /**
   * Give back the meta keywords.
   *
   * @access public
   * @return string meta keywords
   *
   */
  public function getKeywords() {
    return $this->_sKeywords ? $this->_sKeywords : I18n::get('website.keywords');
  }

  /**
   * Set meta title.
   *
   * @access public
   * @param string $sTitle title to be set.
   *
   */
  public function setTitle($sTitle) {
    if (!empty($sTitle))
      $this->_sTitle = & $sTitle;
  }

  /**
   * Give back the page title.
   *
   * @access public
   * @return string page title
   *
   */
  public function getTitle() {
    # Normally title should be already set.
    if(!$this->_sTitle) {
      if ($this->_sController == 'errors')
        $this->setTitle(I18n::get('error.' . $this->_aRequest['id'] . '.title'));

      else
        $this->setTitle(I18n::get('global.' . strtolower($this->_sController)));
    }

    return $this->_sTitle;
  }

  /**
   * Set the page content.
   *
   * @access public
   * @param string $sContent HTML content
   * @see vendor/candycms/core/helpers/Dispatcher.helper.php
   *
   */
  public function setContent($sContent) {
    $this->_sContent = & $sContent;
  }

  /**
   * Give back the page content (HTML).
   *
   * @access public
   * @return string $this->_sContent
   */
  public function getContent() {
    return $this->_sContent;
  }

  /**
   * Give back ID.
   *
   * @access public
   * @return integer $this->_iId
   *
   */
  public function getId() {
    return (int) $this->_iId;
  }

  /**
   * Quick hack for displaying title without html tags.
   *
   * @static
   * @access protected
   * @param string $sTitle title to modifiy
   * @return string modified title
   *
   */
  protected static function _removeHighlight($sTitle) {
    $sTitle = str_replace('<mark>', '', $sTitle);
    $sTitle = str_replace('</mark>', '', $sTitle);
    return $sTitle;
  }

  /**
   * Set error messages.
   *
   * @access protected
   * @param string $sField field to be checked
   * @param string $sMessage error to be displayed
   * @return object $this due to method chaining
   * @todo extend tests for JSON requests
   * @todo remove exit() function from JSON
   *
   */
  protected function _setError($sField, $sMessage = '') {
    if ($sField == 'file' || $sField == 'image') {
      # AJAX files uploads
      if (isset($this->_aRequest['type']) && $this->_aRequest['type'] == 'json' && empty($this->_aFile)) {
        header('Content-Type: application/json');
        exit(json_encode(array(
                    'success' => false,
                    'error'   => array($sField => $sMessage ? $sMessage : I18n::get('error.form.missing.file')),
                    'data'    => WEBSITE_MODE == 'development' ? $this->_aFile : ''
                )));
      }

      # Normal file uploads
      elseif (!isset($this->_aFile[$sField]) || empty($this->_aFile[$sField]['name']))
        $this->_aError[$sField] = $sMessage ?
                $sMessage :
                I18n::get('error.form.missing.file');
    }

    else {
      # AJAX inputs
      if (isset($this->_aRequest['type']) && $this->_aRequest['type'] == 'json' &&
              (!isset($this->_aRequest[$this->_sController][$sField]) || empty($this->_aRequest[$this->_sController][$sField]))) {
        header('Content-Type: application/json');
        exit(json_encode(array(
                    'success' => false,
                    'error'   => array($sField => $sMessage ? $sMessage : I18n::get('error.form.missing.' . strtolower($sField))),
                    'data'    => WEBSITE_MODE == 'development' ? $this->_aRequest : ''
                )));
      }

      # Normal inputs
      if (!isset($this->_aRequest[$this->_sController][$sField]) || empty($this->_aRequest[$this->_sController][$sField]))
          $sError = I18n::get('error.form.missing.' . strtolower($sField)) ?
                I18n::get('error.form.missing.' . strtolower($sField)) :
                I18n::get('error.form.missing.standard');

      if ('email' == $sField && !Helper::checkEmailAddress($this->_aRequest[$this->_sController]['email']))
          $sError = $sError ? $sError : I18n::get('error.mail.format');

      if ($sError)
        $this->_aError[$sField] = !$sMessage ? $sError : $sMessage;
    }

    return $this;
  }

  /**
   * Show a entry.
   *
   * @access public
   * @return string HTML
   *
   */
  public function show() {
    $this->oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    $sType = isset($this->_aRequest['type']) && 'ajax' !== $this->_aRequest['type'] ?
            strtoupper($this->_aRequest['type']) :
            '';

    $sMethod = $this->_iId || isset($this->_aRequest['site']) ?
            '_show' . $sType :
            '_overview' . $sType;

    return $this->$sMethod();
  }

  /**
   * Just a backup method to show an entry.
   *
   * @access protected
   * @return string HTML
   *
   */
  protected function _show() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->' . __FUNCTION__);
    return Helper::redirectTo('/errors/404');
  }

  /**
   * Just a backup method to show an overview and stay compatible.
   *
   * @access protected
   * @return string HTML
   *
   */
  protected function _overview() {
    return $this->_show();
  }

  /**
   * Just a backup method to show an entry as XML.
   *
   * @access protected
   * @return string XML
   *
   */
  protected function _showXML() {
    header('Content-Type: application/xml');
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->' . __FUNCTION__);
    return Helper::redirectTo('/errors/404');
  }

  /**
   * Just a backup method to show an overview as XML.
   *
   * @access protected
   * @return string XML
   *
   */
  protected function _overviewXML() {
    header('Content-Type: application/xml');
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->' . __FUNCTION__);
    return Helper::redirectTo('/errors/404');
  }

  /**
   * Just a backup method to show an entry as JSON.
   *
   * @access protected
   * @return string json
   *
   */
  protected function _showJSON() {
    header('Content-Type: application/json');

    if (method_exists($this->_oModel, 'getId'))
      return json_encode(array(
                  'success' => true,
                  'error'   => '',
                  'data'    => $this->_oModel->getId($this->_iId)
              ));

    else {
      AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->' . __FUNCTION__);
      return json_encode(array(
                  'success' => false,
                  'error'   => 'There is no JSON handling method called ' . __FUNCTION__ . ' for this controller.'
              ));
    }
  }

  /**
   * Just a backup method to show an overview as JSON.
   *
   * @access protected
   * @return string json
   *
   */
  protected function _overviewJSON() {
    if (method_exists($this->_oModel, 'getOverview'))
      return json_encode(array(
                  'success' => true,
                  'error'   => '',
                  'data'    => $this->_oModel->getOverview()
              ));

    else {
      AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->' . __FUNCTION__);
      return json_encode(array(
                  'success' => false,
                  'error'   => 'There is no JSON handling method called ' . __FUNCTION__ . ' for this controller.'
              ));
    }
  }

  /**
   * Just a backup method to show an entry as RSS.
   *
   * @access protected
   * @return string json
   *
   */
  protected function _showRSS() {
    header('Content-Type: application/rss+xml');
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->' . __FUNCTION__);
    return Helper::redirectTo('/errors/404');
  }

  /**
   * Just a backup method to show an overview as RSS.
   *
   * @access protected
   * @return string json
   *
   */
  protected function _overviewRSS() {
    header('Content-Type: application/rss+xml');
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->' . __FUNCTION__);
    return Helper::redirectTo('/errors/404');
  }

  /**
   * Create an action.
   *
   * Create entry or show form template if we have enough rights.
   *
   * @access public
   * @param integer $iUserRole required user right
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function create($iUserRole = 3) {
    $this->oSmarty->setCaching(false);

    if ($this->_aSession['user']['role'] < $iUserRole)
      return Helper::redirectTo('/errors/403');

    elseif (isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'])
      $this->_create();

    else
      return isset($this->_aRequest[$this->_sController]) ?
              $this->_create() :
              $this->_showFormTemplate();
  }

  /**
   * Update entry or show form template if we have enough rights.
   *
   * @access public
   * @param integer $iUserRole required user right
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function update($iUserRole = 3) {
    $this->oSmarty->setCaching(false);

    if (!$this->_iId)
      return Helper::redirectTo('/errors/403');

    elseif ($this->_aSession['user']['role'] < $iUserRole)
      return Helper::redirectTo('/errors/401');

    elseif (isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'])
      $this->_update();

    else
      return isset($this->_aRequest[$this->_sController]) ?
              $this->_update() :
              $this->_showFormTemplate();
  }

  /**
   * Delete entry if we have enough rights.
   *
   * @access public
   * @param integer $iUserRole required user right
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function destroy($iUserRole = 3) {
    $this->oSmarty->setCaching(false);

    if (!$this->_iId)
      return Helper::redirectTo('/errors/403');

    else
      return $this->_aSession['user']['role'] < $iUserRole ?
              Helper::redirectTo('/errors/401') :
              $this->_destroy();
  }

  /**
   * Build form template to create or update an entry.
   *
   * @access protected
   * @param string $sTemplateName name of form template
   * @param string $sTitle title to show
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {
    # We don't support JSON
    # @todo put this into a seperated method
    if (isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'])
      return json_encode(array(
                  'success' => false,
                  'error'   => 'There is no JSON handling method called ' . __FUNCTION__ . ' for this controller.'
              ));

    $oTemplate = Smarty::getTemplate($this->_sController, $sTemplateName);

    if ($this->_iId) {
      $aData = $this->_oModel->getId($this->_iId, true);

      if ($sTitle && isset($aData['title']))
        $this->setTitle(vsprintf(I18n::get($sTitle . '.update'), $aData['title']));

      elseif (isset($aData['title']))
        $this->setTitle(vsprintf(I18n::get($this->_sController . '.title.update'), $aData['title']));

      foreach ($aData as $sColumn => $sData)
        $this->oSmarty->assign($sColumn, $sData);
    }
    else {
      foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
        $this->oSmarty->assign($sInput, $sData);

      if ($sTitle)
        $this->setTitle(I18n::get($sTitle . '.create'));

      else
        $this->setTitle(I18n::get($this->_sController . '.title.create'));
    }

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $oPluginManager = PluginManager::getInstance();
    $this->oSmarty->assign('editorinfo', $oPluginManager->getEditorInfo());

    $this->oSmarty->setTemplateDir($oTemplate);
    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Clear all caches for given controllers.
   *
   * @access protected
   *
   */
  protected function _clearAdditionalCaches() {
    foreach ($this->_aDependentCaches as $sCache)
      $this->oSmarty->clearControllerCache($sCache);
  }

  /**
   * Create an entry.
   *
   * Check if required data is given or throw an error instead.
   * If data is given, activate the model, insert them into the database and redirect afterwards.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('title');

    if ($this->_aError)
      return $this->_showFormTemplate();

    else {
      $bResult = $this->_oModel->create() === true;

      $iId = $this->_oModel->getLastInsertId($this->_sController);

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    $iId,
                    $this->_aSession['user']['id'],
                    '', '', $bResult);

      if ($bResult) {
        $this->oSmarty->clearControllerCache($this->_sController);

        # clear additional caches if given
        if (count($this->_aDependentCaches) > 0)
          $this->_clearAdditionalCaches();

        $sRedirectURL = empty($this->_sRedirectURL) ? '/' . $this->_sController . '/' . $iId : $this->_sRedirectURL;

        return Helper::successMessage(
                I18n::get('success.create'),
                $sRedirectURL,
                $this->_aRequest);
      }
      else {
        $sRedirectURL = empty($this->_sRedirectURL) ? '/' . $this->_sController : $this->_sRedirectURL;

        return Helper::errorMessage(
                I18n::get('error.sql'),
                $sRedirectURL,
                $this->_aRequest);
      }
    }
  }

  /**
   * Update an entry.
   *
   * Activate model, insert data into the database and redirect afterwards.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _update() {
    $this->_setError('title');

    $sRedirectURL = empty($this->_sRedirectURL) ?
            '/' . $this->_aRequest['controller'] . '/' . (int) $this->_aRequest['id'] :
            $this->_sRedirectURL;

    if ($this->_aError)
      return $this->_showFormTemplate();

    else {
      $bReturn = $this->_oModel->update((int) $this->_aRequest['id']) === true;

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    (int) $this->_aRequest['id'],
                    $this->_aSession['user']['id'],
                    '', '', $bReturn);

      if ($bReturn) {
        $this->oSmarty->clearControllerCache($this->_sController);

        # Clear additional caches if given
        if (count($this->_aDependentCaches) > 0)
          $this->_clearAdditionalCaches();

        return Helper::successMessage(
                I18n::get('success.update'),
                $sRedirectURL,
                isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ? $this->_aRequest : '');
      }
      else
        return Helper::errorMessage(
                I18n::get('error.sql'),
                $sRedirectURL,
                isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ? $this->_aRequest : '');
    }
  }

  /**
   * Destroy an entry.
   *
   * Activate model, delete data from database and redirect afterwards.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _destroy() {
    $bReturn = $this->_oModel->destroy($this->_iId) === true;

    $sRedirectURL = empty($this->_sRedirectURL) ?
            '/' . $this->_sController :
            $this->_sRedirectURL;

    Logs::insert( $this->_sController,
                  $this->_aRequest['action'],
                  (int) $this->_iId,
                  $this->_aSession['user']['id'],
                  '', '', $bReturn);

    if ($bReturn) {
      $this->oSmarty->clearControllerCache($this->_sController);

      # Clear additional caches if given
        if (count($this->_aDependentCaches) > 0)
          $this->_clearAdditionalCaches();

      return Helper::successMessage(
              I18n::get('success.destroy'),
              $sRedirectURL,
              isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ? $this->_aRequest : '');
    }

    else
      return Helper::errorMessage(
              I18n::get('error.sql'),
              $sRedirectURL,
              isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ? $this->_aRequest : '');
  }

  /**
   * Subscribe to newsletter list.
   *
   * @static
   * @access protected
   * @param array $aData user data
   * @return boolean status of subscription
   *
   */
  protected static function _subscribeToNewsletter($aData, $bDoubleOptIn = false) {
    require_once PATH_STANDARD . '/vendor/mailchimp/mcapi/MCAPI.class.php';

    $oMCAPI = new MCAPI(MAILCHIMP_API_KEY);
    return $oMCAPI->listSubscribe(MAILCHIMP_LIST_ID,
            $aData['email'],
            array('FNAME' => $aData['name'], 'LNAME' => $aData['surname']),
            '',
            $bDoubleOptIn);
  }

  /**
   * Remove from newsletter list
   *
   * @static
   * @access private
   * @param string $sEmail
   * @return boolean status of action
   *
   */
  protected static function _unsubscribeFromNewsletter($sEmail) {
    require_once PATH_STANDARD . '/vendor/mailchimp/mcapi/MCAPI.class.php';

    $oMCAPI = new MCAPI(MAILCHIMP_API_KEY);
    return $oMCAPI->listUnsubscribe(MAILCHIMP_LIST_ID, $sEmail, '', '', false, false);
  }
}
