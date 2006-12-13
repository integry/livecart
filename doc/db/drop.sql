# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.3                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:                                                          #
# Author:                                                                #
# Script type:           Database drop script                            #
# Created on:            2006-12-13 12:20                                #
# ---------------------------------------------------------------------- #


# ---------------------------------------------------------------------- #
# Drop foreign key constraints                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE Product DROP FOREIGN KEY Category_Product;

ALTER TABLE Product DROP FOREIGN KEY Manufacturer_Product;

ALTER TABLE Product DROP FOREIGN KEY ProductImage_Product;

ALTER TABLE Category DROP FOREIGN KEY Category_Category;

ALTER TABLE Specification DROP FOREIGN KEY SpecFieldValue_Specification;

ALTER TABLE Specification DROP FOREIGN KEY Product_Specification;

ALTER TABLE Specification DROP FOREIGN KEY SpecField_Specification;

ALTER TABLE SpecField DROP FOREIGN KEY Category_SpecField;

ALTER TABLE SpecFieldValue DROP FOREIGN KEY SpecField_SpecFieldValue;

ALTER TABLE Filter DROP FOREIGN KEY FilterGroup_Filter;

ALTER TABLE FilterGroup DROP FOREIGN KEY SpecField_FilterGroup;

ALTER TABLE RelatedProduct DROP FOREIGN KEY Product_RelatedProduct_;

ALTER TABLE RelatedProduct DROP FOREIGN KEY Product_RelatedProduct;

ALTER TABLE ProductPrice DROP FOREIGN KEY Product_ProductPrice;

ALTER TABLE ProductPrice DROP FOREIGN KEY Currency_ProductPrice;

ALTER TABLE ProductImage DROP FOREIGN KEY Product_ProductImage;

ALTER TABLE ProductFile DROP FOREIGN KEY Product_ProductFile;

ALTER TABLE ProductFile DROP FOREIGN KEY FileType_ProductFile;

ALTER TABLE Discount DROP FOREIGN KEY Product_Discount;

# ---------------------------------------------------------------------- #
# Drop table "Product"                                                   #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Product ALTER COLUMN status DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN isBestSeller DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN type DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN voteSum DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN voteCount DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN hits DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN unitsType DROP DEFAULT;

ALTER TABLE Product DROP PRIMARY KEY;

# Drop table #

DROP TABLE Product;

# ---------------------------------------------------------------------- #
# Drop table "Category"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Category ALTER COLUMN isActive DROP DEFAULT;

ALTER TABLE Category ALTER COLUMN position DROP DEFAULT;

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
# Drop table "Specification"                                             #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Specification DROP PRIMARY KEY;

# Drop table #

DROP TABLE Specification;

# ---------------------------------------------------------------------- #
# Drop table "SpecField"                                                 #
# ---------------------------------------------------------------------- #

# Drop constraints #

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
# Drop table "RelatedProduct"                                            #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE RelatedProduct DROP PRIMARY KEY;

# Drop table #

DROP TABLE RelatedProduct;

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

ALTER TABLE ProductFile DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductFile;

# ---------------------------------------------------------------------- #
# Drop table "FileType"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE FileType DROP PRIMARY KEY;

# Drop table #

DROP TABLE FileType;

# ---------------------------------------------------------------------- #
# Drop table "Discount"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Discount DROP PRIMARY KEY;

# Drop table #

DROP TABLE Discount;
