
-- -----------------------------------------------------
-- Table `bewer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bewer` (
  `id_bwr` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bwr_name` CHAR(64) NOT NULL,
  `bwr_add_date` TIMESTAMP NOT NULL,
  `bwr_remove_date` TIMESTAMP NULL,
  `bwr_removed` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_bwr`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `beer`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `beer` (
  `id_beer` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_bwr` INT UNSIGNED NOT NULL,
  `beer_product_id` INT(10) UNSIGNED NOT NULL COMMENT 'product id of beer',
  `beer_id` INT(10) UNSIGNED NOT NULL COMMENT 'beer id from api',
  `beer_name` CHAR(64) NOT NULL COMMENT 'beer name',
  `beer_price` DECIMAL(6,2) UNSIGNED NOT NULL COMMENT 'beer price',
  `beer_image` CHAR(255) NOT NULL COMMENT 'beer img url',
  `beer_type` CHAR(16) NOT NULL COMMENT 'beer type',
  `beer_on_sale` TINYINT(1) NOT NULL COMMENT '1 - on sale, 0 - not on sale',
  `beer_liter_price` DECIMAL(6,2) UNSIGNED NOT NULL COMMENT 'price fo one liter of beer',
  `beer_country_code` CHAR(3) NOT NULL,
  `beer_price_per_size` DECIMAL(6,2) UNSIGNED NOT NULL,
  `beer_size` CHAR(32) NOT NULL,
  `beer_country` CHAR(16) NOT NULL,
  `beer_add_date` TIMESTAMP NOT NULL,
  `beer_remove_date` TIMESTAMP NULL,
  `beer_removed` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_beer`),
  INDEX `fk_beer_brewer_idx` (`id_bwr` ASC),
  CONSTRAINT `fk_beer_brewer`
    FOREIGN KEY (`id_bwr`)
    REFERENCES `bewer` (`id_bwr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
