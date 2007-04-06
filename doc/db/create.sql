# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.3                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Database creation script                        #
# Created on:            2007-04-06 13:31                                #
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
    isEnabled BOOL DEFAULT 1 COMMENT '0- not available 1- available 2- disabled (not visble)',
    sku VARCHAR(20),
    name TEXT,
    shortDescription TEXT,
    longDescription TEXT,
    keywords TEXT,
    dateCreated TIMESTAMP,
    dateUpdated TIMESTAMP,
    URL TINYTEXT,
    handle VARCHAR(40),
    isBestSeller BOOL DEFAULT 0,
    type TINYINT UNSIGNED DEFAULT 0 COMMENT '1 - intangible 0 - tangible',
    voteSum INTEGER UNSIGNED DEFAULT 0,
    voteCount INTEGER UNSIGNED DEFAULT 0,
    hits INTEGER UNSIGNED DEFAULT 0 COMMENT 'Number of times product has been viewed by customers',
    minimumQuantity FLOAT,
    shippingSurchargeAmount NUMERIC(12,2),
    isSeparateShipment BOOL,
    isFreeShipping BOOL,
    isBackOrderable BOOL,
    isFractionUnit BOOL,
    shippingWeight NUMERIC(8,3),
    stockCount FLOAT,
    reservedCount FLOAT,
    CONSTRAINT PK_Product PRIMARY KEY (ID)
);

CREATE INDEX IDX_Product_1 ON Product (categoryID);

CREATE INDEX IDX_Product_2 ON Product (sku);

# ---------------------------------------------------------------------- #
# Add table "Category"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Category (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    parentNodeID INTEGER UNSIGNED,
    defaultImageID INTEGER UNSIGNED,
    name TEXT,
    description TEXT,
    keywords TEXT,
    activeProductCount INTEGER UNSIGNED DEFAULT 0,
    totalProductCount INTEGER DEFAULT 0,
    availableProductCount INTEGER,
    isEnabled BOOL DEFAULT 0,
    handle VARCHAR(40),
    lft INTEGER,
    rgt INTEGER,
    CONSTRAINT PK_Category PRIMARY KEY (ID)
);

CREATE INDEX IDX_Category_1 ON Category (defaultImageID);

# ---------------------------------------------------------------------- #
# Add table "Language"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Language (
    ID CHAR(2) NOT NULL,
    isEnabled BOOL,
    isDefault BOOL DEFAULT 0,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_Language PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "SpecificationItem"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationItem (
    specFieldValueID INTEGER UNSIGNED NOT NULL,
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_SpecificationItem PRIMARY KEY (specFieldValueID, productID, specFieldID)
) COMMENT = 'Product specification: maps input field value list to a particular product';

CREATE INDEX IDX_Specification_1 ON SpecificationItem (specFieldValueID);

CREATE INDEX IDX_Specification_2 ON SpecificationItem (productID);

# ---------------------------------------------------------------------- #
# Add table "SpecField"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecField (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    specFieldGroupID INTEGER UNSIGNED,
    name TEXT,
    description TEXT,
    type SMALLINT DEFAULT 1 COMMENT 'Field data type. Available types: 1. selector (numeric) 2. input (numeric) 3. input (text) 4. editor (text) 5. selector (text) 6. Date',
    dataType SMALLINT DEFAULT 0 COMMENT '1. text 2. numeric',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Order number (position relative to other fields)',
    handle VARCHAR(40),
    isMultiValue BOOL,
    isRequired BOOL,
    isDisplayed BOOL,
    isDisplayedInList BOOL,
    valuePrefix TEXT,
    valueSuffix TEXT,
    CONSTRAINT PK_SpecField PRIMARY KEY (ID)
) COMMENT = 'Field data type. Available types: 1. text field 2. drop down list (select one item from a list) 3. select multiple items from a list';

CREATE INDEX IDX_SpecField_1 ON SpecField (categoryID);

# ---------------------------------------------------------------------- #
# Add table "SpecFieldValue"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecFieldValue (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    specFieldID INTEGER UNSIGNED,
    value TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_SpecFieldValue PRIMARY KEY (ID)
) COMMENT = 'Is there a need to translate this field to diferent languages?';

CREATE INDEX IDX_SpecFieldValue_1 ON SpecFieldValue (specFieldID);

# ---------------------------------------------------------------------- #
# Add table "CustomerOrder"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE CustomerOrder (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER UNSIGNED,
    sessionID CHAR(32),
    dateCreated TIMESTAMP,
    dateCompleted TIMESTAMP,
    status TINYINT COMMENT '0 - new 1 - partially paid 2 - paid 3 - awaiting shipment 4 - partially shipped 5 - shipped 6 - confirmed as delivered 7 - pending return 8 - returned',
    CONSTRAINT PK_CustomerOrder PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "OrderedItem"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderedItem (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED NOT NULL,
    customerOrderID INTEGER UNSIGNED NOT NULL,
    shipmentID INTEGER,
    priceCurrencyID CHAR(3),
    count FLOAT,
    reservedProductCount FLOAT,
    dateAdded TIMESTAMP,
    price FLOAT,
    isSavedForLater BOOL,
    CONSTRAINT PK_OrderedItem PRIMARY KEY (ID)
);

CREATE INDEX IDX_OrderedItem_1 ON OrderedItem (productID);

CREATE INDEX IDX_OrderedItem_2 ON OrderedItem (customerOrderID);

CREATE INDEX IDX_OrderedItem_3 ON OrderedItem (isSavedForLater);

# ---------------------------------------------------------------------- #
# Add table "User"                                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE User (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    defaultShippingAddressID INTEGER UNSIGNED,
    defaultBillingAddressID INTEGER UNSIGNED,
    email VARCHAR(60),
    password VARCHAR(16),
    firstName VARCHAR(20),
    middleName VARCHAR(20),
    lastName VARCHAR(20),
    fullName VARCHAR(60),
    nickname VARCHAR(20),
    dateCreated TIMESTAMP,
    isEnabled BOOL,
    CONSTRAINT PK_User PRIMARY KEY (ID)
) COMMENT = 'Store system base user (including frontend and backend)';

CREATE UNIQUE INDEX IDX_email ON User (email);

# ---------------------------------------------------------------------- #
# Add table "AccessControlList"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE AccessControlList (
    UserID INTEGER UNSIGNED NOT NULL,
    RoleGroupID INTEGER UNSIGNED NOT NULL,
    RoleID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_AccessControlList PRIMARY KEY (UserID, RoleGroupID, RoleID)
);

# ---------------------------------------------------------------------- #
# Add table "RoleGroup"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE RoleGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(60) NOT NULL,
    description TEXT,
    parent INTEGER DEFAULT 0,
    lft INTEGER,
    rgt INTEGER,
    CONSTRAINT PK_RoleGroup PRIMARY KEY (ID)
) COMMENT = 'A list of role based groups in a store system';

# ---------------------------------------------------------------------- #
# Add table "UserGroup"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE UserGroup (
    userID INTEGER UNSIGNED NOT NULL,
    roleGroupID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_UserGroup PRIMARY KEY (userID, roleGroupID)
) COMMENT = 'User mapping to roleGroups (many-to-many relationship)';

# ---------------------------------------------------------------------- #
# Add table "Filter"                                                     #
# ---------------------------------------------------------------------- #

CREATE TABLE Filter (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    filterGroupID INTEGER UNSIGNED,
    name TEXT,
    position INTEGER,
    type INTEGER,
    rangeStart FLOAT,
    rangeEnd FLOAT,
    rangeDateStart DATE,
    rangeDateEnd DATE,
    CONSTRAINT PK_Filter PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "FilterGroup"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE FilterGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    specFieldID INTEGER UNSIGNED NOT NULL,
    name TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    isEnabled BOOL,
    CONSTRAINT PK_FilterGroup PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "Role"                                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE Role (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    CONSTRAINT PK_Role PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductRelationship"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductRelationship (
    ProductID INTEGER UNSIGNED NOT NULL,
    relatedProductID INTEGER UNSIGNED NOT NULL,
    productRelationshipGroupID INTEGER UNSIGNED,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_ProductRelationship PRIMARY KEY (ProductID, relatedProductID)
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
    isEnabled BOOL DEFAULT 0,
    position INTEGER UNSIGNED DEFAULT 0,
    pricePrefix TEXT,
    priceSuffix TEXT,
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
    title TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_ProductImage PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductFile"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductFile (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    productFileGroupID INTEGER UNSIGNED,
    fileName VARCHAR(255),
    extension VARCHAR(20),
    title TEXT,
    description TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    allowDownloadDays INTEGER,
    CONSTRAINT PK_ProductFile PRIMARY KEY (ID)
);

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
# Add table "CategoryImage"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE CategoryImage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    title TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_CategoryImage PRIMARY KEY (ID)
);

CREATE INDEX IDX_CategoryImage_1 ON CategoryImage (categoryID);

# ---------------------------------------------------------------------- #
# Add table "SpecificationNumericValue"                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationNumericValue (
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    value FLOAT,
    CONSTRAINT PK_SpecificationNumericValue PRIMARY KEY (productID, specFieldID)
);

CREATE INDEX IDX_SpecificationNumericValue_1 ON SpecificationNumericValue (value ASC,specFieldID ASC);

CREATE INDEX IDX_SpecificationNumericValue_2 ON SpecificationNumericValue (productID,specFieldID);

# ---------------------------------------------------------------------- #
# Add table "SpecificationStringValue"                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationStringValue (
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    value TEXT,
    CONSTRAINT PK_SpecificationStringValue PRIMARY KEY (productID, specFieldID)
);

CREATE INDEX IDX_SpecificationStringValue_1 ON SpecificationStringValue (specFieldID,productID);

# ---------------------------------------------------------------------- #
# Add table "SpecificationDateValue"                                     #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationDateValue (
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    value DATE,
    CONSTRAINT PK_SpecificationDateValue PRIMARY KEY (productID, specFieldID)
);

CREATE INDEX IDX_SpecificationDateValue_1 ON SpecificationDateValue (value,specFieldID);

CREATE INDEX IDX_SpecificationDateValue_2 ON SpecificationDateValue (specFieldID,productID);

# ---------------------------------------------------------------------- #
# Add table "State"                                                      #
# ---------------------------------------------------------------------- #

CREATE TABLE State (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    countryID CHAR(2) NOT NULL,
    code VARCHAR(40) NOT NULL,
    name VARCHAR(100),
    subdivisionType VARCHAR(60),
    CONSTRAINT PK_State PRIMARY KEY (ID)
);

CREATE INDEX IDX_State_1 ON State (countryID);

# ---------------------------------------------------------------------- #
# Add table "PostalCode"                                                 #
# ---------------------------------------------------------------------- #

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

# ---------------------------------------------------------------------- #
# Add table "SpecFieldGroup"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecFieldGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    name TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_SpecFieldGroup PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductRelationshipGroup"                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductRelationshipGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    ProductID INTEGER UNSIGNED,
    position INTEGER UNSIGNED DEFAULT 0,
    name TEXT,
    CONSTRAINT PK_ProductRelationshipGroup PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "HelpComment"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE HelpComment (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    topicID VARCHAR(100),
    username VARCHAR(100),
    text TEXT,
    timeAdded DATETIME,
    CONSTRAINT PK_HelpComment PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductReview"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductReview (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    userID INTEGER UNSIGNED,
    title VARCHAR(255),
    text TEXT,
    dateCreated TIMESTAMP,
    CONSTRAINT PK_ProductReview PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "UserAddress"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE UserAddress (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255),
    address1 VARCHAR(255),
    address2 VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(255),
    postalCode VARCHAR(50),
    country CHAR(2),
    phone VARCHAR(100),
    CONSTRAINT PK_UserAddress PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "UserBillingAddress"                                         #
# ---------------------------------------------------------------------- #

CREATE TABLE UserBillingAddress (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER UNSIGNED NOT NULL,
    userAddressID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_UserBillingAddress PRIMARY KEY (ID)
);

CREATE INDEX IDX_UserBillingAddress_1 ON UserBillingAddress (userID);

CREATE INDEX IDX_UserBillingAddress_2 ON UserBillingAddress (userAddressID);

# ---------------------------------------------------------------------- #
# Add table "Transaction"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE Transaction (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED NOT NULL,
    amount FLOAT,
    currencyID CHAR(3),
    time TIMESTAMP,
    method VARCHAR(40),
    gatewayTransactionID VARCHAR(40),
    ccExpiryYear INTEGER,
    ccExpiryMonth TINYINT,
    ccLastDigits CHAR(4),
    CONSTRAINT PK_Transaction PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "Shipment"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Shipment (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED NOT NULL,
    status TINYINT COMMENT '0 - new 1 - pending 2 - awaiting shipment 3 - shipped 4 - confirmed as delivered 5 - confirmed as lost',
    CONSTRAINT PK_Shipment PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "UserShippingAddress"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE UserShippingAddress (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER UNSIGNED NOT NULL,
    userAddressID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_UserShippingAddress PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "OrderNote"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderNote (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED,
    userID INTEGER UNSIGNED,
    time TIMESTAMP,
    text TEXT,
    CONSTRAINT PK_OrderNote PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "DeliveryZone"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZone (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    isEnabled BOOL,
    isFreeShipping BOOL,
    name TEXT,
    CONSTRAINT PK_DeliveryZone PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneCountry"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneCountry (
    ID CHAR(2) NOT NULL,
    deliveryZoneID INTEGER UNSIGNED,
    CONSTRAINT PK_DeliveryZoneCountry PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneState"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneState (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED,
    stateID INTEGER,
    CONSTRAINT PK_DeliveryZoneState PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneCityMask"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneCityMask (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED,
    mask VARCHAR(60),
    CONSTRAINT PK_DeliveryZoneCityMask PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneZipMask"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneZipMask (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED,
    mask VARCHAR(60),
    CONSTRAINT PK_DeliveryZoneZipMask PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneAddressMask"                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneAddressMask (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED,
    mask VARCHAR(60),
    CONSTRAINT PK_DeliveryZoneAddressMask PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "TaxType"                                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE TaxType (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    isEnabled BOOL,
    isShippingAddressBased BOOL,
    name TEXT,
    CONSTRAINT PK_TaxType PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "TaxRate"                                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE TaxRate (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    taxTypeID INTEGER UNSIGNED,
    deliveryZoneID INTEGER UNSIGNED,
    rate FLOAT,
    CONSTRAINT PK_TaxRate PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ShippingRate"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE ShippingRate (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    CONSTRAINT PK_ShippingRate PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Add table "ProductFileGroup"                                           #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductFileGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    name TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_ProductFileGroup PRIMARY KEY (ID)
);

# ---------------------------------------------------------------------- #
# Foreign key constraints                                                #
# ---------------------------------------------------------------------- #

ALTER TABLE Product ADD CONSTRAINT Category_Product 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Product ADD CONSTRAINT Manufacturer_Product 
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE Product ADD CONSTRAINT ProductImage_Product 
    FOREIGN KEY (defaultImageID) REFERENCES ProductImage (ID) ON DELETE SET NULL;

ALTER TABLE Category ADD CONSTRAINT Category_Category 
    FOREIGN KEY (parentNodeID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Category ADD CONSTRAINT CategoryImage_Category 
    FOREIGN KEY (defaultImageID) REFERENCES CategoryImage (ID) ON DELETE SET NULL;

ALTER TABLE SpecificationItem ADD CONSTRAINT SpecFieldValue_SpecificationItem 
    FOREIGN KEY (specFieldValueID) REFERENCES SpecFieldValue (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationItem ADD CONSTRAINT Product_SpecificationItem 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecificationItem ADD CONSTRAINT SpecField_SpecificationItem 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecField ADD CONSTRAINT Category_SpecField 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SpecField ADD CONSTRAINT SpecFieldGroup_SpecField 
    FOREIGN KEY (specFieldGroupID) REFERENCES SpecFieldGroup (ID) ON DELETE CASCADE;

ALTER TABLE SpecFieldValue ADD CONSTRAINT SpecField_SpecFieldValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE CustomerOrder ADD CONSTRAINT User_CustomerOrder 
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE;

ALTER TABLE OrderedItem ADD CONSTRAINT Product_OrderedItem 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE OrderedItem ADD CONSTRAINT CustomerOrder_OrderedItem 
    FOREIGN KEY (customerOrderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE;

ALTER TABLE OrderedItem ADD CONSTRAINT Shipment_OrderedItem 
    FOREIGN KEY (shipmentID) REFERENCES Shipment (ID) ON DELETE SET NULL;

ALTER TABLE User ADD CONSTRAINT UserBillingAddress_User 
    FOREIGN KEY (defaultBillingAddressID) REFERENCES UserBillingAddress (ID) ON DELETE SET NULL;

ALTER TABLE User ADD CONSTRAINT UserShippingAddress_User 
    FOREIGN KEY (defaultShippingAddressID) REFERENCES UserShippingAddress (ID) ON DELETE CASCADE;

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

ALTER TABLE FilterGroup ADD CONSTRAINT SpecField_FilterGroup 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRelationship ADD CONSTRAINT Product_RelatedProduct_ 
    FOREIGN KEY (ProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRelationship ADD CONSTRAINT Product_ProductRelationship 
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRelationship ADD CONSTRAINT ProductRelationshipGroup_ProductRelationship 
    FOREIGN KEY (productRelationshipGroupID) REFERENCES ProductRelationshipGroup (ID) ON DELETE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT Product_ProductPrice 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT Currency_ProductPrice 
    FOREIGN KEY (currencyID) REFERENCES Currency (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductImage ADD CONSTRAINT Product_ProductImage 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE ProductFile ADD CONSTRAINT Product_ProductFile 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductFile ADD CONSTRAINT ProductFileGroup_ProductFile 
    FOREIGN KEY (productFileGroupID) REFERENCES ProductFileGroup (ID) ON DELETE CASCADE;

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

ALTER TABLE SpecFieldGroup ADD CONSTRAINT Category_SpecFieldGroup 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE;

ALTER TABLE ProductRelationshipGroup ADD CONSTRAINT Product_ProductRelationshipGroup 
    FOREIGN KEY (ProductID) REFERENCES Product (ID) ON DELETE CASCADE;

ALTER TABLE ProductReview ADD CONSTRAINT Product_ProductReview 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE;

ALTER TABLE ProductReview ADD CONSTRAINT User_ProductReview 
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE;

ALTER TABLE UserBillingAddress ADD CONSTRAINT User_UserBillingAddress 
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE;

ALTER TABLE UserBillingAddress ADD CONSTRAINT UserAddress_UserBillingAddress 
    FOREIGN KEY (userAddressID) REFERENCES UserAddress (ID) ON DELETE CASCADE;

ALTER TABLE Transaction ADD CONSTRAINT CustomerOrder_Transaction 
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE;

ALTER TABLE Shipment ADD CONSTRAINT CustomerOrder_Shipment 
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE;

ALTER TABLE UserShippingAddress ADD CONSTRAINT User_UserShippingAddress 
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE;

ALTER TABLE UserShippingAddress ADD CONSTRAINT UserAddress_UserShippingAddress 
    FOREIGN KEY (userAddressID) REFERENCES UserAddress (ID) ON DELETE CASCADE;

ALTER TABLE OrderNote ADD CONSTRAINT CustomerOrder_OrderNote 
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE;

ALTER TABLE OrderNote ADD CONSTRAINT User_OrderNote 
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE;

ALTER TABLE DeliveryZoneCountry ADD CONSTRAINT DeliveryZone_DeliveryZoneCountry 
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE;

ALTER TABLE DeliveryZoneState ADD CONSTRAINT DeliveryZone_DeliveryZoneState 
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE;

ALTER TABLE DeliveryZoneState ADD CONSTRAINT State_DeliveryZoneState 
    FOREIGN KEY (stateID) REFERENCES State (ID) ON DELETE CASCADE;

ALTER TABLE DeliveryZoneCityMask ADD CONSTRAINT DeliveryZone_DeliveryZoneCityMask 
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID);

ALTER TABLE DeliveryZoneZipMask ADD CONSTRAINT DeliveryZone_DeliveryZoneZipMask 
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE;

ALTER TABLE DeliveryZoneAddressMask ADD CONSTRAINT DeliveryZone_DeliveryZoneAddressMask 
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE;

ALTER TABLE TaxRate ADD CONSTRAINT TaxType_TaxRate 
    FOREIGN KEY (taxTypeID) REFERENCES TaxType (ID) ON DELETE CASCADE;

ALTER TABLE TaxRate ADD CONSTRAINT DeliveryZone_TaxRate 
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE;

ALTER TABLE ProductFileGroup ADD CONSTRAINT Product_ProductFileGroup 
    FOREIGN KEY (productID) REFERENCES Product (ID);
