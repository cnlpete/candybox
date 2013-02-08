<?php

/**
 * CRD action of comments.
 *
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
use candyCMS\Plugins\Recaptcha;

class Comments extends Main {

  /**
   * Defines the additional caches that will be deleted on CRUD actions.
   *
   * @access protected
   *
   */
  protected $_aDependentCaches = array('blogs', 'searches', 'sitemap');


  /**
   * Initialize the controller by adding input params, set default id and start template engine.
   *
   * We must override the controller in here.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param array $aFile alias for $_FILE
   * @param array $aCookie alias for $_COOKIE
   *
   */
  public function __construct(&$aRequest, &$aSession, &$aFile = '', &$aCookie = '') {
    parent::__construct($aRequest, $aSession, $aFile, $aCookie);

    # Overwrite the _sController variable
    $this->_sController = 'comments';
  }

  /**
   * Set parent data as array.
   *
   * @param array $aParentData
   *
   */
  public function _setParentData($aParentData = '') {
    $this->_aParentData = & $aParentData;
  }

  /**
   * Show comment entries.
   *
   * @access protected
   * @return string HTML content
   * @todo seperate form and comments
   *
   */
  protected function _show() {
    # Bugfix: Avoid the use of "/comments" and "/comments/ID"
    if (!$this->_aParentData)
      return Helper::redirectTo('/blogs' . $this->_iId ? '/' . $this->_iId : '');

    $sTemplateDir   = Helper::getTemplateDir('comments', 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $this->oSmarty->assign('comments',
              $this->_oModel->getOverview($this->_iId, (int) $this->_aParentData[1]['comment_sum'], LIMIT_COMMENTS));

      # Set author of blog entry
      $this->oSmarty->assign('author_id', (int) $this->_aParentData[1]['author_id']);

      # For correct information, do some math to display entries.
      # NOTE: If you're admin, you can see all entries. That might bring pagination to your view, even
      # when other people don't see it
      $this->oSmarty->assign('comment_number',
              ($this->_oModel->oPagination->getCurrentPage() * LIMIT_COMMENTS) - LIMIT_COMMENTS);

      # Do we need pages?
      $this->oSmarty->assign('_pages_', $this->_oModel->oPagination->showPages('/blogs/' . $this->_iId));
    }

    # We can leave caching on, the form itself will turn caching off, but that is a different template.
    # Also _form is directly attached. Might be good to seperate.
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID) . $this->create();
  }

  /**
   * Build form template to create a comment.
   *
   * @access protected
   * @param string $sTemplateName name of form template, only for E_STRICT
   * @param string $sTitle title to show, only for E_STRICT
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {
    $sTemplateDir   = Helper::getTemplateDir('comments', '_form');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, '_form');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
      $this->oSmarty->assign($sInput, $sData);

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    $oPluginManager = PluginManager::getInstance();
    $this->oSmarty->assign('editorinfo', $oPluginManager->getEditorInfo());

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Create entry, check for captcha or show form template if we have enough roles.
   * We must override the main method due to a diffent required user role.
   *
   * @access public
   * @return string HTML content
   *
   */
  public function create() {
    # No caching for comments
    $this->oSmarty->setCaching(false);

    return isset($this->_aRequest[$this->_sController]) ?
            $this->_create() :
            $this->_showFormTemplate();
  }

  /**
   * Create a blog entry.
   *
   * Check if required data is given or throw an error instead.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('parent_id', I18n::get('error.missing.id'));
    $this->_setError('content');

    if ($this->_aSession['user']['role'] == 0)
      $this->_setError('name');

    if (isset($this->_aRequest[$this->_sController]['email']) && $this->_aRequest[$this->_sController]['email'])
      $this->_setError('email');

    # Do the captchaCheck only for for not logged in users.
    if ($this->_aSession['user']['role'] == 0) {
      $oPluginManager = PluginManager::getInstance();
      $oPluginManager->checkCaptcha($this->_aError);
    }

    if ($this->_aError)
      return $this->_showFormTemplate();

    else {
      # Bugfix for jquery mobile not handling this redirect with hash very vell.
      # @todo: Remove when removing jQuery mobile (before 3.0 release).
      $this->_sRedirectURL = '/blogs/' . (int) $this->_aRequest[$this->_sController]['parent_id'] . (MOBILE ? '' : '#comments');

      if ($this->_oModel->create() === true) {
        $this->_clearAdditionalCaches();

        Logs::insert( 'comments',
                      'create',
                      Helper::getLastEntry('comments'),
                      $this->_aSession['user']['id']);

        return Helper::successMessage(I18n::get('success.create'), $this->_sRedirectURL);
      }
      else
        return Helper::errorMessage(I18n::get('error.sql'), $this->_sRedirectURL);
    }
  }

  /**
   * Delete a a comment.
   *
   * Override to set up correct redirect URL.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _destroy() {
    $this->_sRedirectURL = '/blogs/' . $this->_oModel->getParentId((int) $this->_aRequest['id']);

    return parent::_destroy();
  }
}