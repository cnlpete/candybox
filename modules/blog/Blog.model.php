<?php

/**
 * Handle all blog SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candybox\Module\Blog\Models;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\Pagination;
use PDO;

/**
 * Class Blog
 * @package candyCMS\Core\Models
 *
 */
class Blog extends \candyCMS\Core\Models\Main {

  /**
   * Get count of visible blog entries.
   *
   * @access public
   * @return integer the total count
   *
   */
  public function getCount() {
    # Show unpublished items and entries with diffent languages to moderators or administrators only
    $sWhere = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ?
            'WHERE 1' :
            "WHERE published = '1' AND (language = '" . WEBSITE_LANGUAGE . "' OR language = '')";

    try {
      $oQuery = $this->_oDb->query("SELECT COUNT(*) FROM " . SQL_PREFIX . "blog " . $sWhere);
      return (int) $oQuery->fetchColumn();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Get count of visible blog entries by tags.
   *
   * @access public
   * @param string $sTagname the tagname
   * @return integer the total count
   *
   */
  public function getCountForTag($sTagname) {
    # Show unpublished items and entries with diffent languages to moderators or administrators only
    $sWhere = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ?
            'WHERE 1' :
            "WHERE published = '1' AND (language = '" . WEBSITE_LANGUAGE . "' OR language = '')";

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        COUNT(*)
                                      FROM
                                        " . SQL_PREFIX . "blog
                                      " . $sWhere . "
                                      AND (tags LIKE :commaTagnameComma
                                        OR tags LIKE :commaTagname
                                        OR tags LIKE :tagnameComma
                                        OR tags    = :tagname
                                        OR tags LIKE :commaSpaceTagname
                                        OR tags LIKE :commaSpaceTagnameComma)");

      $oQuery->bindValue(':commaTagnameComma', '%,' . $sTagname . ',%', PDO::PARAM_STR);
      $oQuery->bindValue(':commaTagname', '%,' . $sTagname, PDO::PARAM_STR);
      $oQuery->bindValue(':tagnameComma', $sTagname . ',%', PDO::PARAM_STR);
      $oQuery->bindValue(':tagname', $sTagname, PDO::PARAM_STR);

      # These last two lines are for compatibility, since there might be blog-entries done before 2.1 with a space
      $oQuery->bindValue(':commaSpaceTagname', '%, ' . $sTagname, PDO::PARAM_STR);
      $oQuery->bindValue(':commaSpaceTagnameComma', '%, ' . $sTagname . ',%', PDO::PARAM_STR);
      $oQuery->execute();

      return (int) $oQuery->fetchColumn();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Get blog overview data by tagname.
   *
   * @access public
   * @param integer $iLimit blog post limit, 0 for infinite
   * @param string $sTagname the tagname to query for.
   * @return array data from _setData
   *
   */
  public function getOverviewByTag($iLimit = LIMIT_BLOG, $sTagname = '') {
    # Set limit to 2 to make sure we have some pages to test in test mode.
    # Since we test the limit function we also set limit to 1.
    # todo: set LIMIT_BLOGS to 2 in TestBlogs instead
    if (ACTIVE_TEST && $iLimit != 1)
      $iLimit = 2;

    if (empty($sTagname)) {
      # Remove all characters that might harm us, only allow digits, normal letters and whitespaces
      $sTagname = trim(preg_replace('/[^\d\s\w]/', '',
              str_replace('%20', ' ', Helper::formatInput($this->_aRequest['search'], false))
      ));
    }

    $iResult = $this->getCountForTag($sTagname);

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Pagination.helper.php';
    $this->oPagination = new Pagination($this->_aRequest, (int) $iResult, $iLimit != 0 ? $iLimit : $iResult);

    try {
      # Show unpublished items and entries with diffent languages to moderators or administrators only
      $sWhere = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ?
              'WHERE 1' :
              "WHERE published = '1' AND (language = '" . WEBSITE_LANGUAGE . "' OR language = '')";

      $sLimit = $iLimit != 0 ?
              ' LIMIT ' . $this->oPagination->getOffset() . ', ' . $this->oPagination->getLimit() :
              '';

      $oQuery = $this->_oDb->prepare("SELECT
                                        b.*,
                                        b.priority as sticky
                                        UNIX_TIMESTAMP(b.date) as date,
                                        UNIX_TIMESTAMP(b.date_modified) as date_modified,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email,
                                        u.use_gravatar
                                      FROM
                                        " . SQL_PREFIX . "blog b
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        b.author_id=u.id
                                      " . $sWhere . "
                                      AND (tags LIKE :commaTagnameComma
                                        OR tags LIKE :commaTagname
                                        OR tags LIKE :tagnameComma
                                        OR tags   =  :tagname
                                        OR tags LIKE :commaSpaceTagname
                                        OR tags LIKE :commaSpaceTagnameComma)
                                      GROUP BY
                                        b.id
                                      ORDER BY
                                        b.priority DESC,
                                        b.date DESC" . $sLimit);

      $oQuery->bindValue(':commaTagnameComma', '%,' . $sTagname . ',%', PDO::PARAM_STR);
      $oQuery->bindValue(':commaTagname', '%,' . $sTagname, PDO::PARAM_STR);
      $oQuery->bindValue(':tagnameComma', $sTagname . ',%', PDO::PARAM_STR);
      $oQuery->bindValue(':tagname', $sTagname, PDO::PARAM_STR);

      # These last two lines are for compatibility, since there might be blog-entries done before 2.1 with a space
      $oQuery->bindValue(':commaSpaceTagname', '%, ' . $sTagname, PDO::PARAM_STR);
      $oQuery->bindValue(':commaSpaceTagnameComma', '%, ' . $sTagname . ',%', PDO::PARAM_STR);
      $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    foreach ($aResult as $aRow) {
      # We use the date as identifier to give plugins the possibility to patch into the system.
      $iDate = $aRow['date'];

      # We need to specify 'blogs' because this might also be called for rss
      $aData[$iDate] = $this->_formatForOutput(
              $aRow,
              array('id', 'uid', 'author_id', 'date', 'date_modified'),
              array('sticky', 'published', 'use_gravatar'),
              'blog'
      );

      # Bugfix: Make tags compatible to candyCMS Version 1.x
      $aData[$iDate]['tags_raw'] = $aRow['tags'];

      # Explode using ',' and filter empty items (since explode always gives at least one item)
      $aData[$iDate]['tags'] = array_filter(array_map('trim', explode(',', $aRow['tags'])));
      $this->_formatDates($aData[$iDate], 'date_modified');
    }

    return $aData;
  }

  /**
   * Get blog overview data.
   *
   * @access public
   * @param integer $iLimit blog post limit, 0 for infinite
   * @param boolean $bMultiLang show all entries, no matter what language we have selected
   * @return array data from _setData
   *
   */
  public function getOverview($iLimit = LIMIT_BLOG, $bMultiLang = false) {
    # Set limit to 2 to make sure we have some pages to test in test mode
    # Since we test the limit function we also set limit to 1.
    if (ACTIVE_TEST && $iLimit != 1)
      $iLimit = 2;

    $iResult = $this->getCount();
    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Pagination.helper.php';
    $this->oPagination = new Pagination($this->_aRequest, (int) $iResult, $iLimit != 0 ? $iLimit : $iResult);

    try {
      if ($bMultiLang)
        $sWhere = "WHERE published = '1'";

      # Show unpublished items and entries with diffent languages to moderators or administrators only
      elseif (isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3)
        $sWhere = '';

      else
        $sWhere = "WHERE published = '1' AND (language = '" . WEBSITE_LANGUAGE . "' OR language = '')";

      $sLimit = $iLimit != 0 ?
              ' LIMIT ' . $this->oPagination->getOffset() . ', ' . $this->oPagination->getLimit() :
              '';

      $oQuery = $this->_oDb->prepare("SELECT
                                        b.*,
                                        b.priority as sticky,
                                        UNIX_TIMESTAMP(b.date) as date,
                                        UNIX_TIMESTAMP(b.date_modified) as date_modified,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email,
                                        u.use_gravatar
                                      FROM
                                        " . SQL_PREFIX . "blog b
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        b.author_id=u.id
                                      " . $sWhere . "
                                      GROUP BY
                                        b.id
                                      ORDER BY
                                        b.priority DESC,
                                        b.date DESC" . $sLimit);

      $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    foreach ($aResult as $aRow) {
      # We use the date as identifier to give plugins the possibility to patch into the system.
      $iDate = $aRow['date'];

      # We need to specify 'blogs' because this might also be called for RSS
      $aData[$iDate] = $this->_formatForOutput(
              $aRow,
              array('id', 'uid', 'author_id', 'date', 'date_modified'),
              array('sticky', 'published', 'use_gravatar'),
              'blog'
      );

      # Bugfix: Make tags compatible to candyCMS Version 1.x
      $aData[$iDate]['tags_raw'] = $aRow['tags'];

      # Explode using ',' and filter empty items (since explode always gives at least one item)
      $aData[$iDate]['tags'] = array_filter(
              array_map('trim', explode(',', $aRow['tags']))
      );

      $this->_formatDates($aData[$iDate], 'date_modified');
    }

    return $aData;
  }

  /**
   * Get blog entry data.
   *
   * @access public
   * @param integer $iId Id to work with
   * @param boolean $bUpdate prepare data for update?
   * @return array|boolean array on success, boolean on false
   *
   */
  public function getId($iId, $bUpdate = false) {
    if (empty($iId) || $iId < 1)
      return false;

    # Show unpublished items to moderators or administrators only
    $iPublished = $this->_aSession['user']['role'] >= 3 ? 0 : 1;

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        b.*,
                                        b.priority as sticky,
                                        UNIX_TIMESTAMP(b.date) as date,
                                        UNIX_TIMESTAMP(b.date_modified) as date_modified,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email,
                                        u.use_gravatar
                                      FROM
                                        " . SQL_PREFIX . "blog b
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        b.author_id=u.id
                                      WHERE
                                        b.id = :id
                                      AND
                                        b.published >= :published
                                      LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $bReturn = $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    if (!$bReturn)
      return false;

    elseif ($bUpdate)
      $aData = $this->_formatForUpdate($aRow);

    else {
      $aData[1] = $this->_formatForOutput(
              $aRow,
              array('id', 'uid', 'author_id', 'date', 'date_modified'),
              array('sticky', 'published', 'use_gravatar')
      );

      $aData[1]['tags_raw']  = $aRow['tags'];
      $aData[1]['tags']      = array_filter(
              array_map('trim', explode(',', $aRow['tags']))
      );

      $this->_formatDates($aData[1], 'date_modified');
    }

    return $aData;
  }

  /**
   * Create a blog entry.
   *
   * @access public
   * @param array $aOptions (only for E_STRICT)
   * @return boolean status of query
   *
   */
  public function create($aOptions = array()) {
    $iPublished = isset($this->_aRequest[$this->_sController]['published']) &&
            $this->_aRequest[$this->_sController]['published'] == true ?
            1 :
            0;

    $iSticky = isset($this->_aRequest[$this->_sController]['sticky']) &&
            $this->_aRequest[$this->_sController]['sticky'] == true ?
            1 :
            0;

    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "blog
                                        ( author_id,
                                          title,
                                          tags,
                                          teaser,
                                          keywords,
                                          content,
                                          language,
                                          date,
                                          priority,
                                          published)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :tags,
                                          :teaser,
                                          :keywords,
                                          :content,
                                          :language,
                                          NOW(),
                                          :sticky,
                                          :published )");

      $sTags = Helper::formatInput(
              implode(',', array_filter(array_map('trim', explode(',', $this->_aRequest[$this->_sController]['tags']))))
      );

      $oQuery->bindParam('tags', $sTags, PDO::PARAM_STR);
      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $oQuery->bindParam('sticky', $iSticky, PDO::PARAM_INT);

      foreach (array('title', 'teaser', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
        $oQuery->bindParam(
                $sInput, $sValue, PDO::PARAM_STR);

        unset($sValue);
      }

      foreach (array('keywords', 'language') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput, $sValue, PDO::PARAM_STR);

        unset($sValue);
      }

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = parent::$_oDbStatic->lastInsertId();

      return $bReturn;
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Update a blog entry.
   *
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   *
   */
  public function update($iId) {
    if (empty($iId) || $iId < 1)
      return false;

    $sDateModified = isset($this->_aRequest[$this->_sController]['show_update']) &&
            $this->_aRequest[$this->_sController]['show_update'] == true ?
            date('Y-m-d H:i:s') :
            '0000-00-00 00:00:00';

    $iUpdateAuthor = isset($this->_aRequest[$this->_sController]['show_update']) &&
            $this->_aRequest[$this->_sController]['show_update'] == true ?
            $this->_aSession['user']['id'] :
            (int) $this->_aRequest[$this->_sController]['author_id'];

    $iPublished = isset($this->_aRequest[$this->_sController]['published']) &&
            $this->_aRequest[$this->_sController]['published'] == true ?
            1 :
            0;

    $iSticky = isset($this->_aRequest[$this->_sController]['sticky']) &&
            $this->_aRequest[$this->_sController]['sticky'] == true ?
            1 :
            0;

    $iDate = isset($this->_aRequest[$this->_sController]['update_date']) &&
            $this->_aRequest[$this->_sController]['update_date'] == true ?
            time() :
            (int) $this->_aRequest[$this->_sController]['date'];

    $sDate = date('Y-m-d H:i:s', $iDate);

    $sTags = Helper::formatInput(
            implode(',', array_filter(array_map('trim', explode(',', $this->_aRequest[$this->_sController]['tags']))))
    );

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "blog
                                      SET
                                        author_id = :author_id,
                                        title = :title,
                                        tags = :tags,
                                        teaser = :teaser,
                                        keywords = :keywords,
                                        content = :content,
                                        language = :language,
                                        date = :date,
                                        date_modified = :date_modified,
                                        priority = :sticky,
                                        published = :published
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('tags', $sTags, PDO::PARAM_STR);
      $oQuery->bindParam('author_id', $iUpdateAuthor, PDO::PARAM_INT);
      $oQuery->bindParam('date', $sDate, PDO::PARAM_STR);
      $oQuery->bindParam('date_modified', $sDateModified, PDO::PARAM_STR);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $oQuery->bindParam('sticky', $iSticky, PDO::PARAM_INT);
      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      foreach (array('title', 'teaser', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
        $oQuery->bindParam(
                $sInput, $sValue, PDO::PARAM_STR);

        unset($sValue);
      }

      foreach (array('keywords', 'language') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput, $sValue, PDO::PARAM_STR);

        unset($sValue);
      }

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Delete a blog entry and also delete its comments.
   *
   * @access public
   * @param integer $iId ID to destroy
   * @param string $sController controller to use, obsolete and only for not giving E_STRICT warnings
   * @return boolean status of query
   *
   */
  public function destroy($iId, $sController = '') {
    return parent::destroy($iId);
  }

  /**
   * Get blog overview data. Limit by date
   *
   * @access public
   * @param integer $iLimit how many month should the result set be limited to?
   * @return array data from _setData
   *
   */
  public function getOverviewByMonth($iLimit = 12) {
    try {
      # Show unpublished items and entries with diffent languages to moderators or administrators only
      $sWhere = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ?
              '' :
              "AND published = '1' AND language = '" . WEBSITE_LANGUAGE . "'";

      $oQuery = $this->_oDb->prepare("SELECT
                                        b.*,
                                        UNIX_TIMESTAMP(b.date) as date,
                                        DATE_FORMAT(b.date,'%Y') as year,
                                        DATE_FORMAT(b.date,'%m') as month,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "blog b
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        b.author_id=u.id
                                      WHERE
                                        b.date >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                                        " . $sWhere . "
                                      ORDER BY
                                        b.date DESC");

      $oQuery->bindParam('months', $iLimit, PDO::PARAM_INT);
      $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    foreach ($aResult as $aRow) {
      # We use the date as identifier to give plugins the possibility to patch into the system.
      $iDate = $aRow['date'];

      # We need to specify 'blogs' because this might also be called for rss
      $aData[$iDate] = $this->_formatForOutput(
              $aRow,
              array('id', 'uid', 'author_id', 'date'),
              array('sticky', 'published', 'use_gravatar'),
              'blog'
      );
    }

    return isset($aData) ? $aData : array();
  }
}
