CREATE TABLE faction (
  id          INT           NOT NULL,
  name        VARCHAR(50)   NOT NULL,
  color       BIGINT        NOT NULL DEFAULT '4294967040', /* 0xFFFFFF00 */
  bank        BIGINT        NOT NULL DEFAULT '0',
  bankfreezed INT           NOT NULL DEFAULT '0',
  maxvehicles INT           NOT NULL DEFAULT '1',
  maxmembers  INT           NOT NULL DEFAULT '21',
  stock1      BIGINT        NOT NULL DEFAULT '0',
  stock2      BIGINT        NOT NULL DEFAULT '0',
  stock3      BIGINT        NOT NULL DEFAULT '0',
  HQ          INT           NULL,
  PRIMARY KEY(id),
  KEY(name)
/* FOREIGN KEY(HQ) REFERENCES room(id)
    ON DELETE SET NULL ON UPDATE SET NULL*/
) engine=InnoDB;
