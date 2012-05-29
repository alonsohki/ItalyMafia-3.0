CREATE TABLE room (
  id        INT     NOT NULL  auto_increment,
  house     INT     NULL,
  `number`  INT     NOT NULL,
  owner     INT     NULL,
  fowner    INT     NULL,
  price     INT     NOT NULL DEFAULT '-1',
  fridge    INT     NOT NULL DEFAULT '0',
  PRIMARY KEY(id),
  KEY(house, `number`),
  FOREIGN KEY(house) REFERENCES house(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY(owner) REFERENCES account(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY(fowner) REFERENCES faction(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) engine=InnoDB;
