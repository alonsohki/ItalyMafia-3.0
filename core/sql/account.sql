DROP TABLE IF EXISTS `account`;

CREATE TABLE `account` (
  id              INT           NOT NULL  auto_increment,
  name            VARCHAR(32)   NOT NULL,
  password        VARCHAR(32)   NOT NULL,
  email           VARCHAR(64)   NOT NULL,
  adminlevel      INT           NOT NULL DEFAULT '5',
  last_login      INT           NOT NULL,
  last_ip         VARCHAR(15)   NOT NULL,
  banned          VARCHAR(200)  NULL,
  banned_by       INT           NULL,
  ban_date        INT           NULL,
  ban_expiration  INT           NULL,

  PRIMARY KEY(id),
  UNIQUE(email),
  INDEX login_index(name, password),
  INDEX(banned, banned_by),
  FOREIGN KEY(banned_by) REFERENCES account(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) engine=InnoDB;
