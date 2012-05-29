CREATE TABLE `house` (
  id          INT         NOT NULL,
  name        VARCHAR(50) NOT NULL DEFAULT 'Unnamed house',
  maxrooms    INT         NOT NULL DEFAULT '1',
  price       INT         NOT NULL DEFAULT '1',
  fridgesize  INT         NOT NULL DEFAULT '2000',
  PRIMARY KEY(id),
  INDEX(name)
) engine=InnoDB;
