<?php

/*
 * This software is licensed under GPL <http://www.gnu.org/licenses/gpl.html>.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

abstract class Main {
  protected $_aRequest;
  protected $_aSession;
  protected $_aFile;
  protected $_iId;
  protected $_aError;
  private $_aData = array();
  private $_sContent;
  private $_sTitle;
  private $_oModel;

	# Plugins
	protected $_oAdsense;
	protected $_oFacebook;
	protected $_oLazyLoad;
	protected $_oSmarty;

	public function __construct($aRequest, $aSession, $aFile = '') {
    $this->_aRequest	=& $aRequest;
    $this->_aSession	=& $aSession;
    $this->_aFile			=& $aFile;

    $this->_iId = isset($this->_aRequest['id']) ?
                  (int)$this->_aRequest['id'] :
                  '';

		# Start Smarty
		$this->_setSmarty();
  }

	public function __init() {
		$this->_oModel = '';
	}

	public function __autoload($sClass) {
    require_once('app/controllers/'	.(string)ucfirst($sClass).	'.controller.php');
  }

	protected function _setSmarty() {
		# Initialize smarty
		$this->_oSmarty = new Smarty();
		$this->_oSmarty->cache_dir = CACHE_DIR;
		$this->_oSmarty->compile_dir = COMPILE_DIR;

		# Define constants
		$this->_oSmarty->assign('AJAX_REQUEST', AJAX_REQUEST);
		$this->_oSmarty->assign('URL', WEBSITE_URL);
		$this->_oSmarty->assign('USER_EMAIL', USER_EMAIL);
		$this->_oSmarty->assign('USER_FACEBOOK_ID', USER_FACEBOOK_ID);
		$this->_oSmarty->assign('USER_FULL_NAME', USER_FULL_NAME);
		$this->_oSmarty->assign('USER_ID', USER_ID);
		$this->_oSmarty->assign('USER_NAME', USER_NAME);
		$this->_oSmarty->assign('USER_RIGHT', USER_RIGHT);
		$this->_oSmarty->assign('USER_SURNAME', USER_SURNAME);

		# Define system variables
		$this->_oSmarty->assign('_compress_files_suffix_', WEBSITE_COMPRESS_FILES == true ? '-min' : '');
    $this->_oSmarty->assign('_language_', substr(DEFAULT_LANGUAGE, 0, 2));
		$this->_oSmarty->assign('_pubdate_', date('r'));
		$this->_oSmarty->assign('_request_id_', $this->_iId);

		# Include Google Adsense
		if (class_exists('Adsense')) {
			$this->_oAdsense = new Adsense();
			$this->_oSmarty->assign('_plugin_adsense_', $this->_oAdsense->show());
		}

		# Generate a facebook connect link
		if (class_exists('FacebookCMS') && USER_ID == 0) {
			$this->_oFacebook = new FacebookCMS(array(
				'appId'  => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_SECRET,
			));

			$this->_oSmarty->assign('_plugin_facebook_connect_button_', $this->_oFacebook->getConnectButton());
		}

		# Enable LazyLoad
		if (class_exists('LazyLoad')) {
			$this->_oLazyLoad = new LazyLoad();
			$this->_oSmarty->assign('_plugin_lazyload_', $this->_oLazyLoad->show());
		}

		# Initialize language
		$this->_oSmarty->assign('lang_add_bookmark', LANG_GLOBAL_ADD_BOOKMARK);
		$this->_oSmarty->assign('lang_author', LANG_GLOBAL_AUTHOR);
		$this->_oSmarty->assign('lang_bb_help', LANG_GLOBAL_BBCODE_HELP);
		$this->_oSmarty->assign('lang_by', LANG_GLOBAL_BY);
		$this->_oSmarty->assign('lang_content', LANG_GLOBAL_CONTENT);
		$this->_oSmarty->assign('lang_create_entry_headline', LANG_GLOBAL_CREATE_ENTRY_HEADLINE);
		$this->_oSmarty->assign('lang_currently', LANG_GLOBAL_CURRENTLY);
		$this->_oSmarty->assign('lang_cut', LANG_GLOBAL_CUT);
		$this->_oSmarty->assign('lang_comments', LANG_GLOBAL_COMMENTS);
		$this->_oSmarty->assign('lang_contact', LANG_GLOBAL_CONTACT);
		$this->_oSmarty->assign('lang_deleted_user', LANG_GLOBAL_DELETED_USER);
		$this->_oSmarty->assign('lang_description', LANG_GLOBAL_DESCRIPTION);
		$this->_oSmarty->assign('lang_destroy_entry', LANG_GLOBAL_DESTROY_ENTRY);
		$this->_oSmarty->assign('lang_disclaimer_read', LANG_GLOBAL_TERMS_READ);
		$this->_oSmarty->assign('lang_email', LANG_GLOBAL_EMAIL);
		$this->_oSmarty->assign('lang_email_info', LANG_COMMENT_INFO_EMAIL);
		$this->_oSmarty->assign('lang_files', LANG_GLOBAL_FILES);
		$this->_oSmarty->assign('lang_missing_entry', LANG_ERROR_GLOBAL_MISSING_ENTRY);
		$this->_oSmarty->assign('lang_name', LANG_GLOBAL_NAME);
		$this->_oSmarty->assign('lang_last_update', LANG_GLOBAL_LAST_UPDATE);
		$this->_oSmarty->assign('lang_login', LANG_GLOBAL_LOGIN);
		$this->_oSmarty->assign('lang_no_entries', LANG_ERROR_GLOBAL_NO_ENTRIES);
		$this->_oSmarty->assign('lang_not_published', LANG_ERROR_GLOBAL_NOT_PUBLISHED);
		$this->_oSmarty->assign('lang_optional', LANG_GLOBAL_OPTIONAL);
		$this->_oSmarty->assign('lang_password', LANG_GLOBAL_PASSWORD);
		$this->_oSmarty->assign('lang_password_repeat', LANG_GLOBAL_PASSWORD_REPEAT);
		$this->_oSmarty->assign('lang_published', LANG_GLOBAL_PUBLISHED);
		$this->_oSmarty->assign('lang_quote', LANG_GLOBAL_QUOTE);
		$this->_oSmarty->assign('lang_register', LANG_GLOBAL_REGISTER);
		$this->_oSmarty->assign('lang_registration', LANG_GLOBAL_REGISTRATION);
		$this->_oSmarty->assign('lang_reset', LANG_GLOBAL_RESET);
		$this->_oSmarty->assign('lang_required', LANG_GLOBAL_REQUIRED);
		$this->_oSmarty->assign('lang_search', LANG_GLOBAL_SEARCH);
		$this->_oSmarty->assign('lang_share', LANG_GLOBAL_SHARE);
		$this->_oSmarty->assign('lang_subject', LANG_GLOBAL_SUBJECT);
		$this->_oSmarty->assign('lang_submit', LANG_GLOBAL_CREATE_ENTRY);
		$this->_oSmarty->assign('lang_surname', LANG_GLOBAL_SURNAME);
		$this->_oSmarty->assign('lang_tags', LANG_GLOBAL_TAGS);
		$this->_oSmarty->assign('lang_tags_info', LANG_GLOBAL_TAGS_INFO);
		$this->_oSmarty->assign('lang_teaser', LANG_GLOBAL_TEASER);
		$this->_oSmarty->assign('lang_title', LANG_GLOBAL_TITLE);
		$this->_oSmarty->assign('lang_update', LANG_GLOBAL_UPDATE);
		$this->_oSmarty->assign('lang_update_show', LANG_GLOBAL_UPDATE_SHOW);
		$this->_oSmarty->assign('lang_uploaded_at', LANG_GLOBAL_UPLOADED_AT);
    $this->_oSmarty->assign('lang_user_right', LANG_GLOBAL_USERRIGHT);
    $this->_oSmarty->assign('lang_user_right_1', LANG_GLOBAL_USERRIGHT_1);
    $this->_oSmarty->assign('lang_user_right_2', LANG_GLOBAL_USERRIGHT_2);
    $this->_oSmarty->assign('lang_user_right_3', LANG_GLOBAL_USERRIGHT_3);
    $this->_oSmarty->assign('lang_user_right_4', LANG_GLOBAL_USERRIGHT_4);
	}

  /* Manage Page Title */
  protected function _setTitle($sTitle) {
    $this->_sTitle =& $sTitle;
  }

  public function getTitle() {
    if( $this->_sTitle !== '' )
      return $this->_sTitle;
    else
      return LANG_ERROR_GLOBAL_404;
  }

  /* Manage Page Content */
  protected function _setContent($sContent) {
    $this->_sContent =& $sContent;
  }

  public function getContent() {
    return $this->_sContent;
  }

  public function search() {
    return $this->show();
  }

  public function show() {
    $this->show();
  }

  public function create($sInputName) {
    if( USER_RIGHT < 3 )
      return Helper::errorMessage(LANG_ERROR_GLOBAL_NO_PERMISSION);
    else {
      if( isset($this->_aRequest[$sInputName]) )
        return $this->_create();
      else
        return $this->_showFormTemplate(false);
    }
  }

  public function update($sInputName) {
    if( USER_RIGHT < 3 )
      return Helper::errorMessage(LANG_ERROR_GLOBAL_NO_PERMISSION);
    else {
      if( isset($this->_aRequest[$sInputName]) )
        return $this->_update();
      else
        return $this->_showFormTemplate(true);
    }
  }

  public function destroy() {
    if( USER_RIGHT < 3 )
      return Helper::errorMessage(LANG_ERROR_GLOBAL_NO_PERMISSION);
    else
      return $this->_destroy();
  }
}