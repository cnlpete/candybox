<?php

/**
 * CRUD action of blog entries.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;

class Blogs extends Main {

  /**
   * Show blog entry or blog overview (depends on a given ID or not).
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sTemplateDir  = Helper::getTemplateDir($this->_aRequest['controller'], 'show');
    $sTemplateFile = Helper::getTemplateType($sTemplateDir, 'show');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if ($this->_iId) {
      $this->_aData = $this->_oModel->getId($this->_iId);

      if (!$this->_aData[1]['id'])
        return Helper::redirectTo('/errors/404');

      $sClass = $this->__autoload('Comments');
      $oComments = new $sClass($this->_aRequest, $this->_aSession);
      $oComments->__init($this->_aData);

      $this->oSmarty->assign('blogs', $this->_aData);
      $this->oSmarty->assign('_blog_footer_', $oComments->show());

      # Bugfix: This is necessary, because comments also do a setDir on the singleton object.
      $this->oSmarty->setTemplateDir($sTemplateDir);
    }

    else {
      if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
        $this->_aData = isset($this->_aRequest['search']) && $this->_aRequest['search'] ?
                $this->_oModel->getOverviewByTag() :
                $this->_oModel->getOverview();

        $this->oSmarty->assign('blogs', $this->_aData);
        $this->oSmarty->assign('_blog_footer_', $this->_oModel->oPagination->showSurrounding());
      }
    }

    $this->setDescription($this->_setBlogsDescription());
    $this->setKeywords($this->_setBlogsKeywords());
    $this->setTitle($this->_setBlogsTitle());

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show blog as RSS.
   *
   * @access protected
   * @return string XML (no real return, exits before)
   *
   */
  protected function _rss() {
    $sTemplateDir  = Helper::getTemplateDir($this->_aRequest['controller'], 'rss');
    $sTemplateFile = Helper::getTemplateType($sTemplateDir, 'rss');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('data', $this->_oModel->getOverview());

    exit($this->oSmarty->fetch($sTemplateFile, UNIQUE_ID));
  }

  /**
   * Return the blog meta description and remove highlighted text if needed.
   *
   * @access private
   * @return string meta description
   *
   */
  private function _setBlogsDescription() {
    if (isset($this->_aRequest['page']) && $this->_aRequest['page'] > 1)
      return I18n::get('global.blogs') . ' - ' . I18n::get('global.page') . ' ' . (int) $this->_aRequest['page'];

    elseif ($this->_iId) {
      if (isset($this->_aData[1]['teaser']) && $this->_aData[1]['teaser'])
        return $this->_removeHighlight($this->_aData[1]['teaser']);

      elseif (isset($this->_aData[1]['title']))
        return $this->_removeHighlight($this->_aData[1]['title']);

      else
        return $this->_setBlogsTitle();
    }
    else
      return I18n::get('global.blogs');
  }

  /**
   * Return the blog meta keywords if they are set.
   *
   * @access private
   * @return string meta keywords
   *
   */
  private function _setBlogsKeywords() {
    if ($this->_iId && isset($this->_aData[1]['tags']) && !empty($this->_aData[1]['tags']))
      return $this->_aData[1]['keywords'];
  }

  /**
   * Return the blog title.
   *
   * @access private
   * @return string title
   *
   */
  private function _setBlogsTitle() {
    # Show overview by blog tag
    if (isset($this->_aRequest['search']) && $this->_aRequest['search'] !== 'page')
      return I18n::get('global.tag') . ': ' . $this->_aRequest['search'];

    # Default blog entry
    elseif ($this->_iId)
      return $this->_removeHighlight($this->_aData[1]['title']) . ' - ' . Helper::singleize(I18n::get('global.blogs'));

    # Show overview with pages
    else {
      $iPage = isset($this->_aRequest['page']) ? (int) $this->_aRequest['page'] : 1;

      return $iPage > 1 ?
              Helper::singleize(I18n::get('global.blog')) . ' - ' . I18n::get('global.page') . ' ' . $iPage :
              Helper::singleize(I18n::get('global.blog'));
    }
  }

  /**
   * Build form template to create or update a blog entry.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showFormTemplate() {
    # Get available languages.
    $this->oSmarty->assign('languages', self::getLanguages());
    $this->oSmarty->assign('_tags_', $this->_oModel->getTypeaheadData($this->_sController, 'tags', true));

    return parent::_showFormTemplate();
  }

  /**
   * Get languages from app/languages folder.
   *
   * @static
   * @access public
   * @return array $aLanguages array with our languages
   * @todo test cases
   *
   */
  public static function getLanguages() {
    $aLanguages = array();
    $oPathDir = opendir(PATH_STANDARD . '/app/languages');

    while ($sFile = readdir($oPathDir)) {
      # Skip extra german languages.
      if (substr($sFile, 0, 1) == '.' || substr($sFile, 0, 3) == 'de_')
        continue;

      array_push($aLanguages, substr($sFile, 0, 2));
    }

    closedir($oPathDir);

    return $aLanguages;
  }

  /**
   * Create a blog entry.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('content');

    return parent::_create(array('searches', 'rss', 'sitemaps'));
  }

  /**
   * Update a blog entry.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _update() {
    $this->_setError('content');

    return parent::_update(array('searches', 'rss', 'sitemaps'));
  }

  /**
   * Destroy a blog entry.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _destroy() {
    return parent::_destroy(array('searches', 'rss', 'sitemaps'));
  }
}