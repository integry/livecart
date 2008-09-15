# From model version:    1.1.2                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE Product DROP FOREIGN KEY Category_Product;

ALTER TABLE Product DROP FOREIGN KEY Manufacturer_Product;

ALTER TABLE Product DROP FOREIGN KEY ProductImage_Product;

ALTER TABLE Product DROP FOREIGN KEY Product_Product;

ALTER TABLE Category DROP FOREIGN KEY Category_Category;

ALTER TABLE Category DROP FOREIGN KEY CategoryImage_Category;

ALTER TABLE SpecificationItem DROP FOREIGN KEY SpecFieldValue_SpecificationItem;

ALTER TABLE SpecificationItem DROP FOREIGN KEY Product_SpecificationItem;

ALTER TABLE SpecificationItem DROP FOREIGN KEY SpecField_SpecificationItem;

ALTER TABLE SpecField DROP FOREIGN KEY Category_SpecField;

ALTER TABLE SpecField DROP FOREIGN KEY SpecFieldGroup_SpecField;

ALTER TABLE SpecFieldValue DROP FOREIGN KEY SpecField_SpecFieldValue;

ALTER TABLE CustomerOrder DROP FOREIGN KEY User_CustomerOrder;

ALTER TABLE CustomerOrder DROP FOREIGN KEY UserAddress_CustomerOrder;

ALTER TABLE CustomerOrder DROP FOREIGN KEY UserAddress_CustomerOrder_Shipping;

ALTER TABLE OrderedItem DROP FOREIGN KEY Product_OrderedItem;

ALTER TABLE OrderedItem DROP FOREIGN KEY CustomerOrder_OrderedItem;

ALTER TABLE OrderedItem DROP FOREIGN KEY Shipment_OrderedItem;

ALTER TABLE User DROP FOREIGN KEY ShippingAddress_User;

ALTER TABLE User DROP FOREIGN KEY BillingAddress_User;

ALTER TABLE User DROP FOREIGN KEY UserGroup_User;

ALTER TABLE AccessControlAssociation DROP FOREIGN KEY UserGroup_AccessControlAssociation;

ALTER TABLE AccessControlAssociation DROP FOREIGN KEY Role_AccessControlAssociation;

ALTER TABLE Filter DROP FOREIGN KEY FilterGroup_Filter;

ALTER TABLE FilterGroup DROP FOREIGN KEY SpecField_FilterGroup;

ALTER TABLE ProductRelationship DROP FOREIGN KEY Product_RelatedProduct_;

ALTER TABLE ProductRelationship DROP FOREIGN KEY Product_ProductRelationship;

ALTER TABLE ProductRelationship DROP FOREIGN KEY ProductRelationshipGroup_ProductRelationship;

ALTER TABLE ProductPrice DROP FOREIGN KEY Product_ProductPrice;

ALTER TABLE ProductPrice DROP FOREIGN KEY Currency_ProductPrice;

ALTER TABLE ProductImage DROP FOREIGN KEY Product_ProductImage;

ALTER TABLE ProductFile DROP FOREIGN KEY Product_ProductFile;

ALTER TABLE ProductFile DROP FOREIGN KEY ProductFileGroup_ProductFile;

ALTER TABLE Discount DROP FOREIGN KEY Product_Discount;

ALTER TABLE CategoryImage DROP FOREIGN KEY Category_CategoryImage;

ALTER TABLE SpecificationNumericValue DROP FOREIGN KEY Product_SpecificationNumericValue;

ALTER TABLE SpecificationNumericValue DROP FOREIGN KEY SpecField_SpecificationNumericValue;

ALTER TABLE SpecificationStringValue DROP FOREIGN KEY Product_SpecificationStringValue;

ALTER TABLE SpecificationStringValue DROP FOREIGN KEY SpecField_SpecificationStringValue;

ALTER TABLE SpecificationDateValue DROP FOREIGN KEY Product_SpecificationDateValue;

ALTER TABLE SpecificationDateValue DROP FOREIGN KEY SpecField_SpecificationDateValue;

ALTER TABLE SpecFieldGroup DROP FOREIGN KEY Category_SpecFieldGroup;

ALTER TABLE ProductRelationshipGroup DROP FOREIGN KEY Product_ProductRelationshipGroup;

ALTER TABLE ProductReview DROP FOREIGN KEY Product_ProductReview;

ALTER TABLE ProductReview DROP FOREIGN KEY User_ProductReview;

ALTER TABLE UserAddress DROP FOREIGN KEY State_UserAddress;

ALTER TABLE BillingAddress DROP FOREIGN KEY User_BillingAddress;

ALTER TABLE BillingAddress DROP FOREIGN KEY UserAddress_BillingAddress;

ALTER TABLE Transaction DROP FOREIGN KEY CustomerOrder_Transaction;

ALTER TABLE Transaction DROP FOREIGN KEY Transaction_Transaction;

ALTER TABLE Transaction DROP FOREIGN KEY User_Transaction;

ALTER TABLE Shipment DROP FOREIGN KEY CustomerOrder_Shipment;

ALTER TABLE Shipment DROP FOREIGN KEY ShippingService_Shipment;

ALTER TABLE ShippingAddress DROP FOREIGN KEY User_ShippingAddress;

ALTER TABLE ShippingAddress DROP FOREIGN KEY UserAddress_ShippingAddress;

ALTER TABLE OrderNote DROP FOREIGN KEY CustomerOrder_OrderNote;

ALTER TABLE OrderNote DROP FOREIGN KEY User_OrderNote;

ALTER TABLE DeliveryZoneCountry DROP FOREIGN KEY DeliveryZone_DeliveryZoneCountry;

ALTER TABLE DeliveryZoneState DROP FOREIGN KEY DeliveryZone_DeliveryZoneState;

ALTER TABLE DeliveryZoneState DROP FOREIGN KEY State_DeliveryZoneState;

ALTER TABLE DeliveryZoneCityMask DROP FOREIGN KEY DeliveryZone_DeliveryZoneCityMask;

ALTER TABLE DeliveryZoneZipMask DROP FOREIGN KEY DeliveryZone_DeliveryZoneZipMask;

ALTER TABLE DeliveryZoneAddressMask DROP FOREIGN KEY DeliveryZone_DeliveryZoneAddressMask;

ALTER TABLE TaxRate DROP FOREIGN KEY Tax_TaxRate;

ALTER TABLE TaxRate DROP FOREIGN KEY DeliveryZone_TaxRate;

ALTER TABLE ShippingRate DROP FOREIGN KEY ShippingService_ShippingRate;

ALTER TABLE ProductFileGroup DROP FOREIGN KEY Product_ProductFileGroup;

ALTER TABLE ShippingService DROP FOREIGN KEY DeliveryZone_ShippingService;

ALTER TABLE ShipmentTax DROP FOREIGN KEY TaxRate_ShipmentTax;

ALTER TABLE ShipmentTax DROP FOREIGN KEY Shipment_ShipmentTax;

ALTER TABLE OrderLog DROP FOREIGN KEY User_OrderLog;

ALTER TABLE OrderLog DROP FOREIGN KEY CustomerOrder_OrderLog;

ALTER TABLE DeliveryZoneRealTimeService DROP FOREIGN KEY DeliveryZone_DeliveryZoneRealTimeService;

ALTER TABLE ExpressCheckout DROP FOREIGN KEY UserAddress_ExpressCheckout;

ALTER TABLE ExpressCheckout DROP FOREIGN KEY CustomerOrder_ExpressCheckout;

ALTER TABLE ProductOption DROP FOREIGN KEY Product_ProductOption;

ALTER TABLE ProductOption DROP FOREIGN KEY Category_ProductOption;

ALTER TABLE ProductOption DROP FOREIGN KEY ProductOptionChoice_ProductOption;

ALTER TABLE ProductOptionChoice DROP FOREIGN KEY ProductOption_ProductOptionChoice;

ALTER TABLE OrderedItemOption DROP FOREIGN KEY OrderedItem_OrderedItemOption;

ALTER TABLE OrderedItemOption DROP FOREIGN KEY ProductOptionChoice_OrderedItemOption;

ALTER TABLE ProductRatingType DROP FOREIGN KEY Category_ProductRatingType;

ALTER TABLE ProductRating DROP FOREIGN KEY ProductRatingType_ProductRating;

ALTER TABLE ProductRating DROP FOREIGN KEY ProductReview_ProductRating;

ALTER TABLE CategoryPresentation DROP FOREIGN KEY Category_CategoryPresentation;

ALTER TABLE ProductPriceRule DROP FOREIGN KEY Product_ProductPriceRule;

ALTER TABLE ProductPriceRule DROP FOREIGN KEY UserGroup_ProductPriceRule;

ALTER TABLE ProductPresentation DROP FOREIGN KEY Product_ProductPresentation;

ALTER TABLE NewsletterSubscriber DROP FOREIGN KEY User_NewsletterSubscriber;

ALTER TABLE NewsletterSentMessage DROP FOREIGN KEY NewsletterMessage_NewsletterSentMessage;

ALTER TABLE NewsletterSentMessage DROP FOREIGN KEY NewsletterSubscriber_NewsletterSentMessage;

ALTER TABLE NewsletterSentMessage DROP FOREIGN KEY User_NewsletterSentMessage;

# ---------------------------------------------------------------------- #
# Drop table "ProductPriceRule"                                          #
# ---------------------------------------------------------------------- #

# Drop table #

DROP TABLE IF EXISTS ProductPriceRule;

# ---------------------------------------------------------------------- #
# Modify table "Product"                                                 #
# ---------------------------------------------------------------------- #

DROP INDEX IDX_Product_rating ON Product;

ALTER TABLE Product MODIFY ID INTEGER UNSIGNED NOT NULL COMMENT 'Number of times product has been viewed by customers';

ALTER TABLE Product ALTER COLUMN voteSum DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN voteCount DROP DEFAULT;

ALTER TABLE Product ADD COLUMN isRecurring BOOL;

ALTER TABLE Product ADD COLUMN reviewCount INTEGER NOT NULL;

ALTER TABLE Product CHANGE voteSum ratingSum INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Sum of all rating votes';

ALTER TABLE Product CHANGE voteCount ratingCount INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Count of all rating votes';

ALTER TABLE Product MODIFY ratingSum INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Sum of all rating votes';

ALTER TABLE Product MODIFY ratingCount INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Count of all rating votes';

ALTER TABLE Product MODIFY rating FLOAT NOT NULL COMMENT 'Product rating (voteSum divided by voteCount)';

ALTER TABLE Product MODIFY isRecurring BOOL AFTER isEnabled;

ALTER TABLE Product MODIFY isFeatured BOOL NOT NULL DEFAULT 0 COMMENT 'Determines if the product has been marked as featured product' AFTER isRecurring;

ALTER TABLE Product MODIFY isSeparateShipment BOOL NOT NULL COMMENT 'Determines if a separate shipment is required for delivering this product' AFTER isFeatured;

ALTER TABLE Product MODIFY isFreeShipping BOOL NOT NULL COMMENT 'Determines if free shipping is available for this product' AFTER isSeparateShipment;

ALTER TABLE Product MODIFY isBackOrderable BOOL NOT NULL COMMENT 'Determines if this product is available for backordering. If backordering is enabled, customers can order the product even if it is out of stock' AFTER isFreeShipping;

ALTER TABLE Product MODIFY isFractionalUnit BOOL NOT NULL AFTER isBackOrderable;

ALTER TABLE Product MODIFY sku VARCHAR(20) NOT NULL COMMENT 'Product stock keeping unit code' AFTER isFractionalUnit;

ALTER TABLE Product MODIFY name MEDIUMTEXT COMMENT 'Product name (translatable)' AFTER sku;

ALTER TABLE Product MODIFY shortDescription MEDIUMTEXT COMMENT 'A shorter description of the product (translatable). The short description is usually displayed in the category product list' AFTER name;

ALTER TABLE Product MODIFY longDescription MEDIUMTEXT COMMENT 'A longer description of the product (translatable). The long description is usually displayed in the product detail page' AFTER shortDescription;

ALTER TABLE Product MODIFY keywords TEXT COMMENT 'Additional product search keywords, which may not be included in the product name or description, but can be used when a customer searches for a product' AFTER longDescription;

ALTER TABLE Product MODIFY dateCreated TIMESTAMP NOT NULL COMMENT 'Product creation date' AFTER keywords;

ALTER TABLE Product MODIFY dateUpdated TIMESTAMP COMMENT 'Product last update date' AFTER dateCreated;

ALTER TABLE Product MODIFY URL TINYTEXT COMMENT 'External website URL (manufacturers website, etc.)' AFTER dateUpdated;

ALTER TABLE Product MODIFY type TINYINT UNSIGNED DEFAULT 0 COMMENT 'Determines if the product is intangible (1) or tangible (0)' AFTER URL;

ALTER TABLE Product MODIFY ratingSum INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Sum of all rating votes' AFTER type;

ALTER TABLE Product MODIFY ratingCount INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Count of all rating votes' AFTER ratingSum;

ALTER TABLE Product MODIFY rating FLOAT NOT NULL COMMENT 'Product rating (voteSum divided by voteCount)' AFTER ratingCount;

ALTER TABLE Product MODIFY hits INTEGER UNSIGNED DEFAULT 0 COMMENT 'Number of times the product has been viewed by customers' AFTER rating;

ALTER TABLE Product MODIFY reviewCount INTEGER NOT NULL AFTER hits;

ALTER TABLE Product MODIFY minimumQuantity FLOAT COMMENT 'Minimum amount of the product that can be ordered' AFTER reviewCount;

ALTER TABLE Product MODIFY shippingSurchargeAmount NUMERIC(12,2) COMMENT 'Additional surcharge for shipping (extra large, etc. items)' AFTER minimumQuantity;

ALTER TABLE Product MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Number of times product has been viewed by customers';

CREATE INDEX IDX_Product_isEnabled_Category ON Product (categoryID,isEnabled);

CREATE INDEX IDX_Product_rating ON Product (rating);

# ---------------------------------------------------------------------- #
# Modify table "Category"                                                #
# ---------------------------------------------------------------------- #

ALTER TABLE Category MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE Category MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE INDEX IDX_Category_parentNode ON Category (parentNodeID);

CREATE INDEX IDX_Category_lft ON Category (parentNodeID ASC,lft ASC);

# ---------------------------------------------------------------------- #
# Modify table "OrderedItem"                                             #
# ---------------------------------------------------------------------- #

ALTER TABLE OrderedItem MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE OrderedItem ADD COLUMN parentID INTEGER UNSIGNED;

ALTER TABLE OrderedItem MODIFY parentID INTEGER UNSIGNED AFTER shipmentID;

ALTER TABLE OrderedItem MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "User"                                                    #
# ---------------------------------------------------------------------- #

ALTER TABLE User MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE User ADD COLUMN preferences TEXT;

ALTER TABLE User MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "ProductPrice"                                            #
# ---------------------------------------------------------------------- #

ALTER TABLE ProductPrice ADD COLUMN recurringID INTEGER UNSIGNED;

ALTER TABLE ProductPrice ADD COLUMN serializedRules TEXT;

ALTER TABLE ProductPrice MODIFY recurringID INTEGER UNSIGNED AFTER currencyID;

CREATE INDEX IDX_ProductPrice_1 ON ProductPrice (productID,currencyID);

# ---------------------------------------------------------------------- #
# Modify table "Manufacturer"                                            #
# ---------------------------------------------------------------------- #

ALTER TABLE Manufacturer MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE Manufacturer ADD COLUMN defaultImageID INTEGER UNSIGNED;

ALTER TABLE Manufacturer MODIFY defaultImageID INTEGER UNSIGNED AFTER ID;

ALTER TABLE Manufacturer MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "ProductReview"                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE ProductReview MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE ProductReview ADD COLUMN isEnabled BOOL;

ALTER TABLE ProductReview ADD COLUMN ip INTEGER(11) UNSIGNED;

ALTER TABLE ProductReview ADD COLUMN rating FLOAT;

ALTER TABLE ProductReview ADD COLUMN ratingSum INTEGER NOT NULL;

ALTER TABLE ProductReview ADD COLUMN ratingCount INTEGER NOT NULL;

ALTER TABLE ProductReview ADD COLUMN nickname VARCHAR(100);

ALTER TABLE ProductReview MODIFY isEnabled BOOL AFTER userID;

ALTER TABLE ProductReview MODIFY ip INTEGER(11) UNSIGNED AFTER isEnabled;

ALTER TABLE ProductReview MODIFY dateCreated TIMESTAMP AFTER ip;

ALTER TABLE ProductReview MODIFY rating FLOAT AFTER dateCreated;

ALTER TABLE ProductReview MODIFY ratingSum INTEGER NOT NULL AFTER rating;

ALTER TABLE ProductReview MODIFY ratingCount INTEGER NOT NULL AFTER ratingSum;

ALTER TABLE ProductReview MODIFY nickname VARCHAR(100) AFTER ratingCount;

ALTER TABLE ProductReview MODIFY title VARCHAR(255) AFTER nickname;

ALTER TABLE ProductReview MODIFY text TEXT AFTER title;

ALTER TABLE ProductReview MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE INDEX IP_address ON ProductReview (ip);

# ---------------------------------------------------------------------- #
# Modify table "Transaction"                                             #
# ---------------------------------------------------------------------- #

ALTER TABLE Transaction MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE Transaction MODIFY ccLastDigits CHAR(80) COMMENT 'Last 4 digits of credit card number';

ALTER TABLE Transaction MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "ProductRating"                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE ProductRating MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE ProductRating ADD COLUMN productID INTEGER UNSIGNED NOT NULL;

ALTER TABLE ProductRating ADD COLUMN userID INTEGER UNSIGNED;

ALTER TABLE ProductRating ADD COLUMN dateCreated TIMESTAMP;

ALTER TABLE ProductRating ADD COLUMN ip INTEGER(11) UNSIGNED;

ALTER TABLE ProductRating MODIFY rating INTEGER NOT NULL;

ALTER TABLE ProductRating MODIFY productID INTEGER UNSIGNED NOT NULL AFTER ID;

ALTER TABLE ProductRating MODIFY userID INTEGER UNSIGNED AFTER productID;

ALTER TABLE ProductRating MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

CREATE INDEX IP_address ON ProductRating (ip);

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
    recordCount INTEGER NOT NULL,
    validFrom TIMESTAMP DEFAULT '0000-00-00',
    validTo TIMESTAMP DEFAULT '0000-00-00',
    count FLOAT,
    subtotal FLOAT,
    comparisonType TINYINT COMMENT '0 - equal, 1 - less than or equal, 2 - greater than or equal, 3 - not equal',
    name MEDIUMTEXT,
    description MEDIUMTEXT,
    couponCode VARCHAR(40) NOT NULL,
    serializedCondition TEXT,
    position INTEGER UNSIGNED DEFAULT 0,
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
    type TINYINT NOT NULL,
    amountMeasure TINYINT NOT NULL,
    amount FLOAT,
    position INTEGER UNSIGNED DEFAULT 0,
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
    couponCode VARCHAR(255),
    CONSTRAINT PK_OrderCoupon PRIMARY KEY (ID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

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
    CONSTRAINT PK_ProductBundle PRIMARY KEY (productID, relatedProductID)
)
ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

# ---------------------------------------------------------------------- #
# Add foreign key constraints                                            #
# ---------------------------------------------------------------------- #

ALTER TABLE Product ADD CONSTRAINT Category_Product
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Product ADD CONSTRAINT Manufacturer_Product
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE Product ADD CONSTRAINT ProductImage_Product
    FOREIGN KEY (defaultImageID) REFERENCES ProductImage (ID) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE Product ADD CONSTRAINT Product_Product
    FOREIGN KEY (parentID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Category ADD CONSTRAINT Category_Category
    FOREIGN KEY (parentNodeID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Category ADD CONSTRAINT CategoryImage_Category
    FOREIGN KEY (defaultImageID) REFERENCES CategoryImage (ID) ON DELETE SET NULL ON UPDATE SET NULL;

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

ALTER TABLE OrderedItem ADD CONSTRAINT Product_OrderedItem
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE AccessControlAssociation ADD CONSTRAINT UserGroup_AccessControlAssociation
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE AccessControlAssociation ADD CONSTRAINT Role_AccessControlAssociation
    FOREIGN KEY (roleID) REFERENCES Role (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE ProductPrice ADD CONSTRAINT RecurringProductPeriod_ProductPrice
    FOREIGN KEY (recurringID) REFERENCES RecurringProductPeriod (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Manufacturer ADD CONSTRAINT ManufacturerImage_Manufacturer
    FOREIGN KEY (defaultImageID) REFERENCES ManufacturerImage (ID) ON DELETE SET NULL ON UPDATE SET NULL;

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

ALTER TABLE ProductReview ADD CONSTRAINT Product_ProductReview
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductReview ADD CONSTRAINT User_ProductReview
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE UserAddress ADD CONSTRAINT State_UserAddress
    FOREIGN KEY (stateID) REFERENCES State (ID) ON DELETE SET NULL;

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

ALTER TABLE Shipment ADD CONSTRAINT CustomerOrder_Shipment
    FOREIGN KEY (orderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Shipment ADD CONSTRAINT ShippingService_Shipment
    FOREIGN KEY (shippingServiceID) REFERENCES ShippingService (ID) ON DELETE SET NULL;

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

ALTER TABLE ShippingRate ADD CONSTRAINT ShippingService_ShippingRate
    FOREIGN KEY (shippingServiceID) REFERENCES ShippingService (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductFileGroup ADD CONSTRAINT Product_ProductFileGroup
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ShippingService ADD CONSTRAINT DeliveryZone_ShippingService
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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
    FOREIGN KEY (ID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPresentation ADD CONSTRAINT Product_ProductPresentation
    FOREIGN KEY (ID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE EavObject ADD CONSTRAINT Category_EavObject
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT User_EavObject
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT UserGroup_EavObject
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT Manufacturer_EavObject
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE EavObject ADD CONSTRAINT CustomerOrder_EavObject
    FOREIGN KEY (customerOrderID) REFERENCES CustomerOrder (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT DiscountCondition_DiscountConditionRecord
    FOREIGN KEY (conditionID) REFERENCES DiscountCondition (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT DeliveryZone_DiscountConditionRecord
    FOREIGN KEY (deliveryZoneID) REFERENCES DeliveryZone (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT Product_DiscountConditionRecord
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT Manufacturer_DiscountConditionRecord
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT Category_DiscountConditionRecord
    FOREIGN KEY (categoryID) REFERENCES Category (ID);

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT UserGroup_DiscountConditionRecord
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID);

ALTER TABLE DiscountConditionRecord ADD CONSTRAINT User_DiscountConditionRecord
    FOREIGN KEY (userID) REFERENCES User (ID);

ALTER TABLE ProductBundle ADD CONSTRAINT Product_ProductBundle
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductBundle ADD CONSTRAINT Product_ProductBundle_Related
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;
