<?php

/**
 * Handle comment SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Models;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\Pagination;
use candyCMS\Core\Helpers\PluginManager;
use PDO;

class Comments extends Main {

  /**
   * Get comment data.
   *
   * @access public
   * @param integer $iId blog ID to load data from
   * @param integer $iEntries number of comments for this blog ID
   * @param integer $iLimit comment limit, -1 is infinite
   * @return array data from _setData
   *
   */
  public function getOverview($iId, $iEntries, $iLimit) {
		require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Pagination.helper.php';
    $this->oPagination = new Pagination($this->_aRequest, $iEntries, $iLimit);

    try {
      $sOrder = defined('SORTING_COMMENTS') && (SORTING_COMMENTS == 'ASC' || SORTING_COMMENTS == 'DESC') ?
              SORTING_COMMENTS :
              'ASC';
      $sLimit = $iLimit === -1 ? '' : 'LIMIT ' . $this->oPagination->getOffset() . ', ' . $this->oPagination->getLimit();

      $oQuery = $this->_oDb->prepare("SELECT
                                        c.*,
                                        UNIX_TIMESTAMP(c.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email,
                                        u.use_gravatar
                                      FROM
                                        " . SQL_PREFIX . "comments c
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        u.id=c.author_id
                                      WHERE
                                        c.parent_id = :parent_id
                                      ORDER BY
                                        c.date " . $sOrder . ",
                                        c.id " . $sOrder . "
                                      " . $sLimit);

      $oQuery->bindParam('parent_id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];

      $this->_aData[$iId] = $aRow;

      $this->_formatForOutput($this->_aData[$iId],
              array('id', 'parent_id', 'author_id', 'author_facebook_id', 'user_id'),
              array('use_gravatar'), 'comments');

      $this->_aData[$iId]['url']     = '/' . $this->_sController . '/' . $iId . '#' . $aRow['id'];
      $this->_aData[$iId]['content'] = nl2br($this->_aData[$aRow['id']]['content']);
    }

    # We crawl the facebook avatars
    $oPluginManager = PluginManager::getInstance();
    if ($oPluginManager->hasSessionPlugin()) {
      $aIds = array();
      foreach ($aResult as $aRow) {

        # Skip unnecessary data
        if (empty($aRow['author_facebook_id']))
          continue;

        else
          $aIds[(int)$aRow['id']] = $aRow['author_facebook_id'];
      }
      if (count($aIds) > 0)
        $oPluginManager->getSessionPlugin()->setAvatars($aIds, $this->_aData);
    }

    # Get comment number
    $iLoop = 1;
    foreach ($this->_aData as $aData) {
      $iId = $aData['id'];
      $this->_aData[$iId]['loop'] = $iLoop;
      ++$iLoop;
    }

    return $this->_aData;
  }

  /**
   * Create a comment.
   *
   * @access public
   * @return boolean status of query
   *
   */
  public function create() {
    $sAuthorName = isset($this->_aRequest[$this->_sController]['name']) ?
            Helper::formatInput($this->_aRequest[$this->_sController]['name']) :
            $this->_aSession['user']['full_name'];

    $sAuthorEmail = isset($this->_aRequest[$this->_sController]['email']) ?
            Helper::formatInput($this->_aRequest[$this->_sController]['email']) :
            $this->_aSession['user']['email'];

    $iFacebookId = $this->_aSession['user']['facebook_id'];

    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "comments
                                        ( author_id,
                                          author_facebook_id,
                                          author_name,
                                          author_email,
                                          author_ip,
                                          content,
                                          date,
                                          parent_id)
                                      VALUES
                                        ( :author_id,
                                          :author_facebook_id,
                                          :author_name,
                                          :author_email,
                                          :author_ip,
                                          :content,
                                          NOW(),
                                          :parent_id )");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('author_facebook_id', $iFacebookId, PDO::PARAM_INT);
      $oQuery->bindParam('author_name', $sAuthorName, PDO::PARAM_STR);
      $oQuery->bindParam('author_email', $sAuthorEmail, PDO::PARAM_STR);
      $oQuery->bindParam('author_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
      $oQuery->bindParam('content', Helper::formatInput($this->_aRequest[$this->_sController]['content']), PDO::PARAM_STR);
      $oQuery->bindParam('parent_id', $this->_aRequest[$this->_sController]['parent_id'], PDO::PARAM_INT);

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = parent::$_oDbStatic->lastInsertId();

      return $bReturn;
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }

      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Return the parent ID of a comment.
   *
   * @static
   * @access public
   * @param integer $iId comment ID to get data from
   * @return integer $aResult['parent_id']
   *
   */
  public static function getParentId($iId) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                parent_id
                                              FROM
                                                " . SQL_PREFIX . "comments
                                              WHERE
                                                id = :id
                                              LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
      return (int) $aResult['parent_id'];
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Delete a comment.
   *
   * @static
   * @access public
   * @param integer $iId ID to delete
   * @param string $sController controller to use, obsolete and only for not giving E_STRICT warnings
   * @return boolean status of query
   *
   */
  public function destroy($iId, $sController = '') {
    return parent::destroy($iId, 'comments');
  }
}
