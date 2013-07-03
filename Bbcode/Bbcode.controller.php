<?php

/**
 * Handle BB code.
 *
 * This plugin is the most powerful plugin, if you don't want to write every
 * text in HTML. It also enables users that are not allowed to post HTML to
 * format their text.
 *
 * A detailed documentation of how to use the tags can be found at
 * http://github.com/marcoraddatz/candyCMS/wiki/BBCode
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 * @see https://github.com/marcoraddatz/candyCMS/wiki/BBCode
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\Image;

final class Bbcode {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Bbcode';

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
   * Initialize the plugin and register all needed events.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param object $oPlugins the PluginManager
   *
   */
  public function __construct(&$aRequest, &$aSession, &$oPlugins) {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;

    # now register some events with the pluginmanager
    #$oPlugins->registerContentDisplayPlugin($this);
    $oPlugins->registerEditorPlugin($this);
  }

  /**
   * Search and replace BB code.
   *
   * @final
   * @static
   * @access private
   * @param string $sStr HTML to replace
   * @return string $sStr HTML with formated code
   *
   */
  private final static function _setFormatedText($sStr) {
    # BBCode
    $sStr = str_replace('[hr]', '<hr />', $sStr);
    $sStr = preg_replace('/\[center\](.*)\[\/center]/isU', '<div style=\'text-align:center\'>\1</div>', $sStr);
    $sStr = preg_replace('/\[left\](.*)\[\/left]/isU', '<left>\1</left>', $sStr);
    $sStr = preg_replace('/\[right\](.*)\[\/right]/isU', '<right>\1</right>', $sStr);
    $sStr = preg_replace('/\[p\](.*)\[\/p]/isU', '<p>\1</p>', $sStr);
    $sStr = preg_replace('=\[b\](.*)\[\/b\]=Uis', '<strong>\1</strong>', $sStr);
    $sStr = preg_replace('=\[i\](.*)\[\/i\]=Uis', '<em>\1</em>', $sStr);
    $sStr = preg_replace('=\[u\](.*)\[\/u\]=Uis', '<span style="text-decoration:underline">\1</span>', $sStr);
    $sStr = preg_replace('=\[del\](.*)\[\/del\]=Uis', '<span style="text-decoration:line-through">\1</span>', $sStr);
    $sStr = preg_replace('=\[code](.*)\[\/code]=Uis', '<pre>\1</pre>', $sStr);
    $sStr = preg_replace('#\[abbr=(.*)\](.*)\[\/abbr\]#Uis', '<abbr title="\1">\2</abbr>', $sStr);
    $sStr = preg_replace('#\[acronym=(.*)\](.*)\[\/acronym\]#Uis', '<acronym title="\1">\2</acronym>', $sStr);
    $sStr = preg_replace('#\[color=(.*)\](.*)\[\/color\]#Uis', '<span style="color:\1">\2</span>', $sStr);
    $sStr = preg_replace('#\[size=(.*)\](.*)\[\/size\]#Uis', '<span style="font-size:\1%">\2</span>', $sStr);
    $sStr = preg_replace('#\[anchor:(.*)\]#Uis', '<a name="\1"></a>', $sStr);

    # Load specific icon
    $sStr = preg_replace('#\[icon:(.*)\]#Uis', '<i class="icon-\1"></i>', $sStr);

    # Replace images with image tag (every location allowed
    while (preg_match('=\[img\](.*)\[\/img\]=isU', $sStr, $sUrl)) {
      $sUrl[1] = Helper::removeSlash($sUrl[1]);
      $sImageExtension = strtolower(substr(strrchr($sUrl[1], '.'), 1));
      $sTempFileName = md5(MEDIA_DEFAULT_X . $sUrl[1]);
      $sTempFilePath = Helper::removeSlash(PATH_UPLOAD . '/temp/bbcode/' . $sTempFileName . '.' . $sImageExtension);
      $sHTML = '';

      if (!file_exists($sTempFilePath)) {
        require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Image.helper.php';

        # This might be very slow. So we try to use it rarely.
        $aInfo = @getImageSize($sUrl[1]);

        # If external, download image and save as preview
        if (substr($sUrl[1], 0, 4) == 'http')
          file_put_contents($sTempFilePath, file_get_contents($sUrl[1]));

        if ($aInfo[0] > MEDIA_DEFAULT_X) {
          $oImage = new Image($sTempFileName, 'temp', $sUrl[1], $sImageExtension);
          $oImage->resizeDefault(MEDIA_DEFAULT_X, '', 'bbcode');
        }
      }

      $sUrl[1] = substr($sUrl[1], 0, 4) !== 'http' ? WEBSITE_URL . '/' . $sUrl[1] : $sUrl[1];

      # Remove capty and change image information.
      if (file_exists($sTempFilePath)) {
        $aNewInfo       = getImageSize($sTempFilePath);
        $sTempFilePath  = WEBSITE_URL . Helper::addSlash($sTempFilePath);
        $sClass         = 'js-image';
        $sAlt           = I18n::get('global.image.click_to_enlarge');
      }

      else {
        $aNewInfo[3]    = '';
        $sTempFilePath  = $sUrl[1];
        $sClass         = '';
        $sAlt           = $sTempFilePath;
      }
      
      print_r($sUrl);

      $sHTML .= '<figure class="image">';
      $sHTML .= '<a class="js-fancybox fancybox-thumb" rel="fancybox-thumb" href="' . $sUrl[1] . '">';
      $sHTML .= '<img class="' . $sClass . '" alt="' . $sAlt . '"';
      $sHTML .= 'src="' . $sTempFilePath . '" ' . $aNewInfo[3] . ' />';
      $sHTML .= '</a>';
      $sHTML .= '</figure>';

      $sStr = preg_replace('=\[img\](.*)\[\/img\]=isU', $sHTML, $sStr, 1);
    }

    # using [audio]file.ext[/audio]
    while (preg_match('#\[audio\](.*)\[\/audio\]#Uis', $sStr, $aMatch)) {
      $sUrl = 'http://url2vid.com/?url=' . $aMatch[1] . '&w=' . MEDIA_DEFAULT_X . '&h=30&callback=?';
      $sStr = preg_replace('#\[audio\](.*)\[\/audio\]#Uis',
              '<div class="js-media" title="' . $sUrl . '"><a href="' . $sUrl . '">' . $aMatch[1] . '</a></div>',
              $sStr);
    }

    # [video]file[/video]
    while (preg_match('#\[video\](.*)\[\/video\]#Uis', $sStr, $aMatch)) {
      $sUrl   = 'http://url2vid.com/?url=' . $aMatch[1] . '&w=' . MEDIA_DEFAULT_X . '&h=' . MEDIA_DEFAULT_Y . '&callback=?';
      $sStr = preg_replace('#\[video\](.*)\[\/video\]#Uis',
              '<a href="' . $aMatch[1] . '" class="js-media" title="' . $sUrl . '">' . $aMatch[1] . '</a>',
              $sStr,
              1);
    }

    # [video thumbnail]file[/video]
    while (preg_match('#\[video (.*)\](.*)\[\/video]#Uis', $sStr, $aMatch)) {
      $sUrl = 'http://url2vid.com/?url=' . $aMatch[2] . '&w=' . MEDIA_DEFAULT_X . '&h=' . MEDIA_DEFAULT_Y . '&p=' . $aMatch[1] . '&callback=?';
      $sStr = preg_replace('#\[video (.*)\](.*)\[\/video]#Uis',
              '<div class="js-media" title="' . $sUrl . '"><a href="' . $aMatch[2] . '">' . $aMatch[2] . '</a></div>',
              $sStr,
              1);
    }

    # [video width height thumbnail]file[/video]
    while (preg_match('#\[video ([0-9]+) ([0-9]+) (.*)\](.*)\[\/video\]#Uis', $sStr, $aMatch)) {
      $sUrl = 'http://url2vid.com/?url=' . $aMatch[4] . '&w=' . $aMatch[1] . '&h=' . $aMatch[2] . '&p=' . $aMatch[3] . '&callback=?';
      $sStr = preg_replace('#\[video ([0-9]+) ([0-9]+) (.*)\](.*)\[\/video\]#Uis',
              '<div class="js-media" title="' . $sUrl . '"><a href="' . $aMatch[4] . '" class="js-media">' . $aMatch[4] . '</a></div>',
              $sStr,
              1);
    }

    # Quote
    while (preg_match("/\[quote\]/isU", $sStr) && preg_match("/\[\/quote]/isU", $sStr) ||
      preg_match("/\[quote\=/isU", $sStr) && preg_match("/\[\/quote]/isU", $sStr)) {
      $sStr = preg_replace("/\[quote\](.*)\[\/quote]/isU", "<blockquote>\\1</blockquote>", $sStr);
      $sStr = preg_replace("/\[quote\=(.+)\](.*)\[\/quote]/isU", "<blockquote><h4>" . I18n::get('global.quote.by') . " \\1</h4>\\2</blockquote>", $sStr);
    }

    while (preg_match("/\[toggle\=/isU", $sStr) && preg_match("/\[\/toggle]/isU", $sStr)) {
      $sStr = preg_replace("/\[toggle\=(.+)\](.*)\[\/toggle]/isU", "<span class='js-toggle-headline'>\\1</span><div class=\"js-toggle-element\">\\2</div>", $sStr);
    }

    # Bugfix: Fix quote and allow these tags
    $sStr = str_replace("&lt;blockquote&gt;", "<blockquote>", $sStr);
    $sStr = str_replace("&lt;/blockquote&gt;", "</blockquote>", $sStr);
    $sStr = str_replace("&lt;h4&gt;", "<h4>", $sStr);
    $sStr = str_replace("&lt;/h4&gt;", "</h4>", $sStr);

    return $sStr;
  }

  /**
   * Return the formatted code.
   *
   * @final
   * @static
   * @access public
   * @param string $sStr
   * @return string HTML with formated code
   *
   */
  public final function prepareContent($sStr) {
    return self::_setFormatedText($sStr);
  }

  /**
   * Show nothing, since this plugin does not need to output additional javascript.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function show() {
    return '';
  }

  /**
   * Generate an Info Array ('url' => '', 'iconurl' => '', 'description' => '')
   *
   * @final
   * @access public
   * @return array|boolean infor array or false
   * @todo return array with bbcode logo and link to github info page
   *
   */
  public final function getInfo() {
    return false;
  }
}
