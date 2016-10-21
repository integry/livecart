# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v5.2.2                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Database creation script                        #
# Created on:            2010-10-25 08:33                                #
# Model version:         Version 2010-10-25 1                            #
# ---------------------------------------------------------------------- #


# ---------------------------------------------------------------------- #
# Tables                                                                 #
# ---------------------------------------------------------------------- #

# ---------------------------------------------------------------------- #
# Add table "Product"                                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE Product (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Number of times product has been viewed by customers',
    categoryID INTEGER UNSIGNED COMMENT 'The Category the product belongs to',
    manufacturerID INTEGER UNSIGNED COMMENT 'ID of the assigned manufacturer',
    defaultImageID INTEGER UNSIGNED COMMENT 'ID of ProductImage, which has been designated as the default image for the particular product',
    parentID INTEGER UNSIGNED,
    shippingClassID INTEGER UNSIGNED,
    taxClassID INTEGER UNSIGNED,
    isEnabled BOOL NOT NULL DEFAULT 0 COMMENT 'Determines if the Product is enabled (visible and available in the store frontend) 0- not available 1- available 2- disabled (not visble)',
    isRecurring BOOL,
    isFeatured BOOL NOT NULL DEFAULT 0 COMMENT 'Determines if the product has been marked as featured product',
    isSeparateShipment BOOL NOT NULL COMMENT 'Determines if a separate shipment is required for delivering this product',
    isFreeShipping BOOL NOT NULL COMMENT 'Determines if free shipping is available for this product',
    isBackOrderable BOOL NOT NULL COMMENT 'Determines if this product is available for backordering. If backordering is enabled, customers can order the product even if it is out of stock',
    isFractionalUnit BOOL NOT NULL,
    isUnlimitedStock BOOL NOT NULL,
    sku VARCHAR(20) NOT NULL COMMENT 'Product stock keeping unit code',
    name MEDIUMTEXT COMMENT 'Product name (translatable)',
    shortDescription MEDIUMTEXT COMMENT 'A shorter description of the product (translatable). The short description is usually displayed in the category product list',
    longDescription MEDIUMTEXT COMMENT 'A longer description of the product (translatable). The long description is usually displayed in the product detail page',
    pageTitle MEDIUMTEXT,
    keywords TEXT COMMENT 'Additional product search keywords, which may not be included in the product name or description, but can be used when a customer searches for a product',
    dateCreated TIMESTAMP NOT NULL COMMENT 'Product creation date',
    dateUpdated TIMESTAMP COMMENT 'Product last update date',
    URL TINYTEXT COMMENT 'External website URL (manufacturers website, etc.)',
    type TINYINT UNSIGNED DEFAULT 0 COMMENT 'Determines if the product is intangible (1) or tangible (0)',
    ratingSum INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Sum of all rating votes',
    ratingCount INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Count of all rating votes',
    rating FLOAT NOT NULL COMMENT 'Product rating (voteSum divided by voteCount)',
    hits INTEGER UNSIGNED DEFAULT 0 COMMENT 'Number of times the product has been viewed by customers',
    reviewCount INTEGER NOT NULL,
    minimumQuantity FLOAT COMMENT 'Minimum amount of the product that can be ordered',
    shippingSurchargeAmount NUMERIC(12,2) COMMENT 'Additional surcharge for shipping (extra large, etc. items)',
    shippingWeight NUMERIC(8,3) COMMENT 'Weight of the product (including shipping wrappers, etc). This value is used for calculating the shipping rates.',
    stockCount FLOAT COMMENT 'Number of products in stock',
    reservedCount FLOAT COMMENT 'Number of products that are reserved (ordered and in stock but not delivered yet)',
    salesRank INTEGER COMMENT 'Number of products sold',
    position INTEGER UNSIGNED DEFAULT 0,
    childSettings TEXT COMMENT 'Determines price and shipping weight calculation for child products - whether to add/substract from parent or override completely',
    fractionalStep FLOAT,
    categoryIntervalCache TEXT,
    CONSTRAINT PK_Product PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_Product_Category ON Product (categoryID);

CREATE INDEX IDX_Product_SKU ON Product (sku);

CREATE INDEX IDX_Product_isEnabled ON Product (isEnabled);

CREATE INDEX IDX_Product_dateCreated ON Product (dateCreated);

CREATE INDEX IDX_Product_isFeatured ON Product (isFeatured);

CREATE INDEX IDX_Product_rating ON Product (rating);

CREATE INDEX IDX_Product_salesRank ON Product (salesRank);

CREATE INDEX IDX_Product_isEnabled_Category ON Product (categoryID,isEnabled);

# ---------------------------------------------------------------------- #
# Add table "Category"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Category (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    parentNodeID INTEGER UNSIGNED COMMENT 'Parent Category ID (1 for top level categories, NULL for root category)',
    defaultImageID INTEGER UNSIGNED COMMENT 'ID of CategoryImage, which has been designated as the default image for the particular category',
    eavObjectID INTEGER UNSIGNED,
    name MEDIUMTEXT COMMENT 'Category name (translatable)',
    description MEDIUMTEXT COMMENT 'Category description (translatable)',
    keywords MEDIUMTEXT COMMENT 'Category keywords (translatable) - used for meta tags',
    pageTitle MEDIUMTEXT,
    activeProductCount INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Count of total active (enabled products)',
    totalProductCount INTEGER NOT NULL DEFAULT 0 COMMENT 'Count of all products (enabled and disabled)',
    availableProductCount INTEGER NOT NULL COMMENT 'Count of all products that are available for purchasing (enabled and in stock)',
    isEnabled BOOL DEFAULT 0 COMMENT 'Determines if the Category is enabled (visible and available in the store frontend)',
    lft INTEGER COMMENT 'Determines category order in tree',
    rgt INTEGER COMMENT 'Determines category order in tree',
    CONSTRAINT PK_Category PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_Category_1 ON Category (defaultImageID);

CREATE INDEX IDX_Category_parentNode ON Category (parentNodeID);

CREATE INDEX IDX_Category_lft ON Category (parentNodeID ASC,lft ASC);

# ---------------------------------------------------------------------- #
# Add table "Language"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Language (
    ID CHAR(2) NOT NULL,
    isEnabled BOOL,
    isDefault BOOL DEFAULT 0,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_Language PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "SpecificationItem"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationItem (
    specFieldValueID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the pre-defined attribute value (SpecFieldValue)',
    productID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    specFieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    CONSTRAINT PK_SpecificationItem PRIMARY KEY (specFieldValueID, productID, specFieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Product specification: maps input field value list to a particular product';

CREATE INDEX IDX_Specification_1 ON SpecificationItem (specFieldValueID);

CREATE INDEX IDX_Specification_2 ON SpecificationItem (productID);

# ---------------------------------------------------------------------- #
# Add table "SpecField"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecField (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED COMMENT 'The Category the particular SpecField (attribute) belongs to',
    specFieldGroupID INTEGER UNSIGNED COMMENT 'SpecFieldGroup ID if the attribute is being grouped together with other related attributes. If the attribute is not grouped, the value is NULL.',
    name MEDIUMTEXT COMMENT 'Attribute name (translatable)',
    description MEDIUMTEXT COMMENT 'Attribute description / explanation (translatable)',
    type SMALLINT DEFAULT 1 COMMENT 'Field data type. Available types: 1. selector (numeric) 2. input (numeric) 3. input (text) 4. editor (text) 5. selector (text) 6. Date',
    dataType SMALLINT DEFAULT 0 COMMENT '1. text 2. numeric',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Order number (position relative to other fields)',
    handle VARCHAR(40),
    isMultiValue BOOL NOT NULL COMMENT 'Determines if multiple values can be selected for selector attributes',
    isRequired BOOL NOT NULL COMMENT 'Determines if a value has to be provided/entered for this attribute when creating or updating product information',
    isDisplayed BOOL NOT NULL COMMENT 'Determines if the attribute value is displayed in product page',
    isDisplayedInList BOOL NOT NULL COMMENT 'Determines if the attribute value is displayed in a category/search page (attribute summary)',
    isSortable BOOL NOT NULL,
    valuePrefix MEDIUMTEXT COMMENT 'Fixed prefix for all numeric values',
    valueSuffix MEDIUMTEXT COMMENT 'Fixed suffix for all numeric values (for example, sec, kg, px, etc.)',
    CONSTRAINT PK_SpecField PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Field data type. Available types: 1. text field 2. drop down list (select one item from a list) 3. select multiple items from a list';

CREATE INDEX IDX_SpecField_1 ON SpecField (categoryID);

# ---------------------------------------------------------------------- #
# Add table "SpecFieldValue"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecFieldValue (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    specFieldID INTEGER UNSIGNED COMMENT 'The attribute (SpecField) ID the particular value is assigned to',
    value MEDIUMTEXT COMMENT 'The actual attribute value (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other values that are assigned to the same attribute',
    CONSTRAINT PK_SpecFieldValue PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Is there a need to translate this field to diferent languages?';

CREATE INDEX IDX_SpecFieldValue_1 ON SpecFieldValue (specFieldID);

# ---------------------------------------------------------------------- #
# Add table "CustomerOrder"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE CustomerOrder (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER UNSIGNED COMMENT 'ID of user the order is assigned to',
    billingAddressID INTEGER UNSIGNED COMMENT 'ID of order billing address',
    shippingAddressID INTEGER UNSIGNED COMMENT 'ID of order shipping address',
    eavObjectID INTEGER UNSIGNED,
    currencyID CHAR(3) COMMENT 'ID of currency used to finalize the order',
    invoiceNumber VARCHAR(40),
    checkoutStep TINYINT UNSIGNED NOT NULL COMMENT '0 - cart 1 - registered 2 - selected address 3 - selected shipping method 4 - attempted payment',
    dateCreated TIMESTAMP NOT NULL COMMENT 'Initial order creation date',
    dateCompleted TIMESTAMP COMMENT 'The date the order was finalized (completed checkout)',
    totalAmount FLOAT COMMENT 'Order total amount, including taxes and shipping costs',
    capturedAmount FLOAT COMMENT 'The amount that is captured from customers credit card',
    isMultiAddress BOOL NOT NULL,
    isFinalized BOOL NOT NULL COMMENT 'Determines if the order is completed (completed checkout)',
    isPaid BOOL NOT NULL COMMENT 'Determines if the order has been fully paid',
    isCancelled BOOL NOT NULL COMMENT 'Determines if the order is cancelled',
    status TINYINT COMMENT '1 - backordered 2 - awaiting shipment 3 - shipped 4 - returned',
    shipping TEXT COMMENT 'serialized PHP shipping rate data',
    CONSTRAINT PK_CustomerOrder PRIMARY KEY (ID),
    CONSTRAINT TUC_CustomerOrder_1 UNIQUE (invoiceNumber)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_CustomerOrder_1 ON CustomerOrder (status);

CREATE INDEX IDX_CustomerOrder_2 ON CustomerOrder (isFinalized);

# ---------------------------------------------------------------------- #
# Add table "OrderedItem"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderedItem (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED COMMENT 'ID of ordered Product',
    customerOrderID INTEGER UNSIGNED NOT NULL COMMENT 'ID of order the item is assigned to',
    shipmentID INTEGER UNSIGNED COMMENT 'ID of the shipment the item is assigned to (when the order has been finalized)',
    parentID INTEGER UNSIGNED,
    count FLOAT COMMENT 'Amount of ordered Products',
    reservedProductCount FLOAT COMMENT 'Amount of reserved Products from inventory (stock)',
    dateAdded TIMESTAMP COMMENT 'Date when the product was added to shopping cart',
    price FLOAT COMMENT 'Product item price at the time the product was added to shopping cart',
    isSavedForLater TINYINT COMMENT 'Determines if the product has been added to shopping cart or to a wish list',
    name MEDIUMTEXT,
    CONSTRAINT PK_OrderedItem PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_OrderedItem_1 ON OrderedItem (productID);

CREATE INDEX IDX_OrderedItem_2 ON OrderedItem (customerOrderID);

CREATE INDEX IDX_OrderedItem_3 ON OrderedItem (isSavedForLater);

# ---------------------------------------------------------------------- #
# Add table "User"                                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE User (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    defaultBillingAddressID INTEGER UNSIGNED COMMENT 'ID of users default billing address',
    defaultShippingAddressID INTEGER UNSIGNED COMMENT 'ID of users default shipping address',
    userGroupID INTEGER UNSIGNED COMMENT 'ID of UserGroup if the user is assigned to one',
    eavObjectID INTEGER UNSIGNED,
    locale CHAR(4),
    email VARCHAR(60) COMMENT 'Users e-mail address. E-mail address must be unique and it is used for authorization (instead of a login name).',
    password VARCHAR(100) NOT NULL COMMENT 'Users password, encoded with MD5',
    firstName VARCHAR(60) COMMENT 'First name',
    lastName VARCHAR(60) COMMENT 'Last name',
    companyName VARCHAR(60) COMMENT 'Users company name',
    dateCreated TIMESTAMP NOT NULL COMMENT 'The date the users account was created',
    isEnabled BOOL NOT NULL COMMENT 'Determines if the user account is enabled',
    isAdmin BOOL NOT NULL,
    preferences TEXT,
    CONSTRAINT PK_User PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Store system base user (including frontend and backend)';

CREATE UNIQUE INDEX IDX_email ON User (email);

# ---------------------------------------------------------------------- #
# Add table "AccessControlAssociation"                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE AccessControlAssociation (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    roleID INTEGER UNSIGNED NOT NULL COMMENT 'Referenced Role ID',
    userGroupID INTEGER UNSIGNED NOT NULL COMMENT 'Referenced UserGroup ID',
    CONSTRAINT PK_AccessControlAssociation PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "UserGroup"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE UserGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    eavObjectID INTEGER UNSIGNED,
    name VARCHAR(60) NOT NULL COMMENT 'User group name',
    description TEXT COMMENT 'User group description',
    CONSTRAINT PK_UserGroup PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'A list of role based groups in a store system';

# ---------------------------------------------------------------------- #
# Add table "Filter"                                                     #
# ---------------------------------------------------------------------- #

CREATE TABLE Filter (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    filterGroupID INTEGER UNSIGNED COMMENT 'The FilterGroup ID the particular Filter belongs to',
    name MEDIUMTEXT COMMENT 'Filter name (translatable)',
    position INTEGER COMMENT 'Sort order in relation to other Filters than belong to the same FilterGroup',
    rangeStart FLOAT COMMENT 'Range interval starting value for numeric values. Use NULL if there''s no starting value (negative infinity).',
    rangeEnd FLOAT COMMENT 'Range interval ending value for numeric values. Use NULL if there''s no ending value (infinity).',
    rangeDateStart DATE COMMENT 'Range interval starting value for date values. Use NULL if there''s no starting value (negative infinity).',
    rangeDateEnd DATE COMMENT 'Range interval ending value for date values. Use NULL if there''s no ending value (infinity).',
    CONSTRAINT PK_Filter PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "FilterGroup"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE FilterGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    specFieldID INTEGER UNSIGNED NOT NULL COMMENT 'The attribute (SpecField) ID the particular FilterGroup is being based on',
    name MEDIUMTEXT COMMENT 'FilterGroup name (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other FilterGroups',
    isEnabled BOOL COMMENT 'Determine if the FilterGroup is active',
    displayStyle INTEGER UNSIGNED NOT NULL,
    displayLocation INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_FilterGroup PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "Role"                                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE Role (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL COMMENT 'Role package name (for example, backend.category.add)',
    CONSTRAINT PK_Role PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductRelationship"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductRelationship (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED NOT NULL,
    categoryID INTEGER UNSIGNED,
    relatedProductID INTEGER UNSIGNED NOT NULL COMMENT 'The Product the related Product is assigned to',
    productRelationshipGroupID INTEGER UNSIGNED COMMENT 'ID of the related Product',
    type INTEGER NOT NULL COMMENT '0 - related product (cross-sell), 1 - up-sell',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'ID of the ProductRelationshipGroup - if the related product is assigned to one (grouped together with similar products)',
    CONSTRAINT PK_ProductRelationship PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductPrice"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductPrice (
    productID INTEGER UNSIGNED NOT NULL COMMENT 'The Product the price is being defined for',
    currencyID CHAR(3) NOT NULL COMMENT 'Price Currency ID',
    recurringID INTEGER UNSIGNED,
    price NUMERIC(12,2) NOT NULL COMMENT 'The actual price value',
    listPrice NUMERIC(12,2),
    serializedRules TEXT,
    CONSTRAINT PK_ProductPrice PRIMARY KEY (productID, currencyID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_ProductPrice_1 ON ProductPrice (productID,currencyID);

# ---------------------------------------------------------------------- #
# Add table "Currency"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Currency (
    ID CHAR(3) NOT NULL,
    rate FLOAT(10,5) COMMENT 'Currency rate in relation to the base (default) currency',
    lastUpdated TIMESTAMP COMMENT 'The date the rate was last updated',
    isDefault BOOL DEFAULT 0 COMMENT 'Determines if the currency is the base (default) currency',
    isEnabled BOOL DEFAULT 0,
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other Currencies',
    pricePrefix TEXT COMMENT 'Used for price formatting. Symbols to place before price price, for example $, etc.',
    priceSuffix TEXT COMMENT 'Used for price formatting. Symbols to place after the price - usually the currency code itself',
    decimalSeparator CHAR(3) DEFAULT '.',
    thousandSeparator CHAR(3),
    decimalCount INTEGER DEFAULT 2,
    rounding TEXT,
    CONSTRAINT PK_Currency PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "Manufacturer"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE Manufacturer (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    defaultImageID INTEGER UNSIGNED,
    eavObjectID INTEGER UNSIGNED,
    name VARCHAR(60) NOT NULL COMMENT 'Name (brand name) of the manufacturer',
    CONSTRAINT PK_Manufacturer PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductImage"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductImage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED NOT NULL COMMENT 'The Product the particular image belongs to',
    title MEDIUMTEXT COMMENT 'Image name (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other images that are assigned to the same product (the first image is the default one)',
    CONSTRAINT PK_ProductImage PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductFile"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductFile (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED COMMENT 'The Product the particular file belongs to',
    productFileGroupID INTEGER UNSIGNED COMMENT 'ID of the ProductFileGroup - if the product is assigned to one (grouped together with related files)',
    isPublic BOOL,
    isEmbedded BOOL,
    fileName VARCHAR(255) COMMENT 'File name (the actual filename)',
    extension VARCHAR(20) COMMENT 'File type (for example, zip, mp3, exe, etc.)',
    title MEDIUMTEXT COMMENT 'File title (short description, translatable)',
    description MEDIUMTEXT COMMENT 'File description (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other ProductFiles that are assigned to the same product',
    allowDownloadDays INTEGER COMMENT 'Allow customer to download the product only for a certain number of days after placing the order',
    allowDownloadCount INTEGER,
    filePath TEXT COMMENT 'Local file path or URL',
    CONSTRAINT PK_ProductFile PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

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
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '1- % 2- currency';

# ---------------------------------------------------------------------- #
# Add table "CategoryImage"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE CategoryImage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED COMMENT 'The Category the particular image belongs to',
    title MEDIUMTEXT COMMENT 'Image name (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other images that are assigned to the same category (the first image is the default one)',
    CONSTRAINT PK_CategoryImage PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_CategoryImage_1 ON CategoryImage (categoryID);

# ---------------------------------------------------------------------- #
# Add table "SpecificationNumericValue"                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationNumericValue (
    productID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    specFieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    value FLOAT COMMENT 'The actual attribute value (numeric) assigned to a particular product',
    CONSTRAINT PK_SpecificationNumericValue PRIMARY KEY (productID, specFieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_SpecificationNumericValue_1 ON SpecificationNumericValue (value ASC,specFieldID ASC);

CREATE INDEX IDX_SpecificationNumericValue_2 ON SpecificationNumericValue (productID,specFieldID);

# ---------------------------------------------------------------------- #
# Add table "SpecificationStringValue"                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationStringValue (
    productID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    specFieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    value MEDIUMTEXT COMMENT 'The actual attribute value (string) assigned to a particular product',
    CONSTRAINT PK_SpecificationStringValue PRIMARY KEY (productID, specFieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_SpecificationStringValue_1 ON SpecificationStringValue (specFieldID,productID);

# ---------------------------------------------------------------------- #
# Add table "SpecificationDateValue"                                     #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecificationDateValue (
    productID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    specFieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    value DATE COMMENT 'The actual attribute value (date) assigned to a particular product',
    CONSTRAINT PK_SpecificationDateValue PRIMARY KEY (productID, specFieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_SpecificationDateValue_1 ON SpecificationDateValue (value,specFieldID);

CREATE INDEX IDX_SpecificationDateValue_2 ON SpecificationDateValue (specFieldID,productID);

# ---------------------------------------------------------------------- #
# Add table "State"                                                      #
# ---------------------------------------------------------------------- #

CREATE TABLE State (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    countryID CHAR(2) NOT NULL COMMENT '2-letter country code',
    code VARCHAR(40) NOT NULL COMMENT 'State code (for example, FL for Florida, etc.)',
    name VARCHAR(100) COMMENT 'State name',
    subdivisionType VARCHAR(60) COMMENT 'For US states, the value for this field would be "State", for Canadian provinces, it would be "Province"',
    CONSTRAINT PK_State PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

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
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "SpecFieldGroup"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE SpecFieldGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED COMMENT 'The Category the particular SpecFieldGroup (attribute group) belongs to',
    name MEDIUMTEXT COMMENT 'Group name (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other groups',
    CONSTRAINT PK_SpecFieldGroup PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductRelationshipGroup"                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductRelationshipGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    ProductID INTEGER UNSIGNED,
    categoryID INTEGER UNSIGNED,
    type INTEGER NOT NULL COMMENT 'see ProductRelationship',
    position INTEGER UNSIGNED DEFAULT 0,
    name MEDIUMTEXT,
    CONSTRAINT PK_ProductRelationshipGroup PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

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
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductReview"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductReview (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    userID INTEGER UNSIGNED,
    isEnabled BOOL,
    ip INTEGER(11) UNSIGNED,
    dateCreated TIMESTAMP,
    rating FLOAT,
    ratingSum INTEGER NOT NULL,
    ratingCount INTEGER NOT NULL,
    nickname VARCHAR(100),
    title VARCHAR(255),
    text TEXT,
    CONSTRAINT PK_ProductReview PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IP_address ON ProductReview (ip);

# ---------------------------------------------------------------------- #
# Add table "UserAddress"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE UserAddress (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    stateID INTEGER UNSIGNED COMMENT 'Pre-defined address state/province ID. If the customers address is in a country for which the states are not defined, the value of this field would be NULL instead and the value would be entered in the stateName field.',
    eavObjectID INTEGER UNSIGNED,
    firstName VARCHAR(60) COMMENT 'Customers first name',
    lastName VARCHAR(60) COMMENT 'Customers last name',
    companyName VARCHAR(60) COMMENT 'Customers company name',
    address1 VARCHAR(255) COMMENT 'First address line',
    address2 VARCHAR(255) COMMENT 'Secondary address line',
    city VARCHAR(60) COMMENT 'Address city',
    stateName VARCHAR(60) COMMENT 'Address state name (entered only if pre-defined states are not available for this country)',
    postalCode VARCHAR(50) COMMENT 'Postal/ZIP code',
    countryID CHAR(2) COMMENT '2-letter country code',
    phone VARCHAR(100) COMMENT 'Phone number',
    CONSTRAINT PK_UserAddress PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "BillingAddress"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE BillingAddress (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the User that is associated to the address',
    userAddressID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the UserAddress entity',
    CONSTRAINT PK_BillingAddress PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_BillingAddress_1 ON BillingAddress (userID);

CREATE INDEX IDX_BillingAddress_2 ON BillingAddress (userAddressID);

# ---------------------------------------------------------------------- #
# Add table "Transaction"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE Transaction (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED NOT NULL COMMENT 'ID of order the transaction is assigned to',
    parentTransactionID INTEGER UNSIGNED COMMENT 'If the transaction relies on some other transaction (for example, captures funds that were authorized earlier or voids a transaction), the ID of that transaction',
    userID INTEGER UNSIGNED,
    eavObjectID INTEGER UNSIGNED,
    amount FLOAT COMMENT 'Transaction amount',
    currencyID CHAR(3) COMMENT 'Transaction currency ID/code',
    realAmount FLOAT COMMENT 'Processed amount in payment processor currency. For example, if the order currency is EUR and the payment gateway only supports USD transactions, the transaction will actually be carried out in USD.',
    realCurrencyID CHAR(3) COMMENT 'Payment processor currency, which may be different than the order currency in case the payment processor doesn''t support transactions in that currency.',
    time TIMESTAMP COMMENT 'Date and time of the transaction',
    method VARCHAR(40) COMMENT 'Payment method class name (for example, PaypalDirectPayment)',
    methodType TINYINT NOT NULL COMMENT '0 - offline payment 1 - credit card online payment 2 - non-credit card online payment (Paypal, Moneybookers, e-gold, etc.)',
    gatewayTransactionID VARCHAR(40) COMMENT 'ID that was assigned to this transaction by the payment gateway. This ID is needed if any child transactions (capture or void) are going to be performed',
    type TINYINT NOT NULL COMMENT '0 - sale (authorize & capture) 1 - authorize 2 - capture 3 - void',
    isCompleted BOOL NOT NULL COMMENT 'Determines if the transaction has been completely finalized (no more captures possible)',
    isVoided BOOL NOT NULL,
    ccExpiryYear INTEGER COMMENT 'Credit card expiration year',
    ccExpiryMonth TINYINT COMMENT 'Credit card expiration month',
    ccLastDigits CHAR(80) COMMENT 'Last 4 digits of credit card number',
    ccType VARCHAR(40),
    ccName VARCHAR(100),
    ccCVV VARCHAR(40),
    comment TEXT,
    serializedData TEXT,
    CONSTRAINT PK_Transaction PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "Shipment"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE Shipment (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED NOT NULL COMMENT 'ID of order the shipment is assigned to',
    shippingServiceID INTEGER UNSIGNED COMMENT 'ID of the selected ShippingService for this item. In case a real-time shipping rate service is used, the value for this field would be NULL',
    shippingAddressID INTEGER UNSIGNED,
    amount FLOAT COMMENT 'Total product price amount',
    shippingAmount FLOAT COMMENT 'Shipping price amount',
    taxAmount FLOAT COMMENT 'Total associated tax amount',
    status TINYINT COMMENT '0 - new 1 - pending 2 - awaiting shipment 3 - shipped 4 - confirmed as delivered 5 - confirmed as lost',
    dateShipped TIMESTAMP COMMENT 'Date the product was shipped to customer',
    trackingCode VARCHAR(100) COMMENT 'Online tracking code for this shipment',
    shippingServiceData TEXT COMMENT 'Serialized ShipmentDeliveryRate class data - for real-time shipping rates only',
    CONSTRAINT PK_Shipment PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ShippingAddress"                                            #
# ---------------------------------------------------------------------- #

CREATE TABLE ShippingAddress (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the User that is associated to the address',
    userAddressID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the UserAddress entity',
    CONSTRAINT PK_ShippingAddress PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "OrderNote"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderNote (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED NOT NULL,
    userID INTEGER UNSIGNED NOT NULL,
    isAdmin BOOL NOT NULL,
    isRead BOOL NOT NULL,
    time TIMESTAMP NOT NULL,
    text TEXT,
    CONSTRAINT PK_OrderNote PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "DeliveryZone"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZone (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    isEnabled BOOL COMMENT 'Determines if the particular delivery zone is enabled',
    isFreeShipping BOOL COMMENT 'Determines if free shipping is available for this delivery zone',
    isRealTimeDisabled BOOL COMMENT 'Determines if the real-time shipping rates are disabled for this delivery zone',
    position INTEGER UNSIGNED DEFAULT 0,
    type INTEGER UNSIGNED NOT NULL,
    name VARCHAR(100) COMMENT 'Delivery zone name',
    CONSTRAINT PK_DeliveryZone PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneCountry"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneCountry (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED COMMENT 'ID of the referenced DeliveryZone',
    countryCode CHAR(2) NOT NULL COMMENT '2-letter country code',
    CONSTRAINT PK_DeliveryZoneCountry PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneState"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneState (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED COMMENT 'ID of the referenced DeliveryZone',
    stateID INTEGER UNSIGNED COMMENT 'ID of the referenced State',
    CONSTRAINT PK_DeliveryZoneState PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneCityMask"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneCityMask (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED COMMENT 'ID of the referenced DeliveryZone',
    mask VARCHAR(60) COMMENT 'City name mask. For example, "New Y*k" or "New Y" would match "New York".',
    CONSTRAINT PK_DeliveryZoneCityMask PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;


# ---------------------------------------------------------------------- #
# Add table "MysqlQueue"                                       			 #
# ---------------------------------------------------------------------- #

CREATE TABLE IF NOT EXISTS `MysqlQueue` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` int(10) DEFAULT NULL COMMENT 'Queue name',
  `message` longblob COMMENT 'The queued message',
  `added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the message was added to the queue',
  `consumed` TIMESTAMP NULL DEFAULT NULL COMMENT 'When the message was consumed for the last time',
  `priority` INT NOT NULL DEFAULT  '0' COMMENT 'The higher the value, the sooner the message will be consumed',
  `isProcessed` tinyint(1) DEFAULT NULL COMMENT 'Determines if the message is being processed',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  COLLATE utf8_general_ci;
CREATE INDEX IDX_name ON MysqlQueue (name);
CREATE INDEX IDX_priority ON MysqlQueue (priority);

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneZipMask"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneZipMask (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED COMMENT 'ID of the referenced DeliveryZone',
    mask VARCHAR(60) COMMENT 'ZIP/postal code mask. For example, "90*" would match "90210".',
    CONSTRAINT PK_DeliveryZoneZipMask PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneAddressMask"                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneAddressMask (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED COMMENT 'ID of the referenced DeliveryZone',
    mask VARCHAR(60) COMMENT 'Address mask. For example, "*th Avenue" corresponds to "5th Avenue", "6th Avenue", etc.',
    CONSTRAINT PK_DeliveryZoneAddressMask PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "Tax"                                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE Tax (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name MEDIUMTEXT COMMENT 'Tax name (translatable). For example, "VAT"',
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_Tax PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "TaxRate"                                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE TaxRate (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    taxID INTEGER UNSIGNED COMMENT 'ID of the referenced Tax',
    taxClassID INTEGER UNSIGNED,
    deliveryZoneID INTEGER UNSIGNED COMMENT 'ID of the referenced DeliveryZone',
    rate FLOAT COMMENT 'Tax rate. For example, 20, to set a 20% rate.',
    CONSTRAINT PK_TaxRate PRIMARY KEY (ID),
    CONSTRAINT TUC_TaxRate_DeliveryZone_Tax UNIQUE (deliveryZoneID, taxID, taxClassID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ShippingRate"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE ShippingRate (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    shippingServiceID INTEGER UNSIGNED COMMENT 'ID of the referenced ShippingService',
    weightRangeStart FLOAT COMMENT 'Lowest allowed shipment weight for the ShippingRate entity to be applicable',
    weightRangeEnd FLOAT COMMENT 'Highest allowed shipment weight for the ShippingRate entity to be applicable',
    subtotalRangeStart FLOAT COMMENT 'Lowest allowed shipment subtotal (in base Currency) for the ShippingRate entity to be applicable',
    subtotalRangeEnd FLOAT COMMENT 'Highest allowed shipment subtotal (in base Currency) for the ShippingRate entity to be applicable',
    flatCharge FLOAT COMMENT 'Constant flat fee (in base Currency) that does not change if shipment weight or subtotal is changed',
    perItemCharge FLOAT COMMENT 'Fixed charge per each item (in base Currency)',
    subtotalPercentCharge FLOAT COMMENT 'Fee calculation as a percentage of a subtotal',
    perKgCharge FLOAT COMMENT 'Charge per each kg of weight',
    perItemChargeClass TEXT,
    CONSTRAINT PK_ShippingRate PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductFileGroup"                                           #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductFileGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED COMMENT 'The Product the file group belongs to',
    name MEDIUMTEXT COMMENT 'File group name (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other ProductFileGroups that are assigned to the same product',
    CONSTRAINT PK_ProductFileGroup PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ShippingService"                                            #
# ---------------------------------------------------------------------- #

CREATE TABLE ShippingService (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED COMMENT 'ID of the referenced DeliveryZone',
    eavObjectID INTEGER UNSIGNED,
    isFinal BOOL,
    name MEDIUMTEXT COMMENT 'Service name (translatable). For example, "Next Day Delivery"',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other ShippingServices',
    rangeType TINYINT COMMENT '0 - weight based range 1 - subtotal based range',
    description MEDIUMTEXT,
    deliveryTimeMinDays INTEGER,
    deliveryTimeMaxDays INTEGER,
    CONSTRAINT PK_ShippingService PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "StaticPage"                                                 #
# ---------------------------------------------------------------------- #

CREATE TABLE StaticPage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    parentID INTEGER UNSIGNED,
    eavObjectID INTEGER UNSIGNED,
    handle VARCHAR(40) COMMENT 'URL slug. For example, for Terms Of Service page it could be "terms.of.service"',
    title MEDIUMTEXT COMMENT 'Page title (translatable)',
    text MEDIUMTEXT COMMENT 'Page text (translatable)',
    metaDescription MEDIUMTEXT,
    isInformationBox BOOL NOT NULL COMMENT 'Determines if a link to the page is being displayed in the "Information Box" menu',
    menu TEXT,
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other StaticPages',
    CONSTRAINT PK_StaticPage PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ShipmentTax"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE ShipmentTax (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    taxRateID INTEGER UNSIGNED COMMENT 'ID of the TaxRate that is being applied to the shipment',
    shipmentID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the shipment the tax is being applied to',
    type TINYINT COMMENT 'applied to: NULL - total amount (deprecated), 1 - subtotal, 2 - shipping amount',
    amount FLOAT COMMENT 'Tax amount',
    CONSTRAINT PK_ShipmentTax PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "OrderLog"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderLog (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED,
    userID INTEGER UNSIGNED,
    type TINYINT,
    action TINYINT,
    time TIMESTAMP,
    oldTotal FLOAT,
    newTotal FLOAT,
    oldValue TEXT,
    newValue TEXT,
    CONSTRAINT PK_OrderLog PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "NewsPost"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE NewsPost (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    isEnabled BOOL,
    position INTEGER UNSIGNED DEFAULT 0,
    time TIMESTAMP,
    title MEDIUMTEXT,
    text MEDIUMTEXT,
    moreText MEDIUMTEXT,
    CONSTRAINT PK_NewsPost PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "DeliveryZoneRealTimeService"                                #
# ---------------------------------------------------------------------- #

CREATE TABLE DeliveryZoneRealTimeService (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    deliveryZoneID INTEGER UNSIGNED,
    serviceClassName VARCHAR(100),
    CONSTRAINT PK_DeliveryZoneRealTimeService PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ExpressCheckout"                                            #
# ---------------------------------------------------------------------- #

CREATE TABLE ExpressCheckout (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    addressID INTEGER UNSIGNED,
    orderID INTEGER UNSIGNED NOT NULL,
    method VARCHAR(40),
    paymentData TEXT,
    CONSTRAINT PK_ExpressCheckout PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductOption"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductOption (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    categoryID INTEGER UNSIGNED,
    defaultChoiceID INTEGER UNSIGNED,
    name MEDIUMTEXT,
    description MEDIUMTEXT,
    selectMessage MEDIUMTEXT,
    type TINYINT,
    isRequired BOOL,
    isDisplayed BOOL,
    isDisplayedInList BOOL,
    isDisplayedInCart BOOL,
    isPriceIncluded BOOL COMMENT 'Include product price when displaying option price (base product price + option choice price = option display price)',
    displayType INTEGER COMMENT '0 - select box, 1 - radio buttons',
    position INTEGER UNSIGNED DEFAULT 0,
    settings TEXT,
    maxFileSize INTEGER,
    fileExtensions VARCHAR(100),
    CONSTRAINT PK_ProductOption PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductOptionChoice"                                        #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductOptionChoice (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    optionID INTEGER UNSIGNED,
    priceDiff NUMERIC(12,2),
    hasImage BOOL,
    position INTEGER UNSIGNED DEFAULT 0,
    name MEDIUMTEXT,
    CONSTRAINT PK_ProductOptionChoice PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "OrderedItemOption"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderedItemOption (
    orderedItemID INTEGER UNSIGNED NOT NULL,
    choiceID INTEGER UNSIGNED NOT NULL,
    priceDiff NUMERIC(12,2),
    optionText MEDIUMTEXT,
    CONSTRAINT PK_OrderedItemOption PRIMARY KEY (orderedItemID, choiceID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductRatingType"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductRatingType (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    name MEDIUMTEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_ProductRatingType PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductRating"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductRating (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED NOT NULL,
    userID INTEGER UNSIGNED,
    reviewID INTEGER UNSIGNED,
    ratingTypeID INTEGER UNSIGNED,
    rating INTEGER NOT NULL,
    dateCreated TIMESTAMP,
    ip INTEGER(11) UNSIGNED,
    CONSTRAINT PK_ProductRating PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IP_address ON ProductRating (ip);

# ---------------------------------------------------------------------- #
# Add table "CategoryPresentation"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE CategoryPresentation (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    productID INTEGER UNSIGNED,
    isSubcategories BOOL,
    isAllVariations BOOL,
    isVariationImages BOOL,
    theme VARCHAR(70),
    listStyle VARCHAR(20),
    CONSTRAINT PK_CategoryPresentation PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "SearchLog"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE SearchLog (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    keywords VARCHAR(100),
    ip INTEGER,
    time DATETIME,
    CONSTRAINT PK_SearchLog PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_SearchLog_1 ON SearchLog (keywords);

CREATE INDEX IDX_SearchLog_2 ON SearchLog (time);

# ---------------------------------------------------------------------- #
# Add table "NewsletterSubscriber"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE NewsletterSubscriber (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INTEGER UNSIGNED,
    isEnabled BOOL DEFAULT 0,
    email VARCHAR(100),
    confirmationCode VARCHAR(40),
    CONSTRAINT PK_NewsletterSubscriber PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "NewsletterMessage"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE NewsletterMessage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    status TINYINT NOT NULL DEFAULT 0 COMMENT '0 - not sent 1 - partially sent 2 - sent',
    time DATETIME,
    subject VARCHAR(200),
    text TEXT,
    html TEXT,
    CONSTRAINT PK_NewsletterMessage PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "NewsletterSentMessage"                                      #
# ---------------------------------------------------------------------- #

CREATE TABLE NewsletterSentMessage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    messageID INTEGER UNSIGNED,
    userID INTEGER UNSIGNED,
    subscriberID INTEGER UNSIGNED,
    CONSTRAINT PK_NewsletterSentMessage PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "RecurringProductPeriod"                                     #
# ---------------------------------------------------------------------- #

CREATE TABLE RecurringProductPeriod (
    ID INTEGER UNSIGNED NOT NULL,
    productID INTEGER UNSIGNED,
    position INTEGER UNSIGNED DEFAULT 0,
    periodType TINYINT COMMENT '0 - day, 1 - week, 2 - month, 3 - year',
    periodLength INTEGER,
    rebillCount INTEGER,
    name MEDIUMTEXT,
    description MEDIUMTEXT,
    CONSTRAINT PK_RecurringProductPeriod PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "EavDateValue"                                               #
# ---------------------------------------------------------------------- #

CREATE TABLE EavDateValue (
    objectID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    fieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    value DATE COMMENT 'The actual attribute value (date) assigned to a particular product',
    CONSTRAINT PK_EavDateValue PRIMARY KEY (objectID, fieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_EavDateValue_1 ON EavDateValue (value,fieldID);

CREATE INDEX IDX_EavDateValue_2 ON EavDateValue (fieldID,objectID);

# ---------------------------------------------------------------------- #
# Add table "EavStringValue"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE EavStringValue (
    objectID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    fieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    value MEDIUMTEXT COMMENT 'The actual attribute value (string) assigned to a particular product',
    CONSTRAINT PK_EavStringValue PRIMARY KEY (objectID, fieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_EavStringValue_1 ON EavStringValue (fieldID,objectID);

# ---------------------------------------------------------------------- #
# Add table "EavNumericValue"                                            #
# ---------------------------------------------------------------------- #

CREATE TABLE EavNumericValue (
    objectID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    fieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    value FLOAT COMMENT 'The actual attribute value (numeric) assigned to a particular product',
    CONSTRAINT PK_EavNumericValue PRIMARY KEY (objectID, fieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_EavNumericValue_1 ON EavNumericValue (value ASC,fieldID ASC);

CREATE INDEX IDX_EavNumericValue_2 ON EavNumericValue (objectID,fieldID);

# ---------------------------------------------------------------------- #
# Add table "EavItem"                                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE EavItem (
    valueID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the pre-defined attribute value (SpecFieldValue)',
    objectID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the product the value is linked to',
    fieldID INTEGER UNSIGNED NOT NULL COMMENT 'ID of the attribute (SpecField)',
    CONSTRAINT PK_EavItem PRIMARY KEY (valueID, objectID, fieldID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Product specification: maps input field value list to a particular product';

CREATE INDEX IDX_Specification_1 ON EavItem (valueID);

CREATE INDEX IDX_Specification_2 ON EavItem (objectID);

# ---------------------------------------------------------------------- #
# Add table "EavValue"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE EavValue (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    fieldID INTEGER UNSIGNED COMMENT 'The attribute (SpecField) ID the particular value is assigned to',
    value MEDIUMTEXT COMMENT 'The actual attribute value (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other values that are assigned to the same attribute',
    CONSTRAINT PK_EavValue PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Is there a need to translate this field to diferent languages?';

CREATE INDEX IDX_SpecFieldValue_1 ON EavValue (fieldID);

# ---------------------------------------------------------------------- #
# Add table "EavField"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE EavField (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    eavFieldGroupID INTEGER UNSIGNED COMMENT 'SpecFieldGroup ID if the attribute is being grouped together with other related attributes. If the attribute is not grouped, the value is NULL.',
    classID INTEGER COMMENT 'The Category the particular SpecField (attribute) belongs to',
    stringIdentifier CHAR(40),
    name MEDIUMTEXT COMMENT 'Attribute name (translatable)',
    description MEDIUMTEXT COMMENT 'Attribute description / explanation (translatable)',
    type SMALLINT DEFAULT 1 COMMENT 'Field data type. Available types: 1. selector (numeric) 2. input (numeric) 3. input (text) 4. editor (text) 5. selector (text) 6. Date',
    dataType SMALLINT DEFAULT 0 COMMENT '1. text 2. numeric',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Order number (position relative to other fields)',
    handle VARCHAR(40),
    isMultiValue BOOL COMMENT 'Determines if multiple values can be selected for selector attributes',
    isRequired BOOL COMMENT 'Determines if a value has to be provided/entered for this attribute when creating or updating product information',
    isDisplayed BOOL COMMENT 'Determines if the attribute value is displayed in product page',
    isDisplayedInList BOOL COMMENT 'Determines if the attribute value is displayed in a category/search page (attribute summary)',
    valuePrefix MEDIUMTEXT COMMENT 'Fixed prefix for all numeric values',
    valueSuffix MEDIUMTEXT COMMENT 'Fixed suffix for all numeric values (for example, sec, kg, px, etc.)',
    CONSTRAINT PK_EavField PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = 'Field data type. Available types: 1. text field 2. drop down list (select one item from a list) 3. select multiple items from a list';

CREATE INDEX IDX_SpecField_1 ON EavField (classID);

# ---------------------------------------------------------------------- #
# Add table "EavFieldGroup"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE EavFieldGroup (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    classID INTEGER COMMENT 'The Category the particular SpecFieldGroup (attribute group) belongs to',
    stringIdentifier CHAR(40),
    name MEDIUMTEXT COMMENT 'Group name (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other groups',
    CONSTRAINT PK_EavFieldGroup PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_EavFieldGroup_1 ON EavFieldGroup (classID);

# ---------------------------------------------------------------------- #
# Add table "EavObject"                                                  #
# ---------------------------------------------------------------------- #

CREATE TABLE EavObject (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    customerOrderID INTEGER UNSIGNED,
    manufacturerID INTEGER UNSIGNED,
    userID INTEGER UNSIGNED,
    userGroupID INTEGER UNSIGNED,
    userAddressID INTEGER UNSIGNED,
    transactionID INTEGER UNSIGNED,
    shippingServiceID INTEGER UNSIGNED,
    staticPageID INTEGER UNSIGNED,
    classID TINYINT UNSIGNED,
    CONSTRAINT PK_EavObject PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ManufacturerImage"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE ManufacturerImage (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    manufacturerID INTEGER UNSIGNED NOT NULL COMMENT 'The Product the particular image belongs to',
    title MEDIUMTEXT COMMENT 'Image name (translatable)',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'Sort order in relation to other images that are assigned to the same product (the first image is the default one)',
    CONSTRAINT PK_ManufacturerImage PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductRatingSummary"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductRatingSummary (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    ratingTypeID INTEGER UNSIGNED,
    ratingSum INTEGER NOT NULL,
    ratingCount INTEGER NOT NULL,
    rating FLOAT NOT NULL,
    CONSTRAINT PK_ProductRatingSummary PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductList"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductList (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    isRandomOrder BOOL,
    listStyle INTEGER,
    limitCount INTEGER,
    position INTEGER UNSIGNED DEFAULT 0,
    name MEDIUMTEXT,
    CONSTRAINT PK_ProductList PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductListItem"                                            #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductListItem (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productListID INTEGER UNSIGNED,
    productID INTEGER UNSIGNED,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_ProductListItem PRIMARY KEY (ID),
    CONSTRAINT TUC_ProductListItem_1 UNIQUE (productListID, productID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "DiscountCondition"                                          #
# ---------------------------------------------------------------------- #

CREATE TABLE DiscountCondition (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    parentNodeID INTEGER UNSIGNED,
    lft INTEGER,
    rgt INTEGER,
    isEnabled BOOL NOT NULL,
    isAnyRecord BOOL NOT NULL,
    isAllSubconditions BOOL NOT NULL,
    isActionCondition BOOL NOT NULL,
    isFinal BOOL NOT NULL,
    isReverse BOOL NOT NULL,
    recordCount INTEGER NOT NULL,
    validFrom TIMESTAMP DEFAULT '0000-00-00',
    validTo TIMESTAMP DEFAULT '0000-00-00',
    count FLOAT,
    subtotal FLOAT,
    comparisonType TINYINT COMMENT '0 - equal, 1 - less than or equal, 2 - greater than or equal, 3 - not equal',
    name MEDIUMTEXT,
    description MEDIUMTEXT,
    couponCode VARCHAR(40) NOT NULL,
    couponLimitCount INTEGER,
    couponLimitType TINYINT COMMENT '0 - overall 1 - by user',
    serializedCondition TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    conditionClass VARCHAR(80),
    CONSTRAINT PK_DiscountCondition PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_DiscountCondition_1 ON DiscountCondition (isEnabled,validFrom,validTo,isActionCondition);

CREATE INDEX IDX_DiscountCondition_2 ON DiscountCondition (comparisonType);

CREATE INDEX IDX_DiscountCondition_3 ON DiscountCondition (couponCode);

# ---------------------------------------------------------------------- #
# Add table "DiscountAction"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE DiscountAction (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    conditionID INTEGER UNSIGNED,
    actionConditionID INTEGER UNSIGNED,
    isEnabled BOOL NOT NULL,
    isOrderLevel BOOL NOT NULL,
    type TINYINT NOT NULL,
    actionType TINYINT NOT NULL,
    amount FLOAT,
    discountStep INTEGER,
    discountLimit INTEGER,
    position INTEGER UNSIGNED DEFAULT 0,
    actionClass VARCHAR(80),
    serializedData TEXT,
    CONSTRAINT PK_DiscountAction PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_DiscountAction_1 ON DiscountAction (conditionID,isEnabled);

CREATE INDEX IDX_DiscountAction_2 ON DiscountAction (isEnabled);

# ---------------------------------------------------------------------- #
# Add table "OrderDiscount"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderDiscount (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED,
    amount FLOAT,
    description MEDIUMTEXT,
    CONSTRAINT PK_OrderDiscount PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "OrderCoupon"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderCoupon (
    ID INTEGER UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    orderID INTEGER UNSIGNED,
    discountConditionID INTEGER UNSIGNED,
    couponCode VARCHAR(255),
    CONSTRAINT PK_OrderCoupon PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE INDEX IDX_OrderCoupon_1 ON OrderCoupon (couponCode);

# ---------------------------------------------------------------------- #
# Add table "DiscountConditionRecord"                                    #
# ---------------------------------------------------------------------- #

CREATE TABLE DiscountConditionRecord (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    conditionID INTEGER UNSIGNED,
    productID INTEGER UNSIGNED,
    categoryID INTEGER UNSIGNED,
    manufacturerID INTEGER UNSIGNED,
    userID INTEGER UNSIGNED,
    userGroupID INTEGER UNSIGNED,
    deliveryZoneID INTEGER UNSIGNED,
    categoryLft INTEGER,
    categoryRgt INTEGER,
    CONSTRAINT PK_DiscountConditionRecord PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductBundle"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductBundle (
    productID INTEGER UNSIGNED NOT NULL,
    relatedProductID INTEGER UNSIGNED NOT NULL COMMENT 'The Product the related Product is assigned to',
    position INTEGER UNSIGNED DEFAULT 0 COMMENT 'ID of the ProductRelationshipGroup - if the related product is assigned to one (grouped together with similar products)',
    count FLOAT,
    CONSTRAINT PK_ProductBundle PRIMARY KEY (productID, relatedProductID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductCategory"                                            #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductCategory (
    productID INTEGER UNSIGNED NOT NULL,
    categoryID INTEGER UNSIGNED NOT NULL,
    CONSTRAINT PK_ProductCategory PRIMARY KEY (productID, categoryID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductVariationTemplate"                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductVariationTemplate (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name MEDIUMTEXT,
    CONSTRAINT PK_ProductVariationTemplate PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductVariationType"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductVariationType (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    templateID INTEGER UNSIGNED,
    productID INTEGER UNSIGNED,
    position INTEGER UNSIGNED DEFAULT 0,
    name MEDIUMTEXT,
    CONSTRAINT PK_ProductVariationType PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductVariation"                                           #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductVariation (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    typeID INTEGER UNSIGNED,
    position INTEGER UNSIGNED DEFAULT 0,
    name MEDIUMTEXT,
    CONSTRAINT PK_ProductVariation PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ProductVariationValue"                                      #
# ---------------------------------------------------------------------- #

CREATE TABLE ProductVariationValue (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    productID INTEGER UNSIGNED,
    variationID INTEGER UNSIGNED,
    CONSTRAINT PK_ProductVariationValue PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "SessionData"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE SessionData (
    ID CHAR(32) NOT NULL,
    userID INTEGER UNSIGNED,
    lastUpdated INTEGER,
    cacheUpdated INTEGER,
    data BLOB,
    CONSTRAINT PK_SessionData PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "ShippingClass"                                              #
# ---------------------------------------------------------------------- #

CREATE TABLE ShippingClass (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name MEDIUMTEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_ShippingClass PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "TaxClass"                                                   #
# ---------------------------------------------------------------------- #

CREATE TABLE TaxClass (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    name MEDIUMTEXT,
    position INTEGER UNSIGNED DEFAULT 0,
    CONSTRAINT PK_TaxClass PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "OrderedFile"                                                #
# ---------------------------------------------------------------------- #

CREATE TABLE OrderedFile (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    orderedItemID INTEGER UNSIGNED,
    productFileID INTEGER UNSIGNED,
    timesDownloaded INTEGER,
    lastDownloadTime DATETIME,
    CONSTRAINT PK_OrderedFile PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "CategoryRelationship"                                       #
# ---------------------------------------------------------------------- #

CREATE TABLE CategoryRelationship (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    categoryID INTEGER UNSIGNED,
    relatedCategoryID INTEGER UNSIGNED,
    position INTEGER,
    CONSTRAINT PK_CategoryRelationship PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "SearchableItem"                                             #
# ---------------------------------------------------------------------- #

CREATE TABLE SearchableItem (
    section VARCHAR(64),
    locale VARCHAR(40),
    value TEXT,
    meta TEXT,
    sort INTEGER UNSIGNED
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add table "BackendToolbarItem"                                         #
# ---------------------------------------------------------------------- #

CREATE TABLE BackendToolbarItem (
    ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    ownerID INTEGER UNSIGNED,
    menuID VARCHAR(40),
    productID INTEGER,
    userID INTEGER,
    orderID INTEGER,
    position INTEGER,
    CONSTRAINT PK_BackendToolbarItem PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Foreign key constraints                                                #
# ---------------------------------------------------------------------- #

ALTER TABLE Product ADD CONSTRAINT Category_Product
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Product ADD CONSTRAINT Manufacturer_Product
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE Product ADD CONSTRAINT ProductImage_Product
    FOREIGN KEY (defaultImageID) REFERENCES ProductImage (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE Product ADD CONSTRAINT Product_Product
    FOREIGN KEY (parentID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Product ADD CONSTRAINT ShippingClass_Product
    FOREIGN KEY (shippingClassID) REFERENCES ShippingClass (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE Product ADD CONSTRAINT TaxClass_Product
    FOREIGN KEY (taxClassID) REFERENCES TaxClass (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE Category ADD CONSTRAINT Category_Category
    FOREIGN KEY (parentNodeID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Category ADD CONSTRAINT CategoryImage_Category
    FOREIGN KEY (defaultImageID) REFERENCES CategoryImage (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE Category ADD CONSTRAINT EavObject_Category
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

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

ALTER TABLE CustomerOrder ADD CONSTRAINT UserAddress_CustomerOrder
    FOREIGN KEY (billingAddressID) REFERENCES UserAddress (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE CustomerOrder ADD CONSTRAINT UserAddress_CustomerOrder_Shipping
    FOREIGN KEY (shippingAddressID) REFERENCES UserAddress (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE CustomerOrder ADD CONSTRAINT EavObject_CustomerOrder
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE OrderedItem ADD CONSTRAINT Product_OrderedItem
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE OrderedItem ADD CONSTRAINT CustomerOrder_OrderedItem
    FOREIGN KEY (customerOrderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderedItem ADD CONSTRAINT Shipment_OrderedItem
    FOREIGN KEY (shipmentID) REFERENCES Shipment (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderedItem ADD CONSTRAINT OrderedItem_OrderedItem
    FOREIGN KEY (parentID) REFERENCES OrderedItem (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE User ADD CONSTRAINT ShippingAddress_User
    FOREIGN KEY (defaultShippingAddressID) REFERENCES ShippingAddress (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE User ADD CONSTRAINT BillingAddress_User
    FOREIGN KEY (defaultBillingAddressID) REFERENCES BillingAddress (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE User ADD CONSTRAINT UserGroup_User
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE User ADD CONSTRAINT EavObject_User
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE AccessControlAssociation ADD CONSTRAINT UserGroup_AccessControlAssociation
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE AccessControlAssociation ADD CONSTRAINT Role_AccessControlAssociation
    FOREIGN KEY (roleID) REFERENCES Role (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE UserGroup ADD CONSTRAINT EavObject_UserGroup
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE Filter ADD CONSTRAINT FilterGroup_Filter
    FOREIGN KEY (filterGroupID) REFERENCES FilterGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE FilterGroup ADD CONSTRAINT SpecField_FilterGroup
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRelationship ADD CONSTRAINT Product_RelatedProduct_
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRelationship ADD CONSTRAINT Product_ProductRelationship
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRelationship ADD CONSTRAINT ProductRelationshipGroup_ProductRelationship
    FOREIGN KEY (productRelationshipGroupID) REFERENCES ProductRelationshipGroup (ID) ON DELETE CASCADE;

ALTER TABLE ProductRelationship ADD CONSTRAINT Category_ProductRelationship
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT Product_ProductPrice
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT Currency_ProductPrice
    FOREIGN KEY (currencyID) REFERENCES Currency (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT RecurringProductPeriod_ProductPrice
    FOREIGN KEY (recurringID) REFERENCES RecurringProductPeriod (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Manufacturer ADD CONSTRAINT ManufacturerImage_Manufacturer
    FOREIGN KEY (defaultImageID) REFERENCES ManufacturerImage (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE Manufacturer ADD CONSTRAINT EavObject_Manufacturer
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE ProductImage ADD CONSTRAINT Product_ProductImage
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductFile ADD CONSTRAINT Product_ProductFile
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductFile ADD CONSTRAINT ProductFileGroup_ProductFile
    FOREIGN KEY (productFileGroupID) REFERENCES ProductFileGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE ProductRelationshipGroup ADD CONSTRAINT Category_ProductRelationshipGroup
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductReview ADD CONSTRAINT Product_ProductReview
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductReview ADD CONSTRAINT User_ProductReview
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE UserAddress ADD CONSTRAINT State_UserAddress
    FOREIGN KEY (stateID) REFERENCES State (ID) ON DELETE SET NULL;

ALTER TABLE UserAddress ADD CONSTRAINT EavObject_UserAddress
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE BillingAddress ADD CONSTRAINT User_BillingAddress
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE BillingAddress ADD CONSTRAINT UserAddress_BillingAddress
    FOREIGN KEY (userAddressID) REFERENCES UserAddress (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Transaction ADD CONSTRAINT CustomerOrder_Transaction
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE;

ALTER TABLE Transaction ADD CONSTRAINT Transaction_Transaction
    FOREIGN KEY (parentTransactionID) REFERENCES Transaction (ID) ON DELETE CASCADE;

ALTER TABLE Transaction ADD CONSTRAINT User_Transaction
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE Transaction ADD CONSTRAINT EavObject_Transaction
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE Shipment ADD CONSTRAINT CustomerOrder_Shipment
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Shipment ADD CONSTRAINT ShippingService_Shipment
    FOREIGN KEY (shippingServiceID) REFERENCES ShippingService (ID) ON DELETE SET NULL;

ALTER TABLE Shipment ADD CONSTRAINT UserAddress_Shipment
    FOREIGN KEY (shippingAddressID) REFERENCES UserAddress (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE ShippingAddress ADD CONSTRAINT User_ShippingAddress
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ShippingAddress ADD CONSTRAINT UserAddress_ShippingAddress
    FOREIGN KEY (userAddressID) REFERENCES UserAddress (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderNote ADD CONSTRAINT CustomerOrder_OrderNote
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderNote ADD CONSTRAINT User_OrderNote
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE;

ALTER TABLE DeliveryZoneCountry ADD CONSTRAINT DeliveryZone_DeliveryZoneCountry
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DeliveryZoneState ADD CONSTRAINT DeliveryZone_DeliveryZoneState
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DeliveryZoneState ADD CONSTRAINT State_DeliveryZoneState
    FOREIGN KEY (stateID) REFERENCES State (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DeliveryZoneCityMask ADD CONSTRAINT DeliveryZone_DeliveryZoneCityMask
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DeliveryZoneZipMask ADD CONSTRAINT DeliveryZone_DeliveryZoneZipMask
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DeliveryZoneAddressMask ADD CONSTRAINT DeliveryZone_DeliveryZoneAddressMask
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE TaxRate ADD CONSTRAINT Tax_TaxRate
    FOREIGN KEY (taxID) REFERENCES Tax (ID) ON DELETE CASCADE;

ALTER TABLE TaxRate ADD CONSTRAINT DeliveryZone_TaxRate
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE TaxRate ADD CONSTRAINT TaxClass_TaxRate
    FOREIGN KEY (taxClassID) REFERENCES TaxClass (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ShippingRate ADD CONSTRAINT ShippingService_ShippingRate
    FOREIGN KEY (shippingServiceID) REFERENCES ShippingService (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductFileGroup ADD CONSTRAINT Product_ProductFileGroup
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ShippingService ADD CONSTRAINT DeliveryZone_ShippingService
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ShippingService ADD CONSTRAINT EavObject_ShippingService
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE StaticPage ADD CONSTRAINT StaticPage_StaticPage
    FOREIGN KEY (parentID) REFERENCES StaticPage (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE StaticPage ADD CONSTRAINT EavObject_StaticPage
    FOREIGN KEY (eavObjectID) REFERENCES EavObject (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE ShipmentTax ADD CONSTRAINT TaxRate_ShipmentTax
    FOREIGN KEY (taxRateID) REFERENCES TaxRate (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ShipmentTax ADD CONSTRAINT Shipment_ShipmentTax
    FOREIGN KEY (shipmentID) REFERENCES Shipment (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderLog ADD CONSTRAINT User_OrderLog
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE OrderLog ADD CONSTRAINT CustomerOrder_OrderLog
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DeliveryZoneRealTimeService ADD CONSTRAINT DeliveryZone_DeliveryZoneRealTimeService
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE;

ALTER TABLE ExpressCheckout ADD CONSTRAINT UserAddress_ExpressCheckout
    FOREIGN KEY (addressID) REFERENCES UserAddress (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ExpressCheckout ADD CONSTRAINT CustomerOrder_ExpressCheckout
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductOption ADD CONSTRAINT Product_ProductOption
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductOption ADD CONSTRAINT Category_ProductOption
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductOption ADD CONSTRAINT ProductOptionChoice_ProductOption
    FOREIGN KEY (defaultChoiceID) REFERENCES ProductOptionChoice (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductOptionChoice ADD CONSTRAINT ProductOption_ProductOptionChoice
    FOREIGN KEY (optionID) REFERENCES ProductOption (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderedItemOption ADD CONSTRAINT OrderedItem_OrderedItemOption
    FOREIGN KEY (orderedItemID) REFERENCES OrderedItem (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderedItemOption ADD CONSTRAINT ProductOptionChoice_OrderedItemOption
    FOREIGN KEY (choiceID) REFERENCES ProductOptionChoice (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRatingType ADD CONSTRAINT Category_ProductRatingType
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRating ADD CONSTRAINT ProductRatingType_ProductRating
    FOREIGN KEY (ratingTypeID) REFERENCES ProductRatingType (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRating ADD CONSTRAINT ProductReview_ProductRating
    FOREIGN KEY (reviewID) REFERENCES ProductReview (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRating ADD CONSTRAINT Product_ProductRating
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRating ADD CONSTRAINT User_ProductRating
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE CategoryPresentation ADD CONSTRAINT Category_CategoryPresentation
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE CategoryPresentation ADD CONSTRAINT Product_CategoryPresentation
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE NewsletterSubscriber ADD CONSTRAINT User_NewsletterSubscriber
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE NewsletterSentMessage ADD CONSTRAINT NewsletterMessage_NewsletterSentMessage
    FOREIGN KEY (messageID) REFERENCES NewsletterMessage (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE NewsletterSentMessage ADD CONSTRAINT NewsletterSubscriber_NewsletterSentMessage
    FOREIGN KEY (subscriberID) REFERENCES NewsletterSubscriber (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE NewsletterSentMessage ADD CONSTRAINT User_NewsletterSentMessage
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RecurringProductPeriod ADD CONSTRAINT Product_RecurringProductPeriod
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavDateValue ADD CONSTRAINT EavField_EavDateValue
    FOREIGN KEY (fieldID) REFERENCES EavField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavDateValue ADD CONSTRAINT EavObject_EavDateValue
    FOREIGN KEY (objectID) REFERENCES EavObject (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavStringValue ADD CONSTRAINT EavField_EavStringValue
    FOREIGN KEY (fieldID) REFERENCES EavField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavStringValue ADD CONSTRAINT EavObject_EavStringValue
    FOREIGN KEY (objectID) REFERENCES EavObject (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavNumericValue ADD CONSTRAINT EavObject_EavNumericValue
    FOREIGN KEY (objectID) REFERENCES EavObject (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavNumericValue ADD CONSTRAINT EavField_EavNumericValue
    FOREIGN KEY (fieldID) REFERENCES EavField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavItem ADD CONSTRAINT EavValue_EavItem
    FOREIGN KEY (valueID) REFERENCES EavValue (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavItem ADD CONSTRAINT EavObject_EavItem
    FOREIGN KEY (objectID) REFERENCES EavObject (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavValue ADD CONSTRAINT EavField_EavValue
    FOREIGN KEY (fieldID) REFERENCES EavField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavField ADD CONSTRAINT EavFieldGroup_EavField
    FOREIGN KEY (eavFieldGroupID) REFERENCES EavFieldGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT User_EavObject
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT UserGroup_EavObject
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT Manufacturer_EavObject
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT CustomerOrder_EavObject
    FOREIGN KEY (customerOrderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT UserAddress_EavObject
    FOREIGN KEY (userAddressID) REFERENCES UserAddress (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT Transaction_EavObject
    FOREIGN KEY (transactionID) REFERENCES Transaction (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT Category_EavObject
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT ShippingService_EavObject
    FOREIGN KEY (shippingServiceID) REFERENCES ShippingService (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT StaticPage_EavObject
    FOREIGN KEY (staticPageID) REFERENCES StaticPage (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ManufacturerImage ADD CONSTRAINT Manufacturer_ManufacturerImage
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRatingSummary ADD CONSTRAINT Product_ProductRatingSummary
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRatingSummary ADD CONSTRAINT ProductRatingType_ProductRatingSummary
    FOREIGN KEY (ratingTypeID) REFERENCES ProductRatingType (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductList ADD CONSTRAINT Category_ProductList
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductListItem ADD CONSTRAINT ProductList_ProductListItem
    FOREIGN KEY (productListID) REFERENCES ProductList (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductListItem ADD CONSTRAINT Product_ProductListItem
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountCondition ADD CONSTRAINT DiscountCondition_DiscountCondition
    FOREIGN KEY (parentNodeID) REFERENCES DiscountCondition (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountAction ADD CONSTRAINT DiscountCondition_DiscountAction
    FOREIGN KEY (conditionID) REFERENCES DiscountCondition (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountAction ADD CONSTRAINT DiscountCondition_DiscountAction_ActionCondition
    FOREIGN KEY (actionConditionID) REFERENCES DiscountCondition (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE OrderDiscount ADD CONSTRAINT CustomerOrder_OrderDiscount
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderCoupon ADD CONSTRAINT CustomerOrder_OrderCoupon
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderCoupon ADD CONSTRAINT DiscountCondition_OrderCoupon
    FOREIGN KEY (discountConditionID) REFERENCES DiscountCondition (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT DiscountCondition_DiscountConditionRecord
    FOREIGN KEY (conditionID) REFERENCES DiscountCondition (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT DeliveryZone_DiscountConditionRecord
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT Product_DiscountConditionRecord
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT Manufacturer_DiscountConditionRecord
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT Category_DiscountConditionRecord
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT UserGroup_DiscountConditionRecord
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT User_DiscountConditionRecord
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE ProductBundle ADD CONSTRAINT Product_ProductBundle
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductBundle ADD CONSTRAINT Product_ProductBundle_Related
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductCategory ADD CONSTRAINT Category_ProductCategory
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductCategory ADD CONSTRAINT Product_ProductCategory
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductVariationType ADD CONSTRAINT ProductVariationTemplate_ProductVariationType
    FOREIGN KEY (templateID) REFERENCES ProductVariationTemplate (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductVariationType ADD CONSTRAINT Product_ProductVariationType
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductVariation ADD CONSTRAINT ProductVariationType_ProductVariation
    FOREIGN KEY (typeID) REFERENCES ProductVariationType (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductVariationValue ADD CONSTRAINT Product_ProductVariationValue
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductVariationValue ADD CONSTRAINT ProductVariation_ProductVariationValue
    FOREIGN KEY (variationID) REFERENCES ProductVariation (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE SessionData ADD CONSTRAINT User_SessionData
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderedFile ADD CONSTRAINT OrderedItem_OrderedFile
    FOREIGN KEY (orderedItemID) REFERENCES OrderedItem (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE OrderedFile ADD CONSTRAINT ProductFile_OrderedFile
    FOREIGN KEY (productFileID) REFERENCES ProductFile (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE CategoryRelationship ADD CONSTRAINT Category_CategoryRelationship
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE CategoryRelationship ADD CONSTRAINT Category_CategoryRelationship2
    FOREIGN KEY (relatedCategoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE BackendToolbarItem ADD CONSTRAINT User_BackendToolbarItem
    FOREIGN KEY (ownerID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;
