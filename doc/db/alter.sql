# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.3                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Alter database script                           #
# Created on:            2007-01-30 15:57                                #
# ---------------------------------------------------------------------- #


# ---------------------------------------------------------------------- #
# Drop foreign key constraints                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE Product DROP FOREIGN KEY Category_Product;

ALTER TABLE Product DROP FOREIGN KEY Manufacturer_Product;

ALTER TABLE Product DROP FOREIGN KEY ProductImage_Product;

ALTER TABLE Category DROP FOREIGN KEY Category_Category;

ALTER TABLE SpecificationItem DROP FOREIGN KEY SpecFieldValue_SpecificationItem;

ALTER TABLE SpecificationItem DROP FOREIGN KEY Product_SpecificationItem;

ALTER TABLE SpecificationItem DROP FOREIGN KEY SpecField_SpecificationItem;

ALTER TABLE SpecField DROP FOREIGN KEY Category_SpecField;

ALTER TABLE SpecFieldValue DROP FOREIGN KEY SpecField_SpecFieldValue;

ALTER TABLE Filter DROP FOREIGN KEY FilterGroup_Filter;

ALTER TABLE Filter DROP FOREIGN KEY SpecFieldValue_Filter;

ALTER TABLE FilterGroup DROP FOREIGN KEY SpecField_FilterGroup;

ALTER TABLE RelatedProduct DROP FOREIGN KEY Product_RelatedProduct_;

ALTER TABLE RelatedProduct DROP FOREIGN KEY Product_RelatedProduct;

ALTER TABLE ProductPrice DROP FOREIGN KEY Product_ProductPrice;

ALTER TABLE ProductPrice DROP FOREIGN KEY Currency_ProductPrice;

ALTER TABLE ProductImage DROP FOREIGN KEY Product_ProductImage;

ALTER TABLE ProductFile DROP FOREIGN KEY Product_ProductFile;

ALTER TABLE ProductFile DROP FOREIGN KEY FileType_ProductFile;

ALTER TABLE Discount DROP FOREIGN KEY Product_Discount;

ALTER TABLE CategoryImage DROP FOREIGN KEY Category_CategoryImage;

ALTER TABLE SpecificationNumericValue DROP FOREIGN KEY Product_SpecificationNumericValue;

ALTER TABLE SpecificationNumericValue DROP FOREIGN KEY SpecField_SpecificationNumericValue;

ALTER TABLE SpecificationStringValue DROP FOREIGN KEY Product_SpecificationStringValue;

ALTER TABLE SpecificationStringValue DROP FOREIGN KEY SpecField_SpecificationStringValue;

ALTER TABLE SpecificationDateValue DROP FOREIGN KEY Product_SpecificationDateValue;

ALTER TABLE SpecificationDateValue DROP FOREIGN KEY SpecField_SpecificationDateValue;

# ---------------------------------------------------------------------- #
# Modify table "Product"                                                 #
# ---------------------------------------------------------------------- #

ALTER TABLE Product DROP COLUMN shippingHeight;

ALTER TABLE Product DROP COLUMN shippingWidth;

ALTER TABLE Product DROP COLUMN shippingLength;

ALTER TABLE Product ADD COLUMN stockCount FLOAT;

ALTER TABLE Product ADD COLUMN reservedCount FLOAT;

ALTER TABLE Product MODIFY minimumQuantity FLOAT;

# ---------------------------------------------------------------------- #
# Modify table "Category"                                                #
# ---------------------------------------------------------------------- #

ALTER TABLE Category ALTER COLUMN position DROP DEFAULT;

ALTER TABLE Category DROP COLUMN position;

CREATE TABLE CustomerOrder (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    dateCreated TIMESTAMP,
    dateCompleted TIMESTAMP,
    CONSTRAINT PK_CustomerOrder PRIMARY KEY (ID)
);

CREATE TABLE OrderedItem (
    ProductID INTEGER UNSIGNED NOT NULL,
    CustomerOrderID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_OrderedItem PRIMARY KEY (ProductID, CustomerOrderID)
);

CREATE TABLE User (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(60),
    password VARCHAR(16),
    firstName VARCHAR(20),
    middleName VARCHAR(20),
    lastName VARCHAR(20),
    fullName VARCHAR(60),
    nickname VARCHAR(20),
    creationDate TIMESTAMP,
    isActive BOOL,
    CONSTRAINT PK_User PRIMARY KEY (ID)
) COMMENT = 'Store system base user (including frontend and backend)';

CREATE TABLE UserConfigValue (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER,
    name VARCHAR(25),
    value VARCHAR(100),
    CONSTRAINT PK_UserConfigValue PRIMARY KEY (ID)
) COMMENT = 'User related application data (config)';

CREATE TABLE AccessControlList (
    UserID INTEGER UNSIGNED NOT NULL,
    RoleGroupID INTEGER UNSIGNED NOT NULL,
    RoleID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_AccessControlList PRIMARY KEY (UserID, RoleGroupID, RoleID)
);

CREATE TABLE RoleGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(60) NOT NULL,
    description TEXT,
    parent INTEGER DEFAULT 0,
    lft INTEGER,
    rgt INTEGER,
    CONSTRAINT PK_RoleGroup PRIMARY KEY (ID)
) COMMENT = 'A list of role based groups in a store system';

CREATE TABLE UserGroup (
    userID INTEGER UNSIGNED NOT NULL,
    roleGroupID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_UserGroup PRIMARY KEY (userID, roleGroupID)
) COMMENT = 'User mapping to roleGroups (many-to-many relationship)';

CREATE TABLE Role (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    CONSTRAINT PK_Role PRIMARY KEY (ID)
);

CREATE TABLE InterfaceTranslation (
    ID CHAR(2) NOT NULL,
    interfaceData LONGTEXT,
    CONSTRAINT PK_InterfaceTranslation PRIMARY KEY (ID)
);

CREATE TABLE SiteConfig (
    ID VARCHAR(40) NOT NULL,
    value VARCHAR(100) NOT NULL DEFAULT '',
    CONSTRAINT PK_SiteConfig PRIMARY KEY (ID)
);

CREATE TABLE State (
    code VARCHAR(3) NOT NULL,
    countryCode VARCHAR(2) NOT NULL,
    name VARCHAR(40),
    subdivisionType VARCHAR(60),
    CONSTRAINT PK_State PRIMARY KEY (code, countryCode)
);

CREATE TABLE City (
    countryCode VARCHAR(2) NOT NULL,
    stateCode VARCHAR(3) NOT NULL,
    name VARCHAR(60),
    asciiName VARCHAR(60),
    latitude NUMERIC(2),
    longitude NUMERIC(2),
    CONSTRAINT PK_City PRIMARY KEY (countryCode, stateCode)
);

CREATE TABLE PostalCode (
    countryCode VARCHAR(2) NOT NULL,
    code VARCHAR(10) NOT NULL,
    state VARCHAR(50),
    name VARCHAR(60),
    asciiName VARCHAR(60),
    latitude NUMERIC(2),
    longitude NUMERIC(2),
    CONSTRAINT PK_PostalCode PRIMARY KEY (countryCode, code)
);

CREATE TABLE SpecFieldGroup (
    ID INTEGER NOT NULL,
    categoryID INTEGER,
    name TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_SpecFieldGroup PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add foreign key constraints                                            #
# ---------------------------------------------------------------------- #

ALTER TABLE Product ADD CONSTRAINT Category_Product 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Product ADD CONSTRAINT Manufacturer_Product 
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE Product ADD CONSTRAINT ProductImage_Product 
    FOREIGN KEY (defaultImageID) REFERENCES ProductImage (ID);

ALTER TABLE Category ADD CONSTRAINT Category_Category 
    FOREIGN KEY (parentNodeID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationItem ADD CONSTRAINT SpecFieldValue_SpecificationItem 
    FOREIGN KEY (specFieldValueID) REFERENCES SpecFieldValue (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationItem ADD CONSTRAINT Product_SpecificationItem 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationItem ADD CONSTRAINT SpecField_SpecificationItem 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecField ADD CONSTRAINT Category_SpecField 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecFieldValue ADD CONSTRAINT SpecField_SpecFieldValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderedItem ADD CONSTRAINT Product_OrderedItem 
    FOREIGN KEY (ProductID) REFERENCES Product (ID);

ALTER TABLE OrderedItem ADD CONSTRAINT CustomerOrder_OrderedItem 
    FOREIGN KEY (CustomerOrderID) REFERENCES CustomerOrder (ID);

ALTER TABLE UserConfigValue ADD CONSTRAINT User_UserConfigValue 
    FOREIGN KEY (userID) REFERENCES User (ID);

ALTER TABLE AccessControlList ADD CONSTRAINT User_AccessControlList 
    FOREIGN KEY (UserID) REFERENCES User (ID);

ALTER TABLE AccessControlList ADD CONSTRAINT RoleGroup_AccessControlList 
    FOREIGN KEY (RoleGroupID) REFERENCES RoleGroup (ID);

ALTER TABLE AccessControlList ADD CONSTRAINT Role_AccessControlList 
    FOREIGN KEY (RoleID) REFERENCES Role (ID);

ALTER TABLE UserGroup ADD CONSTRAINT User_UserGroup 
    FOREIGN KEY (userID) REFERENCES User (ID);

ALTER TABLE UserGroup ADD CONSTRAINT RoleGroup_UserGroup 
    FOREIGN KEY (roleGroupID) REFERENCES RoleGroup (ID);

ALTER TABLE Filter ADD CONSTRAINT FilterGroup_Filter 
    FOREIGN KEY (filterGroupID) REFERENCES FilterGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Filter ADD CONSTRAINT SpecFieldValue_Filter 
    FOREIGN KEY (specFieldValueID) REFERENCES SpecFieldValue (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE FilterGroup ADD CONSTRAINT SpecField_FilterGroup 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct_ 
    FOREIGN KEY (ProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct 
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE InterfaceTranslation ADD CONSTRAINT Language_InterfaceTranslation 
    FOREIGN KEY (ID) REFERENCES Language (ID);

ALTER TABLE ProductPrice ADD CONSTRAINT Product_ProductPrice 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT Currency_ProductPrice 
    FOREIGN KEY (currencyID) REFERENCES Currency (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE ProductImage ADD CONSTRAINT Product_ProductImage 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE ProductFile ADD CONSTRAINT Product_ProductFile 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductFile ADD CONSTRAINT FileType_ProductFile 
    FOREIGN KEY (fileTypeID) REFERENCES FileType (ID) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE Discount ADD CONSTRAINT Product_Discount 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE CategoryImage ADD CONSTRAINT Category_CategoryImage 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE;

ALTER TABLE SpecificationNumericValue ADD CONSTRAINT Product_SpecificationNumericValue 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationNumericValue ADD CONSTRAINT SpecField_SpecificationNumericValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationStringValue ADD CONSTRAINT Product_SpecificationStringValue 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationStringValue ADD CONSTRAINT SpecField_SpecificationStringValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationDateValue ADD CONSTRAINT Product_SpecificationDateValue 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationDateValue ADD CONSTRAINT SpecField_SpecificationDateValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE City ADD CONSTRAINT State_City 
    FOREIGN KEY (countryCode, stateCode) REFERENCES State (countryCode,code);

ALTER TABLE SpecFieldGroup ADD CONSTRAINT Category_SpecFieldGroup 
    FOREIGN KEY (categoryID) REFERENCES Category (ID);
