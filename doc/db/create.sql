# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.2                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          K-Shop.dez                                      #
# Project name:                                                          #
# Author:                                                                #
# Script type:           Database creation script                        #
# Created on:            2006-11-08 17:52                                #
# ---------------------------------------------------------------------- #


# ---------------------------------------------------------------------- #
# Tables                                                                 #
# ---------------------------------------------------------------------- #

# ---------------------------------------------------------------------- #
# Add table "Product"                                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE Product (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Number of times product has been viewed by customers',
    categoryID INTEGER UNSIGNED NOT NULL,
    manufacturerID INTEGER UNSIGNED,
    defaultImageID INTEGER UNSIGNED,
    SKU VARCHAR(20),
    dateCreated TIMESTAMP,
    dateUpdated TIMESTAMP,
    status TINYINT DEFAULT 1 COMMENT '0- not available 1- available 2- disabled (not visble)',
    URL TINYTEXT,
    isBestSeller BOOL DEFAULT 0,
    type TINYINT UNSIGNED DEFAULT 0 COMMENT '1 - intangible 0 - tangible',
    voteSum INTEGER UNSIGNED DEFAULT 0,
    voteCount INTEGER UNSIGNED DEFAULT 0,
    hits INTEGER UNSIGNED DEFAULT 0 COMMENT 'Number of times product has been viewed by customers',
    shippingHeight NUMERIC(5,2),
    shippingWidth NUMERIC(5,2),
    shippingLength NUMERIC(5,2),
    shippingWeight NUMERIC(8,3),
    minimumQuantity INTEGER,
    shippingSurgageAmount NUMERIC(12,2),
    isSeparateShipment BOOL,
    isFreeShipping BOOL,
    unitsType TINYINT NOT NULL DEFAULT 0 COMMENT '0- metric 1- english',
    CONSTRAINT PK_Product PRIMARY KEY (ID)
);

CREATE INDEX IDX_Product_1 ON Product (categoryID);

CREATE INDEX IDX_Product_2 ON Product (SKU);

# ---------------------------------------------------------------------- #
# Add table "Category"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Category (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    parentNodeID INTEGER UNSIGNED,
    lft INTEGER,
    rgt INTEGER,
    isActive BOOL DEFAULT 1,
    position INTEGER UNSIGNED DEFAULT 0,
    handle VARCHAR(40),
    CONSTRAINT PK_Category PRIMARY KEY (ID)
);

CREATE UNIQUE INDEX IDX_Category_1 ON Category (handle);

# ---------------------------------------------------------------------- #
# Add table "Language"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Language (
    ID CHAR(2) NOT NULL,
    isEnabled BOOL,
    isDefault BOOL DEFAULT 0,
    CONSTRAINT PK_Language PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductLangData"                                            #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductLangData (
    productID INTEGER UNSIGNED NOT NULL,
    languageID CHAR(2) NOT NULL,
    name VARCHAR(100),
    shortDescription TINYTEXT,
    fullDescription TEXT,
    CONSTRAINT PK_ProductLangData PRIMARY KEY (productID, languageID)
);

CREATE INDEX IDX_ProductLang_1 ON ProductLangData (productID);

CREATE INDEX IDX_ProductLang_2 ON ProductLangData (languageID);

# ---------------------------------------------------------------------- #
# Add table "Specification"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE Specification (
    specFieldValueID INTEGER UNSIGNED NOT NULL,
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_Specification PRIMARY KEY (specFieldValueID, productID, specFieldID)
) COMMENT = 'Product specification: maps input field value list to a particular product';

CREATE INDEX IDX_Specification_1 ON Specification (specFieldValueID);

CREATE INDEX IDX_Specification_2 ON Specification (productID);

# ---------------------------------------------------------------------- #
# Add table "SpecField"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecField (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    type SMALLINT COMMENT 'Field data type. Available types: 1. text field 2. drop down list (select one item from a list) 3. select multiple items from a list',
    dataType SMALLINT COMMENT '0. default (mixed) 1. numeric 2. date/time',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Order number (position relative to other fields)',
    handle VARCHAR(40),
    isMultilingual BOOL,
    CONSTRAINT PK_SpecField PRIMARY KEY (ID)
) COMMENT = 'Field data type. Available types: 1. text field 2. drop down list (select one item from a list) 3. select multiple items from a list';

CREATE INDEX IDX_SpecField_1 ON SpecField (categoryID);

# ---------------------------------------------------------------------- #
# Add table "CategoryLangData"                                           #
# ---------------------------------------------------------------------- #

CREATE TABLE CategoryLangData (
    categoryID INTEGER UNSIGNED NOT NULL,
    languageID CHAR(2) NOT NULL,
    name VARCHAR(100),
    description TEXT,
    keywords VARCHAR(200),
    CONSTRAINT PK_CategoryLangData PRIMARY KEY (categoryID, languageID)
);

CREATE INDEX IDX_CatalogLang_1 ON CategoryLangData (languageID);

CREATE INDEX IDX_CatalogLang_2 ON CategoryLangData (categoryID);

# ---------------------------------------------------------------------- #
# Add table "SpecFieldLangData"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecFieldLangData (
    specFieldID INTEGER UNSIGNED NOT NULL,
    languageID CHAR(2) NOT NULL,
    name VARCHAR(40),
    description TEXT,
    CONSTRAINT PK_SpecFieldLangData PRIMARY KEY (specFieldID, languageID)
);

CREATE INDEX IDX_SpecFieldLang_1 ON SpecFieldLangData (specFieldID);

CREATE INDEX IDX_SpecFieldLang_2 ON SpecFieldLangData (languageID);

# ---------------------------------------------------------------------- #
# Add table "SpecFieldValue"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecFieldValue (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    specFieldID INTEGER UNSIGNED,
    CONSTRAINT PK_SpecFieldValue PRIMARY KEY (ID)
) COMMENT = 'Is there a need to translate this field to diferent languages?';

CREATE INDEX IDX_SpecFieldValue_1 ON SpecFieldValue (specFieldID);

# ---------------------------------------------------------------------- #
# Add table "SpecFieldValueLangData"                                     #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecFieldValueLangData (
    specFieldValueID INTEGER UNSIGNED NOT NULL,
    languageID CHAR(2) NOT NULL,
    value VARCHAR(200),
    CONSTRAINT PK_SpecFieldValueLangData PRIMARY KEY (specFieldValueID, languageID)
);

CREATE INDEX IDX_SpecFieldValueLang_1 ON SpecFieldValueLangData (languageID);

CREATE INDEX IDX_SpecFieldValueLang_2 ON SpecFieldValueLangData (specFieldValueID);

# ---------------------------------------------------------------------- #
# Add table "Filter"                                                     #
# ---------------------------------------------------------------------- #

CREATE TABLE Filter (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    filterGroupID INTEGER UNSIGNED,
    position INTEGER,
    type INTEGER,
    rangeStart FLOAT,
    rangeEnd FLOAT,
    CONSTRAINT PK_Filter PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "FilterGroup"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE FilterGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    specFieldID INTEGER UNSIGNED NOT NULL,
    position INTEGER UNSIGNED DEFAULT 0,
    isEnabled BOOL,
    CONSTRAINT PK_FilterGroup PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "FilterLangData"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE FilterLangData (
    filterID INTEGER UNSIGNED NOT NULL,
    languageID CHAR(2) NOT NULL,
    name VARCHAR(40),
    CONSTRAINT PK_FilterLangData PRIMARY KEY (filterID, languageID)
);

# ---------------------------------------------------------------------- #
# Add table "FilterGroupLangData"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE FilterGroupLangData (
    filterGroupID INTEGER UNSIGNED NOT NULL,
    languageID CHAR(2) NOT NULL,
    name VARCHAR(40),
    CONSTRAINT PK_FilterGroupLangData PRIMARY KEY (filterGroupID, languageID)
);

# ---------------------------------------------------------------------- #
# Add table "RelatedProduct"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE RelatedProduct (
    ProductID INTEGER UNSIGNED NOT NULL,
    relatedProductID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_RelatedProduct PRIMARY KEY (ProductID, relatedProductID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductPrice"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductPrice (
    productID INTEGER UNSIGNED NOT NULL,
    currencyID CHAR(3) NOT NULL,
    price NUMERIC(12,2) NOT NULL,
    CONSTRAINT PK_ProductPrice PRIMARY KEY (productID, currencyID)
);

# ---------------------------------------------------------------------- #
# Add table "Currency"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Currency (
    ID CHAR(3) NOT NULL,
    rate FLOAT(10,5),
    lastUpdated TIMESTAMP,
    isDefault BOOL DEFAULT 0,
    CONSTRAINT PK_Currency PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "Manufacturer"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE Manufacturer (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(60) NOT NULL,
    CONSTRAINT PK_Manufacturer PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductImage"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductImage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED NOT NULL,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_ProductImage PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductImageLangData"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductImageLangData (
    productImageID INTEGER UNSIGNED NOT NULL,
    languageID CHAR(2) NOT NULL,
    title VARCHAR(100) NOT NULL,
    CONSTRAINT PK_ProductImageLangData PRIMARY KEY (productImageID, languageID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductFile"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductFile (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    fileTypeID INTEGER UNSIGNED,
    fileSize INTEGER,
    fileContents BLOB,
    fileName VARCHAR(255),
    CONSTRAINT PK_ProductFile PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "FileType"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE FileType (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'File extension',
    extension CHAR(4),
    name VARCHAR(60),
    contentType VARCHAR(60),
    iconFileName VARCHAR(40),
    CONSTRAINT PK_FileType PRIMARY KEY (ID)
) COMMENT = 'File extension';

# ---------------------------------------------------------------------- #
# Add table "Discount"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Discount (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    amount INTEGER,
    discountType TINYINT COMMENT '1- % 2- currency',
    discountValue NUMERIC,
    CONSTRAINT PK_Discount PRIMARY KEY (ID)
) COMMENT = '1- % 2- currency';

# ---------------------------------------------------------------------- #
# Foreign key constraints                                                #
# ---------------------------------------------------------------------- #

ALTER TABLE Product ADD CONSTRAINT Category_Product 
    FOREIGN KEY (categoryID) REFERENCES Category (ID);

ALTER TABLE Product ADD CONSTRAINT Manufacturer_Product 
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID);

ALTER TABLE Product ADD CONSTRAINT ProductImage_Product 
    FOREIGN KEY (defaultImageID) REFERENCES ProductImage (ID);

ALTER TABLE Category ADD CONSTRAINT Category_Category 
    FOREIGN KEY (parentNodeID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductLangData ADD CONSTRAINT Product_ProductLangData 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductLangData ADD CONSTRAINT Language_ProductLangData 
    FOREIGN KEY (languageID) REFERENCES Language (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE Specification ADD CONSTRAINT SpecFieldValue_Specification 
    FOREIGN KEY (specFieldValueID) REFERENCES SpecFieldValue (ID);

ALTER TABLE Specification ADD CONSTRAINT Product_Specification 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Specification ADD CONSTRAINT SpecField_Specification 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID);

ALTER TABLE SpecField ADD CONSTRAINT Category_SpecField 
    FOREIGN KEY (categoryID) REFERENCES Category (ID);

ALTER TABLE CategoryLangData ADD CONSTRAINT Language_CategoryLangData 
    FOREIGN KEY (languageID) REFERENCES Language (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE CategoryLangData ADD CONSTRAINT Category_CategoryLangData 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecFieldLangData ADD CONSTRAINT SpecField_SpecFieldLangData 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID);

ALTER TABLE SpecFieldLangData ADD CONSTRAINT Language_SpecFieldLangData 
    FOREIGN KEY (languageID) REFERENCES Language (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE SpecFieldValue ADD CONSTRAINT SpecField_SpecFieldValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID);

ALTER TABLE SpecFieldValueLangData ADD CONSTRAINT Language_SpecFieldValueLangData 
    FOREIGN KEY (languageID) REFERENCES Language (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE SpecFieldValueLangData ADD CONSTRAINT SpecFieldValue_SpecFieldValueLangData 
    FOREIGN KEY (specFieldValueID) REFERENCES SpecFieldValue (ID);

ALTER TABLE Filter ADD CONSTRAINT FilterGroup_Filter 
    FOREIGN KEY (filterGroupID) REFERENCES FilterGroup (ID);

ALTER TABLE FilterGroup ADD CONSTRAINT SpecField_FilterGroup 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID);

ALTER TABLE FilterLangData ADD CONSTRAINT Filter_FilterLangData 
    FOREIGN KEY (filterID) REFERENCES Filter (ID);

ALTER TABLE FilterLangData ADD CONSTRAINT Language_FilterLangData 
    FOREIGN KEY (languageID) REFERENCES Language (ID) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE FilterGroupLangData ADD CONSTRAINT Language_FilterGroupLangData 
    FOREIGN KEY (languageID) REFERENCES Language (ID);

ALTER TABLE FilterGroupLangData ADD CONSTRAINT FilterGroup_FilterGroupLangData 
    FOREIGN KEY (filterGroupID) REFERENCES FilterGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct_ 
    FOREIGN KEY (ProductID) REFERENCES Product (ID);

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct 
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID);

ALTER TABLE ProductPrice ADD CONSTRAINT Product_ProductPrice 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE ProductPrice ADD CONSTRAINT Currency_ProductPrice 
    FOREIGN KEY (currencyID) REFERENCES Currency (ID);

ALTER TABLE ProductImage ADD CONSTRAINT Product_ProductImage 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE ProductImageLangData ADD CONSTRAINT Language_ProductImageLangData 
    FOREIGN KEY (languageID) REFERENCES Language (ID);

ALTER TABLE ProductImageLangData ADD CONSTRAINT ProductImage_ProductImageLangData 
    FOREIGN KEY (productImageID) REFERENCES ProductImage (ID);

ALTER TABLE ProductFile ADD CONSTRAINT Product_ProductFile 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE ProductFile ADD CONSTRAINT FileType_ProductFile 
    FOREIGN KEY (fileTypeID) REFERENCES FileType (ID);

ALTER TABLE Discount ADD CONSTRAINT Product_Discount 
    FOREIGN KEY (ID) REFERENCES Product (ID);
