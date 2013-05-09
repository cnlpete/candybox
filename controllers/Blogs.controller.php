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

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

class Blogs extends Main {

  /**
   * Show blog entry or blog overview (depends on a given ID or not).
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'show');
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->setDescription($this->_setBlogsDescription());
    $this->setKeywords($this->_setBlogsKeywords());
    $this->setTitle($this->_setBlogsTitle());

    if ($this->_iId) {
      $this->_aData = $this->_oModel->getId($this->_iId);

      if (!$this->_aData[1]['id'])
        return Helper::redirectTo('/errors/404');

      # If comments are disabled
      if (!DISABLE_COMMENTS) {
        $sClass     = $this->__autoload('Comments');
        $oComments  = new $sClass($this->_aRequest, $this->_aSession);

        $oComments->__init();
        $oComments->_setParentData($this->_aData);

        $this->oSmarty->assign('_comments_', $oComments->show());
      }

      $this->oSmarty->assign('blogs', $this->_aData);

      # Bugfix: This is necessary, because comments also do a setDir on the singleton object.
      $this->oSmarty->setTemplateDir($oTemplate);
    }

    else {
      # Get tags
      if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
        $this->_aData = isset($this->_aRequest['search']) && $this->_aRequest['search'] ?
                $this->_oModel->getOverviewByTag() :
                $this->_oModel->getOverview();

        if (isset($this->_aRequest['search']) && $this->_aRequest['search'])
          # add rss info
          $this->_aRSSInfo[] = array(
                                  'url' => WEBSITE_URL . '/blogs/' . $this->_aRequest['search'] . '.rss',
                                  'title' => $this->_aRequest['search'] . ' - ' . I18n::get('global.blogs'));


        # Limit to maximum pages
        if (isset($this->_aRequest['page']) && (int) $this->_aRequest['page'] > $this->_oModel->oPagination->getPages())
          return Helper::redirectTo('/errors/404');

        else {
          $this->oSmarty->assign('blogs', $this->_aData);
          $this->oSmarty->assign('_pagination_', $this->_oModel->oPagination->showSurrounding());
        }
      }
    }

    # add rss info
    $this->_aRSSInfo[] = array(
                          'url' => WEBSITE_URL . '/blogs.rss',
                          'title' => I18n::get('global.blogs'));


    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Show blog as RSS.
   *
   * @access protected
   * @return string XML
   *
   */
  protected function _overviewRSS() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'overviewRSS');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $this->_aData = isset($this->_aRequest['search']) && $this->_aRequest['search'] ?
          $this->_oModel->getOverviewByTag() :
          $this->_oModel->getOverview();

      $this->oSmarty->assign('data', $this->_aData);
      $this->oSmarty->assign('_WEBSITE', array(
          'title' => $this->_setBlogsTitle(),
          'date'  => date('D, d M Y H:i:s O', time())
      ));
    }

    header('Content-Type: application/rss+xml');
    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
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
   * @param string $sTemplateName name of form template (only for E_STRICT)
   * @param string $sTitle title to show (only for E_STRICT)
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {
    # Get available languages.
    $this->oSmarty->assign('languages', Helper::getLanguages());
    $this->oSmarty->assign('_tags_', $this->_oModel->getTypeaheadData($this->_sController, 'tags', true));

    return parent::_showFormTemplate();
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

    return parent::_create();
  }

  /**
   * Update a blog entry.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _update() {
    $this->_setError('content');

    return parent::_update();
  }
}
