<?php

/**
 * Manage configs and route incoming request.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Dispatcher;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\PluginManager;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton;
use Routes;

$aFiles = array(
    'models/Main.model.php',
    'controllers/Main.controller.php',
    'controllers/Sessions.controller.php',
    'controllers/Logs.controller.php',
    'helpers/Helper.helper.php',
    'helpers/AdvancedException.helper.php',
    'helpers/Dispatcher.helper.php',
    'helpers/I18n.helper.php',
    'helpers/SmartySingleton.helper.php',
    'helpers/PluginManager.helper.php'
);

require_once PATH_STANDARD . '/vendor/autoload.php';

foreach ($aFiles as $sFile)
  require PATH_STANDARD . '/vendor/candycms/core/' . $sFile;

class Index {

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
   * @var array
   * @access protected
   */
  protected $_aFile;

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aCookie;

  /**
   * Saves the object.
   *
   * @var object
   * @access protected
   *
   */
  protected $_oObject;

  /**
   * Holds the reference to our PluginManager
   *
   * @var object
   * @access protected
   *
   */
  protected $_oPlugins;

  /**
   * Initialize the software by adding input params.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param array $aFile alias for $_FILE
   * @param array $aCookie alias for $_COOKIE
   *
   */
  public function __construct(&$aRequest, &$aSession = '', &$aFile = '', &$aCookie = '') {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;
    $this->_aFile     = & $aFile;
    $this->_aCookie   = & $aCookie;

    $this->getConfigFiles(array('Plugins'));
    $this->getRoutes();

    # Always initialize the plugin manager, since we want to call the events later.
    $this->_oPlugins = PluginManager::getInstance();
    if (strlen(ALLOW_PLUGINS) > 0) {
      $this->_oPlugins->setRequestAndSession($this->_aRequest, $this->_aSession);
      $this->_oPlugins->load(ALLOW_PLUGINS);

      # Run repetitive plugins (such as cronjob).
      $this->_oPlugins->runRepetitivePlugins();
    }
    $this->getLanguage();
    $this->setUser();
  }

  /**
   * Reset all data
   *
   * @access public
   *
   */
  public function __destruct() {
    # Only reload language each time the controller is activated in development mode.
    if (WEBSITE_MODE == 'development')
      I18n::unsetLanguage();

    # Close database connection
    $sModel = Main::__autoload('Main', true);
    $sModel::disconnectFromDatabase();
  }

  /**
   * Load all config files.
   *
   * @static
   * @access public
   * @param array $aConfigs array of config files
   * @return boolean true if no errors occurred.
   *
   */
  public static function getConfigFiles($aConfigs) {
    foreach ($aConfigs as $sConfig) {
      try {
        if (!file_exists(PATH_STANDARD . '/app/config/' . ucfirst($sConfig) . '.inc.php'))
          throw new AdvancedException('Missing ' . ucfirst($sConfig) . ' config file.');

        else
          require_once PATH_STANDARD . '/app/config/' . ucfirst($sConfig) . '.inc.php';

        return true;
      }
      catch (AdvancedException $e) {
        die($e->getMessage());
      }
    }
  }

  /**
   * Read the routes from Routes.yml and set request params.
   *
   * @access public
   * @return array $this->_aRequest
   * @see app/config/Routes.yml
   *
   */
  public function getRoutes() {
    require_once PATH_STANDARD . '/vendor/simonhamp/routes/routes.php';

    # Cache routes for performance reasons and clear it for testing and development
    if(!isset($this->_aSession['routes']) || WEBSITE_MODE == 'development' || ACTIVE_TEST)
      $this->_aSession['routes'] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(PATH_STANDARD . '/app/config/Routes.yml'));

    Routes::add($this->_aSession['routes']);

    if (!defined('WEBSITE_LANDING_PAGE'))
      define('WEBSITE_LANDING_PAGE', Routes::route('/'));

    $sURI = isset($_SERVER['REQUEST_URI']) ?
            Helper::removeSlash($_SERVER['REQUEST_URI']) :
            '';

    # Disable slashes at the end of the domain
    $sURILen = strlen($sURI);
    if (substr($sURI, $sURILen - 1, $sURILen) == '/')
      $sURI = substr($sURI, 0, $sURILen - 1);

    if ( strpos( $sURI, '?' ) !== false ) {
      # Break the query string off and attach later
      $sAdditionalParams = parse_url( $sURI, PHP_URL_QUERY );
      $sURI = str_replace( '?' . $sAdditionalParams, '', $sURI );
    }

    $aRouteParts = explode('&', Routes::route($sURI));

    if (strlen($sAdditionalParams) > 0)
      $aRouteParts = array_merge($aRouteParts, explode('&', $sAdditionalParams));

    if (count($aRouteParts) > 0) {
      foreach ($aRouteParts as $sRoutes) {
        $aRoute = explode('=', $sRoutes);

        if(!isset($this->_aRequest[$aRoute[0]]) && strlen(trim($aRoute[0])) > 0)
          $this->_aRequest[$aRoute[0]] = $aRoute[1];
      }
    }

    if (!isset($this->_aRequest['controller']))
      $this->_aRequest['controller'] = WEBSITE_LANDING_PAGE;

    # Set request method for rest services. This is actually FAKE REST
    $this->_aRequest['method'] = isset($this->_aRequest['method']) ?
            strtoupper((string) $this->_aRequest['method']) :
            $_SERVER['REQUEST_METHOD'];

    # Show files from public folder (robots.txt, human.txt and favicon.ico)
    if (preg_match('/\.txt/', $sURI) || preg_match('/\.ico/', $sURI) && !isset($this->_aRequest['action'])) {
			$sFileRoot = Helper::removeSlash(WEBSITE_CDN) . '/' . $sURI;

      if (file_exists($sFileRoot))
        exit(file_get_contents($sFileRoot));

      else
        return Helper::redirectTo('/errors/404');
		}

    return $this->_aRequest;
  }

  /**
   * Sets the language. This can be done via a language request and be temporarily saved in a cookie.
   *
   * @access public
   * @return string language
   * @see app/config/Candy.inc.php
   *
   */
  public function getLanguage() {
    if (!defined('DEFAULT_LANGUAGE'))
      define('DEFAULT_LANGUAGE', 'en');

    $aRequest = (isset($this->_aCookie) && is_array($this->_aCookie)) ?
                  array_merge($this->_aRequest, $this->_aCookie) :
                  $this->_aRequest;

    # Get language by browser
    $aBrowserLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $sBrowserLanguage = (string) substr($aBrowserLanguage[0], 0, 2);

    # We got a language request? This is either done via putting ?language=en to an URL or via cookie
    # Get the request...
    if (isset($aRequest['language']) && !isset($this->_aRequest['blogs']) &&
            file_exists(PATH_STANDARD . '/app/languages/' . strtolower((string) $aRequest['language']) . '.yml')) {
      $sLanguage = strtolower((string) $aRequest['language']);
      setcookie('default_language', (string) $sLanguage, time() + 2592000, '/');
    }

    # ...or use cookie...
    elseif (isset($aRequest['default_language']) &&
            file_exists(PATH_STANDARD . '/app/languages/' . strtolower((string) $aRequest['default_language']) . '.yml'))
      $sLanguage = strtolower((string) $aRequest['default_language']);

    # ...or browsers default language...
    elseif (!ACTIVE_TEST && file_exists(PATH_STANDARD . '/app/languages/' . strtolower($sBrowserLanguage) . '.yml'))
      $sLanguage = $sBrowserLanguage;

    # ...or fall back to default language.
    else
      $sLanguage = strtolower(DEFAULT_LANGUAGE);

    # Set iso language codes
    switch (substr($sLanguage, 0, 2)) {
      case 'de':
        $sLocale = 'de_DE';
        ini_set('date.timezone', 'Europe/Berlin');

        break;

      case 'en':
        $sLocale = 'en_US';
        ini_set('date.timezone', 'America/New_York');

        break;

      case 'es':
        $sLocale = 'es_ES';
        ini_set('date.timezone', 'Europe/Madrid');

        break;

      case 'fr':
        $sLocale = 'fr_FR';
        ini_set('date.timezone', 'Europe/Paris');

        break;

      case 'pt':
        $sLocale = 'pt_PT';
        ini_set('date.timezone', 'Europe/Lisbon');

        break;
    }

    if (!defined('WEBSITE_LANGUAGE'))
      define('WEBSITE_LANGUAGE', substr($sLanguage, 0, 2));

    if (!defined('WEBSITE_LOCALE'))
      define('WEBSITE_LOCALE', $sLocale);

    setlocale(LC_ALL, $sLocale);
    new I18n($sLanguage, $this->_aSession, $this->_aPlugins);

    return $sLocale;
  }

  /**
   * Store and show flash status messages in the application.
   *
   * @access protected
   * @see app/config/Candy.inc.php
   * @return array $aFlashMessage The message, its type and the headline of the message.
   *
   */
  protected function _getFlashMessage() {
    $aFlashMessage = isset($this->_aSession['flash_message']) ? $this->_aSession['flash_message'] : '';

    unset($this->_aSession['flash_message']);
    return $aFlashMessage;
  }

  /**
   * Checks the empuxa server for a new candyCMS version.
   *
   * @access private
   * @return string string with info message and link to download.
   *
   */
  private function _checkForNewVersion() {
    if ($this->_aSession['user']['role'] == 4 && ALLOW_VERSION_CHECK === true &&
            (WEBSITE_MODE == 'staging' || WEBSITE_MODE == 'production')) {
      $oFile = @fopen('https://raw.github.com/marcoraddatz/candycms/master/version.txt', 'rb');
      $sVersionContent = @stream_get_contents($oFile);
      @fclose($oFile);

      $sVersionContent = (int) $sVersionContent > (int) file_get_contents(PATH_STANDARD . '/version.txt') ?
              (int) $sVersionContent :
              '';
    }

    return isset($sVersionContent) && !empty($sVersionContent) ?
            I18n::get('global.update.available', $sVersionContent, Helper::createLinkTo('http://www.candycms.com', true)) :
            '';
  }

  /**
   * Return default user data.
   *
   * @static
   * @access protected
   * @return array default user data
   *
   */
  protected static function _resetUser() {
    return array(
        'email' => '',
        'id' => 0,
        'name' => '',
        'surname' => '',
        'password' => '',
        'role' => 0,
        'full_name' => ''
    );
  }

  /**
   * Define user constants for global use.
   *
   * List of user roles:
   * 0 = Guests / unregistered users
   * 1 = Members
   * 2 = Session plugin users
   * 3 = Moderators
   * 4 = Administrators
   *
   * @access public
   * @see index.php
   * @return array $this->_aSession['user']
   *
   */
  public function setUser() {
    # Set standard variables
    $this->_aSession['user'] = self::_resetUser();

    # Get user by token
    if (isset($this->_aRequest['api_token']) && !empty($this->_aRequest['api_token'])) {
      if (EXTENSION_CHECK && file_exists(PATH_STANDARD . '/app/models/Users.model.php')) {
        require_once PATH_STANDARD . '/app/models/Users.model.php';
        $aUser = \candyCMS\Models\Users::getUserByToken(Helper::formatInput($this->_aRequest['api_token']));
      }
      else {
        require_once PATH_STANDARD . '/vendor/candycms/core/models/Users.model.php';
        $aUser = \candyCMS\Core\Models\Users::getUserByToken(Helper::formatInput($this->_aRequest['api_token']));
      }
    }

    # Get user by session
    else {
      if (EXTENSION_CHECK && file_exists(PATH_STANDARD . '/app/models/Sessions.model.php')) {
        require_once PATH_STANDARD . '/app/models/Sessions.model.php';
        $aUser = \candyCMS\Models\Sessions::getUserBySession();
      }
      else {
        require_once PATH_STANDARD . '/vendor/candycms/core/models/Sessions.model.php';
        $aUser = \candyCMS\Core\Models\Sessions::getUserBySession();
      }
    }

    if (is_array($aUser))
      $this->_aSession['user'] = array_merge($this->_aSession['user'], $aUser);

    # Try to get session plugin data from Facebook or similar.
    if ($this->_aSession['user']['role'] == 0) {
      $oPluginManager = PluginManager::getInstance();
      if ($oPluginManager->hasSessionPlugin()) {
        if ($oPluginManager->getSessionPlugin()->setUserData($this->_aSession['user']))
          $this->_aSession['user']['role'] = 2;
      }
    }

    # Set up full name finally
    $this->_aSession['user']['full_name'] = $this->_aSession['user']['name'] . ' ' . $this->_aSession['user']['surname'];

    return $this->_aSession['user'];
  }

  /**
   * Show the application.tpl with all header and footer data such as meta tags etc.
   *
   * @access public
   * @return string $sCachedHTML The whole HTML code of our application.
   *
   */
  public function show() {
    # Set a caching / compile ID
    # Ask if defined because of unit tests.
    if (!defined('UNIQUE_PREFIX'))
      define('UNIQUE_PREFIX', WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|' . $this->_aRequest['controller']);

    if (!defined('UNIQUE_ID')) {
      define('UNIQUE_ID', UNIQUE_PREFIX . '|' . $this->_aSession['user']['role'] .
              (MOBILE ? 'mob|' : 'tpl|') . '|' .
              substr(md5(CURRENT_URL), 0, 10));
    }

    # Start the dispatcher and grab the controller.
    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setRequestAndSession($this->_aRequest, $this->_aSession);

    $oDispatcher = new Dispatcher($this->_aRequest, $this->_aSession, $this->_aFile, $this->_aCookie);
    $oDispatcher->getController();
    $oDispatcher->getAction();

    # Minimal settings for non HTML requests
    if (isset($this->_aRequest['type']) && (
            'ajax'  == $this->_aRequest['type'] ||
            'rss'   == $this->_aRequest['type'] ||
            'json'  == $this->_aRequest['type'] ||
            'xml'   == $this->_aRequest['type']))
      $sCachedHTML = $oDispatcher->oController->getContent();

    # HTML with template
    else {
      $sTemplateDir   = Helper::getTemplateDir('layouts', 'application');
      $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'application');

      # Get flash messages (success and error)
      $oSmarty->assign('_FLASH', $this->_getFlashMessage());

      # Define meta elements
      $aWebsite['title']    = $oDispatcher->oController->getTitle();
      $aWebsite['content']  = $oDispatcher->oController->getContent();
      $aWebsite['update']   = $this->_checkForNewVersion();

      $aWebsite['meta'] = array(
          'description' => $oDispatcher->oController->getDescription(),
          'expires'     => gmdate('D, d M Y H:i:s', time() + 60) . ' GMT',
          'keywords'    => $oDispatcher->oController->getKeywords(),
          'og'          => array(
              'description' => $oDispatcher->oController->getDescription(),
              'site_name'   => WEBSITE_NAME,
              'title'       => $oDispatcher->oController->getTitle(),
              'url'         => CURRENT_URL
          ));

      $oSmarty->assign('_WEBSITE', $aWebsite);

      $oSmarty->setTemplateDir($sTemplateDir);
      $oSmarty->setCaching(\candyCMS\Core\Helpers\SmartySingleton::CACHING_OFF);
      $sCachedHTML = $oSmarty->fetch($sTemplateFile, UNIQUE_ID);
    }


    $sCachedHTML = $this->_oPlugins->runGlobalDisplayPlugins($sCachedHTML);
    $sCachedHTML = $this->_oPlugins->runSimplePlugins($sCachedHTML);
    $sCachedHTML = $this->_oPlugins->runSessionPlugin($sCachedHTML);

    header('Content-Type: text/html; charset=utf-8');
    return $sCachedHTML;
  }
}