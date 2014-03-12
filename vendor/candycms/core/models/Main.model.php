<?php

/**
 * Parent class for most other models. Handles also DB insertations.
 *
 * @abstract
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.5
 *
 */

namespace candyCMS\Core\Models;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\AdvancedException;
use PDO;

/**
 * Class Main
 * @package candyCMS\Core\Models
 *
 */
abstract class Main {

  /**
   * Alias for $_REQUEST
   *
   * @var array
   * @access protected
   *
   */
  protected $_aRequest = array();

  /**
   * Alias for $_SESSION
   *
   * @var array
   * @access protected
   *
   */
  protected $_aSession = array();

  /**
   * ID to process.
   *
   * @var integer
   * @access protected
   *
   */
  protected $_iId;

  /**
   * Name of the current controller.
   *
   * @var string
   * @access protected
   */
  protected $_sController;

  /**
   * PDO object.
   *
   * @var object
   * @access protected
   *
   */
  protected $_oDb;

  /**
   * Page object.
   *
   * @var object
   * @access public
   *
   */
  public $oPagination;

  /**
   * Database connection.
   *
   * @var object
   * @access protected
   *
   */
  static $_oDbStatic;

  /**
   * Return ID of last inserted data.
   *
   * @var integer
   * @access public
   *
   */
  static $iLastInsertId;

  /**
   * Initialize the model by adding input params, set default id connect to database.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param array $aFile alias for $_FILE
   *
   */
  public function __construct(&$aRequest = '', &$aSession = '', &$aFile = '') {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;
    $this->_aFile     = & $aFile;

    $this->_iId = isset($this->_aRequest['id']) && !isset($this->_iId) ? (int) $this->_aRequest['id'] : '';
    $this->_oDb = $this->connectToDatabase();
    $this->_sController = isset($this->_aRequest['controller']) ?
            (string) $this->_aRequest['controller'] :
            '';
  }

  /**
   * Tear down actions.
   *
   * Not unsetting the database, because it is unset by Index.controller.php.
   *
   * @access public
   * @return null
   *
   */
  public function __destruct() {
    # Close all DB connections
    #$this->_oDb = null;
  }

  /**
   * Get a Singleton database PDO Object.
   *
   * @static
   * @access public
   * @return object PDO
   *
   */
  public static function connectToDatabase() {
    if (empty(self::$_oDbStatic)) {
      try {
        $sSQLType   = strtolower(SQL_TYPE);
        $sDatabase  = SQL_SINGLE_DB_MODE === true ?
                SQL_DB :
                SQL_DB . '_' . WEBSITE_MODE;

        self::$_oDbStatic = new PDO($sSQLType . ':host=' . SQL_HOST . ';port=' . SQL_PORT . ';dbname=' . $sDatabase,
                        SQL_USER,
                        SQL_PASSWORD,
                        array(PDO::ATTR_PERSISTENT => true));

        self::$_oDbStatic->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
      catch (PDOException $p) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      }
    }

    return self::$_oDbStatic;
  }

  /**
   * Disconnect from database.
   *
   * @static
   * @access public
   * @return boolean
   *
   */
  public static function disconnectFromDatabase() {
    return self::$_oDbStatic = null;
  }

  /**
   * Remove slashes from content for update purposes.
   *
   * @static
   * @access protected
   * @param array $aRow array with data to update
   * @return array $aData data witout slashes
   *
   */
  protected static function _formatForUpdate(&$aRow) {
    foreach ($aRow as $sColumn => $sData) {
      # Bugfix: Avoid TinyMCE problems.
      # @todo Still in use?
      $sData = str_replace('\"', '', $sData);
      $sData = str_replace('\&quot;', '', $sData);

      if (empty($sData))
        continue;

      $aRow[$sColumn] = htmlentities($sData);
    }

    return $aRow;
  }

  /**
   * Format necessary datetime stamps and add them to $aData
   *
   * @static
   * @access protected
   * @param array $aData array with the timestamp stored in '$sKey'
   * @param string $sKey the key, where the date is stored in $aData
   * @return array reference to $aData
   * @todo move to helper
   *
   */
  protected static function _formatDates(&$aData, $sKey = 'date') {
    if (isset($aData[$sKey])) {
      $iTimeStamp = preg_match('/-/', $aData[$sKey]) ? strtotime($aData[$sKey]) : (int) $aData[$sKey];

      $aDateData = Array(
        'raw'       => $iTimeStamp,
        'rss'       => date('D, d M Y H:i:s O', $iTimeStamp),
        'w3c'       => date('Y-m-d\TH:i:sP', $iTimeStamp),
        'w3c_date'  => date('Y-m-d', $iTimeStamp),
      );

      $aData[$sKey] = $aDateData;
    }

    return $aData;
  }

  /**
   * Format data correctly.
   *
   * @access protected
   * @param array $aData array with data to format
   * @param array $aInts identifiers, which should be cast to int
   * @param array $aBools identifiers, which should be cast to bool
   * @param string $sController name of the controller we are working in
   * @return array $aData rebuild data
   *
   */
  protected function _formatForOutput(&$aData, $aInts = array('id'), $aBools = null, $sController = '') {
    $sController = !$sController ? $this->_sController : $sController;

    # Do we need to highlight text?
    $sHighlight = isset($this->_aRequest['highlight']) ? $this->_aRequest['highlight'] : '';

    foreach (array('content', 'teaser', 'title') as $sColumn)
      if (isset($aData[$sColumn]))
        $aData[$sColumn] = Helper::formatOutput($aData[$sColumn], $sHighlight, $sColumn == 'content');

    # Bugfix: Set types
    if ($aInts)
      foreach ($aInts as $sIdent)
        if (isset($aData[$sIdent]))
          $aData[$sIdent] = (int) $aData[$sIdent];

    if ($aBools)
      foreach ($aBools as $sIdent)
        if (isset($aData[$sIdent]))
          $aData[$sIdent] = (bool) $aData[$sIdent];

    # Format data
    self::_formatDates($aData);

    # Generate sitemap data
    # TODO check whether this needs to be done on every page, proposal: only do this in SiteMapController
    Helper::generateSitemapData($aData);

    # Normal user
    if (isset($aData['user_id']) && $aData['user_id'] != 0) {
      $aUserData = array(
          'id'            => $aData['user_id'],
          'use_gravatar'  => isset($aData['use_gravatar']) ? (bool) $aData['use_gravatar'] : false,
          'name'          => $aData['user_name'],
          'surname'       => $aData['user_surname'],
          'facebook_id'   => isset($aData['author_facebook_id']) ? $aData['author_facebook_id'] : ''
      );

      if ($this->_aSession['user']['role'] >= 3) {
        $aUserData['email'] = $aData['user_email'];
        $aUserData['ip']    = isset($aData['author_ip']) ? $aData['author_ip'] : '';
      }
    }

    $aData['author'] = self::_formatForUserOutput($aUserData);

    # Encode data for SEO
    $aData['title_encoded'] = isset($aData['title']) ? urlencode($aData['title']) : $aData['author']['full_name_encoded'];

    # URL to entry
    $aData['url_clean']   = WEBSITE_URL . '/' . $sController . '/' . $aData['id'];
    $aData['url']         = $aData['url_clean'] . '/' . str_replace("%2F", '+', $aData['title_encoded']);
    $aData['url_encoded'] = urlencode($aData['url']); #SEO

    $aData['url_destroy']       = $aData['url_clean'] . '/destroy';
    $aData['url_update']        = $aData['url_clean'] . '/update';

    # Destroy redundant data
    unset(  $aData['user_id'],
            $aData['user_name'],
            $aData['user_surname'],
            $aData['user_email'],
            $aData['use_gravatar'],
            $aData['author_name'],
            $aData['author_email'],
            $aData['author_ip']);

    return $aData;
  }

  /**
   * Formats / adds all relevant Information for displaying a user.
   *
   * @access protected
   * @param array $aData array of given userdata, required fields are 'email', 'id', 'name', 'surname' and 'use_gravatar'
   * @param boolean $bDestroySensitiveData should we provide sensitve data or not?
   * @return array $aData returns reference of $aData
   *
   */
  protected static function _formatForUserOutput(&$aData, $bDestroySensitiveData = true) {
    # Set up ints first
    $aData['id']    = (int) $aData['id'];
    $aData['role']  = (int) isset($aData['role']) ? $aData['role'] : 0;

    # Format user registration dates
    self::_formatDates($aData);

    # Create avatars
    Helper::createAvatarURLs(
            $aData,
            $aData['id'],
            isset($aData['email']) ? $aData['email'] : WEBSITE_MAIL,
            isset($aData['use_gravatar']) ? (bool) $aData['use_gravatar'] : false
    );

    # Build full user name
    $aData['name']      = isset($aData['name']) ? (string) $aData['name'] : '';
    $aData['surname']   = isset($aData['surname']) ? (string) $aData['surname'] : '';
    $aData['full_name'] = trim($aData['name'] . ' ' . $aData['surname']);
    $aData['full_name_encoded'] = urlencode($aData['full_name']);

    # URL to entry
    $aData['url_clean']   = WEBSITE_URL . '/users/' . $aData['id'];
    $aData['url']         = $aData['url_clean'] . '/' . $aData['full_name_encoded'];
    $aData['url_encoded'] = urlencode($aData['url']);
    $aData['url_destroy'] = $aData['url_clean'] . '/destroy';
    $aData['url_update']  = $aData['url_clean'] . '/update';

    # Destroy sensitive data
    if ($bDestroySensitiveData === true) {
      $aData['verification_code'] = isset($aData['verification_code']) && empty($aData['verification_code']) ?
              0 :
              1;

      unset(  $aData['api_token'],
              $aData['registration_ip'],
              $aData['password'],
              $aData['password_temporary']);
    }

    return $aData;
  }

  /**
   * Return last inserted ID.
   *
   * @static
   * @access public
   * @return integer self::$iLastInsertId last inserted ID.
   *
   */
  public static function getLastInsertId() {
    return (int) self::$iLastInsertId;
  }

  /**
   * Return data for autocompletion.
   *
   * @static
   * @access public
   * @param string $sTable table to get data from
   * @param string $sColumn column to get data from
   * @param boolean $bSplit split data by comma
   * @return string formatted data
   *
   */
  public static function getTypeaheadData($sTable, $sColumn, $bSplit = false) {
    try {
      $oQuery = self::$_oDbStatic->query("SELECT
                                            " . $sColumn . "
                                          FROM
                                            " . SQL_PREFIX . $sTable . "
                                          GROUP BY
                                            " . $sColumn);

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);

      $aEntries = array();
      foreach ($aResult as $aRow) {
        if ($bSplit) {
          $aItems = array_filter(array_map('trim', explode(',', $aRow[$sColumn])));

          foreach ($aItems as $sItem)
            if (!in_array($sItem, $aEntries))
              $aEntries[] = $sItem;
        }

        else
          $aEntries[] = $aRow[$sColumn];
      }

      return json_encode($aEntries);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        self::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Dynamically load models.
   *
   * @static
   * @access public
   * @param string $sClass name of model to load
   * @return string model name
   *
   */
  public static function __autoload($sClass) {
    $sClass = (string) ucfirst(strtolower($sClass));

    if (EXTENSION_CHECK && file_exists(PATH_STANDARD . '/app/models/' . $sClass . '.model.php')) {
      require_once PATH_STANDARD . '/app/models/' . $sClass . '.model.php';
      return '\candyCMS\Models\\' . $sClass;
    }
    elseif (file_exists(PATH_STANDARD . '/vendor/candycms/core/models/' . $sClass . '.model.php')) {
      require_once PATH_STANDARD . '/vendor/candycms/core/models/' . $sClass . '.model.php';
      return '\candyCMS\Core\Models\\' . $sClass;
    }
  }

  /**
   * Create an entry.
   * This is just a fallback method if a model has no create method.
   *
   * @access public
   * @param array $aOptions options to handle
   * @return boolean false
   *
   */
  public function create($aOptions) {
    return false;
  }

  /**
   * Update an entry.
   * This is just a fallback method if a model has no update method.
   *
   * @access public
   * @param integer $iId ID to update
   * @return boolean false
   *
   */
  public function update($iId) {
    return false;
  }

  /**
   * Destroy an entry.
   *
   * @access public
   * @param integer $iId ID to destroy
   * @param string $sController controller to use
   * @return array|boolean array on success, boolean on false
   *
   */
  public function destroy($iId, $sController = '') {
    if (empty($iId) || $iId < 1)
      return false;

    else {
      $sController = $sController ? (string) $sController : (string) $this->_sController;

      if (empty($sController))
        return false;

      try {
        $oQuery = $this->_oDb->prepare("DELETE FROM
                                          " . SQL_PREFIX . $sController . "
                                        WHERE
                                          id = :id
                                        LIMIT
                                          1");

        $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
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
  }

  /**
   * Get search information.
   *
   * @access public
   * @param string $sSearch query string to search
   * @param string $sController controller to use
   * @param string $sOrderBy how to order search
   * @return array $aData search data
   *
   */
  public function search($sSearch, $sController = '', $sOrderBy = 't.date DESC') {
    $sController = $sController ? (string) $sController : (string) $this->_sController;

    try {
      $this->oQuery = $this->_oDb->prepare("SELECT
                                              t.*,
                                              UNIX_TIMESTAMP(t.date) as date,
                                              u.id as user_id,
                                              u.name as user_name,
                                              u.surname as user_surname,
                                              u.email as user_email
                                            FROM
                                              " . SQL_PREFIX . $sController . " t
                                            JOIN
                                              " . SQL_PREFIX . "users u
                                            ON
                                              u.id = t.author_id
                                            WHERE
                                              t.title LIKE :searchString
                                            OR
                                              t.content LIKE :searchString
                                            ORDER BY
                                              " . (string) $sOrderBy);

      $this->oQuery->bindValue('searchString', '%' . $sSearch . '%', PDO::PARAM_STR);
      $this->oQuery->execute();
      $aResult = $this->oQuery->fetchAll(PDO::FETCH_ASSOC);

      # Build table names and order them
      $aData['controller'] = $sController;
      $aData['title'] = I18n::get('global.' . strtolower($sController));

      $iEntries = 0;
      foreach ($aResult as $aRow) {
        if (isset($aRow['published']) && $aRow['published'] == 0)
          continue;

        $iDate = $aRow['date'];
        $aData[$iDate] = $this->_formatForOutput(
                $aRow,
                array('id', 'author_id'),
                null,
                $sController);

        ++$iEntries;
      }

      $aData['entries'] = $iEntries;
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    return isset($aData) ? $aData : array();
  }
}