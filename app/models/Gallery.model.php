<?php

/*
 * This software is licensed under GPL <http://www.gnu.org/licenses/gpl.html>.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

class Model_Gallery extends Model_Main {
	private $_aThumbs;

	private final function _setData($bEdit = false) {
    $sWhere = '';

		if( !empty($this->_iId) )
			$sWhere = "WHERE a.id = '"	.$this->_iId.	"'";

    try {
      $oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD);
      $oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $oQuery = $oDb->query("	SELECT
                                a.*,
                                u.id AS uid,
                                u.name,
                                u.surname,
                                COUNT(f.id) AS filesSum
                              FROM
                                gallery_album a
                              LEFT JOIN
                                user u
                              ON
                                a.authorID=u.id
                              LEFT JOIN
                                gallery_file f
                              ON
                                f.aid=a.id
                              "	.$sWhere.	"
                              GROUP BY
                                a.id
                              ORDER BY
                                a.id DESC");

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
      $oDb = null;
    }
    catch (AdvancedException $e) {
      $oDb->rollBack();
      $e->getMessage();
    }

		if($bEdit == true) {
      $aRow = & $aResult;

      # Fix fetchAll with array 0
			$this->_aData = array(
          'title'       => Helper::removeSlahes($aRow[0]['title']),
					'description' => Helper::removeSlahes($aRow[0]['description'], true));
		}
		else {
			foreach ($aResult as $aRow) {
				$iId = $aRow['id'];
				$this->_aData[$iId] = array(
            'id'          => $aRow['id'],
            'authorID'    => $aRow['authorID'],
            'title'       => Helper::formatOutput($aRow['title']),
            'description' => Helper::formatOutput($aRow['description'], true),
            'date'        => Helper::formatTimestamp($aRow['date']),
            'files_sum'   => $aRow['filesSum']
				);

				if($aRow['filesSum'] > 0)
					$this->_aData[$iId]['files'] = $this->getThumbs($iId, LIMIT_ALBUM_THUMBS);
				else
					$this->_aData[$iId]['files'] = '';
			}
		}
	}

	public final function getData($iId = '', $bEdit = false) {
		if( !empty($iId) )
			$this->_iId = (int)$iId;

		$this->_setData($bEdit);
		return $this->_aData;
	}

	public final function getId() {
		return $this->_iId;
	}

	private final function _setThumbs($iAid, $iLimit) {
		# Clear existing array
		if(!empty($this->_aThumbs))
			unset($this->_aThumbs);

    try {
			$oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD, array(
									PDO::ATTR_PERSISTENT => true
							));
			$oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$oQuery = $oDb->prepare("SELECT * FROM gallery_file WHERE aid = :album_id");

			$oQuery->bindParam('album_id', $iAid);
			$bReturn = $oQuery->execute();

			$aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
		}
		catch (AdvancedException $e) {
			$oDb->rollBack();
			$e->getMessage();
		}

    $this->_iEntries = count($aResult);
    $this->oPages = new Pages($this->_aRequest, $this->_iEntries, $iLimit);

    if($this->_iEntries > 0) {
      try {
        $oQuery = $oDb->prepare("	SELECT
                                    *
                                  FROM
                                    gallery_file
                                  WHERE
                                    aid= :album_id
                                  ORDER BY
                                    date ASC
                                  LIMIT
                                    :offset,
                                    :limit");

        $oQuery->bindParam('album_id', $iAid);
        $oQuery->bindParam('limit', $this->oPages->getLimit(), PDO::PARAM_INT);
        $oQuery->bindParam('offset', $this->oPages->getOffset(), PDO::PARAM_INT);
        $oQuery->execute();

        $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
        $oDb = null;
      }
      catch (AdvancedException $e) {
        $oDb->rollBack();
        $e->getMessage();
      }

      $iLoop = 0;
      foreach ($aResult as $aRow) {
        $iId = $aRow['id'];
        $this->_aThumbs[$iId] = array(
            'id'          => $aRow['id'],
            'file'        => $aRow['file'],
            'full_path'   => WEBSITE_URL. '/' .PATH_UPLOAD.	'/gallery/'	.$aRow['aid'],
            'description' => Helper::formatOutput($aRow['description']),
            'date'        => Helper::formatTimestamp($aRow['date']),
            'extension'   => $aRow['extension'],
            'loop'        => $iLoop
        );

        $iLoop++;
      }
    }
    else {
      $oDb = null;
      return false;
    }
	}

	public final function getThumbs($iAid, $iLimit) {
		$this->_setThumbs($iAid, $iLimit);
		return $this->_aThumbs;
	}

	private final function _setAlbumName($iAid) {
    try {
			$oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD);
			$oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$oQuery = $oDb->prepare("SELECT title FROM gallery_album WHERE id = :album_id");

			$oQuery->bindParam('album_id', $iAid);
			$bReturn = $oQuery->execute();

			$aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
      return $aResult['title'];
		}
		catch (AdvancedException $e) {
			$oDb->rollBack();
			$e->getMessage();
		}
	}

	public final function getAlbumName($iAid) {
		return $this->_setAlbumName($iAid);
	}

	private final function _setAlbumDescription($iAid) {
		$oGetDescription = new Query("SELECT
																		description
																	FROM
																		gallery_album
																	WHERE
																		id='"	.(int)$iAid.	"'");

		$this->_aAlbumDescription = $oGetDescription->fetch();
		return $this->_aAlbumDescription['description'];
	}

	public final function getAlbumDescription($iAid) {
		return $this->_setAlbumDescription($iAid);
	}

	public function create() {
    try {
      $oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD);
      $oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $oQuery = $oDb->prepare(" INSERT INTO
                                  gallery_album(authorID, title, description, date)
                                VALUES
                                  ( :author_id, :title, :description, :date )");

      $iUserId = USER_ID;
      $oQuery->bindParam('author_id', $iUserId);
      $oQuery->bindParam('title', Helper::formatInput($this->_aRequest['title']));
      $oQuery->bindParam('description', Helper::formatInput($this->_aRequest['description']));
      $oQuery->bindParam('date', time());
      $bResult = $oQuery->execute();

      $this->_iId = $oDb->lastInsertId();
      $oDb = null;

    } catch (AdvancedException $e) {
      $oDb->rollBack();
      $e->getMessage();
    }

    if($bResult == true) {
      $sPath = PATH_UPLOAD.	'/gallery/'	.(int)$this->_iId;

      $sPathThumbS = $sPath.	'/32';
      $sPathThumbL = $sPath.	'/'	.THUMB_DEFAULT_X;
      $sPathThumbP = $sPath.	'/' .POPUP_DEFAULT_X;
      $sPathThumbO = $sPath.	'/original';

      if(!is_dir($sPath))
        mkdir($sPath, 0755);

      if(!is_dir($sPathThumbS))
        mkdir($sPathThumbS, 0755);

      if(!is_dir($sPathThumbL))
        mkdir($sPathThumbL, 0755);

      if(!is_dir($sPathThumbP))
        mkdir($sPathThumbP, 0755);

      if(!is_dir($sPathThumbO))
        mkdir($sPathThumbO, 0755);
    }

		return $bResult;
	}

	public function update($iId) {
		return new Query("UPDATE
												`gallery_album`
											SET
												title = '"	.Helper::formatInput($this->_aRequest['title']).	"',
												description = '"	.Helper::formatInput($this->_aRequest['description']).	"'
											WHERE
												`id` = '"	.(int)$iId.	"'");
	}

	public final function destroy($iId) {
		$sPath = PATH_UPLOAD.	'/gallery/'	.(int)$iId;

		# Delete Files
		$oGetImages = new Query("	SELECT
																file
															FROM
																gallery_file
															WHERE
																aid = '"	.(int)$iId.	"'");

		while($aRow = $oGetImages->fetch()) {
			@unlink($sPath.	'/32/'	.$aRow['file']);
			@unlink($sPath.	'/'	.THUMB_DEFAULT_X.	'/'	.$aRow['file']);
			@unlink($sPath.	'/' .POPUP_DEFAULT_X. '/'	.$aRow['file']);
			@unlink($sPath.	'/original/'	.$aRow['file']);
		}

		# Clear Database
		new Query("	DELETE FROM
									`gallery_file`
								WHERE
									`aid` = '"	.(int)$this->_aRequest['id'].	"'");

		new Query("	DELETE FROM
									`gallery_album`
								WHERE
									`id` = '"	.(int)$this->_aRequest['id'].	"'");

		# Delete Folders
		@rmdir($sPath.	'/32/');
		@rmdir($sPath.	'/'	.THUMB_DEFAULT_X);
		@rmdir($sPath.	'/' .POPUP_DEFAULT_X);
		@rmdir($sPath.	'/original');
		@rmdir($sPath);

		return $oGetImages;
	}

	public final function createFile($iUserId = '') {
		$oUploadFile = new Upload($this->_aRequest, $this->_aFile);
		$sFilePath = $oUploadFile->uploadGalleryFile();
    $this->_aRequest['description']  = (isset($this->_aRequest['description']) && !empty($this->_aRequest['description']))
                                      ? $this->_aRequest['description']
                                      : '';

		new Query("	INSERT INTO
									gallery_file(aid, authorID, file, extension, description, date)
								VALUES(
									'"	.(int)$this->_aRequest['id'].	"',
									'"	.(int)$iUserId.	"',
									'"	.$oUploadFile->getId().	"',
									'"	.$oUploadFile->getExtension().	"',
									'"	.Helper::formatInput($this->_aRequest['description']).	"',
									'"	.time().	"')");

		return $sFilePath;
	}

	public final function updateFile($iId) {
		$oQuery = new Query(" UPDATE
                            `gallery_file`
                          SET
                            description = '"	.Helper::formatInput($this->_aRequest['description']).	"'
                          WHERE
                            `id` = '"	.$iId.	"'");
    return $oQuery;
	}

	public final function destroyFile($iId) {
		$oGetFileData = new Query("	SELECT
																	file, aid
																FROM
																	gallery_file
																WHERE
																	id = '"	.(int)$iId.	"'");

		$aRow = $oGetFileData->fetch();
		$sPath = PATH_UPLOAD.	'/gallery/'	.$aRow['aid'];

		@unlink($sPath.	'/32/'	.$aRow['file']);
		@unlink($sPath.	'/'	.THUMB_DEFAULT_X.	'/'	.$aRow['file']);
		@unlink($sPath.	'/' .POPUP_DEFAULT_X. '/'	.$aRow['file']);
		@unlink($sPath.	'/original/'	.$aRow['file']);

		return new Query("DELETE FROM
                        `gallery_file`
                      WHERE
                        `id` = '"	.(int)$this->_aRequest['id'].	"'");
	}
}