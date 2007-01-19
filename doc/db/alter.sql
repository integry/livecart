# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.3                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Alter database script                           #
# Created on:            2007-01-19 18:24                                #
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

# ---------------------------------------------------------------------- #
# Modify table "Product"                                                 #
# ---------------------------------------------------------------------- #

DROP INDEX IDX_Product_2 ON Product;

ALTER TABLE Product CHANGE SKU sku VARCHAR(20);

CREATE INDEX IDX_Product_2 ON Product (sku);

CREATE TABLE SpecificationNumericValue (
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    value FLOAT,
    CONSTRAINT PK_SpecificationNumericValue PRIMARY KEY (productID, specFieldID)
);

CREATE TABLE SpecificationStringValue (
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    value TEXT,
    CONSTRAINT PK_SpecificationStringValue PRIMARY KEY (productID, specFieldID)
);

CREATE TABLE SpecificationDateValue (
    productID INTEGER UNSIGNED NOT NULL,
    specFieldID INTEGER UNSIGNED NOT NULL,
    value DATE,
    CONSTRAINT PK_SpecificationDateValue PRIMARY KEY (productID, specFieldID)
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

ALTER TABLE Filter ADD CONSTRAINT FilterGroup_Filter 
    FOREIGN KEY (filterGroupID) REFERENCES FilterGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Filter ADD CONSTRAINT SpecFieldValue_Filter 
    FOREIGN KEY (specFieldValueID) REFERENCES SpecFieldValue (ID) ON DELETE SET NULL;

ALTER TABLE FilterGroup ADD CONSTRAINT SpecField_FilterGroup 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct_ 
    FOREIGN KEY (ProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE RelatedProduct ADD CONSTRAINT Product_RelatedProduct 
    FOREIGN KEY (relatedProductID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

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
    FOREIGN KEY (ID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE CategoryImage ADD CONSTRAINT Category_CategoryImage 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE;

ALTER TABLE SpecificationNumericValue ADD CONSTRAINT Product_SpecificationNumericValue 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE SpecificationNumericValue ADD CONSTRAINT SpecField_SpecificationNumericValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID);

ALTER TABLE SpecificationStringValue ADD CONSTRAINT Product_SpecificationStringValue 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE SpecificationStringValue ADD CONSTRAINT SpecField_SpecificationStringValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID);

ALTER TABLE SpecificationDateValue ADD CONSTRAINT Product_SpecificationDateValue 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE SpecificationDateValue ADD CONSTRAINT SpecField_SpecificationDateValue 
    FOREIGN KEY (specFieldID) REFERENCES SpecField (ID);
