<?php
/*
    part-db version 0.1
    Copyright (C) 2005 Christoph Lechner
    http://www.cl-projects.de/

    part-db version 0.2+
    Copyright (C) 2009 K. Jacobs and others (see authors.php)
    http://code.google.com/p/part-db/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

    /*
     * DATABASE UPDATE STEPS:
     *
     * This file contains all steps to update the database step by step to the latest version.
     *
     * To add a new step, you have to:
     *      - increment the constant "LATEST_DB_VERSION" by one
     *      - add a new "case" element at the end of the function below.
     *          -> this new "case" must have the number "LATEST_DB_VERSION - 1"!
     */

    define('LATEST_DB_VERSION', 17);  // <-- increment here

    /*
     * Get update steps
     *
     * This function will be executed one time for every update step until we have the latest version.
     *
     * Arguments:
     *      $current_version:       the current version
     *
     * Return:
     *      an array of SQL queries which we have to execute
     */
    function get_db_update_steps($current_version)
    {
        $updateSteps = array();

        switch($current_version)
        {
          case 0:
            // there are no tables (empty database), so we will create them.
            // Please note: We will directly create the database version 13, not the first version!

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `internal` (
                `keyName` char(30) CHARACTER SET ascii NOT NULL,
                `keyValue` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                UNIQUE KEY `keyName` (`keyName`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

            // insert internal records
            $updateSteps[] = "INSERT INTO internal (keyName, keyValue) VALUES ('dbVersion', '13');"; // <-- We will create the version 13

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `attachements` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `class_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `element_id` int(11) NOT NULL,
                  `type_id` int(11) NOT NULL,
                  `filename` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `show_in_table` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `attachements_class_name_k` (`class_name`),
                  KEY `attachements_element_id_k` (`element_id`),
                  KEY `attachements_type_id_fk` (`type_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `attachement_types` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `parent_id` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `attachement_types_parent_id_k` (`parent_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            // create attachement types "Bilder" and "Datenblätter"
            $updateSteps[] = "INSERT INTO `attachement_types` (name, parent_id) VALUES ('Bilder', NULL)";
            $updateSteps[] = "INSERT INTO `attachement_types` (name, parent_id) VALUES ('Datenblätter', NULL)";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `categories` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `parent_id` int(11) DEFAULT NULL,
                  `disable_footprints` tinyint(1) NOT NULL DEFAULT '0',
                  `disable_manufacturers` tinyint(1) NOT NULL DEFAULT '0',
                  `disable_autodatasheets` tinyint(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `categories_parent_id_k` (`parent_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `devices` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `parent_id` int(11) DEFAULT NULL,
                  `order_quantity` int(11) NOT NULL DEFAULT '0',
                  `order_only_missing_parts` tinyint(1) NOT NULL DEFAULT '0',
                  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `devices_parent_id_k` (`parent_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `device_parts` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `id_part` int(11) NOT NULL DEFAULT '0',
                  `id_device` int(11) NOT NULL DEFAULT '0',
                  `quantity` int(11) NOT NULL DEFAULT '0',
                  `mountnames` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `device_parts_combination_uk` (`id_part`,`id_device`),
                  KEY `device_parts_id_part_k` (`id_part`),
                  KEY `device_parts_id_device_k` (`id_device`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `footprints` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `filename` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `parent_id` int(11) DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `footprints_parent_id_k` (`parent_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `manufacturers` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `parent_id` int(11) DEFAULT NULL,
                  `address` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `phone_number` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `fax_number` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `email_address` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `website` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `manufacturers_parent_id_k` (`parent_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `orderdetails` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `part_id` int(11) NOT NULL,
                  `id_supplier` int(11) NOT NULL DEFAULT '0',
                  `supplierpartnr` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `obsolete` tinyint(1) DEFAULT '0',
                  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `orderdetails_part_id_k` (`part_id`),
                  KEY `orderdetails_id_supplier_k` (`id_supplier`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `parts` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `id_category` int(11) NOT NULL DEFAULT '0',
                  `name` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `instock` int(11) NOT NULL DEFAULT '0',
                  `mininstock` int(11) NOT NULL DEFAULT '0',
                  `comment` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `visible` tinyint(1) NOT NULL,
                  `id_footprint` int(11) DEFAULT NULL,
                  `id_storelocation` int(11) DEFAULT NULL,
                  `order_orderdetails_id` int(11) DEFAULT NULL,
                  `order_quantity` int(11) NOT NULL DEFAULT '1',
                  `manual_order` tinyint(1) NOT NULL DEFAULT '0',
                  `id_manufacturer` int(11) DEFAULT NULL,
                  `id_master_picture_attachement` int(11) DEFAULT NULL,
                  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `last_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (`id`),
                  KEY `parts_id_category_k` (`id_category`),
                  KEY `parts_id_footprint_k` (`id_footprint`),
                  KEY `parts_id_storelocation_k` (`id_storelocation`),
                  KEY `parts_order_orderdetails_id_k` (`order_orderdetails_id`),
                  KEY `parts_id_manufacturer_k` (`id_manufacturer`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `pricedetails` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `orderdetails_id` int(11) NOT NULL,
                  `price` decimal(6,2) NOT NULL,
                  `price_related_quantity` int(11) NOT NULL DEFAULT '1',
                  `min_discount_quantity` int(11) NOT NULL DEFAULT '1',
                  `manual_input` tinyint(1) NOT NULL DEFAULT '1',
                  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `pricedetails_combination_uk` (`orderdetails_id`,`min_discount_quantity`),
                  KEY `pricedetails_orderdetails_id_k` (`orderdetails_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `storelocations` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `parent_id` int(11) DEFAULT NULL,
                  `is_full` tinyint(1) NOT NULL DEFAULT '0',
                  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `storelocations_parent_id_k` (`parent_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `suppliers` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `name` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `parent_id` int(11) DEFAULT NULL,
                  `address` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `phone_number` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `fax_number` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `email_address` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `website` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `suppliers_parent_id_k` (`parent_id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";


            $updateSteps[] = "ALTER TABLE `attachements`
                  ADD CONSTRAINT `attachements_type_id_fk` FOREIGN KEY (`type_id`) REFERENCES `attachement_types` (`id`)";

            $updateSteps[] = "ALTER TABLE `attachement_types`
                  ADD CONSTRAINT `attachement_types_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `attachement_types` (`id`)";

            $updateSteps[] = "ALTER TABLE `categories`
                  ADD CONSTRAINT `categories_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)";

            $updateSteps[] = "ALTER TABLE `devices`
                  ADD CONSTRAINT `devices_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `devices` (`id`)";

            $updateSteps[] = "ALTER TABLE `footprints`
                  ADD CONSTRAINT `footprints_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `footprints` (`id`)";

            $updateSteps[] = "ALTER TABLE `manufacturers`
                  ADD CONSTRAINT `manufacturers_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `manufacturers` (`id`)";

            $updateSteps[] = "ALTER TABLE `parts`
                  ADD CONSTRAINT `parts_id_footprint_fk` FOREIGN KEY (`id_footprint`) REFERENCES `footprints` (`id`),
                  ADD CONSTRAINT `parts_id_manufacturer_fk` FOREIGN KEY (`id_manufacturer`) REFERENCES `manufacturers` (`id`),
                  ADD CONSTRAINT `parts_id_storelocation_fk` FOREIGN KEY (`id_storelocation`) REFERENCES `storelocations` (`id`),
                  ADD CONSTRAINT `parts_order_orderdetails_id_fk` FOREIGN KEY (`order_orderdetails_id`) REFERENCES `orderdetails` (`id`)";

            $updateSteps[] = "ALTER TABLE `storelocations`
                  ADD CONSTRAINT `storelocations_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `storelocations` (`id`)";

            $updateSteps[] = "ALTER TABLE `suppliers`
                  ADD CONSTRAINT `suppliers_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `suppliers` (`id`)";

            break;

          case 1:
            $updateSteps[] = NULL; // nothing to do (steps removed)
            break;

          case 2:
            $updateSteps[] = "ALTER TABLE  `part_device` ADD  `mountname` mediumtext NOT NULL AFTER  `quantity` ;";
            break;

          case 3:
            $updateSteps[] = "ALTER TABLE  `storeloc` ADD  `parentnode` int(11) NOT NULL default '0' AFTER  `name` ;";
            $updateSteps[] = "ALTER TABLE  `storeloc` ADD  `is_full` boolean NOT NULL default false AFTER `parentnode` ;";
            break;

          case 4:
            $updateSteps[] = "ALTER TABLE  `part_device` DROP PRIMARY KEY;";
            break;

          case 5:
            $updateSteps[] = "ALTER TABLE  `devices` ADD  `parentnode` int(11) NOT NULL default '0' AFTER  `name` ;";
            break;

          case 6:
            $updateSteps[] = "ALTER TABLE  footprints ADD  parentnode INT(11) NOT NULL default '0' AFTER name;";
            break;

          case 7:
            $updateSteps[] = "ALTER TABLE  parts  ADD  obsolete boolean NOT NULL default false AFTER comment;";
            break;

          case 8:
            // footprints auf neues schema umbennenen
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_2KBB-R'                   WHERE name='2KBB-R';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_2KBB'                     WHERE name='2KBB';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_2KBP'                     WHERE name='2KBP';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_1010'                          WHERE name='1010';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_1012'                          WHERE name='1012';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_1014'                          WHERE name='1014';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_1212'                          WHERE name='1212';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_1214'                          WHERE name='1214';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_0405'                          WHERE name='0405';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_0505'                          WHERE name='0505';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_0605'                          WHERE name='0605';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_0807'                          WHERE name='0807';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_0808'                          WHERE name='0808';";
            $updateSteps[] = "UPDATE footprints SET name='ELKO_SMD_0810'                          WHERE name='0810';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-KOHLE_0204'                  WHERE name='0204';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-KOHLE_0207'                  WHERE name='0207';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-KOHLE_0309'                  WHERE name='0309';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-KOHLE_0414'                  WHERE name='0414';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-KOHLE_0617'                  WHERE name='0617';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-KOHLE_0922'                  WHERE name='0922';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_3202'                           WHERE name='3202';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_64W'                            WHERE name='64W';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_64Y'                            WHERE name='64Y';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_72-PT'                          WHERE name='72PT';";
            $updateSteps[] = "UPDATE footprints SET name='7-SEGMENT_1-20CM'                       WHERE name='7SEG-1';";
            $updateSteps[] = "UPDATE footprints SET name='7-SEGMENT_2'                            WHERE name='7SEG-2';";
            $updateSteps[] = "UPDATE footprints SET name='7-SEGMENT_3-TOT4301'                    WHERE name='7SEG-3';";
            $updateSteps[] = "UPDATE footprints SET name='7-SEGMENT_2-VQE'                        WHERE name='7SEG-VQE-3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_147323-02'              WHERE name='AMP-147323-2';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_AK700-3-5'                WHERE name='AK70-3-5';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZ_ABRACON_ABS13'                    WHERE name='ABS13';";
            $updateSteps[] = "UPDATE footprints SET name='RESONATOR-ABRACON_ABM3B'                WHERE name='ABM3B';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-02'         WHERE name='AMP-HE14S2';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-03'         WHERE name='AMP-HE14S3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-04'         WHERE name='AMP-HE14S4';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-05'         WHERE name='AMP-HE14S5';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-06'         WHERE name='AMP-HE14S6';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-07'         WHERE name='AMP-HE14S7';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-08'         WHERE name='AMP-HE14S8';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-09'         WHERE name='AMP-HE14S9';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-GERADE_HE14-10'         WHERE name='AMP-HE14S10';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-02'    WHERE name='AMP-HE14R2';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-03'    WHERE name='AMP-HE14R3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-04'    WHERE name='AMP-HER4';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-05'    WHERE name='AMP-HE14R5';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-06'    WHERE name='AMP-HE14R6';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-07'    WHERE name='AMP-HE14R7';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-08'    WHERE name='AMP-HE14R8';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-09'    WHERE name='AMP-HE14R9';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-10'    WHERE name='AMP-HE14R10';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-02'                  WHERE name='AMPMT-S2';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-03'                  WHERE name='AMPMT-S3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-04'                  WHERE name='AMPMT-S4';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-05'                  WHERE name='AMPMT-S5';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-06'                  WHERE name='AMPMT-S6';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-07'                  WHERE name='AMPMT-S7';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-08'                  WHERE name='AMPMT-S8';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-09'                  WHERE name='AMPMT-S9';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-10'                  WHERE name='AMPMT-S10';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-AMP_MT-12'                  WHERE name='AMPMT-S12';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-02'                 WHERE name='ARK5MM-2';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-03'                 WHERE name='ARK5MM-3';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-04'                 WHERE name='ARK5MM-4';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-05'                 WHERE name='ARK5MM-5';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-06'                 WHERE name='ARK5MM-6';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-07'                 WHERE name='ARK5MM-7';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-08'                 WHERE name='ARK5MM-8';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-09'                 WHERE name='ARK5MM-9';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-10'                 WHERE name='ARK5MM-10';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-11'                 WHERE name='ARK5MM-11';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM508-12'                 WHERE name='ARK5MM-12';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-02'                 WHERE name='ARK350MM2';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-03'                 WHERE name='ARK350MM3';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-04'                 WHERE name='ARK350MM4';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-05'                 WHERE name='ARK350MM5';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-06'                 WHERE name='ARK350MM6';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-07'                 WHERE name='ARK350MM7';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-08'                 WHERE name='ARK350MM8';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-09'                 WHERE name='ARK350MM9';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-10'                 WHERE name='ARK350MM10';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-11'                 WHERE name='ARK350MM11';";
            $updateSteps[] = "UPDATE footprints SET name='SCHRAUBKLEMME_RM350-12'                 WHERE name='ARK350MM12';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_B25V'                           WHERE name='B25V';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_B25X'                           WHERE name='B25X';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_COAX-B35N61'                  WHERE name='B35N61';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_B3F-10XX1'                       WHERE name='B3F10XX1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X05'              WHERE name='BL1X5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X06'              WHERE name='BL1X6';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X07'              WHERE name='BL1X7';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X08'              WHERE name='BL1X8';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X09'              WHERE name='BL1X9';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X10'              WHERE name='BL1X10';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X12'              WHERE name='BL1X12';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X13'              WHERE name='BL1X13';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X15'              WHERE name='BL1X15';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X17'              WHERE name='BL1X17';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X18'              WHERE name='BL1X18';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_1X20'              WHERE name='BL1X20';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X05'              WHERE name='BL2X5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X06'              WHERE name='BL2X6';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X07'              WHERE name='BL2X7';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X08'              WHERE name='BL2X8';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X09'              WHERE name='BL2X9';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X10'              WHERE name='BL2X10';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X12'              WHERE name='BL2X12';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X13'              WHERE name='BL2X13';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X15'              WHERE name='BL2X15';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X17'              WHERE name='BL2X17';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X18'              WHERE name='BL2X18';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE_2X20'              WHERE name='BL2X20';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X05'        WHERE name='BLF1X5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X06'        WHERE name='BLF1X6';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X07'        WHERE name='BLF1X7';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X08'        WHERE name='BLF1X8';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X09'        WHERE name='BLF1X9';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X10'        WHERE name='BLF1X10';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X12'        WHERE name='BLF1X12';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X13'        WHERE name='BLF1X13';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X15'        WHERE name='BLF1X15';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X17'        WHERE name='BLF1X17';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X18'        WHERE name='BLF1X18';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_1X20'        WHERE name='BLF1X20';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X05'        WHERE name='BLF2X5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X06'        WHERE name='BLF2X6';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X07'        WHERE name='BLF2X7';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X08'        WHERE name='BLF2X8';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X09'        WHERE name='BLF2X9';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X10'        WHERE name='BLF2X10';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X12'        WHERE name='BLF2X12';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X13'        WHERE name='BLF2X13';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X15'        WHERE name='BLF2X15';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X17'        WHERE name='BLF2X17';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X18'        WHERE name='BLF2X18';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-GERADE-FLACH_2X20'        WHERE name='BLF2X20';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_BNC-W'                        WHERE name='BNC';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X05'         WHERE name='BLW2X5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X06'         WHERE name='BLW2X6';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X07'         WHERE name='BLW2X7';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X08'         WHERE name='BLW2X8';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X09'         WHERE name='BLW2X9';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X10'         WHERE name='BLW2X10';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X12'         WHERE name='BLW2X12';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X13'         WHERE name='BLW2X13';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X15'         WHERE name='BLW2X15';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X17'         WHERE name='BLW2X17';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X18'         WHERE name='BLW2X18';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_2X20'         WHERE name='BLW2X20';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X02'         WHERE name='BLW1X2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X05'         WHERE name='BLW1X5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X06'         WHERE name='BLW1X6';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X07'         WHERE name='BLW1X7';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X08'         WHERE name='BLW1X8';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X09'         WHERE name='BLW1X9';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X10'         WHERE name='BLW1X10';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X12'         WHERE name='BLW1X12';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X13'         WHERE name='BLW1X13';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X15'         WHERE name='BLW1X15';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X17'         WHERE name='BLW1X17';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X18'         WHERE name='BLW1X18';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSENLEISTE-ABGEWINKELT_1X20'         WHERE name='BLW1X20';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_CB417'                            WHERE name='CB417';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_CB429'                            WHERE name='CB429';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_0402'                         WHERE name='CAP-0402';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_0603'                         WHERE name='CAP-0603';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_0805'                         WHERE name='CAP-0805';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_1206'                         WHERE name='CAP-1206';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_1210'                         WHERE name='CAP-1210';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_1812'                         WHERE name='CAP-1812';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_1825'                         WHERE name='CAP-1825';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD_2220'                         WHERE name='CAP-2220';";
            $updateSteps[] = "UPDATE footprints SET name='KERKO-SMD-ARRAY_4X0603-0612'            WHERE name='CAP-4x0603';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_DCPOWERCONNECTOR'                WHERE name='BUxx';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC10H'          WHERE name='BPC10H';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC10V'          WHERE name='BPC10V';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC3H'           WHERE name='BPC3H';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC3V'           WHERE name='BPC3V';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC5H'           WHERE name='BPC5H';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC5V'           WHERE name='BPC5V';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC7H'           WHERE name='BPC7H';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-DICKSCHICHT_BPC7V'           WHERE name='BPC7V';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DFS'                                 WHERE name='DFS';";
            $updateSteps[] = "UPDATE footprints SET name='KONDENSATOR_CTS_A_15MM'                 WHERE name='CTS-A-15';";
            $updateSteps[] = "UPDATE footprints SET name='KONDENSATOR_CTS_B_20MM'                 WHERE name='CTS-B-20';";
            $updateSteps[] = "UPDATE footprints SET name='KONDENSATOR_CTS_C_25MM'                 WHERE name='CTS-C-25';";
            $updateSteps[] = "UPDATE footprints SET name='KONDENSATOR_CTS_D_30MM'                 WHERE name='CTS-D-30';";
            $updateSteps[] = "UPDATE footprints SET name='RESONATOR-MURATA_CSTCE-G-A'             WHERE name='CSTCE-GA';";
            $updateSteps[] = "UPDATE footprints SET name='KARTENSLOT_CF-1'                        WHERE name='CF-CON';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZOSZILLATOR_CFPT-125'               WHERE name='CFPT125';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZOSZILLATOR_CFPT-126'               WHERE name='CFPT-126';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZOSZILLATOR_CFPT-37 '               WHERE name='CFPT37';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-CENTRONICS_F14'                  WHERE name='CENTRONICS-F14';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-CENTRONICS_F24'                  WHERE name='CENTRONICS-F24';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-CENTRONICS_F36'                  WHERE name='CENTRONICS-F36';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-CENTRONICS_F50'                  WHERE name='CENTRONICS-F50';";
            $updateSteps[] = "UPDATE footprints SET name='REEDRELAIS_SIL'                         WHERE name='CELDUC-SIL';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_CELDUC-SK-ABD'                   WHERE name='CELDUC-SK-ABD';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_CELDUC-SK-AL '                   WHERE name='CELDUC-SK-AL';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_CELDUC-SK-L  '                   WHERE name='CELDUC-SK-L';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_DIN41617-13'                  WHERE name='DIN41617-13';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_DIN41617-21'                  WHERE name='DIN41617-21';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_DIN41617-31'                  WHERE name='DIN41617-31';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_3S'                      WHERE name='DINMAB3S';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_4'                       WHERE name='DINMAB4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_5'                       WHERE name='DINMAB5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_5S'                      WHERE name='DINMAB5S';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_5SV'                     WHERE name='DINMAB5SV';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_6'                       WHERE name='DINMAB6';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_6V'                      WHERE name='DINMAB6V';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_7S'                      WHERE name='DINMAB7S';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_7SV'                     WHERE name='DINMAB7SV';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_8S'                      WHERE name='DINMAB8S';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_8SN'                     WHERE name='DINMAB8SN';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_8SNV'                    WHERE name='DINMAB8SNV';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-DIN_MAB_8SV'                     WHERE name='DINMAB8SV';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SMA'                              WHERE name='DIODE-SMA';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SMB'                              WHERE name='DIODE-SMB';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SMC'                              WHERE name='DIODE-SMC';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP14'                               WHERE name='DIP14';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP14A4'                             WHERE name='DIP14A4';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP14A8'                             WHERE name='DIP14A8';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP16'                               WHERE name='DIP16';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP16A4'                             WHERE name='DIP16A4';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP16A8'                             WHERE name='DIP16A8';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP18'                               WHERE name='DIP18';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP02'                               WHERE name='DIP2';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP04'                               WHERE name='DIP4';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP06'                               WHERE name='DIP6';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP08'                               WHERE name='DIP8';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP08A4'                             WHERE name='DIP8A4';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP20'                               WHERE name='DIP20';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP22'                               WHERE name='DIP22';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP24'                               WHERE name='DIP24';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP24A12'                            WHERE name='DIP24A12';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP24W'                              WHERE name='DIP24W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP28'                               WHERE name='DIP28';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP28W'                              WHERE name='DIP28W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP32-3'                             WHERE name='DIP32';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP32W'                              WHERE name='DIP32W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP36W'                              WHERE name='DIP36W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP40W'                              WHERE name='DIP40W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP42W'                              WHERE name='DIP42W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP48W'                              WHERE name='DIP48W';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_DIP4S'                    WHERE name='DIP4S';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DIP52W'                              WHERE name='DIP52W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_DPAK-369C'                           WHERE name='DPAK369C';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO14'                             WHERE name='DO14';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO15'                             WHERE name='DO15';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO16'                             WHERE name='DO16';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO201'                            WHERE name='DO201';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO204AC'                          WHERE name='DO204';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO214AA'                          WHERE name='DO214AA';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO214AB'                          WHERE name='DO214AB';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO214AC'                          WHERE name='DO214AC';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO27'                             WHERE name='DO27';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO32'                             WHERE name='DO32';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO34'                             WHERE name='DO34';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO35'                             WHERE name='DO35';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO39'                             WHERE name='DO39';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO41'                             WHERE name='DO41';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_DO7'                              WHERE name='DO7';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_DK1A-L2-5V'                      WHERE name='DK1AL2';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_W-09'             WHERE name='DSUB-F9';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_W-15'             WHERE name='DSUB-F15';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_W-25'             WHERE name='DSUB-F25';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_W-37'             WHERE name='DSUB-F37';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-09'                             WHERE name='DSUB-F9D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-09V'                            WHERE name='DSUB-F9DV';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-15'                             WHERE name='DSUB-F15D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-15V'                            WHERE name='DSUB-F15DV';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-25'                             WHERE name='DSUB-F25D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-25V'                            WHERE name='DSUB-F25DV';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-37'                             WHERE name='DSUB-F37D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_W-37V'                            WHERE name='DSUB-F37DV';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_M-09'             WHERE name='DSUB-M9';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_M-15'             WHERE name='DSUB-M15';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_M-25'             WHERE name='DSUB-M25';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D-PLATINENMONTAGE_M-37'             WHERE name='DSUB-M37';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-09'                             WHERE name='DSUB-M9D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-09V'                            WHERE name='DSUB-M9DV';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-15'                             WHERE name='DSUB-M15D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-15V'                            WHERE name='DSUB-M15DV';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-25'                             WHERE name='DSUB-M25D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-25V'                            WHERE name='DSUB-M25DV';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-37'                             WHERE name='DSUB-M37D';";
            $updateSteps[] = "UPDATE footprints SET name='SUB-D_M-37V'                            WHERE name='DSUB-M37DV';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_ED16'                             WHERE name='ED16';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_ED22'                             WHERE name='ED22';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_ED26'                             WHERE name='ED26';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_ED38'                             WHERE name='ED38';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_ED43'                             WHERE name='ED43';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_EF12'                             WHERE name='EF12';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_EF16'                             WHERE name='EF16';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_EUROCARD-64M-2-L'             WHERE name='EUROCARD64M2L';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_EUROCARD-96M-3-L'             WHERE name='EUROCARD96M3L';";
            $updateSteps[] = "UPDATE footprints SET name='DREHSCHALTER-PANASONIC_EVQVX-11MM'      WHERE name='EVQVX-11MM';";
            $updateSteps[] = "UPDATE footprints SET name='DREHSCHALTER-PANASONIC_EVQVX-9MM'       WHERE name='EVQVX-9MM';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_F126'                             WHERE name='F126';";
            $updateSteps[] = "UPDATE footprints SET name='LOETOESE_FASTON-V'                      WHERE name='FASTON-V';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_FB100'                    WHERE name='FB100';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_FB15 '                    WHERE name='FB15';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_FB15A'                    WHERE name='FB15A';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_FB32 '                    WHERE name='FB32';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_FPCON65'                      WHERE name='FPCON65';";
            $updateSteps[] = "UPDATE footprints SET name='SICHERUNGSHALTER_Laengs'                WHERE name='FUSE1';";
            $updateSteps[] = "UPDATE footprints SET name='SICHERUNGSHALTER_Quer'                  WHERE name='FUSE2';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_GP20'                             WHERE name='GP20';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_G2RL-1'                          WHERE name='G2RL1';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_G2RL-1A'                         WHERE name='G2RL1A';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_G2RL-1A-E'                       WHERE name='G2RL1AE';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_G2RL-1-E'                        WHERE name='G2RL1E';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_G2RL-2'                          WHERE name='G2RL2';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_G2RL-2A'                         WHERE name='G2RL2A';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_G6D'                             WHERE name='G6D';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_GBU4'                     WHERE name='GBU4';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_JJM-1A'                          WHERE name='JJM1A';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_JJM-1C'                          WHERE name='JJM1C';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_JJM-2W'                          WHERE name='JJM2W';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZ_025MM'                            WHERE name='HC18';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZ_HC49'                             WHERE name='HC49';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZ_HC49-4H'                          WHERE name='HC49U';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_VIEWCOM_HS-1-25GY_50'      WHERE name='HS1-25GY-50';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_5MM-S'                            WHERE name='L5MM-S';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_KBU-4-6-8'                WHERE name='KBU46x8';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_KL195-25'                  WHERE name='KL195-25';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_KL195-38'                  WHERE name='KL195-38';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_KL195-50'                  WHERE name='KL195-50';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_KL195-63'                  WHERE name='KL195-63';";
            $updateSteps[] = "UPDATE footprints SET name='LED-ROT_0603'                           WHERE name='LED-0603';";
            $updateSteps[] = "UPDATE footprints SET name='LED-ROT_0805'                           WHERE name='LED-0805';";
            $updateSteps[] = "UPDATE footprints SET name='LED-ROT_1206'                           WHERE name='LED-1206';";
            $updateSteps[] = "UPDATE footprints SET name='LED-ROT_3MM'                            WHERE name='LED-3';";
            $updateSteps[] = "UPDATE footprints SET name='LED-ROT_5MM'                            WHERE name='LED-5';";
            $updateSteps[] = "UPDATE footprints SET name='LOETOESE_LSP'                           WHERE name='LSP10';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_LSH125'                          WHERE name='LSH125';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_LSH43'                           WHERE name='LSH43';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_LSH50'                           WHERE name='LSH50';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_LSH70'                           WHERE name='LSH70';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_LSH80'                           WHERE name='LSH80';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_LSH95'                           WHERE name='LSH95';";
            $updateSteps[] = "UPDATE footprints SET name='IC_LQFP64'                              WHERE name='LQFP64';";
            $updateSteps[] = "UPDATE footprints SET name='IC_LQFP48'                              WHERE name='LQFP48';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_LMI-L115-02'                  WHERE name='LMI-L115-2';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_LMI-L115-03'                  WHERE name='LMI-L115-3';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_LMI-L115-05'                  WHERE name='LMI-L115-5';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_LMI-L115-10'                  WHERE name='LMI-L115-10';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_LMI-L115-20'                  WHERE name='LMI-L115-20';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_10_1'               WHERE name='MATNLOK-926310-1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_10_2'               WHERE name='MATNLOK-926310-2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_10_3'               WHERE name='MATNLOK-926310-3';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_10_4'               WHERE name='MATNLOK-926310-4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_10_5'               WHERE name='MATNLOK-926310-5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_11_1'               WHERE name='MATNLOK-926311-1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_11_2'               WHERE name='MATNLOK-926311-2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_11_3'               WHERE name='MATNLOK-926311-3';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_11_4'               WHERE name='MATNLOK-926311-4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_11_5'               WHERE name='MATNLOK-926311-5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_12_1'               WHERE name='MATNLOK-926312-1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_12_2'               WHERE name='MATNLOK-926312-2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_12_3'               WHERE name='MATNLOK-926312-3';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_12_4'               WHERE name='MATNLOK-926312-4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_12_5'               WHERE name='MATNLOK-926312-5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_13_1'               WHERE name='MATNLOK-926313-1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_13_2'               WHERE name='MATNLOK-926313-2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_13_3'               WHERE name='MATNLOK-926313-3';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_13_4'               WHERE name='MATNLOK-926313-4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_13_5'               WHERE name='MATNLOK-926313-5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_14_1'               WHERE name='MATNLOK-926314-1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_14_2'               WHERE name='MATNLOK-926314-2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_14_3'               WHERE name='MATNLOK-926314-3';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_14_4'               WHERE name='MATNLOK-926314-4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_14_5'               WHERE name='MATNLOK-926314-5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_15_1'               WHERE name='MATNLOK-926315-1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_15_2'               WHERE name='MATNLOK-926315-2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_15_3'               WHERE name='MATNLOK-926315-3';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_15_4'               WHERE name='MATNLOK-926315-4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_15_5'               WHERE name='MATNLOK-926315-5';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_16_1'               WHERE name='MATNLOK-926316-1';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_16_2'               WHERE name='MATNLOK-926316-2';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_16_3'               WHERE name='MATNLOK-926316-3';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_16_4'               WHERE name='MATNLOK-926316-4';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MATNLOK_9263_16_5'               WHERE name='MATNLOK-926316-5';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_MELF'                             WHERE name='MELF';";
            $updateSteps[] = "UPDATE footprints SET name='IC_MBxS'                                WHERE name='MB2S';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-04'            WHERE name='MICROMATCH4F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-06'            WHERE name='MICROMATCH6F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-08'            WHERE name='MICROMATCH8F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-10'            WHERE name='MICROMATCH10F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-12'            WHERE name='MICROMATCH12F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-14'            WHERE name='MICROMATCH14F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-16'            WHERE name='MICROMATCH16F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-18'            WHERE name='MICROMATCH18F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_FEMALE-20'            WHERE name='MICROMATCH20F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-04'              WHERE name='MICROMATCH4M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-06'              WHERE name='MICROMATCH6M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-08'              WHERE name='MICROMATCH8M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-10'              WHERE name='MICROMATCH10M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-12'              WHERE name='MICROMATCH12M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-14'              WHERE name='MICROMATCH14M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-16'              WHERE name='MICROMATCH16M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-18'              WHERE name='MICROMATCH18M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_MALE-20'              WHERE name='MICROMATCH20M';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-04'               WHERE name='MICROMATCH-SMD4F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-06'               WHERE name='MICROMATCH-SMD6F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-08'               WHERE name='MICROMATCH-SMD8F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-10'               WHERE name='MICROMATCH-SMD10F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-12'               WHERE name='MICROMATCH-SMD12F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-14'               WHERE name='MICROMATCH-SMD14F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-16'               WHERE name='MICROMATCH-SMD16F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-18'               WHERE name='MICROMATCH-SMD18F';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE-MICROMATCH_SMD-20'               WHERE name='MICROMATCH-SMD20F';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZ_MM505'                            WHERE name='MM505';";
            $updateSteps[] = "UPDATE footprints SET name='IC_MLF28'                               WHERE name='MLF28';";
            $updateSteps[] = "UPDATE footprints SET name='IC_MLF32'                               WHERE name='MLF32';";
            $updateSteps[] = "UPDATE footprints SET name='IC_MLF44'                               WHERE name='MLF44';";
            $updateSteps[] = "UPDATE footprints SET name='IC_MLF64'                               WHERE name='MLF64';";
            $updateSteps[] = "UPDATE footprints SET name='LED_MINITOP'                            WHERE name='MINITOPLED';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_MINIMELF'                         WHERE name='MINIMELF';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_MICROMELF'                        WHERE name='MICROMELF';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_RJ11'                            WHERE name='MODULAR-RJ11';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_RJ11-SHLD'                       WHERE name='MODULAR-RJ11S';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_RJ12'                            WHERE name='MODULAR-RJ12';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_RJ12-SHLD'                       WHERE name='MODULAR-RJ12S';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_RJ45'                            WHERE name='MODULAR-RJ45';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_RJ45-SHLD'                       WHERE name='MODULAR-RJ45S';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-02'        WHERE name='MOLEX-PSL2G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-03'        WHERE name='MOLEX-PSL3G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-04'        WHERE name='MOLEX-PSL4G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-05'        WHERE name='MOLEX-PSL5G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-06'        WHERE name='MOLEX-PSL6G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-07'        WHERE name='MOLEX-PSL7G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-08'        WHERE name='MOLEX-PSL8G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-09'        WHERE name='MOLEX-PSL9G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-GERADE_PSL-10'        WHERE name='MOLEX-PSL10G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-02'   WHERE name='MOLEX-PSL2W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-03'   WHERE name='MOLEX-PSL3W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-04'   WHERE name='MOLEX-PSL4W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-05'   WHERE name='MOLEX-PSL5W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-06'   WHERE name='MOLEX-PSL6W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-07'   WHERE name='MOLEX-PSL7W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-08'   WHERE name='MOLEX-PSL8W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-09'   WHERE name='MOLEX-PSL9W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-10'   WHERE name='MOLEX-PSL10W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-03'             WHERE name='MOLEX53047-3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-04'             WHERE name='MOLEX53047-4';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-05'             WHERE name='MOLEX53047-5';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-06'             WHERE name='MOLEX53047-6';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-07'             WHERE name='MOLEX53047-7';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-08'             WHERE name='MOLEX53047-8';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-09'             WHERE name='MOLEX53047-9';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-10'             WHERE name='MOLEX53047-10';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-11'             WHERE name='MOLEX53047-11';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-12'             WHERE name='MOLEX53047-12';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-13'             WHERE name='MOLEX53047-13';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-14'             WHERE name='MOLEX53047-14';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53047-15'             WHERE name='MOLEX53047-15';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-02'             WHERE name='MOLEX53048-2';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-03'             WHERE name='MOLEX53048-3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-04'             WHERE name='MOLEX53048-4';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-05'             WHERE name='MOLEX53048-5';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-06'             WHERE name='MOLEX53048-6';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-07'             WHERE name='MOLEX53048-7';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-08'             WHERE name='MOLEX53048-8';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-09'             WHERE name='MOLEX53048-9';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-10'             WHERE name='MOLEX53048-10';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-11'             WHERE name='MOLEX53048-11';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-12'             WHERE name='MOLEX53048-12';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-13'             WHERE name='MOLEX53048-13';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-14'             WHERE name='MOLEX53048-14';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53048-15'             WHERE name='MOLEX53048-15';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-02'             WHERE name='MOLEX53261-2';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-03'             WHERE name='MOLEX53261-3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-04'             WHERE name='MOLEX53261-4';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-05'             WHERE name='MOLEX53261-5';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-06'             WHERE name='MOLEX53261-6';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-07'             WHERE name='MOLEX53261-7';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-08'             WHERE name='MOLEX53261-8';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-09'             WHERE name='MOLEX53261-9';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-10'             WHERE name='MOLEX53261-10';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-11'             WHERE name='MOLEX53261-11';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-12'             WHERE name='MOLEX53261-12';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-13'             WHERE name='MOLEX53261-13';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-14'             WHERE name='MOLEX53261-14';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-MOLEX_53261-15'             WHERE name='MOLEX53261-15';";
            $updateSteps[] = "UPDATE footprints SET name='IC_MULTIWATT15'                         WHERE name='MULTIWATT15';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_MURATA_2012-LQH3C'                WHERE name='MURATA-2012-LQH3C';";
            $updateSteps[] = "UPDATE footprints SET name='RESONATOR-MURATA_CSTCC-G-A'             WHERE name='MURATA-CSTCC-G-A';";
            $updateSteps[] = "UPDATE footprints SET name='EMV-MURATA_NFE61P'                      WHERE name='MURATA-NFE61P';";
            $updateSteps[] = "UPDATE footprints SET name='IC_MSOP10'                              WHERE name='MSOP10';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_PHONE-JACK'                      WHERE name='PHONE-JACK';";
            $updateSteps[] = "UPDATE footprints SET name='LASER_PDLD-PIGTAIL'                     WHERE name='PDLD';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE_PCPWR514M'                  WHERE name='PCPWR514M';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_PB-G90-1A-1'                     WHERE name='PBG90';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_P600'                             WHERE name='P600';";
            $updateSteps[] = "UPDATE footprints SET name='SCHALTREGLER_NME-S'                     WHERE name='NME-S';";
            $updateSteps[] = "UPDATE footprints SET name='SCHALTREGLER_NMA-D'                     WHERE name='NMA-D';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-MYRRA_30-2'                       WHERE name='MYRRA-EI30';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-MYRRA_38-2'                       WHERE name='MYRRA-EI38';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-MYRRA_48-2'                       WHERE name='MYRRA-EI48';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-MYRRA_66-2'                       WHERE name='MYRRA-EI66';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-MYRRA_54-2'                       WHERE name='MYRRA-EL54';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-MYRRA_60-2'                       WHERE name='MYRRA-EL60';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-MYRRA_48-40'                      WHERE name='MYRRA-UI48';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X10'            WHERE name='PHSMD2X10';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X11'            WHERE name='PHSMD2X11';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X12'            WHERE name='PHSMD2X12';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X13'            WHERE name='PHSMD2X13';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X14'            WHERE name='PHSMD2X14';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X15'            WHERE name='PHSMD2X15';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X16'            WHERE name='PHSMD2X16';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X02'            WHERE name='PHSMD2X2';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X03'            WHERE name='PHSMD2X3';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X04'            WHERE name='PHSMD2X4';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X05'            WHERE name='PHSMD2X5';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X06'            WHERE name='PHSMD2X6';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X07'            WHERE name='PHSMD2X7';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X08'            WHERE name='PHSMD2X8';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-SMD_2X09'            WHERE name='PHSMD2X9';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_PT10-H'                         WHERE name='PT10H10';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PSO36'                               WHERE name='PSO36';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PSO20'                               WHERE name='PSO20';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PQFP100'                             WHERE name='PQFP100';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PQFP128'                             WHERE name='PQFP128';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PQFP160'                             WHERE name='PQFP160';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PQFP208'                             WHERE name='PQFP208';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PQFP240'                             WHERE name='PQFP240';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PQFP44'                              WHERE name='PQFP44';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PQFP48'                              WHERE name='PQFP48';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PLCC20'                              WHERE name='PLCC20';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PLCC28'                              WHERE name='PLCC28';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PLCC32'                              WHERE name='PLCC32';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PLCC44'                              WHERE name='PLCC44';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PLCC52'                              WHERE name='PLCC52';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PLCC68'                              WHERE name='PLCC68';";
            $updateSteps[] = "UPDATE footprints SET name='IC_PLCC84'                              WHERE name='PLCC84';";
            $updateSteps[] = "UPDATE footprints SET name='LED_PLCC2'                              WHERE name='PLCC2';";
            $updateSteps[] = "UPDATE footprints SET name='IC_QSOP16'                              WHERE name='QSOP16';";
            $updateSteps[] = "UPDATE footprints SET name='IC_QSOP20'                              WHERE name='QSOP20';";
            $updateSteps[] = "UPDATE footprints SET name='IC_QSOP24'                              WHERE name='QSOP24';";
            $updateSteps[] = "UPDATE footprints SET name='IC_QSOP28'                              WHERE name='QSOP28';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_0102-MLF'                WHERE name='RES-0102MLF';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_0204-MLF'                WHERE name='RES-0204MLF';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_0207-MLF'                WHERE name='RES-0207MLF';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_0402'                    WHERE name='RES-0402';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_0603'                    WHERE name='RES-0603';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_0805'                    WHERE name='RES-0805';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_1206'                    WHERE name='RES-1206';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_1210'                    WHERE name='RES-1210';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_1218'                    WHERE name='RES-1218';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_2010'                    WHERE name='RES-2010';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD_2512'                    WHERE name='RES-2512';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-SMD-ARRAY_4X0603-0612'       WHERE name='RES-4x0603';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_RB1A'                     WHERE name='RB1A';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_RAWA400-9P'                WHERE name='RAWA400-9P';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_RAWA400-8P'                WHERE name='RAWA400-8P';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_RAWA400-11P'               WHERE name='RAWA400-11P';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_RA37-3'                    WHERE name='RA37-3';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH10'                    WHERE name='RH10';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH100'                   WHERE name='RH100';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH100X'                  WHERE name='RH100X';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH25'                    WHERE name='RH25';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH250'                   WHERE name='RH250';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH5'                     WHERE name='RH5-304';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH50'                    WHERE name='RH50';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND-ALU_RH75'                    WHERE name='RH75';";
            $updateSteps[] = "UPDATE footprints SET name='DREHSCHALTER_DIP10-1'                   WHERE name='ROTARYDIP10-1';";
            $updateSteps[] = "UPDATE footprints SET name='DREHSCHALTER_DIP10'                     WHERE name='ROTARYDIP10';";
            $updateSteps[] = "UPDATE footprints SET name='DREHSCHALTER_DIP16-1'                   WHERE name='ROTARYDIP16-1';";
            $updateSteps[] = "UPDATE footprints SET name='DREHSCHALTER_DIP16'                     WHERE name='ROTARYDIP16';";
            $updateSteps[] = "UPDATE footprints SET name='RELAIS_RY2'                             WHERE name='RY2';";
            $updateSteps[] = "UPDATE footprints SET name='SD-KARTE_Schwarz'                       WHERE name='SD-CARD';";
            $updateSteps[] = "UPDATE footprints SET name='IC_BECK-SC12'                           WHERE name='SC12';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_S64Y'                           WHERE name='S64Y';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SHARP-S2xxEx'                        WHERE name='S2XXEX';";
            $updateSteps[] = "UPDATE footprints SET name='SCHIEBESCHALTER_SECME-1K2-RH'           WHERE name='SECME1K2RH';";
            $updateSteps[] = "UPDATE footprints SET name='SCHIEBESCHALTER_SECME-1K2-RL'           WHERE name='SECME1K2RL';";
            $updateSteps[] = "UPDATE footprints SET name='SCHIEBESCHALTER_SECME-1K2-SH'           WHERE name='SECME1K2SH';";
            $updateSteps[] = "UPDATE footprints SET name='SCHIEBESCHALTER_SECME-1K2-SL'           WHERE name='SECME1K2SL';";
            $updateSteps[] = "UPDATE footprints SET name='SCHIEBESCHALTER_SECME-1K2-SLB'          WHERE name='SECME1K2SLB';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_SFT1030'                          WHERE name='SFT1030';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_SFT1040'                          WHERE name='SFT1040';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_SFT1240'                          WHERE name='SFT1240';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_SFT830D'                          WHERE name='SFT830D';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_SFT830S'                          WHERE name='SFT830S';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_SFT840D'                          WHERE name='SFT840D';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL04'                       WHERE name='SIL4';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL05'                       WHERE name='SIL5';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL06'                       WHERE name='SIL6';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL07'                       WHERE name='SIL7';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL08'                       WHERE name='SIL8';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL09'                       WHERE name='SIL9';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL10'                       WHERE name='SIL10';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL11'                       WHERE name='SIL11';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL12'                       WHERE name='SIL12';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL13'                       WHERE name='SIL13';";
            $updateSteps[] = "UPDATE footprints SET name='WIDERSTAND_SIL14'                       WHERE name='SIL14';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-254-MC'              WHERE name='SK104-254MC';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-254-STIS'            WHERE name='SK104-254STIS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-254-STS'             WHERE name='SK104-254STS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-254-STSB'            WHERE name='SK104-254STSB';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-381-MC'              WHERE name='SK104-381MC';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-381-STIS'            WHERE name='SK104-381STIS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-381-STS'             WHERE name='SK104-381STS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-381-STSB'            WHERE name='SK104-381STSB';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-508-MC'              WHERE name='SK104-508MC';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-508-STIS'            WHERE name='SK104-508STIS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-508-STS'             WHERE name='SK104-508STS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-508-STSB'            WHERE name='SK104-508STSB';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-635-MC'              WHERE name='SK104-635MC';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-635-STIS'            WHERE name='SK104-635STIS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-635-STS'             WHERE name='SK104-635STS';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK104-635-STSB'            WHERE name='SK104-635STSB';";
            $updateSteps[] = "UPDATE footprints SET name='TASTER_SKHH-3MM'                        WHERE name='SKHH-V4x3Y';";
            $updateSteps[] = "UPDATE footprints SET name='GLEICHRICHTER_SKBB'                     WHERE name='SKBB';";
            $updateSteps[] = "UPDATE footprints SET name='KUEHLKOERPER_SK96-84'                   WHERE name='SK96-84';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_SMA-JH'                       WHERE name='SMA-JH';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER_SMA-JV'                       WHERE name='SMA-JV';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-SMD_LP-500X'                      WHERE name='SMLP500x';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_SMSL-1305'                        WHERE name='SMSL1305';";
            $updateSteps[] = "UPDATE footprints SET name='TRAFO-SMD_SL2'                          WHERE name='SMSL2';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD123-1'                         WHERE name='SOD123A';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD123-3'                         WHERE name='SOD123B';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD123-5'                         WHERE name='SOD123C';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD57'                            WHERE name='SOD57';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD61-A'                          WHERE name='SOD61A';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD61-B'                          WHERE name='SOD61B';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD61-C'                          WHERE name='SOD61C';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD61-D'                          WHERE name='SOD61D';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD61-E'                          WHERE name='SOD61E';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD64'                            WHERE name='SOD64';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD80'                            WHERE name='SOD80';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD81'                            WHERE name='SOD81';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE_SOD87'                            WHERE name='SOD87';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO14'                                WHERE name='SOIC14';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO16'                                WHERE name='SOIC16';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO16W'                               WHERE name='SOIC16W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO18W'                               WHERE name='SOIC18W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO20W'                               WHERE name='SOIC20W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO24W'                               WHERE name='SOIC24W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO28W'                               WHERE name='SOIC28W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO32-400'                            WHERE name='SOIC32';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO32-525'                            WHERE name='SOIC32W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SO08'                                WHERE name='SOIC8';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SOT143'                              WHERE name='SOT143';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SOT223'                              WHERE name='SOT223';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SOT23-5'                             WHERE name='SOT23-5';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SOT23-6'                             WHERE name='SOT23-6';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SOT23'                               WHERE name='SOT23';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SOT363'                              WHERE name='SOT363';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SQFP100'                             WHERE name='SQFP14X20';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SQFP64'                              WHERE name='SQFP64';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP14'                              WHERE name='SSOP14';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP16'                              WHERE name='SSOP16';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP20'                              WHERE name='SSOP20';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP24'                              WHERE name='SSOP24';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP28'                              WHERE name='SSOP28';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP30'                              WHERE name='SSOP30';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP48'                              WHERE name='SSOP48';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP56'                              WHERE name='SSOP56';";
            $updateSteps[] = "UPDATE footprints SET name='IC_SSOP56DL'                            WHERE name='SSOP56DL';";
            $updateSteps[] = "UPDATE footprints SET name='BUZZER_TDB'                             WHERE name='TDB';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_T18'                            WHERE name='T18';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_T7-YA'                          WHERE name='T7YA';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_T7-YB'                          WHERE name='T7YB';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX14'                           WHERE name='TEX14';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX16'                           WHERE name='TEX16';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX18'                           WHERE name='TEX18';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX20'                           WHERE name='TEX20';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX22'                           WHERE name='TEX22';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX24'                           WHERE name='TEX24';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX24W'                          WHERE name='TEX24W';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX28'                           WHERE name='TEX28';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX28W'                          WHERE name='TEX28W';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX32W'                          WHERE name='TEX32W';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX40W'                          WHERE name='TEX40W';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX40WW'                         WHERE name='TEX40WW';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX42W'                          WHERE name='TEX42W';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX48W'                          WHERE name='TEX48W';";
            $updateSteps[] = "UPDATE footprints SET name='SOCKEL_TEX64WW'                         WHERE name='TEX64W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO126'                               WHERE name='TO126';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO18'                                WHERE name='TO18';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO18D'                               WHERE name='TO18D';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO202'                               WHERE name='TO202';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO218'                               WHERE name='TO218';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO220'                               WHERE name='TO220-3';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO220-5'                             WHERE name='TO220-5';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO247'                               WHERE name='TO247';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO252'                               WHERE name='TO252';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO263'                               WHERE name='TO263';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO3'                                 WHERE name='TO3';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO39-4'                              WHERE name='TO39-4';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO39'                                WHERE name='TO39';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO51'                                WHERE name='TO51';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO52'                                WHERE name='TO52';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO66'                                WHERE name='TO66';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO72-3'                              WHERE name='TO72-3';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO72-4'                              WHERE name='TO72-4';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO92-2'                              WHERE name='TO92-2';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO92'                                WHERE name='TO92-3';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TO92-G4'                             WHERE name='TO92-3G';";
            $updateSteps[] = "UPDATE footprints SET name='LASER_TORX173'                          WHERE name='TORX173';";
            $updateSteps[] = "UPDATE footprints SET name='LASER_TOTX173'                          WHERE name='TOTX173';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TQFP100'                             WHERE name='TQPP100';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TQFP112'                             WHERE name='TQFP112';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TQFP144'                             WHERE name='TQFP144';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TQFP32'                              WHERE name='TQFP32';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TQFP44'                              WHERE name='TQFP44';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TQFP64'                              WHERE name='TQFP64';";
            $updateSteps[] = "UPDATE footprints SET name='BUCHSE_RJ45-SHLD-LED'                   WHERE name='TRJ19201';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_TSM-4YJ'                        WHERE name='TSM4YJ';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_TSM-4YL'                        WHERE name='TSM4YL';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_TSM-4ZJ'                        WHERE name='TSM4ZJ';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_TSM-4ZL'                        WHERE name='TSM4ZL';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_TS53-YJ'                        WHERE name='TS53YJ';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMER_TS53-YL'                        WHERE name='TS53YL';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSOP32'                              WHERE name='TSSOP32W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSOP48'                              WHERE name='TSSOP48W';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSOP86'                              WHERE name='TSSOP86';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP08'                             WHERE name='TSSOP8';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP14'                             WHERE name='TSSOP14';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP16'                             WHERE name='TSSOP16';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP20'                             WHERE name='TSSOP20';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP24'                             WHERE name='TSSOP24';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP28'                             WHERE name='TSSOP28';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP48'                             WHERE name='TSSOP48';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP56'                             WHERE name='TSSOP56';";
            $updateSteps[] = "UPDATE footprints SET name='IC_TSSOP64'                             WHERE name='TSSOP64';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_TYCO_H38'                         WHERE name='TYCO-H38';";
            $updateSteps[] = "UPDATE footprints SET name='TRIMMKONDENSATOR-SCHWARZ_TZ03F'         WHERE name='TZ03F';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER-USB_A-1'                      WHERE name='USB-A1';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER-USB_A-2'                      WHERE name='USB-A2';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER-USB_B-1'                      WHERE name='USB-B1';";
            $updateSteps[] = "UPDATE footprints SET name='VERBINDER-USB_B-2'                      WHERE name='USB-B2';";
            $updateSteps[] = "UPDATE footprints SET name='IC_UMAX10'                              WHERE name='UMAX10';";
            $updateSteps[] = "UPDATE footprints SET name='IC_UMAX08'                              WHERE name='UMAX8';";
            $updateSteps[] = "UPDATE footprints SET name='IC_VSO40'                               WHERE name='VSO40';";
            $updateSteps[] = "UPDATE footprints SET name='IC_VSO56'                               WHERE name='VSO56';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_102'                    WHERE name='WAGO233-102';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_103'                    WHERE name='WAGO233-103';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_104'                    WHERE name='WAGO233-104';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_105'                    WHERE name='WAGO233-105';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_106'                    WHERE name='WAGO233-106';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_107'                    WHERE name='WAGO233-107';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_108'                    WHERE name='WAGO233-108';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_109'                    WHERE name='WAGO233-109';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_110'                    WHERE name='WAGO233-110';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_112'                    WHERE name='WAGO233-112';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_116'                    WHERE name='WAGO233-116';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_124'                    WHERE name='WAGO233-124';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_136'                    WHERE name='WAGO233-136';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_148'                    WHERE name='WAGO233-148';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_202'                    WHERE name='WAGO233-202';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_203'                    WHERE name='WAGO233-203';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_204'                    WHERE name='WAGO233-204';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_205'                    WHERE name='WAGO233-205';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_206'                    WHERE name='WAGO233-206';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_207'                    WHERE name='WAGO233-207';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_208'                    WHERE name='WAGO233-208';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_209'                    WHERE name='WAGO233-209';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_210'                    WHERE name='WAGO233-210';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_212'                    WHERE name='WAGO233-212';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_216'                    WHERE name='WAGO233-216';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_224'                    WHERE name='WAGO233-224';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_236'                    WHERE name='WAGO233-236';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_248'                    WHERE name='WAGO233-248';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_402'                    WHERE name='WAGO233-402';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_403'                    WHERE name='WAGO233-403';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_404'                    WHERE name='WAGO233-404';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_405'                    WHERE name='WAGO233-405';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_406'                    WHERE name='WAGO233-406';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_407'                    WHERE name='WAGO233-407';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_408'                    WHERE name='WAGO233-408';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_409'                    WHERE name='WAGO233-409';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_410'                    WHERE name='WAGO233-410';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_412'                    WHERE name='WAGO233-412';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_416'                    WHERE name='WAGO233-416';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_424'                    WHERE name='WAGO233-424';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_436'                    WHERE name='WAGO233-436';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_448'                    WHERE name='WAGO233-448';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_502'                    WHERE name='WAGO233-502';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_503'                    WHERE name='WAGO233-503';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_504'                    WHERE name='WAGO233-504';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_505'                    WHERE name='WAGO233-505';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_506'                    WHERE name='WAGO233-506';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_507'                    WHERE name='WAGO233-507';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_508'                    WHERE name='WAGO233-508';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_509'                    WHERE name='WAGO233-509';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_510'                    WHERE name='WAGO233-510';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_512'                    WHERE name='WAGO233-512';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_516'                    WHERE name='WAGO233-516';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_524'                    WHERE name='WAGO233-524';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_536'                    WHERE name='WAGO233-536';";
            $updateSteps[] = "UPDATE footprints SET name='KLEMME-WAGO-233_548'                    WHERE name='WAGO233-548';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-02'                WHERE name='WAGO733-332';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-03'                WHERE name='WAGO733-333';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-04'                WHERE name='WAGO733-334';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-05'                WHERE name='WAGO733-335';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-06'                WHERE name='WAGO733-336';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-07'                WHERE name='WAGO733-337';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-08'                WHERE name='WAGO733-338';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-09'                WHERE name='WAGO733-340';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_733-10'                WHERE name='WAGO733-342';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-02'                WHERE name='WAGO734-132';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-03'                WHERE name='WAGO734-133';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-04'                WHERE name='WAGO734-134';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-05'                WHERE name='WAGO734-135';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-06'                WHERE name='WAGO734-136';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-07'                WHERE name='WAGO734-137';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-08'                WHERE name='WAGO734-138';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-09'                WHERE name='WAGO734-139';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-10'                WHERE name='WAGO734-140';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-11'                WHERE name='WAGO734-142';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-12'                WHERE name='WAGO734-143';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-13'                WHERE name='WAGO734-146';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-14'                WHERE name='WAGO734-148';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-15'                WHERE name='WAGO734-150';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-WAGO_734-16'                WHERE name='WAGO734-154';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_WE612SV'                          WHERE name='WE612SV';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_WE622MV'                          WHERE name='WE622MV';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_WE632LV'                          WHERE name='WE632LV';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_WE642XV'                          WHERE name='WE642XV';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_PD_S'                             WHERE name='WED-S';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_PD_L'                             WHERE name='WEPD-L';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_PD_M'                             WHERE name='WEPD-M';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_PD_XL'                            WHERE name='WEPD-XL';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_PD_XXL'                           WHERE name='WEPD-XXL';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_PD4'                              WHERE name='WEPD4';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_PDM'                              WHERE name='WEPDM';";
            $updateSteps[] = "UPDATE footprints SET name='SPULE_WESV'                             WHERE name='WESV';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X03'         WHERE name='WS6G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X05'         WHERE name='WS10G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X07'         WHERE name='WS14G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X08'         WHERE name='WS16G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X10'         WHERE name='WS20G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X13'         WHERE name='WS26G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X17'         WHERE name='WS34G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X20'         WHERE name='WS40G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X25'         WHERE name='WS50G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-GERADE-RAHMEN_2X32'         WHERE name='WS64G';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X05'    WHERE name='WS10W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X07'    WHERE name='WS14W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X08'    WHERE name='WS16W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X10'    WHERE name='WS20W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X13'    WHERE name='WS26W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X17'    WHERE name='WS34W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X20'    WHERE name='WS40W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X25'    WHERE name='WS50W';";
            $updateSteps[] = "UPDATE footprints SET name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X32'    WHERE name='WS64W';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZOSZILLATOR_DIP8'                   WHERE name='XTAL-DIP8';";
            $updateSteps[] = "UPDATE footprints SET name='QUARZOSZILLATOR_DIP14'                  WHERE name='XTAL-DIP14';";
            $updateSteps[] = "UPDATE footprints SET name='KARTENSLOT_SD'                          WHERE name='YAMAICHI-FPS';";
            $updateSteps[] = "UPDATE footprints SET name=''                                       WHERE name='';";
            break;

          case 9:
            $updateSteps[] = "ALTER TABLE `parts` ADD `description` mediumtext AFTER `name`;";
            $updateSteps[] = "ALTER TABLE `parts` ADD `visible`     boolean NOT NULL AFTER `obsolete`;";
            break;

          case 10:
            $updateSteps[] = "ALTER TABLE `preise` CHANGE COLUMN `t` `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';";
            $updateSteps[] = "ALTER TABLE `preise` CHANGE COLUMN `ma` `manual_input` tinyint(1) NOT NULL DEFAULT '0';";
            $updateSteps[] = "ALTER TABLE `preise` CHANGE COLUMN `preis` `price` decimal(6,2) NOT NULL DEFAULT '0.00';";
            $updateSteps[] = "ALTER TABLE `preise` ADD `id_supplier` int(11) NOT NULL DEFAULT '0' AFTER `part_id`;";
            $updateSteps[] = "ALTER TABLE `preise` ADD `supplierpartnr` mediumtext NOT NULL AFTER `id_supplier`;";
            break;

          case 11:
            $updateSteps[] = "ALTER TABLE `footprints` ADD `filename` mediumtext AFTER `name`;";
            $updateSteps[] = "UPDATE footprints SET filename = name;";

            // footprints auf neues schema umbennenen
            $updateSteps[] = "UPDATE footprints SET name='2KBB-R' WHERE name='GLEICHRICHTER_2KBB-R';";
            $updateSteps[] = "UPDATE footprints SET name='2KBB' WHERE name='GLEICHRICHTER_2KBB';";
            $updateSteps[] = "UPDATE footprints SET name='2KBP' WHERE name='GLEICHRICHTER_2KBP';";
            $updateSteps[] = "UPDATE footprints SET name='1010' WHERE name='ELKO_SMD_1010';";
            $updateSteps[] = "UPDATE footprints SET name='1012' WHERE name='ELKO_SMD_1012';";
            $updateSteps[] = "UPDATE footprints SET name='1014' WHERE name='ELKO_SMD_1014';";
            $updateSteps[] = "UPDATE footprints SET name='1212' WHERE name='ELKO_SMD_1212';";
            $updateSteps[] = "UPDATE footprints SET name='1214' WHERE name='ELKO_SMD_1214';";
            $updateSteps[] = "UPDATE footprints SET name='0405' WHERE name='ELKO_SMD_0405';";
            $updateSteps[] = "UPDATE footprints SET name='0505' WHERE name='ELKO_SMD_0505';";
            $updateSteps[] = "UPDATE footprints SET name='0605' WHERE name='ELKO_SMD_0605';";
            $updateSteps[] = "UPDATE footprints SET name='0807' WHERE name='ELKO_SMD_0807';";
            $updateSteps[] = "UPDATE footprints SET name='0808' WHERE name='ELKO_SMD_0808';";
            $updateSteps[] = "UPDATE footprints SET name='0810' WHERE name='ELKO_SMD_0810';";
            $updateSteps[] = "UPDATE footprints SET name='0204' WHERE name='WIDERSTAND-KOHLE_0204';";
            $updateSteps[] = "UPDATE footprints SET name='0207' WHERE name='WIDERSTAND-KOHLE_0207';";
            $updateSteps[] = "UPDATE footprints SET name='0309' WHERE name='WIDERSTAND-KOHLE_0309';";
            $updateSteps[] = "UPDATE footprints SET name='0414' WHERE name='WIDERSTAND-KOHLE_0414';";
            $updateSteps[] = "UPDATE footprints SET name='0617' WHERE name='WIDERSTAND-KOHLE_0617';";
            $updateSteps[] = "UPDATE footprints SET name='0922' WHERE name='WIDERSTAND-KOHLE_0922';";
            $updateSteps[] = "UPDATE footprints SET name='3202' WHERE name='TRIMMER_3202';";
            $updateSteps[] = "UPDATE footprints SET name='64W' WHERE name='TRIMMER_64W';";
            $updateSteps[] = "UPDATE footprints SET name='64Y' WHERE name='TRIMMER_64Y';";
            $updateSteps[] = "UPDATE footprints SET name='72PT' WHERE name='TRIMMER_72-PT';";
            $updateSteps[] = "UPDATE footprints SET name='7SEG-1' WHERE name='7-SEGMENT_1-20CM';";
            $updateSteps[] = "UPDATE footprints SET name='7SEG-2' WHERE name='7-SEGMENT_2';";
            $updateSteps[] = "UPDATE footprints SET name='7SEG-3' WHERE name='7-SEGMENT_3-TOT4301';";
            $updateSteps[] = "UPDATE footprints SET name='7SEG-VQE-3' WHERE name='7-SEGMENT_2-VQE';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-147323-2' WHERE name='STIFTLEISTE-AMP_147323-02';";
            $updateSteps[] = "UPDATE footprints SET name='AK70-3-5' WHERE name='SCHRAUBKLEMME_AK700-3-5';";
            $updateSteps[] = "UPDATE footprints SET name='ABS13' WHERE name='QUARZ_ABRACON_ABS13';";
            $updateSteps[] = "UPDATE footprints SET name='ABM3B' WHERE name='RESONATOR-ABRACON_ABM3B';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S2' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-02';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S3' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-03';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S4' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-04';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S5' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-05';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S6' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-06';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S7' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-07';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S8' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-08';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S9' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-09';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14S10' WHERE name='STIFTLEISTE-AMP-GERADE_HE14-10';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R2' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-02';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R3' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-03';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HER4' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-04';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R5' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-05';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R6' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-06';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R7' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-07';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R8' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-08';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R9' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-09';";
            $updateSteps[] = "UPDATE footprints SET name='AMP-HE14R10' WHERE name='STIFTLEISTE-AMP-ABGEWINKELT_HE14-10';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S2' WHERE name='STIFTLEISTE-AMP_MT-02';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S3' WHERE name='STIFTLEISTE-AMP_MT-03';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S4' WHERE name='STIFTLEISTE-AMP_MT-04';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S5' WHERE name='STIFTLEISTE-AMP_MT-05';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S6' WHERE name='STIFTLEISTE-AMP_MT-06';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S7' WHERE name='STIFTLEISTE-AMP_MT-07';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S8' WHERE name='STIFTLEISTE-AMP_MT-08';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S9' WHERE name='STIFTLEISTE-AMP_MT-09';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S10' WHERE name='STIFTLEISTE-AMP_MT-10';";
            $updateSteps[] = "UPDATE footprints SET name='AMPMT-S12' WHERE name='STIFTLEISTE-AMP_MT-12';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-2' WHERE name='SCHRAUBKLEMME_RM508-02';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-3' WHERE name='SCHRAUBKLEMME_RM508-03';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-4' WHERE name='SCHRAUBKLEMME_RM508-04';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-5' WHERE name='SCHRAUBKLEMME_RM508-05';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-6' WHERE name='SCHRAUBKLEMME_RM508-06';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-7' WHERE name='SCHRAUBKLEMME_RM508-07';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-8' WHERE name='SCHRAUBKLEMME_RM508-08';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-9' WHERE name='SCHRAUBKLEMME_RM508-09';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-10' WHERE name='SCHRAUBKLEMME_RM508-10';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-11' WHERE name='SCHRAUBKLEMME_RM508-11';";
            $updateSteps[] = "UPDATE footprints SET name='ARK5MM-12' WHERE name='SCHRAUBKLEMME_RM508-12';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM2' WHERE name='SCHRAUBKLEMME_RM350-02';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM3' WHERE name='SCHRAUBKLEMME_RM350-03';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM4' WHERE name='SCHRAUBKLEMME_RM350-04';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM5' WHERE name='SCHRAUBKLEMME_RM350-05';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM6' WHERE name='SCHRAUBKLEMME_RM350-06';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM7' WHERE name='SCHRAUBKLEMME_RM350-07';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM8' WHERE name='SCHRAUBKLEMME_RM350-08';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM9' WHERE name='SCHRAUBKLEMME_RM350-09';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM10' WHERE name='SCHRAUBKLEMME_RM350-10';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM11' WHERE name='SCHRAUBKLEMME_RM350-11';";
            $updateSteps[] = "UPDATE footprints SET name='ARK350MM12' WHERE name='SCHRAUBKLEMME_RM350-12';";
            $updateSteps[] = "UPDATE footprints SET name='B25V' WHERE name='TRIMMER_B25V';";
            $updateSteps[] = "UPDATE footprints SET name='B25X' WHERE name='TRIMMER_B25X';";
            $updateSteps[] = "UPDATE footprints SET name='B35N61' WHERE name='VERBINDER_COAX-B35N61';";
            $updateSteps[] = "UPDATE footprints SET name='B3F10XX1' WHERE name='TASTER_B3F-10XX1';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X5' WHERE name='BUCHSENLEISTE-GERADE_1X05';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X6' WHERE name='BUCHSENLEISTE-GERADE_1X06';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X7' WHERE name='BUCHSENLEISTE-GERADE_1X07';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X8' WHERE name='BUCHSENLEISTE-GERADE_1X08';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X9' WHERE name='BUCHSENLEISTE-GERADE_1X09';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X10' WHERE name='BUCHSENLEISTE-GERADE_1X10';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X12' WHERE name='BUCHSENLEISTE-GERADE_1X12';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X13' WHERE name='BUCHSENLEISTE-GERADE_1X13';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X15' WHERE name='BUCHSENLEISTE-GERADE_1X15';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X17' WHERE name='BUCHSENLEISTE-GERADE_1X17';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X18' WHERE name='BUCHSENLEISTE-GERADE_1X18';";
            $updateSteps[] = "UPDATE footprints SET name='BL1X20' WHERE name='BUCHSENLEISTE-GERADE_1X20';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X5' WHERE name='BUCHSENLEISTE-GERADE_2X05';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X6' WHERE name='BUCHSENLEISTE-GERADE_2X06';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X7' WHERE name='BUCHSENLEISTE-GERADE_2X07';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X8' WHERE name='BUCHSENLEISTE-GERADE_2X08';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X9' WHERE name='BUCHSENLEISTE-GERADE_2X09';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X10' WHERE name='BUCHSENLEISTE-GERADE_2X10';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X12' WHERE name='BUCHSENLEISTE-GERADE_2X12';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X13' WHERE name='BUCHSENLEISTE-GERADE_2X13';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X15' WHERE name='BUCHSENLEISTE-GERADE_2X15';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X17' WHERE name='BUCHSENLEISTE-GERADE_2X17';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X18' WHERE name='BUCHSENLEISTE-GERADE_2X18';";
            $updateSteps[] = "UPDATE footprints SET name='BL2X20' WHERE name='BUCHSENLEISTE-GERADE_2X20';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X5' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X05';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X6' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X06';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X7' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X07';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X8' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X08';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X9' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X09';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X10' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X10';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X12' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X12';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X13' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X13';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X15' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X15';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X17' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X17';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X18' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X18';";
            $updateSteps[] = "UPDATE footprints SET name='BLF1X20' WHERE name='BUCHSENLEISTE-GERADE-FLACH_1X20';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X5' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X05';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X6' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X06';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X7' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X07';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X8' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X08';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X9' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X09';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X10' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X10';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X12' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X12';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X13' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X13';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X15' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X15';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X17' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X17';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X18' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X18';";
            $updateSteps[] = "UPDATE footprints SET name='BLF2X20' WHERE name='BUCHSENLEISTE-GERADE-FLACH_2X20';";
            $updateSteps[] = "UPDATE footprints SET name='BNC' WHERE name='VERBINDER_BNC-W';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X5' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X05';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X6' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X06';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X7' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X07';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X8' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X08';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X9' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X09';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X10' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X10';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X12' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X12';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X13' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X13';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X15' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X15';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X17' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X17';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X18' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X18';";
            $updateSteps[] = "UPDATE footprints SET name='BLW2X20' WHERE name='BUCHSENLEISTE-ABGEWINKELT_2X20';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X2' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X02';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X5' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X05';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X6' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X06';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X7' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X07';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X8' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X08';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X9' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X09';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X10' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X10';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X12' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X12';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X13' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X13';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X15' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X15';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X17' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X17';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X18' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X18';";
            $updateSteps[] = "UPDATE footprints SET name='BLW1X20' WHERE name='BUCHSENLEISTE-ABGEWINKELT_1X20';";
            $updateSteps[] = "UPDATE footprints SET name='CB417' WHERE name='DIODE_CB417';";
            $updateSteps[] = "UPDATE footprints SET name='CB429' WHERE name='DIODE_CB429';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-0402' WHERE name='KERKO-SMD_0402';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-0603' WHERE name='KERKO-SMD_0603';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-0805' WHERE name='KERKO-SMD_0805';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-1206' WHERE name='KERKO-SMD_1206';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-1210' WHERE name='KERKO-SMD_1210';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-1812' WHERE name='KERKO-SMD_1812';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-1825' WHERE name='KERKO-SMD_1825';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-2220' WHERE name='KERKO-SMD_2220';";
            $updateSteps[] = "UPDATE footprints SET name='CAP-4x0603' WHERE name='KERKO-SMD-ARRAY_4X0603-0612';";
            $updateSteps[] = "UPDATE footprints SET name='BUxx' WHERE name='BUCHSE_DCPOWERCONNECTOR';";
            $updateSteps[] = "UPDATE footprints SET name='BPC10H' WHERE name='WIDERSTAND-DICKSCHICHT_BPC10H';";
            $updateSteps[] = "UPDATE footprints SET name='BPC10V' WHERE name='WIDERSTAND-DICKSCHICHT_BPC10V';";
            $updateSteps[] = "UPDATE footprints SET name='BPC3H' WHERE name='WIDERSTAND-DICKSCHICHT_BPC3H';";
            $updateSteps[] = "UPDATE footprints SET name='BPC3V' WHERE name='WIDERSTAND-DICKSCHICHT_BPC3V';";
            $updateSteps[] = "UPDATE footprints SET name='BPC5H' WHERE name='WIDERSTAND-DICKSCHICHT_BPC5H';";
            $updateSteps[] = "UPDATE footprints SET name='BPC5V' WHERE name='WIDERSTAND-DICKSCHICHT_BPC5V';";
            $updateSteps[] = "UPDATE footprints SET name='BPC7H' WHERE name='WIDERSTAND-DICKSCHICHT_BPC7H';";
            $updateSteps[] = "UPDATE footprints SET name='BPC7V' WHERE name='WIDERSTAND-DICKSCHICHT_BPC7V';";
            $updateSteps[] = "UPDATE footprints SET name='DFS' WHERE name='IC_DFS';";
            $updateSteps[] = "UPDATE footprints SET name='CTS-A-15' WHERE name='KONDENSATOR_CTS_A_15MM';";
            $updateSteps[] = "UPDATE footprints SET name='CTS-B-20' WHERE name='KONDENSATOR_CTS_B_20MM';";
            $updateSteps[] = "UPDATE footprints SET name='CTS-C-25' WHERE name='KONDENSATOR_CTS_C_25MM';";
            $updateSteps[] = "UPDATE footprints SET name='CTS-D-30' WHERE name='KONDENSATOR_CTS_D_30MM';";
            $updateSteps[] = "UPDATE footprints SET name='CSTCE-GA' WHERE name='RESONATOR-MURATA_CSTCE-G-A';";
            $updateSteps[] = "UPDATE footprints SET name='CF-CON' WHERE name='KARTENSLOT_CF-1';";
            $updateSteps[] = "UPDATE footprints SET name='CFPT125' WHERE name='QUARZOSZILLATOR_CFPT-125';";
            $updateSteps[] = "UPDATE footprints SET name='CFPT-126' WHERE name='QUARZOSZILLATOR_CFPT-126';";
            $updateSteps[] = "UPDATE footprints SET name='CFPT37' WHERE name='QUARZOSZILLATOR_CFPT-37';";
            $updateSteps[] = "UPDATE footprints SET name='CENTRONICS-F14' WHERE name='BUCHSE-CENTRONICS_F14';";
            $updateSteps[] = "UPDATE footprints SET name='CENTRONICS-F24' WHERE name='BUCHSE-CENTRONICS_F24';";
            $updateSteps[] = "UPDATE footprints SET name='CENTRONICS-F36' WHERE name='BUCHSE-CENTRONICS_F36';";
            $updateSteps[] = "UPDATE footprints SET name='CENTRONICS-F50' WHERE name='BUCHSE-CENTRONICS_F50';";
            $updateSteps[] = "UPDATE footprints SET name='CELDUC-SIL' WHERE name='REEDRELAIS_SIL';";
            $updateSteps[] = "UPDATE footprints SET name='CELDUC-SK-ABD' WHERE name='RELAIS_CELDUC-SK-ABD';";
            $updateSteps[] = "UPDATE footprints SET name='CELDUC-SK-AL' WHERE name='RELAIS_CELDUC-SK-AL';";
            $updateSteps[] = "UPDATE footprints SET name='CELDUC-SK-L' WHERE name='RELAIS_CELDUC-SK-L';";
            $updateSteps[] = "UPDATE footprints SET name='DIN41617-13' WHERE name='VERBINDER_DIN41617-13';";
            $updateSteps[] = "UPDATE footprints SET name='DIN41617-21' WHERE name='VERBINDER_DIN41617-21';";
            $updateSteps[] = "UPDATE footprints SET name='DIN41617-31' WHERE name='VERBINDER_DIN41617-31';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB3S' WHERE name='BUCHSE-DIN_MAB_3S';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB4' WHERE name='BUCHSE-DIN_MAB_4';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB5' WHERE name='BUCHSE-DIN_MAB_5';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB5S' WHERE name='BUCHSE-DIN_MAB_5S';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB5SV' WHERE name='BUCHSE-DIN_MAB_5SV';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB6' WHERE name='BUCHSE-DIN_MAB_6';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB6V' WHERE name='BUCHSE-DIN_MAB_6V';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB7S' WHERE name='BUCHSE-DIN_MAB_7S';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB7SV' WHERE name='BUCHSE-DIN_MAB_7SV';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB8S' WHERE name='BUCHSE-DIN_MAB_8S';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB8SN' WHERE name='BUCHSE-DIN_MAB_8SN';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB8SNV' WHERE name='BUCHSE-DIN_MAB_8SNV';";
            $updateSteps[] = "UPDATE footprints SET name='DINMAB8SV' WHERE name='BUCHSE-DIN_MAB_8SV';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE-SMA' WHERE name='DIODE_SMA';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE-SMB' WHERE name='DIODE_SMB';";
            $updateSteps[] = "UPDATE footprints SET name='DIODE-SMC' WHERE name='DIODE_SMC';";
            $updateSteps[] = "UPDATE footprints SET name='DIP14' WHERE name='IC_DIP14';";
            $updateSteps[] = "UPDATE footprints SET name='DIP14A4' WHERE name='IC_DIP14A4';";
            $updateSteps[] = "UPDATE footprints SET name='DIP14A8' WHERE name='IC_DIP14A8';";
            $updateSteps[] = "UPDATE footprints SET name='DIP16' WHERE name='IC_DIP16';";
            $updateSteps[] = "UPDATE footprints SET name='DIP16A4' WHERE name='IC_DIP16A4';";
            $updateSteps[] = "UPDATE footprints SET name='DIP16A8' WHERE name='IC_DIP16A8';";
            $updateSteps[] = "UPDATE footprints SET name='DIP18' WHERE name='IC_DIP18';";
            $updateSteps[] = "UPDATE footprints SET name='DIP2' WHERE name='IC_DIP02';";
            $updateSteps[] = "UPDATE footprints SET name='DIP4' WHERE name='IC_DIP04';";
            $updateSteps[] = "UPDATE footprints SET name='DIP6' WHERE name='IC_DIP06';";
            $updateSteps[] = "UPDATE footprints SET name='DIP8' WHERE name='IC_DIP08';";
            $updateSteps[] = "UPDATE footprints SET name='DIP8A4' WHERE name='IC_DIP08A4';";
            $updateSteps[] = "UPDATE footprints SET name='DIP20' WHERE name='IC_DIP20';";
            $updateSteps[] = "UPDATE footprints SET name='DIP22' WHERE name='IC_DIP22';";
            $updateSteps[] = "UPDATE footprints SET name='DIP24' WHERE name='IC_DIP24';";
            $updateSteps[] = "UPDATE footprints SET name='DIP24A12' WHERE name='IC_DIP24A12';";
            $updateSteps[] = "UPDATE footprints SET name='DIP24W' WHERE name='IC_DIP24W';";
            $updateSteps[] = "UPDATE footprints SET name='DIP28' WHERE name='IC_DIP28';";
            $updateSteps[] = "UPDATE footprints SET name='DIP28W' WHERE name='IC_DIP28W';";
            $updateSteps[] = "UPDATE footprints SET name='DIP32' WHERE name='IC_DIP32-3';";
            $updateSteps[] = "UPDATE footprints SET name='DIP32W' WHERE name='IC_DIP32W';";
            $updateSteps[] = "UPDATE footprints SET name='DIP36W' WHERE name='IC_DIP36W';";
            $updateSteps[] = "UPDATE footprints SET name='DIP40W' WHERE name='IC_DIP40W';";
            $updateSteps[] = "UPDATE footprints SET name='DIP42W' WHERE name='IC_DIP42W';";
            $updateSteps[] = "UPDATE footprints SET name='DIP48W' WHERE name='IC_DIP48W';";
            $updateSteps[] = "UPDATE footprints SET name='DIP4S' WHERE name='GLEICHRICHTER_DIP4S';";
            $updateSteps[] = "UPDATE footprints SET name='DIP52W' WHERE name='IC_DIP52W';";
            $updateSteps[] = "UPDATE footprints SET name='DPAK369C' WHERE name='IC_DPAK-369C';";
            $updateSteps[] = "UPDATE footprints SET name='DO14' WHERE name='DIODE_DO14';";
            $updateSteps[] = "UPDATE footprints SET name='DO15' WHERE name='DIODE_DO15';";
            $updateSteps[] = "UPDATE footprints SET name='DO16' WHERE name='DIODE_DO16';";
            $updateSteps[] = "UPDATE footprints SET name='DO201' WHERE name='DIODE_DO201';";
            $updateSteps[] = "UPDATE footprints SET name='DO204' WHERE name='DIODE_DO204AC';";
            $updateSteps[] = "UPDATE footprints SET name='DO214AA' WHERE name='DIODE_DO214AA';";
            $updateSteps[] = "UPDATE footprints SET name='DO214AB' WHERE name='DIODE_DO214AB';";
            $updateSteps[] = "UPDATE footprints SET name='DO214AC' WHERE name='DIODE_DO214AC';";
            $updateSteps[] = "UPDATE footprints SET name='DO27' WHERE name='DIODE_DO27';";
            $updateSteps[] = "UPDATE footprints SET name='DO32' WHERE name='DIODE_DO32';";
            $updateSteps[] = "UPDATE footprints SET name='DO34' WHERE name='DIODE_DO34';";
            $updateSteps[] = "UPDATE footprints SET name='DO35' WHERE name='DIODE_DO35';";
            $updateSteps[] = "UPDATE footprints SET name='DO39' WHERE name='DIODE_DO39';";
            $updateSteps[] = "UPDATE footprints SET name='DO41' WHERE name='DIODE_DO41';";
            $updateSteps[] = "UPDATE footprints SET name='DO7' WHERE name='DIODE_DO7';";
            $updateSteps[] = "UPDATE footprints SET name='DK1AL2' WHERE name='RELAIS_DK1A-L2-5V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F9' WHERE name='SUB-D-PLATINENMONTAGE_W-09';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F15' WHERE name='SUB-D-PLATINENMONTAGE_W-15';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F25' WHERE name='SUB-D-PLATINENMONTAGE_W-25';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F37' WHERE name='SUB-D-PLATINENMONTAGE_W-37';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F9D' WHERE name='SUB-D_W-09';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F9DV' WHERE name='SUB-D_W-09V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F15D' WHERE name='SUB-D_W-15';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F15DV' WHERE name='SUB-D_W-15V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F25D' WHERE name='SUB-D_W-25';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F25DV' WHERE name='SUB-D_W-25V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F37D' WHERE name='SUB-D_W-37';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-F37DV' WHERE name='SUB-D_W-37V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M9' WHERE name='SUB-D-PLATINENMONTAGE_M-09';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M15' WHERE name='SUB-D-PLATINENMONTAGE_M-15';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M25' WHERE name='SUB-D-PLATINENMONTAGE_M-25';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M37' WHERE name='SUB-D-PLATINENMONTAGE_M-37';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M9D' WHERE name='SUB-D_M-09';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M9DV' WHERE name='SUB-D_M-09V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M15D' WHERE name='SUB-D_M-15';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M15DV' WHERE name='SUB-D_M-15V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M25D' WHERE name='SUB-D_M-25';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M25DV' WHERE name='SUB-D_M-25V';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M37D' WHERE name='SUB-D_M-37';";
            $updateSteps[] = "UPDATE footprints SET name='DSUB-M37DV' WHERE name='SUB-D_M-37V';";
            $updateSteps[] = "UPDATE footprints SET name='ED16' WHERE name='SPULE_ED16';";
            $updateSteps[] = "UPDATE footprints SET name='ED22' WHERE name='SPULE_ED22';";
            $updateSteps[] = "UPDATE footprints SET name='ED26' WHERE name='SPULE_ED26';";
            $updateSteps[] = "UPDATE footprints SET name='ED38' WHERE name='SPULE_ED38';";
            $updateSteps[] = "UPDATE footprints SET name='ED43' WHERE name='SPULE_ED43';";
            $updateSteps[] = "UPDATE footprints SET name='EF12' WHERE name='SPULE_EF12';";
            $updateSteps[] = "UPDATE footprints SET name='EF16' WHERE name='SPULE_EF16';";
            $updateSteps[] = "UPDATE footprints SET name='EUROCARD64M2L' WHERE name='VERBINDER_EUROCARD-64M-2-L';";
            $updateSteps[] = "UPDATE footprints SET name='EUROCARD96M3L' WHERE name='VERBINDER_EUROCARD-96M-3-L';";
            $updateSteps[] = "UPDATE footprints SET name='EVQVX-11MM' WHERE name='DREHSCHALTER-PANASONIC_EVQVX-11MM';";
            $updateSteps[] = "UPDATE footprints SET name='EVQVX-9MM' WHERE name='DREHSCHALTER-PANASONIC_EVQVX-9MM';";
            $updateSteps[] = "UPDATE footprints SET name='F126' WHERE name='DIODE_F126';";
            $updateSteps[] = "UPDATE footprints SET name='FASTON-V' WHERE name='LOETOESE_FASTON-V';";
            $updateSteps[] = "UPDATE footprints SET name='FB100' WHERE name='GLEICHRICHTER_FB100';";
            $updateSteps[] = "UPDATE footprints SET name='FB15' WHERE name='GLEICHRICHTER_FB15';";
            $updateSteps[] = "UPDATE footprints SET name='FB15A' WHERE name='GLEICHRICHTER_FB15A';";
            $updateSteps[] = "UPDATE footprints SET name='FB32' WHERE name='GLEICHRICHTER_FB32';";
            $updateSteps[] = "UPDATE footprints SET name='FPCON65' WHERE name='VERBINDER_FPCON65';";
            $updateSteps[] = "UPDATE footprints SET name='FUSE1' WHERE name='SICHERUNGSHALTER_Laengs';";
            $updateSteps[] = "UPDATE footprints SET name='FUSE2' WHERE name='SICHERUNGSHALTER_Quer';";
            $updateSteps[] = "UPDATE footprints SET name='GP20' WHERE name='DIODE_GP20';";
            $updateSteps[] = "UPDATE footprints SET name='G2RL1' WHERE name='RELAIS_G2RL-1';";
            $updateSteps[] = "UPDATE footprints SET name='G2RL1A' WHERE name='RELAIS_G2RL-1A';";
            $updateSteps[] = "UPDATE footprints SET name='G2RL1AE' WHERE name='RELAIS_G2RL-1A-E';";
            $updateSteps[] = "UPDATE footprints SET name='G2RL1E' WHERE name='RELAIS_G2RL-1-E';";
            $updateSteps[] = "UPDATE footprints SET name='G2RL2' WHERE name='RELAIS_G2RL-2';";
            $updateSteps[] = "UPDATE footprints SET name='G2RL2A' WHERE name='RELAIS_G2RL-2A';";
            $updateSteps[] = "UPDATE footprints SET name='G6D' WHERE name='RELAIS_G6D';";
            $updateSteps[] = "UPDATE footprints SET name='GBU4' WHERE name='GLEICHRICHTER_GBU4';";
            $updateSteps[] = "UPDATE footprints SET name='JJM1A' WHERE name='RELAIS_JJM-1A';";
            $updateSteps[] = "UPDATE footprints SET name='JJM1C' WHERE name='RELAIS_JJM-1C';";
            $updateSteps[] = "UPDATE footprints SET name='JJM2W' WHERE name='RELAIS_JJM-2W';";
            $updateSteps[] = "UPDATE footprints SET name='HC18' WHERE name='QUARZ_025MM';";
            $updateSteps[] = "UPDATE footprints SET name='HC49' WHERE name='QUARZ_HC49';";
            $updateSteps[] = "UPDATE footprints SET name='HC49U' WHERE name='QUARZ_HC49-4H';";
            $updateSteps[] = "UPDATE footprints SET name='HS1-25GY-50' WHERE name='KUEHLKOERPER_VIEWCOM_HS-1-25GY_50';";
            $updateSteps[] = "UPDATE footprints SET name='L5MM-S' WHERE name='SPULE_5MM-S';";
            $updateSteps[] = "UPDATE footprints SET name='KBU46x8' WHERE name='GLEICHRICHTER_KBU-4-6-8';";
            $updateSteps[] = "UPDATE footprints SET name='KL195-25' WHERE name='KUEHLKOERPER_KL195-25';";
            $updateSteps[] = "UPDATE footprints SET name='KL195-38' WHERE name='KUEHLKOERPER_KL195-38';";
            $updateSteps[] = "UPDATE footprints SET name='KL195-50' WHERE name='KUEHLKOERPER_KL195-50';";
            $updateSteps[] = "UPDATE footprints SET name='KL195-63' WHERE name='KUEHLKOERPER_KL195-63';";
            $updateSteps[] = "UPDATE footprints SET name='LED-0603' WHERE name='LED-ROT_0603';";
            $updateSteps[] = "UPDATE footprints SET name='LED-0805' WHERE name='LED-ROT_0805';";
            $updateSteps[] = "UPDATE footprints SET name='LED-1206' WHERE name='LED-ROT_1206';";
            $updateSteps[] = "UPDATE footprints SET name='LED-3' WHERE name='LED-ROT_3MM';";
            $updateSteps[] = "UPDATE footprints SET name='LED-5' WHERE name='LED-ROT_5MM';";
            $updateSteps[] = "UPDATE footprints SET name='LSP10' WHERE name='LOETOESE_LSP';";
            $updateSteps[] = "UPDATE footprints SET name='LSH125' WHERE name='TASTER_LSH125';";
            $updateSteps[] = "UPDATE footprints SET name='LSH43' WHERE name='TASTER_LSH43';";
            $updateSteps[] = "UPDATE footprints SET name='LSH50' WHERE name='TASTER_LSH50';";
            $updateSteps[] = "UPDATE footprints SET name='LSH70' WHERE name='TASTER_LSH70';";
            $updateSteps[] = "UPDATE footprints SET name='LSH80' WHERE name='TASTER_LSH80';";
            $updateSteps[] = "UPDATE footprints SET name='LSH95' WHERE name='TASTER_LSH95';";
            $updateSteps[] = "UPDATE footprints SET name='LQFP64' WHERE name='IC_LQFP64';";
            $updateSteps[] = "UPDATE footprints SET name='LQFP48' WHERE name='IC_LQFP48';";
            $updateSteps[] = "UPDATE footprints SET name='LMI-L115-2' WHERE name='VERBINDER_LMI-L115-02';";
            $updateSteps[] = "UPDATE footprints SET name='LMI-L115-3' WHERE name='VERBINDER_LMI-L115-03';";
            $updateSteps[] = "UPDATE footprints SET name='LMI-L115-5' WHERE name='VERBINDER_LMI-L115-05';";
            $updateSteps[] = "UPDATE footprints SET name='LMI-L115-10' WHERE name='VERBINDER_LMI-L115-10';";
            $updateSteps[] = "UPDATE footprints SET name='LMI-L115-20' WHERE name='VERBINDER_LMI-L115-20';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926310-1' WHERE name='BUCHSE-MATNLOK_9263_10_1';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926310-2' WHERE name='BUCHSE-MATNLOK_9263_10_2';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926310-3' WHERE name='BUCHSE-MATNLOK_9263_10_3';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926310-4' WHERE name='BUCHSE-MATNLOK_9263_10_4';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926310-5' WHERE name='BUCHSE-MATNLOK_9263_10_5';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926311-1' WHERE name='BUCHSE-MATNLOK_9263_11_1';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926311-2' WHERE name='BUCHSE-MATNLOK_9263_11_2';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926311-3' WHERE name='BUCHSE-MATNLOK_9263_11_3';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926311-4' WHERE name='BUCHSE-MATNLOK_9263_11_4';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926311-5' WHERE name='BUCHSE-MATNLOK_9263_11_5';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926312-1' WHERE name='BUCHSE-MATNLOK_9263_12_1';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926312-2' WHERE name='BUCHSE-MATNLOK_9263_12_2';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926312-3' WHERE name='BUCHSE-MATNLOK_9263_12_3';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926312-4' WHERE name='BUCHSE-MATNLOK_9263_12_4';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926312-5' WHERE name='BUCHSE-MATNLOK_9263_12_5';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926313-1' WHERE name='BUCHSE-MATNLOK_9263_13_1';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926313-2' WHERE name='BUCHSE-MATNLOK_9263_13_2';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926313-3' WHERE name='BUCHSE-MATNLOK_9263_13_3';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926313-4' WHERE name='BUCHSE-MATNLOK_9263_13_4';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926313-5' WHERE name='BUCHSE-MATNLOK_9263_13_5';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926314-1' WHERE name='BUCHSE-MATNLOK_9263_14_1';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926314-2' WHERE name='BUCHSE-MATNLOK_9263_14_2';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926314-3' WHERE name='BUCHSE-MATNLOK_9263_14_3';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926314-4' WHERE name='BUCHSE-MATNLOK_9263_14_4';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926314-5' WHERE name='BUCHSE-MATNLOK_9263_14_5';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926315-1' WHERE name='BUCHSE-MATNLOK_9263_15_1';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926315-2' WHERE name='BUCHSE-MATNLOK_9263_15_2';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926315-3' WHERE name='BUCHSE-MATNLOK_9263_15_3';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926315-4' WHERE name='BUCHSE-MATNLOK_9263_15_4';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926315-5' WHERE name='BUCHSE-MATNLOK_9263_15_5';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926316-1' WHERE name='BUCHSE-MATNLOK_9263_16_1';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926316-2' WHERE name='BUCHSE-MATNLOK_9263_16_2';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926316-3' WHERE name='BUCHSE-MATNLOK_9263_16_3';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926316-4' WHERE name='BUCHSE-MATNLOK_9263_16_4';";
            $updateSteps[] = "UPDATE footprints SET name='MATNLOK-926316-5' WHERE name='BUCHSE-MATNLOK_9263_16_5';";
            $updateSteps[] = "UPDATE footprints SET name='MELF' WHERE name='DIODE_MELF';";
            $updateSteps[] = "UPDATE footprints SET name='MB2S' WHERE name='IC_MBxS';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH4F' WHERE name='BUCHSE-MICROMATCH_FEMALE-04';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH6F' WHERE name='BUCHSE-MICROMATCH_FEMALE-06';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH8F' WHERE name='BUCHSE-MICROMATCH_FEMALE-08';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH10F' WHERE name='BUCHSE-MICROMATCH_FEMALE-10';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH12F' WHERE name='BUCHSE-MICROMATCH_FEMALE-12';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH14F' WHERE name='BUCHSE-MICROMATCH_FEMALE-14';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH16F' WHERE name='BUCHSE-MICROMATCH_FEMALE-16';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH18F' WHERE name='BUCHSE-MICROMATCH_FEMALE-18';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH20F' WHERE name='BUCHSE-MICROMATCH_FEMALE-20';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH4M' WHERE name='BUCHSE-MICROMATCH_MALE-04';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH6M' WHERE name='BUCHSE-MICROMATCH_MALE-06';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH8M' WHERE name='BUCHSE-MICROMATCH_MALE-08';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH10M' WHERE name='BUCHSE-MICROMATCH_MALE-10';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH12M' WHERE name='BUCHSE-MICROMATCH_MALE-12';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH14M' WHERE name='BUCHSE-MICROMATCH_MALE-14';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH16M' WHERE name='BUCHSE-MICROMATCH_MALE-16';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH18M' WHERE name='BUCHSE-MICROMATCH_MALE-18';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH20M' WHERE name='BUCHSE-MICROMATCH_MALE-20';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD4F' WHERE name='BUCHSE-MICROMATCH_SMD-04';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD6F' WHERE name='BUCHSE-MICROMATCH_SMD-06';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD8F' WHERE name='BUCHSE-MICROMATCH_SMD-08';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD10F' WHERE name='BUCHSE-MICROMATCH_SMD-10';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD12F' WHERE name='BUCHSE-MICROMATCH_SMD-12';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD14F' WHERE name='BUCHSE-MICROMATCH_SMD-14';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD16F' WHERE name='BUCHSE-MICROMATCH_SMD-16';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD18F' WHERE name='BUCHSE-MICROMATCH_SMD-18';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMATCH-SMD20F' WHERE name='BUCHSE-MICROMATCH_SMD-20';";
            $updateSteps[] = "UPDATE footprints SET name='MM505' WHERE name='QUARZ_MM505';";
            $updateSteps[] = "UPDATE footprints SET name='MLF28' WHERE name='IC_MLF28';";
            $updateSteps[] = "UPDATE footprints SET name='MLF32' WHERE name='IC_MLF32';";
            $updateSteps[] = "UPDATE footprints SET name='MLF44' WHERE name='IC_MLF44';";
            $updateSteps[] = "UPDATE footprints SET name='MLF64' WHERE name='IC_MLF64';";
            $updateSteps[] = "UPDATE footprints SET name='MINITOPLED' WHERE name='LED_MINITOP';";
            $updateSteps[] = "UPDATE footprints SET name='MINIMELF' WHERE name='DIODE_MINIMELF';";
            $updateSteps[] = "UPDATE footprints SET name='MICROMELF' WHERE name='DIODE_MICROMELF';";
            $updateSteps[] = "UPDATE footprints SET name='MODULAR-RJ11' WHERE name='BUCHSE_RJ11';";
            $updateSteps[] = "UPDATE footprints SET name='MODULAR-RJ11S' WHERE name='BUCHSE_RJ11-SHLD';";
            $updateSteps[] = "UPDATE footprints SET name='MODULAR-RJ12' WHERE name='BUCHSE_RJ12';";
            $updateSteps[] = "UPDATE footprints SET name='MODULAR-RJ12S' WHERE name='BUCHSE_RJ12-SHLD';";
            $updateSteps[] = "UPDATE footprints SET name='MODULAR-RJ45' WHERE name='BUCHSE_RJ45';";
            $updateSteps[] = "UPDATE footprints SET name='MODULAR-RJ45S' WHERE name='BUCHSE_RJ45-SHLD';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL2G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-02';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL3G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-03';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL4G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-04';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL5G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-05';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL6G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-06';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL7G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-07';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL8G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-08';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL9G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-09';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL10G' WHERE name='STIFTLEISTE-MOLEX-GERADE_PSL-10';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL2W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-02';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL3W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-03';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL4W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-04';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL5W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-05';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL6W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-06';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL7W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-07';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL8W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-08';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL9W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-09';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX-PSL10W' WHERE name='STIFTLEISTE-MOLEX-ABGEWINKELT_PSL-10';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-3' WHERE name='STIFTLEISTE-MOLEX_53047-03';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-4' WHERE name='STIFTLEISTE-MOLEX_53047-04';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-5' WHERE name='STIFTLEISTE-MOLEX_53047-05';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-6' WHERE name='STIFTLEISTE-MOLEX_53047-06';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-7' WHERE name='STIFTLEISTE-MOLEX_53047-07';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-8' WHERE name='STIFTLEISTE-MOLEX_53047-08';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-9' WHERE name='STIFTLEISTE-MOLEX_53047-09';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-10' WHERE name='STIFTLEISTE-MOLEX_53047-10';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-11' WHERE name='STIFTLEISTE-MOLEX_53047-11';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-12' WHERE name='STIFTLEISTE-MOLEX_53047-12';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-13' WHERE name='STIFTLEISTE-MOLEX_53047-13';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-14' WHERE name='STIFTLEISTE-MOLEX_53047-14';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53047-15' WHERE name='STIFTLEISTE-MOLEX_53047-15';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-2' WHERE name='STIFTLEISTE-MOLEX_53048-02';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-3' WHERE name='STIFTLEISTE-MOLEX_53048-03';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-4' WHERE name='STIFTLEISTE-MOLEX_53048-04';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-5' WHERE name='STIFTLEISTE-MOLEX_53048-05';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-6' WHERE name='STIFTLEISTE-MOLEX_53048-06';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-7' WHERE name='STIFTLEISTE-MOLEX_53048-07';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-8' WHERE name='STIFTLEISTE-MOLEX_53048-08';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-9' WHERE name='STIFTLEISTE-MOLEX_53048-09';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-10' WHERE name='STIFTLEISTE-MOLEX_53048-10';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-11' WHERE name='STIFTLEISTE-MOLEX_53048-11';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-12' WHERE name='STIFTLEISTE-MOLEX_53048-12';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-13' WHERE name='STIFTLEISTE-MOLEX_53048-13';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-14' WHERE name='STIFTLEISTE-MOLEX_53048-14';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53048-15' WHERE name='STIFTLEISTE-MOLEX_53048-15';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-2' WHERE name='STIFTLEISTE-MOLEX_53261-02';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-3' WHERE name='STIFTLEISTE-MOLEX_53261-03';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-4' WHERE name='STIFTLEISTE-MOLEX_53261-04';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-5' WHERE name='STIFTLEISTE-MOLEX_53261-05';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-6' WHERE name='STIFTLEISTE-MOLEX_53261-06';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-7' WHERE name='STIFTLEISTE-MOLEX_53261-07';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-8' WHERE name='STIFTLEISTE-MOLEX_53261-08';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-9' WHERE name='STIFTLEISTE-MOLEX_53261-09';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-10' WHERE name='STIFTLEISTE-MOLEX_53261-10';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-11' WHERE name='STIFTLEISTE-MOLEX_53261-11';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-12' WHERE name='STIFTLEISTE-MOLEX_53261-12';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-13' WHERE name='STIFTLEISTE-MOLEX_53261-13';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-14' WHERE name='STIFTLEISTE-MOLEX_53261-14';";
            $updateSteps[] = "UPDATE footprints SET name='MOLEX53261-15' WHERE name='STIFTLEISTE-MOLEX_53261-15';";
            $updateSteps[] = "UPDATE footprints SET name='MULTIWATT15' WHERE name='IC_MULTIWATT15';";
            $updateSteps[] = "UPDATE footprints SET name='MURATA-2012-LQH3C' WHERE name='SPULE_MURATA_2012-LQH3C';";
            $updateSteps[] = "UPDATE footprints SET name='MURATA-CSTCC-G-A' WHERE name='RESONATOR-MURATA_CSTCC-G-A';";
            $updateSteps[] = "UPDATE footprints SET name='MURATA-NFE61P' WHERE name='EMV-MURATA_NFE61P';";
            $updateSteps[] = "UPDATE footprints SET name='MSOP10' WHERE name='IC_MSOP10';";
            $updateSteps[] = "UPDATE footprints SET name='PHONE-JACK' WHERE name='BUCHSE_PHONE-JACK';";
            $updateSteps[] = "UPDATE footprints SET name='PDLD' WHERE name='LASER_PDLD-PIGTAIL';";
            $updateSteps[] = "UPDATE footprints SET name='PCPWR514M' WHERE name='STIFTLEISTE_PCPWR514M';";
            $updateSteps[] = "UPDATE footprints SET name='PBG90' WHERE name='RELAIS_PB-G90-1A-1';";
            $updateSteps[] = "UPDATE footprints SET name='P600' WHERE name='DIODE_P600';";
            $updateSteps[] = "UPDATE footprints SET name='NME-S' WHERE name='SCHALTREGLER_NME-S';";
            $updateSteps[] = "UPDATE footprints SET name='NMA-D' WHERE name='SCHALTREGLER_NMA-D';";
            $updateSteps[] = "UPDATE footprints SET name='MYRRA-EI30' WHERE name='TRAFO-MYRRA_30-2';";
            $updateSteps[] = "UPDATE footprints SET name='MYRRA-EI38' WHERE name='TRAFO-MYRRA_38-2';";
            $updateSteps[] = "UPDATE footprints SET name='MYRRA-EI48' WHERE name='TRAFO-MYRRA_48-2';";
            $updateSteps[] = "UPDATE footprints SET name='MYRRA-EI66' WHERE name='TRAFO-MYRRA_66-2';";
            $updateSteps[] = "UPDATE footprints SET name='MYRRA-EL54' WHERE name='TRAFO-MYRRA_54-2';";
            $updateSteps[] = "UPDATE footprints SET name='MYRRA-EL60' WHERE name='TRAFO-MYRRA_60-2';";
            $updateSteps[] = "UPDATE footprints SET name='MYRRA-UI48' WHERE name='TRAFO-MYRRA_48-40';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X10' WHERE name='STIFTLEISTE-GERADE-SMD_2X10';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X11' WHERE name='STIFTLEISTE-GERADE-SMD_2X11';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X12' WHERE name='STIFTLEISTE-GERADE-SMD_2X12';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X13' WHERE name='STIFTLEISTE-GERADE-SMD_2X13';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X14' WHERE name='STIFTLEISTE-GERADE-SMD_2X14';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X15' WHERE name='STIFTLEISTE-GERADE-SMD_2X15';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X16' WHERE name='STIFTLEISTE-GERADE-SMD_2X16';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X2' WHERE name='STIFTLEISTE-GERADE-SMD_2X02';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X3' WHERE name='STIFTLEISTE-GERADE-SMD_2X03';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X4' WHERE name='STIFTLEISTE-GERADE-SMD_2X04';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X5' WHERE name='STIFTLEISTE-GERADE-SMD_2X05';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X6' WHERE name='STIFTLEISTE-GERADE-SMD_2X06';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X7' WHERE name='STIFTLEISTE-GERADE-SMD_2X07';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X8' WHERE name='STIFTLEISTE-GERADE-SMD_2X08';";
            $updateSteps[] = "UPDATE footprints SET name='PHSMD2X9' WHERE name='STIFTLEISTE-GERADE-SMD_2X09';";
            $updateSteps[] = "UPDATE footprints SET name='PT10H10' WHERE name='TRIMMER_PT10-H';";
            $updateSteps[] = "UPDATE footprints SET name='PSO36' WHERE name='IC_PSO36';";
            $updateSteps[] = "UPDATE footprints SET name='PSO20' WHERE name='IC_PSO20';";
            $updateSteps[] = "UPDATE footprints SET name='PQFP100' WHERE name='IC_PQFP100';";
            $updateSteps[] = "UPDATE footprints SET name='PQFP128' WHERE name='IC_PQFP128';";
            $updateSteps[] = "UPDATE footprints SET name='PQFP160' WHERE name='IC_PQFP160';";
            $updateSteps[] = "UPDATE footprints SET name='PQFP208' WHERE name='IC_PQFP208';";
            $updateSteps[] = "UPDATE footprints SET name='PQFP240' WHERE name='IC_PQFP240';";
            $updateSteps[] = "UPDATE footprints SET name='PQFP44' WHERE name='IC_PQFP44';";
            $updateSteps[] = "UPDATE footprints SET name='PQFP48' WHERE name='IC_PQFP48';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC20' WHERE name='IC_PLCC20';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC28' WHERE name='IC_PLCC28';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC32' WHERE name='IC_PLCC32';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC44' WHERE name='IC_PLCC44';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC52' WHERE name='IC_PLCC52';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC68' WHERE name='IC_PLCC68';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC84' WHERE name='IC_PLCC84';";
            $updateSteps[] = "UPDATE footprints SET name='PLCC2' WHERE name='LED_PLCC2';";
            $updateSteps[] = "UPDATE footprints SET name='QSOP16' WHERE name='IC_QSOP16';";
            $updateSteps[] = "UPDATE footprints SET name='QSOP20' WHERE name='IC_QSOP20';";
            $updateSteps[] = "UPDATE footprints SET name='QSOP24' WHERE name='IC_QSOP24';";
            $updateSteps[] = "UPDATE footprints SET name='QSOP28' WHERE name='IC_QSOP28';";
            $updateSteps[] = "UPDATE footprints SET name='RES-0102MLF' WHERE name='WIDERSTAND-SMD_0102-MLF';";
            $updateSteps[] = "UPDATE footprints SET name='RES-0204MLF' WHERE name='WIDERSTAND-SMD_0204-MLF';";
            $updateSteps[] = "UPDATE footprints SET name='RES-0207MLF' WHERE name='WIDERSTAND-SMD_0207-MLF';";
            $updateSteps[] = "UPDATE footprints SET name='RES-0402' WHERE name='WIDERSTAND-SMD_0402';";
            $updateSteps[] = "UPDATE footprints SET name='RES-0603' WHERE name='WIDERSTAND-SMD_0603';";
            $updateSteps[] = "UPDATE footprints SET name='RES-0805' WHERE name='WIDERSTAND-SMD_0805';";
            $updateSteps[] = "UPDATE footprints SET name='RES-1206' WHERE name='WIDERSTAND-SMD_1206';";
            $updateSteps[] = "UPDATE footprints SET name='RES-1210' WHERE name='WIDERSTAND-SMD_1210';";
            $updateSteps[] = "UPDATE footprints SET name='RES-1218' WHERE name='WIDERSTAND-SMD_1218';";
            $updateSteps[] = "UPDATE footprints SET name='RES-2010' WHERE name='WIDERSTAND-SMD_2010';";
            $updateSteps[] = "UPDATE footprints SET name='RES-2512' WHERE name='WIDERSTAND-SMD_2512';";
            $updateSteps[] = "UPDATE footprints SET name='RES-4x0603' WHERE name='WIDERSTAND-SMD-ARRAY_4X0603-0612';";
            $updateSteps[] = "UPDATE footprints SET name='RB1A' WHERE name='GLEICHRICHTER_RB1A';";
            $updateSteps[] = "UPDATE footprints SET name='RAWA400-9P' WHERE name='KUEHLKOERPER_RAWA400-9P';";
            $updateSteps[] = "UPDATE footprints SET name='RAWA400-8P' WHERE name='KUEHLKOERPER_RAWA400-8P';";
            $updateSteps[] = "UPDATE footprints SET name='RAWA400-11P' WHERE name='KUEHLKOERPER_RAWA400-11P';";
            $updateSteps[] = "UPDATE footprints SET name='RA37-3' WHERE name='KUEHLKOERPER_RA37-3';";
            $updateSteps[] = "UPDATE footprints SET name='RH10' WHERE name='WIDERSTAND-ALU_RH10';";
            $updateSteps[] = "UPDATE footprints SET name='RH100' WHERE name='WIDERSTAND-ALU_RH100';";
            $updateSteps[] = "UPDATE footprints SET name='RH100X' WHERE name='WIDERSTAND-ALU_RH100X';";
            $updateSteps[] = "UPDATE footprints SET name='RH25' WHERE name='WIDERSTAND-ALU_RH25';";
            $updateSteps[] = "UPDATE footprints SET name='RH250' WHERE name='WIDERSTAND-ALU_RH250';";
            $updateSteps[] = "UPDATE footprints SET name='RH5-304' WHERE name='WIDERSTAND-ALU_RH5';";
            $updateSteps[] = "UPDATE footprints SET name='RH50' WHERE name='WIDERSTAND-ALU_RH50';";
            $updateSteps[] = "UPDATE footprints SET name='RH75' WHERE name='WIDERSTAND-ALU_RH75';";
            $updateSteps[] = "UPDATE footprints SET name='ROTARYDIP10-1' WHERE name='DREHSCHALTER_DIP10-1';";
            $updateSteps[] = "UPDATE footprints SET name='ROTARYDIP10' WHERE name='DREHSCHALTER_DIP10';";
            $updateSteps[] = "UPDATE footprints SET name='ROTARYDIP16-1' WHERE name='DREHSCHALTER_DIP16-1';";
            $updateSteps[] = "UPDATE footprints SET name='ROTARYDIP16' WHERE name='DREHSCHALTER_DIP16';";
            $updateSteps[] = "UPDATE footprints SET name='RY2' WHERE name='RELAIS_RY2';";
            $updateSteps[] = "UPDATE footprints SET name='SD-CARD' WHERE name='SD-KARTE_Schwarz';";
            $updateSteps[] = "UPDATE footprints SET name='SC12' WHERE name='IC_BECK-SC12';";
            $updateSteps[] = "UPDATE footprints SET name='S64Y' WHERE name='TRIMMER_S64Y';";
            $updateSteps[] = "UPDATE footprints SET name='S2XXEX' WHERE name='IC_SHARP-S2xxEx';";
            $updateSteps[] = "UPDATE footprints SET name='SECME1K2RH' WHERE name='SCHIEBESCHALTER_SECME-1K2-RH';";
            $updateSteps[] = "UPDATE footprints SET name='SECME1K2RL' WHERE name='SCHIEBESCHALTER_SECME-1K2-RL';";
            $updateSteps[] = "UPDATE footprints SET name='SECME1K2SH' WHERE name='SCHIEBESCHALTER_SECME-1K2-SH';";
            $updateSteps[] = "UPDATE footprints SET name='SECME1K2SL' WHERE name='SCHIEBESCHALTER_SECME-1K2-SL';";
            $updateSteps[] = "UPDATE footprints SET name='SECME1K2SLB' WHERE name='SCHIEBESCHALTER_SECME-1K2-SLB';";
            $updateSteps[] = "UPDATE footprints SET name='SFT1030' WHERE name='SPULE_SFT1030';";
            $updateSteps[] = "UPDATE footprints SET name='SFT1040' WHERE name='SPULE_SFT1040';";
            $updateSteps[] = "UPDATE footprints SET name='SFT1240' WHERE name='SPULE_SFT1240';";
            $updateSteps[] = "UPDATE footprints SET name='SFT830D' WHERE name='SPULE_SFT830D';";
            $updateSteps[] = "UPDATE footprints SET name='SFT830S' WHERE name='SPULE_SFT830S';";
            $updateSteps[] = "UPDATE footprints SET name='SFT840D' WHERE name='SPULE_SFT840D';";
            $updateSteps[] = "UPDATE footprints SET name='SIL4' WHERE name='WIDERSTAND_SIL04';";
            $updateSteps[] = "UPDATE footprints SET name='SIL5' WHERE name='WIDERSTAND_SIL05';";
            $updateSteps[] = "UPDATE footprints SET name='SIL6' WHERE name='WIDERSTAND_SIL06';";
            $updateSteps[] = "UPDATE footprints SET name='SIL7' WHERE name='WIDERSTAND_SIL07';";
            $updateSteps[] = "UPDATE footprints SET name='SIL8' WHERE name='WIDERSTAND_SIL08';";
            $updateSteps[] = "UPDATE footprints SET name='SIL9' WHERE name='WIDERSTAND_SIL09';";
            $updateSteps[] = "UPDATE footprints SET name='SIL10' WHERE name='WIDERSTAND_SIL10';";
            $updateSteps[] = "UPDATE footprints SET name='SIL11' WHERE name='WIDERSTAND_SIL11';";
            $updateSteps[] = "UPDATE footprints SET name='SIL12' WHERE name='WIDERSTAND_SIL12';";
            $updateSteps[] = "UPDATE footprints SET name='SIL13' WHERE name='WIDERSTAND_SIL13';";
            $updateSteps[] = "UPDATE footprints SET name='SIL14' WHERE name='WIDERSTAND_SIL14';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-254MC' WHERE name='KUEHLKOERPER_SK104-254-MC';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-254STIS' WHERE name='KUEHLKOERPER_SK104-254-STIS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-254STS' WHERE name='KUEHLKOERPER_SK104-254-STS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-254STSB' WHERE name='KUEHLKOERPER_SK104-254-STSB';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-381MC' WHERE name='KUEHLKOERPER_SK104-381-MC';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-381STIS' WHERE name='KUEHLKOERPER_SK104-381-STIS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-381STS' WHERE name='KUEHLKOERPER_SK104-381-STS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-381STSB' WHERE name='KUEHLKOERPER_SK104-381-STSB';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-508MC' WHERE name='KUEHLKOERPER_SK104-508-MC';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-508STIS' WHERE name='KUEHLKOERPER_SK104-508-STIS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-508STS' WHERE name='KUEHLKOERPER_SK104-508-STS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-508STSB' WHERE name='KUEHLKOERPER_SK104-508-STSB';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-635MC' WHERE name='KUEHLKOERPER_SK104-635-MC';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-635STIS' WHERE name='KUEHLKOERPER_SK104-635-STIS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-635STS' WHERE name='KUEHLKOERPER_SK104-635-STS';";
            $updateSteps[] = "UPDATE footprints SET name='SK104-635STSB' WHERE name='KUEHLKOERPER_SK104-635-STSB';";
            $updateSteps[] = "UPDATE footprints SET name='SKHH-V4x3Y' WHERE name='TASTER_SKHH-3MM';";
            $updateSteps[] = "UPDATE footprints SET name='SKBB' WHERE name='GLEICHRICHTER_SKBB';";
            $updateSteps[] = "UPDATE footprints SET name='SK96-84' WHERE name='KUEHLKOERPER_SK96-84';";
            $updateSteps[] = "UPDATE footprints SET name='SMA-JH' WHERE name='VERBINDER_SMA-JH';";
            $updateSteps[] = "UPDATE footprints SET name='SMA-JV' WHERE name='VERBINDER_SMA-JV';";
            $updateSteps[] = "UPDATE footprints SET name='SMLP500x' WHERE name='TRAFO-SMD_LP-500X';";
            $updateSteps[] = "UPDATE footprints SET name='SMSL1305' WHERE name='SPULE_SMSL-1305';";
            $updateSteps[] = "UPDATE footprints SET name='SMSL2' WHERE name='TRAFO-SMD_SL2';";
            $updateSteps[] = "UPDATE footprints SET name='SOD123A' WHERE name='DIODE_SOD123-1';";
            $updateSteps[] = "UPDATE footprints SET name='SOD123B' WHERE name='DIODE_SOD123-3';";
            $updateSteps[] = "UPDATE footprints SET name='SOD123C' WHERE name='DIODE_SOD123-5';";
            $updateSteps[] = "UPDATE footprints SET name='SOD57' WHERE name='DIODE_SOD57';";
            $updateSteps[] = "UPDATE footprints SET name='SOD61A' WHERE name='DIODE_SOD61-A';";
            $updateSteps[] = "UPDATE footprints SET name='SOD61B' WHERE name='DIODE_SOD61-B';";
            $updateSteps[] = "UPDATE footprints SET name='SOD61C' WHERE name='DIODE_SOD61-C';";
            $updateSteps[] = "UPDATE footprints SET name='SOD61D' WHERE name='DIODE_SOD61-D';";
            $updateSteps[] = "UPDATE footprints SET name='SOD61E' WHERE name='DIODE_SOD61-E';";
            $updateSteps[] = "UPDATE footprints SET name='SOD64' WHERE name='DIODE_SOD64';";
            $updateSteps[] = "UPDATE footprints SET name='SOD80' WHERE name='DIODE_SOD80';";
            $updateSteps[] = "UPDATE footprints SET name='SOD81' WHERE name='DIODE_SOD81';";
            $updateSteps[] = "UPDATE footprints SET name='SOD87' WHERE name='DIODE_SOD87';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC14' WHERE name='IC_SO14';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC16' WHERE name='IC_SO16';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC16W' WHERE name='IC_SO16W';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC18W' WHERE name='IC_SO18W';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC20W' WHERE name='IC_SO20W';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC24W' WHERE name='IC_SO24W';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC28W' WHERE name='IC_SO28W';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC32' WHERE name='IC_SO32-400';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC32W' WHERE name='IC_SO32-525';";
            $updateSteps[] = "UPDATE footprints SET name='SOIC8' WHERE name='IC_SO08';";
            $updateSteps[] = "UPDATE footprints SET name='SOT143' WHERE name='IC_SOT143';";
            $updateSteps[] = "UPDATE footprints SET name='SOT223' WHERE name='IC_SOT223';";
            $updateSteps[] = "UPDATE footprints SET name='SOT23-5' WHERE name='IC_SOT23-5';";
            $updateSteps[] = "UPDATE footprints SET name='SOT23-6' WHERE name='IC_SOT23-6';";
            $updateSteps[] = "UPDATE footprints SET name='SOT23' WHERE name='IC_SOT23';";
            $updateSteps[] = "UPDATE footprints SET name='SOT363' WHERE name='IC_SOT363';";
            $updateSteps[] = "UPDATE footprints SET name='SQFP14X20' WHERE name='IC_SQFP100';";
            $updateSteps[] = "UPDATE footprints SET name='SQFP64' WHERE name='IC_SQFP64';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP14' WHERE name='IC_SSOP14';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP16' WHERE name='IC_SSOP16';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP20' WHERE name='IC_SSOP20';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP24' WHERE name='IC_SSOP24';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP28' WHERE name='IC_SSOP28';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP30' WHERE name='IC_SSOP30';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP48' WHERE name='IC_SSOP48';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP56' WHERE name='IC_SSOP56';";
            $updateSteps[] = "UPDATE footprints SET name='SSOP56DL' WHERE name='IC_SSOP56DL';";
            $updateSteps[] = "UPDATE footprints SET name='TDB' WHERE name='BUZZER_TDB';";
            $updateSteps[] = "UPDATE footprints SET name='T18' WHERE name='TRIMMER_T18';";
            $updateSteps[] = "UPDATE footprints SET name='T7YA' WHERE name='TRIMMER_T7-YA';";
            $updateSteps[] = "UPDATE footprints SET name='T7YB' WHERE name='TRIMMER_T7-YB';";
            $updateSteps[] = "UPDATE footprints SET name='TEX14' WHERE name='SOCKEL_TEX14';";
            $updateSteps[] = "UPDATE footprints SET name='TEX16' WHERE name='SOCKEL_TEX16';";
            $updateSteps[] = "UPDATE footprints SET name='TEX18' WHERE name='SOCKEL_TEX18';";
            $updateSteps[] = "UPDATE footprints SET name='TEX20' WHERE name='SOCKEL_TEX20';";
            $updateSteps[] = "UPDATE footprints SET name='TEX22' WHERE name='SOCKEL_TEX22';";
            $updateSteps[] = "UPDATE footprints SET name='TEX24' WHERE name='SOCKEL_TEX24';";
            $updateSteps[] = "UPDATE footprints SET name='TEX24W' WHERE name='SOCKEL_TEX24W';";
            $updateSteps[] = "UPDATE footprints SET name='TEX28' WHERE name='SOCKEL_TEX28';";
            $updateSteps[] = "UPDATE footprints SET name='TEX28W' WHERE name='SOCKEL_TEX28W';";
            $updateSteps[] = "UPDATE footprints SET name='TEX32W' WHERE name='SOCKEL_TEX32W';";
            $updateSteps[] = "UPDATE footprints SET name='TEX40W' WHERE name='SOCKEL_TEX40W';";
            $updateSteps[] = "UPDATE footprints SET name='TEX40WW' WHERE name='SOCKEL_TEX40WW';";
            $updateSteps[] = "UPDATE footprints SET name='TEX42W' WHERE name='SOCKEL_TEX42W';";
            $updateSteps[] = "UPDATE footprints SET name='TEX48W' WHERE name='SOCKEL_TEX48W';";
            $updateSteps[] = "UPDATE footprints SET name='TEX64W' WHERE name='SOCKEL_TEX64WW';";
            $updateSteps[] = "UPDATE footprints SET name='TO126' WHERE name='IC_TO126';";
            $updateSteps[] = "UPDATE footprints SET name='TO18' WHERE name='IC_TO18';";
            $updateSteps[] = "UPDATE footprints SET name='TO18D' WHERE name='IC_TO18D';";
            $updateSteps[] = "UPDATE footprints SET name='TO202' WHERE name='IC_TO202';";
            $updateSteps[] = "UPDATE footprints SET name='TO218' WHERE name='IC_TO218';";
            $updateSteps[] = "UPDATE footprints SET name='TO220-3' WHERE name='IC_TO220';";
            $updateSteps[] = "UPDATE footprints SET name='TO220-5' WHERE name='IC_TO220-5';";
            $updateSteps[] = "UPDATE footprints SET name='TO247' WHERE name='IC_TO247';";
            $updateSteps[] = "UPDATE footprints SET name='TO252' WHERE name='IC_TO252';";
            $updateSteps[] = "UPDATE footprints SET name='TO263' WHERE name='IC_TO263';";
            $updateSteps[] = "UPDATE footprints SET name='TO3' WHERE name='IC_TO3';";
            $updateSteps[] = "UPDATE footprints SET name='TO39-4' WHERE name='IC_TO39-4';";
            $updateSteps[] = "UPDATE footprints SET name='TO39' WHERE name='IC_TO39';";
            $updateSteps[] = "UPDATE footprints SET name='TO51' WHERE name='IC_TO51';";
            $updateSteps[] = "UPDATE footprints SET name='TO52' WHERE name='IC_TO52';";
            $updateSteps[] = "UPDATE footprints SET name='TO66' WHERE name='IC_TO66';";
            $updateSteps[] = "UPDATE footprints SET name='TO72-3' WHERE name='IC_TO72-3';";
            $updateSteps[] = "UPDATE footprints SET name='TO72-4' WHERE name='IC_TO72-4';";
            $updateSteps[] = "UPDATE footprints SET name='TO92-2' WHERE name='IC_TO92-2';";
            $updateSteps[] = "UPDATE footprints SET name='TO92-3' WHERE name='IC_TO92';";
            $updateSteps[] = "UPDATE footprints SET name='TO92-3G' WHERE name='IC_TO92-G4';";
            $updateSteps[] = "UPDATE footprints SET name='TORX173' WHERE name='LASER_TORX173';";
            $updateSteps[] = "UPDATE footprints SET name='TOTX173' WHERE name='LASER_TOTX173';";
            $updateSteps[] = "UPDATE footprints SET name='TQPP100' WHERE name='IC_TQFP100';";
            $updateSteps[] = "UPDATE footprints SET name='TQFP112' WHERE name='IC_TQFP112';";
            $updateSteps[] = "UPDATE footprints SET name='TQFP144' WHERE name='IC_TQFP144';";
            $updateSteps[] = "UPDATE footprints SET name='TQFP32' WHERE name='IC_TQFP32';";
            $updateSteps[] = "UPDATE footprints SET name='TQFP44' WHERE name='IC_TQFP44';";
            $updateSteps[] = "UPDATE footprints SET name='TQFP64' WHERE name='IC_TQFP64';";
            $updateSteps[] = "UPDATE footprints SET name='TRJ19201' WHERE name='BUCHSE_RJ45-SHLD-LED';";
            $updateSteps[] = "UPDATE footprints SET name='TSM4YJ' WHERE name='TRIMMER_TSM-4YJ';";
            $updateSteps[] = "UPDATE footprints SET name='TSM4YL' WHERE name='TRIMMER_TSM-4YL';";
            $updateSteps[] = "UPDATE footprints SET name='TSM4ZJ' WHERE name='TRIMMER_TSM-4ZJ';";
            $updateSteps[] = "UPDATE footprints SET name='TSM4ZL' WHERE name='TRIMMER_TSM-4ZL';";
            $updateSteps[] = "UPDATE footprints SET name='TS53YJ' WHERE name='TRIMMER_TS53-YJ';";
            $updateSteps[] = "UPDATE footprints SET name='TS53YL' WHERE name='TRIMMER_TS53-YL';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP32W' WHERE name='IC_TSOP32';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP48W' WHERE name='IC_TSOP48';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP86' WHERE name='IC_TSOP86';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP8' WHERE name='IC_TSSOP08';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP14' WHERE name='IC_TSSOP14';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP16' WHERE name='IC_TSSOP16';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP20' WHERE name='IC_TSSOP20';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP24' WHERE name='IC_TSSOP24';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP28' WHERE name='IC_TSSOP28';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP48' WHERE name='IC_TSSOP48';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP56' WHERE name='IC_TSSOP56';";
            $updateSteps[] = "UPDATE footprints SET name='TSSOP64' WHERE name='IC_TSSOP64';";
            $updateSteps[] = "UPDATE footprints SET name='TYCO-H38' WHERE name='SPULE_TYCO_H38';";
            $updateSteps[] = "UPDATE footprints SET name='TZ03F' WHERE name='TRIMMKONDENSATOR-SCHWARZ_TZ03F';";
            $updateSteps[] = "UPDATE footprints SET name='USB-A1' WHERE name='VERBINDER-USB_A-1';";
            $updateSteps[] = "UPDATE footprints SET name='USB-A2' WHERE name='VERBINDER-USB_A-2';";
            $updateSteps[] = "UPDATE footprints SET name='USB-B1' WHERE name='VERBINDER-USB_B-1';";
            $updateSteps[] = "UPDATE footprints SET name='USB-B2' WHERE name='VERBINDER-USB_B-2';";
            $updateSteps[] = "UPDATE footprints SET name='UMAX10' WHERE name='IC_UMAX10';";
            $updateSteps[] = "UPDATE footprints SET name='UMAX8' WHERE name='IC_UMAX08';";
            $updateSteps[] = "UPDATE footprints SET name='VSO40' WHERE name='IC_VSO40';";
            $updateSteps[] = "UPDATE footprints SET name='VSO56' WHERE name='IC_VSO56';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-102' WHERE name='KLEMME-WAGO-233_102';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-103' WHERE name='KLEMME-WAGO-233_103';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-104' WHERE name='KLEMME-WAGO-233_104';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-105' WHERE name='KLEMME-WAGO-233_105';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-106' WHERE name='KLEMME-WAGO-233_106';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-107' WHERE name='KLEMME-WAGO-233_107';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-108' WHERE name='KLEMME-WAGO-233_108';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-109' WHERE name='KLEMME-WAGO-233_109';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-110' WHERE name='KLEMME-WAGO-233_110';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-112' WHERE name='KLEMME-WAGO-233_112';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-116' WHERE name='KLEMME-WAGO-233_116';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-124' WHERE name='KLEMME-WAGO-233_124';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-136' WHERE name='KLEMME-WAGO-233_136';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-148' WHERE name='KLEMME-WAGO-233_148';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-202' WHERE name='KLEMME-WAGO-233_202';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-203' WHERE name='KLEMME-WAGO-233_203';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-204' WHERE name='KLEMME-WAGO-233_204';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-205' WHERE name='KLEMME-WAGO-233_205';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-206' WHERE name='KLEMME-WAGO-233_206';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-207' WHERE name='KLEMME-WAGO-233_207';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-208' WHERE name='KLEMME-WAGO-233_208';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-209' WHERE name='KLEMME-WAGO-233_209';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-210' WHERE name='KLEMME-WAGO-233_210';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-212' WHERE name='KLEMME-WAGO-233_212';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-216' WHERE name='KLEMME-WAGO-233_216';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-224' WHERE name='KLEMME-WAGO-233_224';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-236' WHERE name='KLEMME-WAGO-233_236';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-248' WHERE name='KLEMME-WAGO-233_248';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-402' WHERE name='KLEMME-WAGO-233_402';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-403' WHERE name='KLEMME-WAGO-233_403';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-404' WHERE name='KLEMME-WAGO-233_404';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-405' WHERE name='KLEMME-WAGO-233_405';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-406' WHERE name='KLEMME-WAGO-233_406';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-407' WHERE name='KLEMME-WAGO-233_407';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-408' WHERE name='KLEMME-WAGO-233_408';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-409' WHERE name='KLEMME-WAGO-233_409';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-410' WHERE name='KLEMME-WAGO-233_410';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-412' WHERE name='KLEMME-WAGO-233_412';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-416' WHERE name='KLEMME-WAGO-233_416';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-424' WHERE name='KLEMME-WAGO-233_424';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-436' WHERE name='KLEMME-WAGO-233_436';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-448' WHERE name='KLEMME-WAGO-233_448';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-502' WHERE name='KLEMME-WAGO-233_502';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-503' WHERE name='KLEMME-WAGO-233_503';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-504' WHERE name='KLEMME-WAGO-233_504';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-505' WHERE name='KLEMME-WAGO-233_505';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-506' WHERE name='KLEMME-WAGO-233_506';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-507' WHERE name='KLEMME-WAGO-233_507';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-508' WHERE name='KLEMME-WAGO-233_508';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-509' WHERE name='KLEMME-WAGO-233_509';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-510' WHERE name='KLEMME-WAGO-233_510';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-512' WHERE name='KLEMME-WAGO-233_512';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-516' WHERE name='KLEMME-WAGO-233_516';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-524' WHERE name='KLEMME-WAGO-233_524';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-536' WHERE name='KLEMME-WAGO-233_536';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO233-548' WHERE name='KLEMME-WAGO-233_548';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-332' WHERE name='STIFTLEISTE-WAGO_733-02';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-333' WHERE name='STIFTLEISTE-WAGO_733-03';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-334' WHERE name='STIFTLEISTE-WAGO_733-04';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-335' WHERE name='STIFTLEISTE-WAGO_733-05';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-336' WHERE name='STIFTLEISTE-WAGO_733-06';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-337' WHERE name='STIFTLEISTE-WAGO_733-07';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-338' WHERE name='STIFTLEISTE-WAGO_733-08';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-340' WHERE name='STIFTLEISTE-WAGO_733-09';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO733-342' WHERE name='STIFTLEISTE-WAGO_733-10';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-132' WHERE name='STIFTLEISTE-WAGO_734-02';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-133' WHERE name='STIFTLEISTE-WAGO_734-03';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-134' WHERE name='STIFTLEISTE-WAGO_734-04';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-135' WHERE name='STIFTLEISTE-WAGO_734-05';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-136' WHERE name='STIFTLEISTE-WAGO_734-06';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-137' WHERE name='STIFTLEISTE-WAGO_734-07';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-138' WHERE name='STIFTLEISTE-WAGO_734-08';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-139' WHERE name='STIFTLEISTE-WAGO_734-09';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-140' WHERE name='STIFTLEISTE-WAGO_734-10';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-142' WHERE name='STIFTLEISTE-WAGO_734-11';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-143' WHERE name='STIFTLEISTE-WAGO_734-12';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-146' WHERE name='STIFTLEISTE-WAGO_734-13';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-148' WHERE name='STIFTLEISTE-WAGO_734-14';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-150' WHERE name='STIFTLEISTE-WAGO_734-15';";
            $updateSteps[] = "UPDATE footprints SET name='WAGO734-154' WHERE name='STIFTLEISTE-WAGO_734-16';";
            $updateSteps[] = "UPDATE footprints SET name='WE612SV' WHERE name='SPULE_WE612SV';";
            $updateSteps[] = "UPDATE footprints SET name='WE622MV' WHERE name='SPULE_WE622MV';";
            $updateSteps[] = "UPDATE footprints SET name='WE632LV' WHERE name='SPULE_WE632LV';";
            $updateSteps[] = "UPDATE footprints SET name='WE642XV' WHERE name='SPULE_WE642XV';";
            $updateSteps[] = "UPDATE footprints SET name='WED-S' WHERE name='SPULE_PD_S';";
            $updateSteps[] = "UPDATE footprints SET name='WEPD-L' WHERE name='SPULE_PD_L';";
            $updateSteps[] = "UPDATE footprints SET name='WEPD-M' WHERE name='SPULE_PD_M';";
            $updateSteps[] = "UPDATE footprints SET name='WEPD-XL' WHERE name='SPULE_PD_XL';";
            $updateSteps[] = "UPDATE footprints SET name='WEPD-XXL' WHERE name='SPULE_PD_XXL';";
            $updateSteps[] = "UPDATE footprints SET name='WEPD4' WHERE name='SPULE_PD4';";
            $updateSteps[] = "UPDATE footprints SET name='WEPDM' WHERE name='SPULE_PDM';";
            $updateSteps[] = "UPDATE footprints SET name='WESV' WHERE name='SPULE_WESV';";
            $updateSteps[] = "UPDATE footprints SET name='WS6G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X03';";
            $updateSteps[] = "UPDATE footprints SET name='WS10G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X05';";
            $updateSteps[] = "UPDATE footprints SET name='WS14G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X07';";
            $updateSteps[] = "UPDATE footprints SET name='WS16G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X08';";
            $updateSteps[] = "UPDATE footprints SET name='WS20G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X10';";
            $updateSteps[] = "UPDATE footprints SET name='WS26G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X13';";
            $updateSteps[] = "UPDATE footprints SET name='WS34G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X17';";
            $updateSteps[] = "UPDATE footprints SET name='WS40G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X20';";
            $updateSteps[] = "UPDATE footprints SET name='WS50G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X25';";
            $updateSteps[] = "UPDATE footprints SET name='WS64G' WHERE name='STIFTLEISTE-GERADE-RAHMEN_2X32';";
            $updateSteps[] = "UPDATE footprints SET name='WS10W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X05';";
            $updateSteps[] = "UPDATE footprints SET name='WS14W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X07';";
            $updateSteps[] = "UPDATE footprints SET name='WS16W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X08';";
            $updateSteps[] = "UPDATE footprints SET name='WS20W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X10';";
            $updateSteps[] = "UPDATE footprints SET name='WS26W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X13';";
            $updateSteps[] = "UPDATE footprints SET name='WS34W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X17';";
            $updateSteps[] = "UPDATE footprints SET name='WS40W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X20';";
            $updateSteps[] = "UPDATE footprints SET name='WS50W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X25';";
            $updateSteps[] = "UPDATE footprints SET name='WS64W' WHERE name='STIFTLEISTE-ABGEWINKELT-RAHMEN_2X32';";
            $updateSteps[] = "UPDATE footprints SET name='XTAL-DIP8' WHERE name='QUARZOSZILLATOR_DIP8';";
            $updateSteps[] = "UPDATE footprints SET name='XTAL-DIP14' WHERE name='QUARZOSZILLATOR_DIP14';";
            $updateSteps[] = "UPDATE footprints SET name='YAMAICHI-FPS' WHERE name='KARTENSLOT_SD';";
            $updateSteps[] = "UPDATE footprints SET name='' WHERE name='';";
            break;

          case 12:

            /*****************************************************************************************
            **                                                                                      **
            ** Update to Database version 13 (for Part-DB Verison 0.3.0):                           **
            **      - Change to the MySQL Engine "InnoDB" because of the support for transactions   **
            **      - Make a lot of changes for the new object-oriented design of Part-DB           **
            **      - Add new keys                                                                  **
            **      - Make all existing keys unique over the whole database (change names)          **
            **      - Use now foreign keys                                                          **
            **      - Remove unused tables/columns                                                  **
            **                                                                                      **
            ** ATTENTION: IT IS STRONGLY RECOMMENDED TO MAKE A DATABASE BACKUP BEFORE UPDATING!!!!  **
            **                                                                                      **
            ** Please note:                                                                         **
            ** This is a huge update, so the risk of a failure is higher than usual.                **
            ** Because of this, automatic database updates are temporary disabled for this one.     **
            ** The user will get a warning and a recommendation to make a database backup.          **
            **                                                                                      **
            ** January 2013, kami89                                                                 **
            **                                                                                      **
            ******************************************************************************************/

            // drop table "pending_orders" (until now, that table is not used - we will create a new one later when we need it)
            $updateSteps[] = "DROP TABLE `pending_orders`";

            // Change all tables to InnoDB (for the support of transactions) and UTF-8
            $updateSteps[] = "ALTER TABLE `categories` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `datasheets` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `devices` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `footprints` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `internal` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
            $updateSteps[] = "ALTER TABLE `parts` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `part_device` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `pictures` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `preise` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `storeloc` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
            $updateSteps[] = "ALTER TABLE `suppliers` ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";

            // Fix a charset bug of an older version of Part-DB (see german post on uC.net: http://www.mikrocontroller.net/topic/269289#3147877)
            $charset_search_replace = array('Ã¤' => 'ä', 'Ã„' => 'Ä', 'Ã¶' => 'ö', 'Ã–' => 'Ö', 'Ã¼' => 'ü', 'Ãœ' => 'Ü',
                                            'Â°' => '°', 'Âµ' => 'µ', 'â‚¬' => '€', 'â€°' => '‰', 'Ã¨' => 'è', 'Ãˆ' => 'È',
                                            'Ã©' => 'é', 'Ã‰' => 'É', 'Ã' => 'à', 'Ã€' => 'À', 'Â£' => '£', 'Ã¸' => 'ø');
            foreach ($charset_search_replace as $search => $replace)
            {
                $updateSteps[] = "UPDATE `categories` SET name = REPLACE(name, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `datasheets` SET datasheeturl = REPLACE(datasheeturl, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `devices` SET name = REPLACE(name, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `footprints` SET name = REPLACE(name, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `footprints` SET filename = REPLACE(filename, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `parts` SET name = REPLACE(name, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `parts` SET description = REPLACE(description, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `parts` SET comment = REPLACE(comment, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `parts` SET supplierpartnr = REPLACE(supplierpartnr, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `part_device` SET mountname = REPLACE(mountname, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `pictures` SET pict_fname = REPLACE(pict_fname, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `storeloc` SET name = REPLACE(name, '".$search."', '".$replace."')";
                $updateSteps[] = "UPDATE `suppliers` SET name = REPLACE(name, '".$search."', '".$replace."')";
            }

            // Fix broken foreign keys if there are some (this is quite important, because later we use
            // the foreign keys of MySQL and if there are broken records in the table, the update will fail!
            $updateSteps[] = "UPDATE `parts` SET id_footprint = '0' WHERE id_footprint NOT IN (SELECT id FROM `footprints`)";
            $updateSteps[] = "UPDATE `parts` SET id_storeloc = '0' WHERE id_storeloc NOT IN (SELECT id FROM `storeloc`)";
            $updateSteps[] = "UPDATE `parts` SET id_supplier = '0' WHERE id_supplier NOT IN (SELECT id FROM `suppliers`)";
            $updateSteps[] = "INSERT IGNORE INTO `categories` (name) VALUES ('Unsortiert')";
            $updateSteps[] = "UPDATE `parts` SET id_category = (SELECT `id` FROM `categories` WHERE name='Unsortiert') ".
                             "WHERE id_category NOT IN (SELECT id FROM `categories`)";
            $updateSteps[] = "DELETE FROM categories WHERE id NOT IN (SELECT id_category FROM parts) AND name='Unsortiert'";
            $updateSteps[] = "UPDATE `categories` ".
                                "LEFT JOIN `categories` AS categories2 ON categories2.id = categories.parentnode ".
                                "SET categories.parentnode = 0 WHERE categories2.id IS NULL";
            $updateSteps[] = "UPDATE `devices` ".
                                "LEFT JOIN `devices` AS devices2 ON devices2.id = devices.parentnode ".
                                "SET devices.parentnode = 0 WHERE devices2.id IS NULL";
            $updateSteps[] = "UPDATE `footprints` ".
                                "LEFT JOIN `footprints` AS footprints2 ON footprints2.id = footprints.parentnode ".
                                "SET footprints.parentnode = 0 WHERE footprints2.id IS NULL";
            $updateSteps[] = "UPDATE `storeloc` ".
                                "LEFT JOIN `storeloc` AS storeloc2 ON storeloc2.id = storeloc.parentnode ".
                                "SET storeloc.parentnode = 0 WHERE storeloc2.id IS NULL";
            $updateSteps[] = "DELETE FROM `datasheets` WHERE part_id NOT IN (SELECT id FROM `parts`)";
            $updateSteps[] = "DELETE FROM `preise` WHERE part_id NOT IN (SELECT id FROM `parts`)";
            $updateSteps[] = "DELETE FROM `pictures` WHERE part_id NOT IN (SELECT id FROM `parts`)";
            $updateSteps[] = "DELETE FROM `part_device` WHERE id_part NOT IN (SELECT id FROM `parts`)";
            $updateSteps[] = "DELETE FROM `part_device` WHERE id_device NOT IN (SELECT id FROM `devices`)";

            // table "suppliers"
            $updateSteps[] = "ALTER TABLE `suppliers` MODIFY `name` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `parent_id` int(11) DEFAULT NULL AFTER `name`";
            $updateSteps[] = "CREATE INDEX suppliers_parent_id_k ON suppliers(parent_id)";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `address` MEDIUMTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `phone_number` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `fax_number` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `email_address` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `website` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `datetime_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $updateSteps[] = "UPDATE `suppliers` SET datetime_added=CURRENT_TIMESTAMP";
            // In the new table "orderdetails", it's not allowed to have orderdetails without a supplier!
            // But until now, it was allowed to have parts without a supplier. So maybe there are now parts
            // with a price or supplierpartnumber, but without a supplier. For these parts we create a dummy
            // supplier and change the supplier of these illegal parts to this new dummy supplier.
            $updateSteps[] = "INSERT IGNORE INTO `suppliers` (name, parent_id) VALUES ('Unbekannt', NULL)";
            $updateSteps[] = "UPDATE `parts` SET supplierpartnr='' WHERE supplierpartnr='0'"; // "0" is not a supplier part-nr! ;-)
            $updateSteps[] = "UPDATE `parts` LEFT JOIN `preise` ON parts.id = preise.part_id ".
                                "SET parts.id_supplier = (SELECT `id` FROM `suppliers` WHERE name='Unbekannt') ".
                                "WHERE (parts.id_supplier = 0) AND ((preise.price > 0) OR (parts.supplierpartnr != ''))";
            $updateSteps[] = "DELETE FROM suppliers WHERE id NOT IN (SELECT id_supplier FROM parts) AND name='Unbekannt'";

            // table "categories"
            $updateSteps[] = "ALTER TABLE `categories` MODIFY `name` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `categories` DROP INDEX `parentnode`";
            $updateSteps[] = "ALTER TABLE `categories` CHANGE `parentnode` `parent_id` int(11) DEFAULT NULL";
            $updateSteps[] = "CREATE INDEX categories_parent_id_k ON categories(parent_id)";
            $updateSteps[] = "ALTER TABLE `categories` ADD `disable_footprints` BOOLEAN NOT NULL DEFAULT FALSE";
            $updateSteps[] = "ALTER TABLE `categories` ADD `disable_manufacturers` BOOLEAN NOT NULL DEFAULT FALSE";
            $updateSteps[] = "ALTER TABLE `categories` ADD `disable_autodatasheets` BOOLEAN NOT NULL DEFAULT FALSE";
            $updateSteps[] = "UPDATE `categories` SET parent_id=NULL WHERE parent_id=0";

            // table "devices"
            $updateSteps[] = "ALTER TABLE `devices` MODIFY `name` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `devices` CHANGE `parentnode` `parent_id` int(11) DEFAULT NULL";
            $updateSteps[] = "CREATE INDEX devices_parent_id_k ON devices(parent_id)";
            $updateSteps[] = "ALTER TABLE `devices` ADD `order_quantity` INT(11) NOT NULL DEFAULT '0'";
            $updateSteps[] = "ALTER TABLE `devices` ADD `order_only_missing_parts` BOOLEAN NOT NULL DEFAULT false";
            $updateSteps[] = "ALTER TABLE `devices` ADD `datetime_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $updateSteps[] = "UPDATE `devices` SET datetime_added=CURRENT_TIMESTAMP";
            $updateSteps[] = "UPDATE `devices` SET parent_id=NULL WHERE parent_id=0";

            // table "footprints"
            $updateSteps[] = "ALTER TABLE `footprints` MODIFY `name` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `footprints` CHANGE `parentnode` `parent_id` int(11) DEFAULT NULL";
            $updateSteps[] = "CREATE INDEX footprints_parent_id_k ON footprints(parent_id)";
            $updateSteps[] = "ALTER TABLE `footprints` MODIFY `filename` mediumtext NOT NULL";
            $updateSteps[] = "UPDATE `footprints` SET `filename` = replace(`filename`,'tools/footprints/','%BASE%/img/footprints/')";
            $updateSteps[] = "UPDATE `footprints` SET parent_id=NULL WHERE parent_id=0";

            // table "storeloc" will now be renamed to "storelocations"
            $updateSteps[] = "ALTER TABLE `storeloc` RENAME `storelocations`";
            $updateSteps[] = "ALTER TABLE `storelocations` MODIFY `id` int(11) AUTO_INCREMENT NOT NULL";
            $updateSteps[] = "ALTER TABLE `storelocations` MODIFY `name` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `storelocations` CHANGE `parentnode` `parent_id` int(11) DEFAULT NULL";
            $updateSteps[] = "CREATE INDEX storelocations_parent_id_k ON storelocations(parent_id)";
            $updateSteps[] = "ALTER TABLE `storelocations` ADD `datetime_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $updateSteps[] = "UPDATE `storelocations` SET datetime_added=CURRENT_TIMESTAMP";
            $updateSteps[] = "UPDATE `storelocations` SET parent_id=NULL WHERE parent_id=0";

            // table "preise" will now be renamed to "orderdetails"
            $updateSteps[] = "ALTER TABLE `preise` RENAME `orderdetails`";
            $updateSteps[] = "DROP INDEX `ma` ON `orderdetails`";
            $updateSteps[] = "DROP INDEX `part_id` ON `orderdetails`";
            $updateSteps[] = "ALTER TABLE `orderdetails` MODIFY `supplierpartnr` TINYTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `orderdetails` MODIFY `price` DECIMAL(6,2) DEFAULT NULL";
            $updateSteps[] = "ALTER TABLE `orderdetails` MODIFY `part_id` INT(11) NOT NULL";
            $updateSteps[] = "ALTER TABLE `orderdetails` ADD `obsolete` BOOL DEFAULT false";
            $updateSteps[] = "ALTER TABLE `orderdetails` ADD `datetime_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $updateSteps[] = "CREATE INDEX orderdetails_part_id_k ON orderdetails(part_id)";
            $updateSteps[] = "CREATE INDEX orderdetails_id_supplier_k ON orderdetails(id_supplier)";
            $updateSteps[] = "UPDATE `orderdetails` ".
                                "LEFT JOIN `parts` ON parts.id = orderdetails.part_id ".
                                "SET orderdetails.id_supplier=parts.id_supplier, ".
                                "orderdetails.supplierpartnr=parts.supplierpartnr, ".
                                "orderdetails.obsolete=parts.obsolete ".
                                "WHERE parts.id IS NOT NULL";
            $updateSteps[] = "INSERT IGNORE INTO `orderdetails` ".
                                "(`part_id`, `id_supplier`, `supplierpartnr`, `last_update`, `manual_input`, `obsolete`) ".
                                "SELECT `id`, `id_supplier`, `supplierpartnr`, now(), '1', `obsolete` FROM `parts` ".
                                "WHERE (id_supplier > '0') AND (parts.id NOT IN (SELECT part_id FROM orderdetails))";
            $updateSteps[] = "UPDATE `orderdetails` SET datetime_added=CURRENT_TIMESTAMP";

            // create table "pricedetails"
            $updateSteps[] = "CREATE TABLE `pricedetails` (".
                                "`id` int(11) NOT NULL AUTO_INCREMENT,".
                                "`orderdetails_id` INT(11) NOT NULL,".
                                "`price` DECIMAL(6,2) NOT NULL,".
                                "`price_related_quantity` INT(11) NOT NULL DEFAULT 1,".
                                "`min_discount_quantity`INT(11) NOT NULL DEFAULT 1,".
                                "`manual_input` BOOL NOT NULL DEFAULT true,".
                                "`last_modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
                                " PRIMARY KEY  (`id`),".
                                " UNIQUE KEY pricedetails_combination_uk (`orderdetails_id`, `min_discount_quantity`)".
                                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
            $updateSteps[] = "CREATE INDEX pricedetails_orderdetails_id_k ON pricedetails(orderdetails_id)";
            $updateSteps[] = "INSERT INTO `pricedetails` ".
                                "(`orderdetails_id`, `price`, `price_related_quantity`, ".
                                    "`min_discount_quantity`, `manual_input`) ".
                                "SELECT `id`, `price`, '1', '1', `manual_input` FROM `orderdetails` ".
                                "WHERE (price > 0)";

            // clean up table "orderdetails"
            $updateSteps[] = "ALTER TABLE `orderdetails` DROP COLUMN `manual_input`";
            $updateSteps[] = "ALTER TABLE `orderdetails` DROP COLUMN `price`";
            $updateSteps[] = "ALTER TABLE `orderdetails` DROP COLUMN `last_update`";

            // table "parts"
            $updateSteps[] = "ALTER TABLE `parts` DROP INDEX `id_storeloc`";
            $updateSteps[] = "ALTER TABLE `parts` DROP INDEX `id_category`";
            $updateSteps[] = "ALTER TABLE `parts` ADD `order_orderdetails_id` INT(11) DEFAULT NULL";
            $updateSteps[] = "ALTER TABLE `parts` ADD `order_quantity` INT(11) NOT NULL DEFAULT '1'";
            $updateSteps[] = "ALTER TABLE `parts` ADD `manual_order` BOOLEAN NOT NULL DEFAULT false";
            $updateSteps[] = "ALTER TABLE `parts` CHANGE `id_storeloc` `id_storelocation` int(11) DEFAULT NULL";
            $updateSteps[] = "ALTER TABLE `parts` MODIFY `id_footprint` INT(11) DEFAULT NULL";
            $updateSteps[] = "ALTER TABLE `parts` DROP COLUMN `id_supplier`";
            $updateSteps[] = "ALTER TABLE `parts` DROP COLUMN `supplierpartnr`";
            $updateSteps[] = "ALTER TABLE `parts` DROP COLUMN `obsolete`";
            $updateSteps[] = "ALTER TABLE `parts` ADD `id_manufacturer` INT(11) DEFAULT NULL";
            $updateSteps[] = "ALTER TABLE `parts` ADD `id_master_picture_attachement` INT(11) DEFAULT NULL";
            $updateSteps[] = "ALTER TABLE `parts` MODIFY `name` mediumtext NOT NULL";
            $updateSteps[] = "ALTER TABLE `parts` MODIFY `comment` mediumtext NOT NULL";
            $updateSteps[] = "ALTER TABLE `parts` MODIFY `description` mediumtext NOT NULL";
            $updateSteps[] = "ALTER TABLE `parts` ADD `datetime_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $updateSteps[] = "ALTER TABLE `parts` ADD `last_modified` TIMESTAMP NOT NULL";
            $updateSteps[] = "CREATE INDEX parts_id_category_k ON parts(id_category)";
            $updateSteps[] = "CREATE INDEX parts_id_footprint_k ON parts(id_footprint)";
            $updateSteps[] = "CREATE INDEX parts_id_storelocation_k ON parts(id_storelocation)";
            $updateSteps[] = "CREATE INDEX parts_order_orderdetails_id_k ON parts(order_orderdetails_id)";
            $updateSteps[] = "CREATE INDEX parts_id_manufacturer_k ON parts(id_manufacturer)";
            $updateSteps[] = "UPDATE `parts`, `pictures` SET ".
                                "parts.id_master_picture_attachement=pictures.id ".
                                "WHERE (pictures.part_id=parts.id) AND (pictures.pict_masterpict=TRUE)";
            $updateSteps[] = "UPDATE `parts` SET datetime_added=CURRENT_TIMESTAMP";
            $updateSteps[] = "UPDATE `parts` SET last_modified=CURRENT_TIMESTAMP";
            $updateSteps[] = "UPDATE `parts` SET id_footprint=NULL WHERE id_footprint=0";
            $updateSteps[] = "UPDATE `parts` SET id_storelocation=NULL WHERE id_storelocation=0";
            $updateSteps[] = "UPDATE `parts` SET id_manufacturer=NULL WHERE id_manufacturer=0";

            // table "part_device" will now be renamed to "device_parts"
            // (we will create a new table and copy all records to the new table,
            // but we will group device parts with the same device + part
            // because multiple device parts with the same part are no longer allowed)
            $updateSteps[] = "CREATE TABLE IF NOT EXISTS `device_parts` (
                `id` INT(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                `id_part` INT(11) NOT NULL DEFAULT '0',
                `id_device` INT(11) NOT NULL DEFAULT '0',
                `quantity` INT(11) NOT NULL DEFAULT '0',
                `mountnames` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                UNIQUE KEY device_parts_combination_uk (`id_part`, `id_device`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
            $updateSteps[] = "CREATE INDEX device_parts_id_part_k ON device_parts(id_part)";
            $updateSteps[] = "CREATE INDEX device_parts_id_device_k ON device_parts(id_device)";
            $updateSteps[] = "INSERT INTO `device_parts` ".
                                "(`id_part`, `id_device`, `quantity`, `mountnames`) ".
                                "SELECT `id_part`, `id_device`, SUM(`quantity`), GROUP_CONCAT(`mountname`) FROM `part_device` ".
                                "GROUP BY id_part, id_device ";
            $updateSteps[] = "DROP TABLE `part_device`";

            // table "internal"
            $updateSteps[] = "ALTER TABLE `internal` MODIFY `keyValue` VARCHAR(255)"; // Maybe we need more space for some values...
            $updateSteps[] = "DELETE FROM `internal` WHERE keyName='dbAutoUpdate'"; // this is now in config.php

            // create table "attachement_types"
            $updateSteps[] = "CREATE TABLE `attachement_types` (".
                                "`id` INT(11) NOT NULL AUTO_INCREMENT,".
                                "`name` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,".
                                "`parent_id` INT(11) DEFAULT NULL,".
                                " PRIMARY KEY  (`id`)".
                                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
             $updateSteps[] = "CREATE INDEX attachement_types_parent_id_k ON attachement_types(parent_id)";

            // create attachement types "Bilder" and "Datenblätter"
            $updateSteps[] = "INSERT INTO attachement_types (name, parent_id) VALUES ('Bilder', NULL)";
            $updateSteps[] = "INSERT INTO attachement_types (name, parent_id) VALUES ('Datenblätter', NULL)";

            // table "pictures" will now be changed to "attachements"
            $updateSteps[] = "ALTER TABLE `pictures` RENAME `attachements`";
            $updateSteps[] = "ALTER TABLE `attachements` CHANGE `part_id` `element_id` INT(11) NOT NULL";
            $updateSteps[] = "DROP INDEX `pict_type` ON `attachements`";
            $updateSteps[] = "ALTER TABLE `attachements` CHANGE `pict_fname` `filename` MEDIUMTEXT NOT NULL";
            $updateSteps[] = "ALTER TABLE `attachements` DROP COLUMN `pict_width`";
            $updateSteps[] = "ALTER TABLE `attachements` DROP COLUMN `pict_height`";
            $updateSteps[] = "ALTER TABLE `attachements` DROP COLUMN `pict_type`";
            $updateSteps[] = "ALTER TABLE `attachements` DROP COLUMN `tn_obsolete`";
            $updateSteps[] = "ALTER TABLE `attachements` DROP COLUMN `tn_t`";
            $updateSteps[] = "ALTER TABLE `attachements` DROP COLUMN `tn_pictid`";
            $updateSteps[] = "ALTER TABLE `attachements` CHANGE `pict_masterpict` `show_in_table` BOOLEAN NOT NULL DEFAULT FALSE";
            $updateSteps[] = "ALTER TABLE `attachements` ADD `name` TINYTEXT NOT NULL AFTER `id`";
            $updateSteps[] = "ALTER TABLE `attachements` ADD `class_name` VARCHAR(255) NOT NULL AFTER `name`";
            $updateSteps[] = "ALTER TABLE `attachements` ADD `type_id` INT(11) NOT NULL AFTER `element_id`";
            $updateSteps[] = "CREATE INDEX attachements_class_name_k ON attachements(class_name)";
            $updateSteps[] = "CREATE INDEX attachements_element_id_k ON attachements(element_id)";
            $updateSteps[] = "UPDATE `attachements` SET name='Bild'";
            $updateSteps[] = "UPDATE `attachements` SET type_id=(SELECT `id` FROM `attachement_types` WHERE name = 'Bilder')";
            $updateSteps[] = "UPDATE `attachements` SET class_name='Part'";
            $updateSteps[] = "UPDATE `attachements` SET show_in_table=FALSE";
            $updateSteps[] = "UPDATE `attachements` set `filename` = REPLACE(`filename`,'img_','%BASE%/media/img_')";

            // table "datasheets" will now be added to "attachements", and then "datasheets" will be deleted
            $updateSteps[] = "INSERT INTO `attachements` ".
                                "(`element_id`, `name`, `filename`, `class_name`, `type_id`, `show_in_table`) ".
                                "SELECT `part_id` as `element_id`, 'Datenblatt' as `name`, `datasheeturl` as `filename`, ".
                                "'Part', (SELECT `id` FROM `attachement_types` WHERE name = 'Datenblätter'), TRUE FROM `datasheets`";
            $updateSteps[] = "DROP TABLE `datasheets`";

            // create table "manufacturers"
            $updateSteps[] = "CREATE TABLE `manufacturers` (".
                                "`id` INT(11) NOT NULL AUTO_INCREMENT,".
                                "`name` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,".
                                "`parent_id` INT(11) DEFAULT NULL,".
                                "`address` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,".
                                "`phone_number` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,".
                                "`fax_number` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,".
                                "`email_address` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,".
                                "`website` TINYTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,".
                                "`datetime_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,".
                                " PRIMARY KEY  (`id`)".
                                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
            $updateSteps[] = "CREATE INDEX manufacturers_parent_id_k ON manufacturers(parent_id)";

            // add foreign keys
            $updateSteps[] = "ALTER TABLE `categories` ADD CONSTRAINT categories_parent_id_fk FOREIGN KEY (parent_id) REFERENCES categories(id)";
            $updateSteps[] = "ALTER TABLE `devices` ADD CONSTRAINT devices_parent_id_fk FOREIGN KEY (parent_id) REFERENCES devices(id)";
            $updateSteps[] = "ALTER TABLE `attachement_types` ADD CONSTRAINT attachement_types_parent_id_fk FOREIGN KEY (parent_id) REFERENCES attachement_types(id)";
            $updateSteps[] = "ALTER TABLE `footprints` ADD CONSTRAINT footprints_parent_id_fk FOREIGN KEY (parent_id) REFERENCES footprints(id)";
            $updateSteps[] = "ALTER TABLE `manufacturers` ADD CONSTRAINT manufacturers_parent_id_fk FOREIGN KEY (parent_id) REFERENCES manufacturers(id)";
            $updateSteps[] = "ALTER TABLE `parts` ADD CONSTRAINT parts_id_footprint_fk FOREIGN KEY (id_footprint) REFERENCES footprints(id)";
            $updateSteps[] = "ALTER TABLE `parts` ADD CONSTRAINT parts_id_storelocation_fk FOREIGN KEY (id_storelocation) REFERENCES storelocations(id)";
            $updateSteps[] = "ALTER TABLE `parts` ADD CONSTRAINT parts_order_orderdetails_id_fk FOREIGN KEY (order_orderdetails_id) REFERENCES orderdetails(id)";
            $updateSteps[] = "ALTER TABLE `parts` ADD CONSTRAINT parts_id_manufacturer_fk FOREIGN KEY (id_manufacturer) REFERENCES manufacturers(id)";
            $updateSteps[] = "ALTER TABLE `storelocations` ADD CONSTRAINT storelocations_parent_id_fk FOREIGN KEY (parent_id) REFERENCES storelocations(id)";
            $updateSteps[] = "ALTER TABLE `suppliers` ADD CONSTRAINT suppliers_parent_id_fk FOREIGN KEY (parent_id) REFERENCES suppliers(id)";
            $updateSteps[] = "ALTER TABLE `attachements` ADD CONSTRAINT attachements_type_id_fk FOREIGN KEY (type_id) REFERENCES attachement_types(id)";
            break;

          case 13:
            // we have created the new directory "data", now we have to rename all filenames
            $updateSteps[] = "UPDATE `attachements` set `filename` = REPLACE(`filename`,'%BASE%/media/','%BASE%/data/media/')";
            break;

          case 14:
            // if a part has no master picture attachement, but it has picture attachements, set one of them as the master picture attachement
            $updateSteps[] = "UPDATE `parts` ".
                                "INNER JOIN `attachements` ".
                                "ON (attachements.element_id=parts.id) ".
                                    "AND (attachements.class_name='Part') ".
                                    "AND ((LOCATE('.jpg', LOWER(attachements.filename)) > 0) ".
                                        "OR (LOCATE('.jpeg', LOWER(attachements.filename)) > 0) ".
                                        "OR (LOCATE('.png', LOWER(attachements.filename)) > 0) ".
                                        "OR (LOCATE('.gif', LOWER(attachements.filename)) > 0) ".
                                        "OR (LOCATE('.bmp', LOWER(attachements.filename)) > 0)) ".
                                "SET parts.id_master_picture_attachement=attachements.id ".
                                "WHERE (parts.id_master_picture_attachement IS NULL)";
            break;

          case 15:
            // add new columns to the suppliers/manufacturers table for the new automatic links to the parts on the company's website
            $updateSteps[] = "ALTER TABLE `suppliers` ADD `auto_product_url` TINYTEXT NOT NULL AFTER `website`";
            $updateSteps[] = "ALTER TABLE `manufacturers` ADD `auto_product_url` TINYTEXT NOT NULL AFTER `website`";
            // add some additional columns for manual links, maybe this will be fully supported in future...
            $updateSteps[] = "ALTER TABLE `parts` ADD `manufacturer_product_url` TINYTEXT NOT NULL AFTER `id_master_picture_attachement`";
            $updateSteps[] = "ALTER TABLE `orderdetails` ADD `supplier_product_url` TINYTEXT NOT NULL AFTER `obsolete`";
            break;

          case 16:
            //Price is now stored with 5 decimal places.
            $updateSteps[] = "ALTER TABLE `pricedetails` MODIFY `price` DECIMAL(9,5)";
            break;

          /*case 17:
            // create table "users"
            $updateSteps[] = "CREATE TABLE `users` (".
                // Benutzerinformationen
                "`id` INT(11) NOT NULL AUTO_INCREMENT,".            // Benutzer-ID
                "`name` VARCHAR(30) NOT NULL,".                     // Anmeldename
                "`password` VARCHAR(32),".                          // MD5-Hash des Passworts
                "`first_name` TINYTEXT,".                           // Vorname
                "`last_name` TINYTEXT,".                            // Nachname
                "`department` TINYTEXT,".                           // Abteilung
                "`email` TINYTEXT,".                                // E-Mail Adresse
                // Einstellungen und Gruppenzugehörigkeit
                "`auto_login` BOOLEAN NOT NULL DEFAULT '0',".       // Automatische Anmeldung ein/aus
                "`session_id` VARCHAR(32),".                        // Session-ID für die Identifizierung
                "`group_id` INT(11) NOT NULL,".                     // Gruppen-ID von der Gruppe des Users
                // System-Rechte
                "`perms_system` INT(3) NOT NULL,".                  // Allgemeine Rechte ("Kleinkram")
                "`perms_system_group_childs` INT(3) NOT NULL,".     // Gruppenverwaltung (nur Untergruppen der eigenen Gruppe)
                "`perms_system_group_others` INT(3) NOT NULL,".     // Gruppenverwaltung (alle, ausser die Untergruppen)
                "`perms_system_users_subgroups` INT(3) NOT NULL,".  // Benutzerverwaltung (Benutzer aller untergeortneten Gruppen)
                "`perms_system_users_others` INT(3) NOT NULL,".     // Benutzerverwaltung (alle, ausser von untergeordneten Gruppen)
                "`perms_system_dbupdate` INT(3) NOT NULL,".         // Datenbankaktualisierung bzw. dessen Einstellungen
                "`perms_system_dbbackup` INT(3) NOT NULL,".         // Datenbank-Backup bzw. dessen Einstellungen
                // Bauteil-Rechte
                "`perms_parts` INT(3) NOT NULL,".                   // Betrachten/Erstellen/Löschen/Verschieben
                "`perms_parts_name` INT(3) NOT NULL,".              // Name
                "`perms_parts_description` INT(3) NOT NULL,".       // Beschreibung
                "`perms_parts_instock` INT(3) NOT NULL,".           // Menge (an Lager)
                "`perms_parts_mininstock` INT(3) NOT NULL,".        // Mindestmenge
                "`perms_parts_footprint` INT(3) NOT NULL,".         // Footprint
                "`perms_parts_storelocation` INT(3) NOT NULL,".     // Lagerort
                "`perms_parts_obsolete` INT(3) NOT NULL,".          // Obsolet
                "`perms_parts_comment` INT(3) NOT NULL,".           // Kommentar
                "`perms_parts_orderdetails` INT(3) NOT NULL,".      // Bestellinformationen (Lieferanten, Bestellnummern)
                "`perms_parts_prices` INT(3) NOT NULL,".            // Preisinformationen
                "`perms_parts_attachements` INT(3) NOT NULL,".      // Dateianhänge (Bilder, Datenblätter, ...)
                // Baugruppen-Rechte
                "`perms_devices` INT(3) NOT NULL,".                 // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                "`perms_devices_parts` INT(3) NOT NULL,".           // Bauteile betrachten/bearbeiten
                // Lagerorte-Rechte
                "`perms_storelocations` INT(3) NOT NULL,".          // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                "`perms_storelocations_isfull` INT(3) NOT NULL,".   // Eigenschaft, ob Lagerort voll ist
                // Footprints-Rechte
                "`perms_footprints` INT(3) NOT NULL,".              // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Kategorien-Rechte
                "`perms_categories` INT(3) NOT NULL,".              // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Lieferanten-Rechte
                "`perms_suppliers` INT(3) NOT NULL,".               // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Hersteller-Rechte
                "`perms_manufacturers` INT(3) NOT NULL,".           // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Attribute
                " PRIMARY KEY  (`id`),".
                " UNIQUE KEY `name` (`name`)".
                ") ENGINE=InnoDB;";

            // create table "groups"
            $updateSteps[] = "CREATE TABLE `groups` (".
                // Gruppeninformationen
                "`id` INT(11) NOT NULL AUTO_INCREMENT,".            // Gruppen-ID
                "`name` TINYTEXT NOT NULL,".                        // Gruppenname
                "`parent_id` INT(11) NOT NULL,".                    // ID der übergeordneten Gruppe ('-1' bei root)
                "`comment` MEDIUMTEXT,".                            // Kommentar (optional)
                // System-Rechte
                "`perms_system` INT(3) NOT NULL,".                  // Allgemeine Rechte ("Kleinkram")
                "`perms_system_group_childs` INT(3) NOT NULL,".     // Gruppenverwaltung (nur Untergruppen der eigenen Gruppe)
                "`perms_system_group_others` INT(3) NOT NULL,".     // Gruppenverwaltung (alle, ausser die Untergruppen)
                "`perms_system_users_subgroups` INT(3) NOT NULL,".  // Benutzerverwaltung (Benutzer aller untergeortneten Gruppen)
                "`perms_system_users_others` INT(3) NOT NULL,".     // Benutzerverwaltung (alle, ausser von untergeordneten Gruppen)
                "`perms_system_dbupdate` INT(3) NOT NULL,".         // Datenbankaktualisierung bzw. dessen Einstellungen
                "`perms_system_dbbackup` INT(3) NOT NULL,".         // Datenbank-Backup bzw. dessen Einstellungen
                // Bauteil-Rechte
                "`perms_parts` INT(3) NOT NULL,".                   // Betrachten/Erstellen/Löschen/Verschieben
                "`perms_parts_name` INT(3) NOT NULL,".              // Name
                "`perms_parts_description` INT(3) NOT NULL,".       // Beschreibung
                "`perms_parts_instock` INT(3) NOT NULL,".           // Menge (an Lager)
                "`perms_parts_mininstock` INT(3) NOT NULL,".        // Mindestmenge
                "`perms_parts_footprint` INT(3) NOT NULL,".         // Footprint
                "`perms_parts_storelocation` INT(3) NOT NULL,".     // Lagerort
                "`perms_parts_obsolete` INT(3) NOT NULL,".          // Obsolet
                "`perms_parts_comment` INT(3) NOT NULL,".           // Kommentar
                "`perms_parts_orderdetails` INT(3) NOT NULL,".      // Bestellinformationen (Lieferanten, Bestellnummern)
                "`perms_parts_prices` INT(3) NOT NULL,".            // Preisinformationen
                "`perms_parts_attachements` INT(3) NOT NULL,".      // Dateianhänge (Bilder, Datenblätter, ...)
                // Baugruppen-Rechte
                "`perms_devices` INT(3) NOT NULL,".                 // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                "`perms_devices_parts` INT(3) NOT NULL,".           // Bauteile betrachten/bearbeiten
                // Lagerorte-Rechte
                "`perms_storelocations` INT(3) NOT NULL,".          // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                "`perms_storelocations_isfull` INT(3) NOT NULL,".   // Eigenschaft, ob Lagerort voll ist
                // Footprints-Rechte
                "`perms_footprints` INT(3) NOT NULL,".              // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Kategorien-Rechte
                "`perms_categories` INT(3) NOT NULL,".              // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Lieferanten-Rechte
                "`perms_suppliers` INT(3) NOT NULL,".               // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Hersteller-Rechte
                "`perms_manufacturers` INT(3) NOT NULL,".           // Betrachten/Bearbeiten/Erstellen/Löschen/Verschieben
                // Attribute
                " PRIMARY KEY  (`id`)".
                ") ENGINE=InnoDB;";

            // create user "admin"
            $updateSteps[] = "INSERT INTO users SET ".
                "name='admin',".
                "password='81dc9bdb52d04dc20036dbd8313ed055',". // Passwort = "1234"
                "first_name='',".
                "last_name='',".
                "department='',".
                "email='',".
                "auto_login='0',".
                "session_id='',".
                "group_id='0',". // Gruppe 0 existiert nicht in der Datenbank, dies ist nur für den Benutzer "admin" erlaubt
                "perms_system='127',".
                "perms_system_group_childs='127',".
                "perms_system_group_others='127',".
                "perms_system_users_subgroups='127',".
                "perms_system_users_others='127',".
                "perms_system_dbupdate='127',".
                "perms_system_dbbackup='127',".
                "perms_parts='127',".
                "perms_parts_name='127',".
                "perms_parts_description='127',".
                "perms_parts_instock='127',".
                "perms_parts_mininstock='127',".
                "perms_parts_footprint='127',".
                "perms_parts_storelocation='127',".
                "perms_parts_obsolete='127',".
                "perms_parts_comment='127',".
                "perms_parts_orderdetails='127',".
                "perms_parts_prices='127',".
                "perms_parts_attachements='127',".
                "perms_devices='127',".
                "perms_devices_parts='127',".
                "perms_storelocations='127',".
                "perms_storelocations_isfull='127',".
                "perms_footprints='127',".
                "perms_categories='127',".
                "perms_suppliers='127',".
                "perms_manufacturers='127'";
            break;*/

/*
        Templates:

          case 14:
            $updateSteps[] = "INSERT INTO internal (keyName, keyValue) VALUES ('test', 'muh')";
            break;
          case 15:
            $updateSteps[] = "DELETE FROM internal WHERE keyName='test2'";
            break;
*/
          default:
            throw new Exception("Unbekannte Datenbankversion \"$current_version\"!");
            break;
        }

        return $updateSteps;
    }
