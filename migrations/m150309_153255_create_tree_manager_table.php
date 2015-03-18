<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2015 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use yii\db\Migration;

class m150309_153255_create_tree_manager_table extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->execute(
            "
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `dmstr_page`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dmstr_page` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique tree node identifier',
  `root` INT(11) NULL DEFAULT NULL COMMENT 'Tree root identifier',
  `lft` INT(11) NOT NULL COMMENT 'Nested set left property',
  `rgt` INT(11) NOT NULL COMMENT 'Nested set right property',
  `lvl` SMALLINT(5) NOT NULL COMMENT 'Nested set level / depth',
  `page_title` VARCHAR(255) NULL COMMENT 'The page title',
  `name` VARCHAR(60) NOT NULL COMMENT 'The tree node name / label',
  `name_id` VARCHAR(255) NOT NULL COMMENT 'The unique name_id',
  `slug` VARCHAR(255) NULL COMMENT 'The auto generated slugged name_id',
  `route` VARCHAR(255) NULL COMMENT 'The controller/view route',
  `view` VARCHAR(255) NULL COMMENT 'The view to render through the given route',
  `default_meta_keywords` VARCHAR(255) NULL COMMENT 'SEO - meta keywords - comma seperated',
  `default_meta_description` TEXT NULL COMMENT 'SEO - meta description',
  `request_params` TEXT NULL COMMENT 'JSON - request params',
  `owner` INT(11) NULL COMMENT 'The owner user id how created the page node',
  `access_owner` INT(11) NULL DEFAULT NULL,
  `access_domain` VARCHAR(8) NULL DEFAULT NULL,
  `access_read` VARCHAR(255) NULL DEFAULT NULL,
  `access_update` VARCHAR(255) NULL DEFAULT NULL,
  `access_delete` VARCHAR(255) NULL DEFAULT NULL,
  `icon` VARCHAR(255) NULL DEFAULT NULL COMMENT 'The icon to use for the node',
  `icon_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Icon Type: 1 = CSS Class, 2 = Raw Markup',
  `active` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Whether the node is active (will be set to false on deletion)',
  `selected` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Whether the node is selected/checked by default',
  `disabled` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Whether the node is enabled',
  `readonly` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Whether the node is read only (unlike disabled - will allow toolbar actions)',
  `visible` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Whether the node is visible',
  `collapsed` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Whether the node is collapsed by default',
  `movable_u` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Whether the node is movable one position up',
  `movable_d` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Whether the node is movable one position down',
  `movable_l` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Whether the node is movable to the left (from sibling to parent)',
  `movable_r` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Whether the node is movable to the right (from sibling to child)',
  `removable` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Whether the node is removable (any children below will be moved as siblings before deletion)',
  `removable_all` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Whether the node is removable along with descendants',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `tbl_tree_NK1` (`root` ASC),
  INDEX `tbl_tree_NK2` (`lft` ASC),
  INDEX `tbl_tree_NK3` (`rgt` ASC),
  INDEX `tbl_tree_NK4` (`lvl` ASC),
  INDEX `tbl_tree_NK5` (`active` ASC),
  UNIQUE INDEX `name_id_UNIQUE` (`name_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
"
        );
    }

    public function safeDown()
    {
        $this->dropTable('dmstr_page');
    }
}
