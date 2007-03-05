# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.2                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Alter database script                           #
# Created on:            2007-03-05 01:55                                #
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

ALTER TABLE OrderedItem DROP FOREIGN KEY Product_OrderedItem;

ALTER TABLE OrderedItem DROP FOREIGN KEY CustomerOrder_OrderedItem;

ALTER TABLE UserConfigValue DROP FOREIGN KEY User_UserConfigValue;

ALTER TABLE AccessControlList DROP FOREIGN KEY User_AccessControlList;

ALTER TABLE AccessControlList DROP FOREIGN KEY RoleGroup_AccessControlList;

ALTER TABLE AccessControlList DROP FOREIGN KEY Role_AccessControlList;

ALTER TABLE UserGroup DROP FOREIGN KEY User_UserGroup;

ALTER TABLE UserGroup DROP FOREIGN KEY RoleGroup_UserGroup;

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

ALTER TABLE City DROP FOREIGN KEY State_City;

ALTER TABLE SpecFieldGroup DROP FOREIGN KEY Category_SpecFieldGroup;

# ---------------------------------------------------------------------- #
# Drop table "FileType"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE FileType DROP PRIMARY KEY;

# Drop table #

DROP TABLE FileType;

# ---------------------------------------------------------------------- #
# Modify table "Product"                                                 #
# ---------------------------------------------------------------------- #

ALTER TABLE Product ADD COLUMN isBackOrderable BOOL;

ALTER TABLE Product MODIFY keywords TEXT AFTER longDescription;

ALTER TABLE Product MODIFY dateCreated TIMESTAMP AFTER keywords;

ALTER TABLE Product MODIFY dateUpdated TIMESTAMP AFTER dateCreated;

ALTER TABLE Product MODIFY URL TINYTEXT AFTER dateUpdated;

ALTER TABLE Product MODIFY handle VARCHAR(40) AFTER URL;

ALTER TABLE Product MODIFY isBestSeller BOOL DEFAULT 0 AFTER handle;

ALTER TABLE Product MODIFY type TINYINT UNSIGNED DEFAULT 0 COMMENT '1 - intangible 0 - tangible' AFTER isBestSeller;

ALTER TABLE Product MODIFY voteSum INTEGER UNSIGNED DEFAULT 0 AFTER type;

ALTER TABLE Product MODIFY voteCount INTEGER UNSIGNED DEFAULT 0 AFTER voteSum;

ALTER TABLE Product MODIFY hits INTEGER UNSIGNED DEFAULT 0 COMMENT 'Number of times product has been viewed by customers' AFTER voteCount;

ALTER TABLE Product MODIFY isBackOrderable BOOL AFTER isFreeShipping;

ALTER TABLE Product MODIFY shippingWeight NUMERIC(8,3) AFTER isBackOrderable;

ALTER TABLE Product MODIFY stockCount FLOAT AFTER shippingWeight;

ALTER TABLE Product MODIFY reservedCount FLOAT AFTER stockCount;

# ---------------------------------------------------------------------- #
# Modify table "Filter"                                                  #
# ---------------------------------------------------------------------- #

ALTER TABLE Filter DROP COLUMN specFieldValueID;

ALTER TABLE Filter DROP COLUMN handle;

# ---------------------------------------------------------------------- #
# Modify table "RelatedProduct"                                          #
# ---------------------------------------------------------------------- #

ALTER TABLE RelatedProduct ADD COLUMN relatedProductGroupID INTEGER NOT NULL;

ALTER TABLE RelatedProduct ADD COLUMN position INTEGER UNSIGNED DEFAULT 0;

ALTER TABLE RelatedProduct MODIFY relatedProductGroupID INTEGER NOT NULL AFTER ProductID;

# ---------------------------------------------------------------------- #
# Modify table "ProductFile"                                             #
# ---------------------------------------------------------------------- #

ALTER TABLE ProductFile DROP COLUMN fileTypeID;

ALTER TABLE ProductFile ADD COLUMN name TEXT;

ALTER TABLE ProductFile ADD COLUMN description TEXT;

ALTER TABLE ProductFile ADD COLUMN position INTEGER UNSIGNED DEFAULT 0;

CREATE TABLE RelatedProductGroup (
    ID INTEGER NOT NULL,
    ProductID INTEGER,
    position INTEGER UNSIGNED DEFAULT 0,
    name TEXT,
    CONSTRAINT PK_RelatedProductGroup PRIMARY KEY (ID)
);

CREATE TABLE HelpComment (
    ID INTEGER NOT NULL AUTO_INCREMENT,
    topicID VARCHAR(100),
    username VARCHAR(100),
    text TEXT,
    timeAdded DATETIME,
    CONSTRAINT PK_HelpComment PRIMARY KEY (ID)
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

ALTER TABLE FilterGroup ADD CONSTRAINT SpecField_FilterGroup 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct_ 
    FOREIGN KEY (ProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct 
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT RelatedProductGroup_RelatedProduct 
    FOREIGN KEY (relatedProductGroupID) REFERENCES RelatedProductGroup (ID) ON DELETE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT Product_ProductPrice 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPrice ADD CONSTRAINT Currency_ProductPrice 
    FOREIGN KEY (currencyID) REFERENCES Currency (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE ProductImage ADD CONSTRAINT Product_ProductImage 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE ProductFile ADD CONSTRAINT Product_ProductFile 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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

ALTER TABLE RelatedProductGroup ADD CONSTRAINT Product_RelatedProductGroup 
    FOREIGN KEY (ProductID) REFERENCES Product (ID) ON DELETE CASCADE;
