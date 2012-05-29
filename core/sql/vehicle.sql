DROP TABLE IF EXISTS `vehicle`;

CREATE TABLE `vehicle` (
  id      INT     NOT NULL auto_increment,
  owner   INT     NOT NULL,
  model   INT     NOT NULL,
  color1  INT     NOT NULL DEFAULT '0',
  color2  INT     NOT NULL DEFAULT '0',
  locked  INT     NOT NULL DEFAULT '0',
  fuel    INT     NOT NULL DEFAULT '100',
  age     INT     NOT NULL DEFAULT '100',
  x       DOUBLE  NOT NULL DEFAULT '0.0',
  y       DOUBLE  NOT NULL DEFAULT '0.0',
  z       DOUBLE  NOT NULL DEFAULT '0.0',
  angle   DOUBLE  NOT NULL DEFAULT '0.0',
  PRIMARY KEY(id),
  FOREIGN KEY(owner) REFERENCES account(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) engine=InnoDB;
