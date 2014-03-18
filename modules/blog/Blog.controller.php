<?php

/**
 * CRUD action of blog entries.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candybox\Module\Blog\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;

/**
 * Class Blog
 * @package candyCMS\Core\Controllers
 *
 */
class Blog extends \candyCMS\Core\Controllers\Main {

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

    if ($this->_iId) {
      $this->_aData = $this->_oModel->getId($this->_iId);

      # Entry does not exist or is unpublished
      if (!$this->_aData[1]['id'])
        return Helper::redirectTo('/errors/404');

      $this->oSmarty->assign('blog', $this->_aData);
    }

    else {
      # Get tags
      if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
        $this->_aData = isset($this->_aRequest['search']) && $this->_aRequest['search'] ?
                $this->_oModel->getOverviewByTag() :
                $this->_oModel->getOverview();

        if (isset($this->_aRequest['search']) && $this->_aRequest['search'])
          # Add RSS info
          $this->_aRSSInfo[] = array( 'url'   => WEBSITE_URL . '/blog/' . $this->_aRequest['search'] . '.rss',
                                      'title' => $this->_aRequest['search'] . ' - ' . I18n::get('global.blog'));

        # Limit to maximum pages
        if (isset($this->_aRequest['page']) && (int) $this->_aRequest['page'] > $this->_oModel->oPagination->getPages())
          return Helper::redirectTo('/errors/404');

        else {
          $this->oSmarty->assign('blog', $this->_aData);
          $this->oSmarty->assign('_pagination_', $this->_oModel->oPagination->showSurrounding());
        }
      }
    }

    $this->setDescription($this->_setBlogDescription());
    $this->setKeywords($this->_setBlogKeywords());
    $this->setTitle($this->_setBlogTitle());

    # Add RSS info
    $this->_aRSSInfo[] = array( 'url'   => WEBSITE_URL . '/blog.rss',
                                'title' => I18n::get('global.blog') );

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
          'title' => $this->_setBlogTitle(),
          'date'  => date('D, d M Y H:i:s O', time())
      ));
    }

    $this->setTitle($this->_setBlogTitle());

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
  private function _setBlogDescription() {
    if (isset($this->_aRequest['page']) && $this->_aRequest['page'] > 1)
      return I18n::get('global.blog') . ' - ' . I18n::get('global.page') . ' ' . (int) $this->_aRequest['page'];

    elseif ($this->_iId) {
      if (isset($this->_aData[1]['teaser']) && $this->_aData[1]['teaser'])
        return $this->_removeHighlight($this->_aData[1]['teaser']);

      elseif (isset($this->_aData[1]['title']))
        return $this->_removeHighlight($this->_aData[1]['title']);

      else
        return $this->_setBlogTitle();
    }
    else
      return I18n::get('global.blog');
  }

  /**
   * Return the blog meta keywords if they are set.
   *
   * @access private
   * @return string meta keywords
   *
   */
  private function _setBlogKeywords() {
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
  private function _setBlogTitle() {
    # Show overview by blog tag
    if (isset($this->_aRequest['search']) && $this->_aRequest['search'] !== 'page')
      return I18n::get('global.tag') . ': ' . $this->_aRequest['search'];

    # Default blog entry
    elseif ($this->_iId)
      return $this->_removeHighlight($this->_aData[1]['title']) . ' - ' . Helper::singleize(I18n::get('global.blog'));

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
