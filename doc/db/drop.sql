# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.3                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Database drop script                            #
# Created on:            2007-03-26 13:00                                #
# ---------------------------------------------------------------------- #


# ---------------------------------------------------------------------- #
# Drop foreign key constraints                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE Product DROP FOREIGN KEY Category_Product;

ALTER TABLE Product DROP FOREIGN KEY Manufacturer_Product;

ALTER TABLE Product DROP FOREIGN KEY ProductImage_Product;

ALTER TABLE Category DROP FOREIGN KEY Category_Category;

ALTER TABLE Category DROP FOREIGN KEY CategoryImage_Category;

ALTER TABLE SpecificationItem DROP FOREIGN KEY SpecFieldValue_SpecificationItem;

ALTER TABLE SpecificationItem DROP FOREIGN KEY Product_SpecificationItem;

ALTER TABLE SpecificationItem DROP FOREIGN KEY SpecField_SpecificationItem;

ALTER TABLE SpecField DROP FOREIGN KEY Category_SpecField;

ALTER TABLE SpecField DROP FOREIGN KEY SpecFieldGroup_SpecField;

ALTER TABLE SpecFieldValue DROP FOREIGN KEY SpecField_SpecFieldValue;

ALTER TABLE Filter DROP FOREIGN KEY FilterGroup_Filter;

ALTER TABLE FilterGroup DROP FOREIGN KEY SpecField_FilterGroup;

ALTER TABLE ProductRelationship DROP FOREIGN KEY Product_RelatedProduct_;

ALTER TABLE ProductRelationship DROP FOREIGN KEY Product_ProductRelationship;

ALTER TABLE ProductRelationship DROP FOREIGN KEY ProductRelationshipGroup_ProductRelationship;

ALTER TABLE ProductPrice DROP FOREIGN KEY Product_ProductPrice;

ALTER TABLE ProductPrice DROP FOREIGN KEY Currency_ProductPrice;

ALTER TABLE ProductImage DROP FOREIGN KEY Product_ProductImage;

ALTER TABLE ProductFile DROP FOREIGN KEY Product_ProductFile;

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

# ---------------------------------------------------------------------- #
# Drop table "Product"                                                   #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Product ALTER COLUMN isEnabled DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN isBestSeller DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN type DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN voteSum DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN voteCount DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN hits DROP DEFAULT;

ALTER TABLE Product DROP PRIMARY KEY;

# Drop table #

DROP TABLE Product;

# ---------------------------------------------------------------------- #
# Drop table "Category"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Category ALTER COLUMN activeProductCount DROP DEFAULT;

ALTER TABLE Category ALTER COLUMN totalProductCount DROP DEFAULT;

ALTER TABLE Category ALTER COLUMN isEnabled DROP DEFAULT;

ALTER TABLE Category DROP PRIMARY KEY;

# Drop table #

DROP TABLE Category;

# ---------------------------------------------------------------------- #
# Drop table "Language"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Language ALTER COLUMN isDefault DROP DEFAULT;

ALTER TABLE Language ALTER COLUMN position DROP DEFAULT;

ALTER TABLE Language DROP PRIMARY KEY;

# Drop table #

DROP TABLE Language;

# ---------------------------------------------------------------------- #
# Drop table "SpecificationItem"                                         #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecificationItem DROP PRIMARY KEY;

# Drop table #

DROP TABLE SpecificationItem;

# ---------------------------------------------------------------------- #
# Drop table "SpecField"                                                 #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecField ALTER COLUMN type DROP DEFAULT;

ALTER TABLE SpecField ALTER COLUMN dataType DROP DEFAULT;

ALTER TABLE SpecField ALTER COLUMN position DROP DEFAULT;

ALTER TABLE SpecField DROP PRIMARY KEY;

# Drop table #

DROP TABLE SpecField;

# ---------------------------------------------------------------------- #
# Drop table "SpecFieldValue"                                            #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecFieldValue ALTER COLUMN position DROP DEFAULT;

ALTER TABLE SpecFieldValue DROP PRIMARY KEY;

# Drop table #

DROP TABLE SpecFieldValue;

# ---------------------------------------------------------------------- #
# Drop table "Filter"                                                    #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Filter DROP PRIMARY KEY;

# Drop table #

DROP TABLE Filter;

# ---------------------------------------------------------------------- #
# Drop table "FilterGroup"                                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE FilterGroup ALTER COLUMN position DROP DEFAULT;

ALTER TABLE FilterGroup DROP PRIMARY KEY;

# Drop table #

DROP TABLE FilterGroup;

# ---------------------------------------------------------------------- #
# Drop table "ProductRelationship"                                       #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductRelationship ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductRelationship DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductRelationship;

# ---------------------------------------------------------------------- #
# Drop table "ProductPrice"                                              #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductPrice DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductPrice;

# ---------------------------------------------------------------------- #
# Drop table "Currency"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Currency ALTER COLUMN isDefault DROP DEFAULT;

ALTER TABLE Currency ALTER COLUMN isEnabled DROP DEFAULT;

ALTER TABLE Currency ALTER COLUMN position DROP DEFAULT;

ALTER TABLE Currency DROP PRIMARY KEY;

# Drop table #

DROP TABLE Currency;

# ---------------------------------------------------------------------- #
# Drop table "Manufacturer"                                              #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Manufacturer DROP PRIMARY KEY;

# Drop table #

DROP TABLE Manufacturer;

# ---------------------------------------------------------------------- #
# Drop table "ProductImage"                                              #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductImage ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductImage DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductImage;

# ---------------------------------------------------------------------- #
# Drop table "ProductFile"                                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductFile ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductFile DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductFile;

# ---------------------------------------------------------------------- #
# Drop table "Discount"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Discount DROP PRIMARY KEY;

# Drop table #

DROP TABLE Discount;

# ---------------------------------------------------------------------- #
# Drop table "CategoryImage"                                             #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE CategoryImage ALTER COLUMN position DROP DEFAULT;

ALTER TABLE CategoryImage DROP PRIMARY KEY;

# Drop table #

DROP TABLE CategoryImage;

# ---------------------------------------------------------------------- #
# Drop table "SpecificationNumericValue"                                 #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecificationNumericValue DROP PRIMARY KEY;

# Drop table #

DROP TABLE SpecificationNumericValue;

# ---------------------------------------------------------------------- #
# Drop table "SpecificationStringValue"                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecificationStringValue DROP PRIMARY KEY;

# Drop table #

DROP TABLE SpecificationStringValue;

# ---------------------------------------------------------------------- #
# Drop table "SpecificationDateValue"                                    #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecificationDateValue DROP PRIMARY KEY;

# Drop table #

DROP TABLE SpecificationDateValue;

# ---------------------------------------------------------------------- #
# Drop table "SpecFieldGroup"                                            #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecFieldGroup ALTER COLUMN position DROP DEFAULT;

ALTER TABLE SpecFieldGroup DROP PRIMARY KEY;

# Drop table #

DROP TABLE SpecFieldGroup;

# ---------------------------------------------------------------------- #
# Drop table "ProductRelationshipGroup"                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductRelationshipGroup ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductRelationshipGroup DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductRelationshipGroup;

# ---------------------------------------------------------------------- #
# Drop table "HelpComment"                                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE HelpComment DROP PRIMARY KEY;

# Drop table #

DROP TABLE HelpComment;
