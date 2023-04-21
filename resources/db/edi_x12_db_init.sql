CREATE SCHEMA `edi_x12_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin ;

CREATE USER `edi_db_user`@localhost IDENTIFIED BY 'test_pass12';
GRANT ALTER,CREATE,SELECT,UPDATE,INDEX,INSERT,CREATE VIEW ON `edi_x12_db`.* TO `edi_db_user`@localhost;
FLUSH PRIVILEGES;

CREATE TABLE `edi_x12_db`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(45) NOT NULL,
  `psw_enc` VARCHAR(45) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `active` BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

INSERT INTO `edi_x12_db`.`users`(user_name,psw_enc) VALUES ("user_test1","pass_test1");
INSERT INTO `edi_x12_db`.`users`(user_name,psw_enc) VALUES ("user_test2","pass_test2");

CREATE TABLE `edi_x12_db`.`edi_files` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(256) NOT NULL,
  `file_type` VARCHAR(80) NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `active` BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

INSERT INTO `edi_x12_db`.`edi_files`( file_name, file_type, content) VALUES('test_file1','.edi','random_content');