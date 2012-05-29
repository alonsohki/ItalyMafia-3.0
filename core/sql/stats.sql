DROP TABLE IF EXISTS `stats`;

CREATE TABLE `stats` (
  acc         INT     NOT NULL,
  skin        INT     NOT NULL DEFAULT '137',
  sex         ENUM('M', 'F') NOT NULL DEFAULT 'M',
  age         INT     NOT NULL DEFAULT '18',
  location    INT     NOT NULL DEFAULT '0',
  x           DOUBLE  NOT NULL DEFAULT '0.0',
  y           DOUBLE  NOT NULL DEFAULT '0.0',
  z           DOUBLE  NOT NULL DEFAULT '0.0',
  angle       DOUBLE  NOT NULL DEFAULT '0.0',
  vworld      INT     NOT NULL DEFAULT '0',
  hunger      INT     NOT NULL DEFAULT '5000',
  injures     DOUBLE  NOT NULL DEFAULT '60.0',
  money       BIGINT  NOT NULL DEFAULT '500',
  bank        BIGINT  NOT NULL DEFAULT '0',
  level       INT     NOT NULL DEFAULT '1',
  experience  INT     NOT NULL DEFAULT '0',
  upgradepts  INT     NOT NULL DEFAULT '0',
  bankfreezed INT     NOT NULL DEFAULT '0',
  strength    INT     NOT NULL DEFAULT '0',
  reports     INT     NOT NULL DEFAULT '0',
  married     INT     NULL,
  faction     INT     NULL,
  onduty      INT     NOT NULL DEFAULT '1',
  rank        INT     NULL,
  vehicle     INT     NULL,
  phone       INT     NULL,
  speedo      ENUM('kmh', 'mph')  NOT NULL DEFAULT 'kmh',
  jailtime    INT     NOT NULL DEFAULT '0',

  /* Upgrades */
  sk_cooking      INT     NOT NULL DEFAULT '0',
  sk_luck         INT     NOT NULL DEFAULT '0',
  sk_alcoholic    INT     NOT NULL DEFAULT '0',
  sk_drugsaddict  INT     NOT NULL DEFAULT '0',
  sk_profkiller   INT     NOT NULL DEFAULT '0',
  sk_gunsdealer   INT     NOT NULL DEFAULT '0',
  sk_drugsdealer  INT     NOT NULL DEFAULT '0',
  sk_negskills    INT     NOT NULL DEFAULT '0',
  sk_farming      INT     NOT NULL DEFAULT '0',
  sk_fishing      INT     NOT NULL DEFAULT '0',

  /* Gun slots */
  slot_0          INT     NOT NULL DEFAULT '0',
  slot_1          INT     NOT NULL DEFAULT '0',
  slot_2          INT     NOT NULL DEFAULT '0',
  slot_3          INT     NOT NULL DEFAULT '0',
  slot_4          INT     NOT NULL DEFAULT '0',
  slot_5          INT     NOT NULL DEFAULT '0',
  slot_6          INT     NOT NULL DEFAULT '0',
  slot_7          INT     NOT NULL DEFAULT '0',
  slot_8          INT     NOT NULL DEFAULT '0',
  slot_9          INT     NOT NULL DEFAULT '0',
  slot_10         INT     NOT NULL DEFAULT '0',
  slot_11         INT     NOT NULL DEFAULT '0',
  slot_12         INT     NOT NULL DEFAULT '0',


  PRIMARY KEY(acc),
  KEY(faction),
  KEY(married),
  FOREIGN KEY(acc) REFERENCES account(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY(vehicle) REFERENCES vehicle(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY(faction) REFERENCES faction(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  FOREIGN KEY(married) REFERENCES account(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) engine=InnoDB;
