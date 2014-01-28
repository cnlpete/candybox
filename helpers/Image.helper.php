<?php

/**
 * Resize images.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Helpers;

use candyCMS\Core\Helpers\Helper;
use Imagine\Image\Box;
use Imagine\Image\Point;

/**
 * Class Image
 * @package candyCMS\Core\Helpers
 *
 */
class Image {

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aInfo = array(0 => 1, 1 => 1);

  /**
   * @var string
   * @access protected
   *
   */
  protected $_sFolder;

  /**
   * @var string
   * @access protected
   *
   */
  protected $_sId;

  /**
   * @var string
   * @access protected
   *
   */
  protected $_sImgType;

  /**
   * @var string
   * @access protected
   *
   */
  protected $_sOriginalPath;

  /**
   * @var string
   * @access protected
   *
   */
  protected $_sUploadDir;

  /**
   * Set up the new image.
   *
   * @access public
   * @param string $sId name of the file
   * @param string $sUploadDir folder to upload image to. Normally the controller name. For gallery use "gallery/id_of_gallery".
   * @param string $sOriginalPath path of the image to clone from incl. file name
   * @param string $sImgType type of image
   *
   */
  public function __construct($sId, $sUploadDir, $sOriginalPath, $sImgType = 'jpg') {
    $this->_sId           = & $sId;
    $this->_sUploadDir    = & $sUploadDir;
    $this->_sOriginalPath = & $sOriginalPath;
    $this->_sImgType      = & $sImgType;

    if ( file_exists($this->_sOriginalPath) )
      $this->_aInfo = getimagesize($this->_sOriginalPath);
  }

  /**
   * Create the new image with given params.
   *
   * @access private
   * @param integer $iSrcX x-coordinate of source point
   * @param integer $iSrcY y-coordinate of source point
   * @param integer $iWidth width to cut
   * @param integer $iHeight height to cut
   * @return string $sPath path of the new image
   *
   */
  private function _createImage($iSrcX, $iSrcY, $iWidth, $iHeight = '') {
    $sPath = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sUploadDir . '/' .
                    $this->_sFolder . '/' . $this->_sId . '.' . $this->_sImgType);

    $iHeight = !empty($iHeight) ? $iHeight : $this->_iImageHeight;

    # Create image using Imagine
    $oImagine = new \Imagine\Gd\Imagine();

    # Cut image
    if ($this->_iImageWidth == $this->_iImageHeight) {
      $oImage   = $oImagine->open($this->_sOriginalPath)
                            ->crop(new Point($iSrcX, $iSrcY), new Box($iWidth, $iWidth))
                            ->resize(new Box($this->_iImageWidth, $this->_iImageHeight))
                            ->save($sPath);
    }

    # Resize only
    else
      $oImage   = $oImagine->open($this->_sOriginalPath)
                            ->resize(new Box($iWidth, $iHeight))
                            ->crop(new Point($iSrcX, $iSrcY), new Box($this->_iImageWidth, $this->_iImageHeight))
                            ->save($sPath);

    # Reduce image size via Smush.it
    if (ALLOW_SMUSHIT) {
      try {
        require_once PATH_STANDARD . '/vendor/tylerhall/smushit-php/class.smushit.php';
      }
      catch (AdvancedException $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }

      # Send information of our created image to the server.
      $oSmushIt = new \SmushIt(WEBSITE_URL . '/' . $sPath);

      # Download new image from Smush.it
      if (empty($oSmushIt->error)) {
        unlink($sPath);
        file_put_contents($sPath, file_get_contents($oSmushIt->compressedUrl));
      }
    }

    return $sPath;
  }

  /**
   * Proportional resizing.
   *
   * @access public
   * @param integer $iWidth width of the new image
   * @param integer $iMaxHeight maximum height of the new image
   * @param string $sFolder folder of the new image
   * @return string $sPath path of the new image
   *
   */
  public function resizeDefault($iWidth, $iMaxHeight = '', $sFolder = '') {
    $this->_sFolder = empty($sFolder) ? $iWidth : $sFolder;
    $iSrcX = 0;
    $iSrcY = 0;

    # Y bigger than X and max height
    if ( ($this->_aInfo[1] > $this->_aInfo[0]) && $iMaxHeight) {
      $this->_iImageWidth   = round($this->_aInfo[0] * ($iMaxHeight / $this->_aInfo[1]));
      $this->_iImageHeight  = $iMaxHeight;

      $iWidth = $this->_iImageWidth;
    }

    # X bigger than Y
    else {
      $iHeight = round($this->_aInfo[1] * ($iWidth / $this->_aInfo[0]));

      $this->_iImageWidth   = $iWidth;
      $this->_iImageHeight  = $iHeight;

      if (!empty($iMaxHeight) && $iMaxHeight < $iHeight) {
        $this->_iImageHeight = $iMaxHeight;
        $iSrcY = ($iHeight - $iMaxHeight) / 2;
      }
    }

    return $this->_createImage($iSrcX, $iSrcY, $iWidth, isset($iHeight) ? $iHeight : '');
  }

  /**
   * Cut resizing.
   *
   * @access public
   * @param integer $iWidth width and height of the new image
   * @param string $sFolder folder of the new image
   * @return string $sPath path of the new image
   *
   */
  public function resizeAndCut($iWidth, $sFolder = '') {
    $this->_sFolder = empty($sFolder) ? $iWidth : $sFolder;

    $this->_iImageWidth   = $iWidth;
    $this->_iImageHeight  = $iWidth;

    # Y bigger than X
    if ($this->_aInfo[1] > $this->_aInfo[0]) {
      $iSrcX = 0;
      $iSrcY = ($this->_aInfo[1] - $this->_aInfo[0]) / 2;
      $iWidth = $this->_aInfo[0];
    }

    # X bigger than Y
    else {
      $iSrcX = ($this->_aInfo[0] - $this->_aInfo[1]) / 2;
      $iSrcY = 0;
      $iWidth = $this->_aInfo[1];
    }

    # Attention: $iWidth is overwritten!
    # Since we cut the image, we also use width for height
    return $this->_createImage($iSrcX, $iSrcY, $iWidth);
  }
}