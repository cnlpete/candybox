<?php

namespace CandyCMS;

class MigrationScript {

  public static function run($oDb) {

    // first generate the new fields
    $sSQL =
        'ALTER TABLE ' . SQL_PREFIX . 'blogs  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'blogs  ADD `date_modified_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'calendars  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'comments  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'contents  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'downloads  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'logs  ADD `time_start_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'logs  ADD `time_end_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'gallery_albums  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'gallery_files  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'sessions  ADD `date_migration` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'users  ADD `date_migration` DATETIME NOT NULL;';

    $bNewCols = $oDb->exec($sSQL);

    if ($bNewCols === false)
      return false;

    // move the data
    $sSQL =
        'UPDATE ' . SQL_PREFIX . 'blogs  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'blogs  SET `date_modified_migration` = FROM_UNIXTIME(`date_modified`) WHERE `date_modified` != 0;' .
        'UPDATE ' . SQL_PREFIX . 'calendars  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'comments  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'contents  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'downloads  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'logs  SET `time_start_migration` = FROM_UNIXTIME(`time_start`);' .
        'UPDATE ' . SQL_PREFIX . 'logs  SET `time_end_migration` = FROM_UNIXTIME(`time_end`);' .
        'UPDATE ' . SQL_PREFIX . 'gallery_albums  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'gallery_files  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'sessions  SET `date_migration` = FROM_UNIXTIME(`date`);' .
        'UPDATE ' . SQL_PREFIX . 'users  SET `date_migration` = FROM_UNIXTIME(`date`);';

    $bMoveData = $oDb->exec($sSQL);

    if ($bMoveData === false)
      return false;

    // drop the old columns
    $sSQL =
        'ALTER TABLE ' . SQL_PREFIX . 'blogs  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'blogs  DROP `date_modified`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'calendars  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'comments  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'contents  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'downloads  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'logs  DROP `time_start`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'logs  DROP `time_end`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'gallery_albums  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'gallery_files  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'sessions  DROP `date`;' .
        'ALTER TABLE ' . SQL_PREFIX . 'users  DROP `date`;';

    $bDropCols = $oDb->exec($sSQL);

    if ($bDropCols === false)
      return false;

    // rename the new columns to match the olds ones
    $sSQL =
        'ALTER TABLE ' . SQL_PREFIX . 'blogs  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'blogs  CHANGE COLUMN `date_modified_migration` `date_modified` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'calendars  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'comments  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'contents  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'downloads  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'logs  CHANGE COLUMN `time_start_migration` `time_start` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'logs  CHANGE COLUMN `time_end_migration` `time_end` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'gallery_albums  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'gallery_files  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'sessions  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'users  CHANGE COLUMN `date_migration` `date` DATETIME NOT NULL;';

    $bRenameCols = $oDb->exec($sSQL);

    return !($bRenameCols === false);
  }
}

?>