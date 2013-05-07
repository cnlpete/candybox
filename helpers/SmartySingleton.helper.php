<?php

/**
 * Make Smarty singleton aware.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Helpers;

use Smarty;

class SmartySingleton extends Smarty {

  /**
   *
   * @access private
   *
   */
  private $_aRequest;

  /**
   *
   * @var static
   * @access private
   *
   */
  private static $_oInstance = null;

  /**
   * Get the Smarty instance
   *
   * @static
   * @access public
   * @return object self::$_oInstance Smarty instance that was found or generated
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
    $this->_aRequest = $aRequest;
    $this->assignByRef('_REQUEST', $aRequest);
    $this->assignByRef('_SESSION', $aSession);
  }

  /**
   * Set all default smarty values.
   *
   * @access public
   *
   */
  public function __construct() {
    parent::__construct();

    $this->setCacheDir(PATH_STANDARD . '/' . PATH_SMARTY . '/cache');
    $this->setCompileDir(PATH_STANDARD . '/' . PATH_SMARTY . '/compile');
    $this->setPluginsDir(SMARTY_DIR . '/plugins');
    $this->setTemplateDir(PATH_STANDARD . '/vendor/candycms/core/views');

    # See http://www.smarty.net/docs/en/variable.merge.compiled.includes.tpl
    $this->merge_compiled_includes = true;

    # Use a readable structure
    $this->use_sub_dirs = true;

    # Only compile our templates on production mode.
    if (WEBSITE_MODE == 'production' || WEBSITE_MODE == 'staging') {
      $this->setCompileCheck(false);
      $this->setCacheModifiedCheck(true);
      $this->setCacheLifetime(-1);
    }

    # Define smarty constants
    $this->assign('ACTIVE_TEST', ACTIVE_TEST);
    $this->assign('ALLOW_PLUGINS', ALLOW_PLUGINS);
    $this->assign('CURRENT_URL', CURRENT_URL);
    $this->assign('DISABLE_COMMENTS', DISABLE_COMMENTS);
    $this->assign('MOBILE', MOBILE);
    $this->assign('MOBILE_DEVICE', MOBILE_DEVICE);
    $this->assign('THUMB_DEFAULT_X', THUMB_DEFAULT_X);
    $this->assign('WEBSITE_LOCALE', WEBSITE_LOCALE);
    $this->assign('WEBSITE_MODE', WEBSITE_MODE);
    $this->assign('WEBSITE_NAME', WEBSITE_NAME);
    $this->assign('WEBSITE_URL', WEBSITE_URL);

    # Define system variables
    $this->assign('_PATH', $this->getPaths());

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Upload.helper.php';
    $iMaximumUploadSize = \candyCMS\Core\Helpers\Upload::getUploadLimit();

    $this->assign('_SYSTEM', array(
        'date'                  => date('Y-m-d'),
        'compress_files_suffix' => WEBSITE_COMPRESS_FILES === true ? '.min' : '',
        'hasSessionPlugin'      => PluginManager::getInstance()->hasSessionPlugin(),
        'maximumUploadSize'     => array(
            'raw'               => $iMaximumUploadSize,
            'b'                 => $iMaximumUploadSize . 'B',
            'kb'                => ($iMaximumUploadSize / 1024) . 'KB',
            'mb'                => ($iMaximumUploadSize / 1048576) . 'MB'),
        'json_language'         => I18n::getJson()));

    # Do we want autoloading of pages?
    $this->assign('_AUTOLOAD', array(
        'enabled' => AUTOLOAD === true,
        'times'   => AUTOLOAD_TIMES
    ));
  }

  /**
   * Assign the language array to smarty templates.
   *
   * @access public
   * @param array $aLang the language array
   *
   */
  public function setDefaultLanguage(&$aLang, $sLanguage) {
    $this->assign('lang', $aLang);
    $this->assign('WEBSITE_LANGUAGE', $sLanguage);
  }

  /**
   * Delete this variable from memory...
   *
   * @access public
   *
   */
  public function __destruct() {
    parent::__destruct();
    self::$_oInstance = null;
  }

  /**
   * Generate all path variables that could be useful for Smarty templates.
   *
   * @access public
   * @return array $aPath path information
   * @todo update PATH and description
   * @todo, does smarty need to know about less?
   *
   */
  public function getPaths() {
    $aPaths['js'] = array(
      'core' => '/vendor/candycms/core/assets/javascripts/core',
      'app' => '/app/assets/javascripts',
      'bootstrap' => '/vendor/twitter/bootstrap/js'
    );
    $aPaths['img'] = array(
      'core' => '/vendor/candycms/core/assets/images',
      'app' => '/app/assets/images',
      'bootstrap' => '/vendor/twitter/bootstrap/img'
    );
    if (WEBSITE_CDN !== '') {
      foreach ($aPaths['js'] as $sKey => $sValue)
        $aPaths['js'][key] = WEBSITE_CDN . '/' . key;
      foreach ($aPaths['img'] as $sKey => $sValue)
        $aPaths['img'][key] = WEBSITE_CDN . '/' . key;
    }
    $aPaths['core']     = '/vendor/candycms/core'; # @todo check if needed
    $aPaths['public']   = WEBSITE_CDN !== '' ? WEBSITE_CDN : '/public';
    $aPaths['css']      = $aPaths['public'] . '/stylesheets';
    $aPaths['plugins']  = (WEBSITE_CDN !== '' ? WEBSITE_CDN : '/vendor/candycms') . '/plugins';
    $aPaths['upload']   = Helper::removeSlash(PATH_UPLOAD);

    # Compile CSS only when in development mode
    if (WEBSITE_MODE == 'development') {
      if (MOBILE === true) {
        Helper::compileStylesheet(
                Helper::removeSlash('/app/assets/stylesheets/mobile/application.less'),
                Helper::removeSlash($aPaths['css'] . '/mobile.css'),
                false);

        if (WEBSITE_COMPRESS_FILES === true)
          Helper::compileStylesheet(
                  Helper::removeSlash('/app/assets/stylesheets/mobile/application.less'),
                  Helper::removeSlash($aPaths['css'] . '/mobile.min.css'));
      }
      else {
        Helper::compileStylesheet(
                Helper::removeSlash('/app/assets/stylesheets/core/application.less'),
                Helper::removeSlash($aPaths['css'] . '/core.css'),
                false);

        if (WEBSITE_COMPRESS_FILES === true)
          Helper::compileStylesheet(
                  Helper::removeSlash('/app/assets/stylesheets/core/application.less'),
                  Helper::removeSlash($aPaths['css'] . '/core.min.css'));
      }
    }

    return $aPaths;
  }

  /**
   * Clear the controller cache.
   *
   * @access public
   * @param string $sController
   *
   */
  public function clearControllerCache($sController) {
    $this->clearCache(null, WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|' . $sController);
  }

  public function isCached($oTemplate) {
    return parent::isCached($oTemplate['file']);
  }
  public function setTemplateDir($oTemplate) {
    return parent::setTemplateDir($oTemplate['dir']);
  }
  public function fetch($oTemplate, $sUniqueId) {
    return parent::fetch($oTemplate['file'], $sUniqueId);
  }

  /**
   * Get the template dir. Check if there are extension files and use them if available.
   *
   * @static
   * @access public
   * @param string $sFolder dir of the templates
   * @param string $sFile file name of the template
   * @param boolean $bPlugin whether to check core or plugins folder for original template
   * @return array dir of the chosen template and file: filename of template
   *
   */
  public static function getTemplate($sFolder, $sFile, $bPlugin = false) {
    $sLowerFolder = strtolower($sFolder);
    $sUCFirstFolder = ucfirst($sFolder);
    $aReturn = array();

    try {
      # Extensions
      if (file_exists(PATH_STANDARD . '/app/views/' . $sLowerFolder . '/' . $sFile . '.tpl'))
        $aReturn['dir'] = PATH_STANDARD . '/app/views/' . $sLowerFolder;

      # Standard Plugin views
      else if ($bPlugin) {
        if (!file_exists(PATH_STANDARD . '/vendor/candycms/plugins/' . $sUCFirstFolder . '/views/' . $sFile . '.tpl'))
          throw new AdvancedException('This plugin template does not exist: ' . $sUCFirstFolder . '/views/' . $sFile . '.tpl');
        else
          $aReturn['dir'] = PATH_STANDARD . '/vendor/candycms/plugins/' . $sUCFirstFolder . '/views';
      }

      # Standard Core views
      else {
        if (!file_exists(PATH_STANDARD . '/vendor/candycms/core/views/' . $sLowerFolder . '/' . $sFile . '.tpl')) {
          # This action might be disabled due to missing form templates.
          # @todo why is this check necessary?
          if (substr($sFile, 0, 5) == '_form')
            return Helper::redirectTo('/errors/403');
          else
            throw new AdvancedException('This template does not exist: ' . $sLowerFolder . '/' . $sFile . '.tpl');
        }

        else
          $aReturn['dir'] = PATH_STANDARD . '/vendor/candycms/core/views/' . $sLowerFolder;
      }
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
    }

    try {
      # Mobile template.
      if (MOBILE === true && file_exists($aReturn['dir'] . '/' . $sFile . '.mob'))
        $aReturn['file'] = $sFile . '.mob';
      # Standard template
      else
        $aReturn['file'] = $sFile . '.tpl';
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      exit($e->getMessage());
    }
    return $aReturn;
  }
}
