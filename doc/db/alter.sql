ALTER TABLE Product MODIFY ID INTEGER UNSIGNED NOT NULL COMMENT 'Number of times product has been viewed by customers';

ALTER TABLE Product ADD COLUMN isOnSale BOOL;

ALTER TABLE Product MODIFY isOnSale BOOL AFTER isFeatured;

ALTER TABLE Product MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Number of times product has been viewed by customers';

ALTER TABLE ProductPrice ADD COLUMN listPrice NUMERIC(12,2);

ALTER TABLE Currency ADD COLUMN decimalSeparator CHAR(3) DEFAULT '.';

ALTER TABLE Currency ADD COLUMN thousandSeparator CHAR(3);

ALTER TABLE Currency ADD COLUMN decimalCount INTEGER DEFAULT 2;

CREATE TABLE SearchLog (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    keywords VARCHAR(100),
    ip INTEGER,
    time DATETIME,
    CONSTRAINT PK_SearchLog PRIMARY KEY (ID),
    CONSTRAINT TUC_SearchLog_1 UNIQUE (keywords, ip)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_SearchLog_1 ON SearchLog (keywords);

CREATE INDEX IDX_SearchLog_2 ON SearchLog (time);