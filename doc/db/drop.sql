# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.2                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          K-Shop.dez                                      #
# Project name:                                                          #
# Author:                                                                #
# Script type:           Database drop script                            #
# Created on:            2006-10-26 13:19                                #
# ---------------------------------------------------------------------- #


# ---------------------------------------------------------------------- #
# Drop foreign key constraints                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE Product DROP FOREIGN KEY Catalog_Product;

ALTER TABLE Product DROP FOREIGN KEY Manufacturer_Product;

ALTER TABLE Product DROP FOREIGN KEY ProductImage_Product;

ALTER TABLE Catalog DROP FOREIGN KEY Catalog_Catalog;

ALTER TABLE ProductLangData DROP FOREIGN KEY Product_ProductLangData;

ALTER TABLE ProductLangData DROP FOREIGN KEY Language_ProductLangData;

ALTER TABLE Specification DROP FOREIGN KEY SpecFieldValue_Specification;

ALTER TABLE Specification DROP FOREIGN KEY Product_Specification;

ALTER TABLE Specification DROP FOREIGN KEY SpecField_Specification;

ALTER TABLE SpecField DROP FOREIGN KEY Catalog_SpecField;

ALTER TABLE CatalogLangData DROP FOREIGN KEY Language_CatalogLangData;

ALTER TABLE CatalogLangData DROP FOREIGN KEY Catalog_CatalogLangData;

ALTER TABLE SpecFieldLangData DROP FOREIGN KEY SpecField_SpecFieldLangData;

ALTER TABLE SpecFieldLangData DROP FOREIGN KEY Language_SpecFieldLangData;

ALTER TABLE SpecFieldValue DROP FOREIGN KEY SpecField_SpecFieldValue;

ALTER TABLE SpecFieldValueLangData DROP FOREIGN KEY Language_SpecFieldValueLangData;

ALTER TABLE SpecFieldValueLangData DROP FOREIGN KEY SpecFieldValue_SpecFieldValueLangData;

ALTER TABLE Filter DROP FOREIGN KEY FilterGroup_Filter;

ALTER TABLE FilterGroup DROP FOREIGN KEY SpecField_FilterGroup;

ALTER TABLE FilterLangData DROP FOREIGN KEY Filter_FilterLangData;

ALTER TABLE FilterLangData DROP FOREIGN KEY Language_FilterLangData;

ALTER TABLE FilterGroupLangData DROP FOREIGN KEY Language_FilterGroupLangData;

ALTER TABLE FilterGroupLangData DROP FOREIGN KEY FilterGroup_FilterGroupLangData;

ALTER TABLE RelatedProduct DROP FOREIGN KEY Product_RelatedProduct_;

ALTER TABLE RelatedProduct DROP FOREIGN KEY Product_RelatedProduct;

ALTER TABLE ProductPrice DROP FOREIGN KEY Product_ProductPrice;

ALTER TABLE ProductPrice DROP FOREIGN KEY Currency_ProductPrice;

ALTER TABLE ProductImage DROP FOREIGN KEY Product_ProductImage;

ALTER TABLE ProductImageLangData DROP FOREIGN KEY Language_ProductImageLangData;

ALTER TABLE ProductImageLangData DROP FOREIGN KEY ProductImage_ProductImageLangData;

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

# Drop indexes #

DROP INDEX IDX_Product_1 ON Product;

DROP INDEX IDX_Product_2 ON Product;

# Drop table #

DROP TABLE Product;

# ---------------------------------------------------------------------- #
# Drop table "Catalog"                                                   #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Catalog ALTER COLUMN isActive DROP DEFAULT;

ALTER TABLE Catalog ALTER COLUMN position DROP DEFAULT;

ALTER TABLE Catalog DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_Catalog_1 ON Catalog;

# Drop table #

DROP TABLE Catalog;

# ---------------------------------------------------------------------- #
# Drop table "Language"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Language ALTER COLUMN isDefault DROP DEFAULT;

ALTER TABLE Language DROP PRIMARY KEY;

# Drop table #

DROP TABLE Language;

# ---------------------------------------------------------------------- #
# Drop table "ProductLangData"                                           #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductLangData DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_ProductLang_1 ON ProductLangData;

DROP INDEX IDX_ProductLang_2 ON ProductLangData;

# Drop table #

DROP TABLE ProductLangData;

# ---------------------------------------------------------------------- #
# Drop table "Specification"                                             #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Specification DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_Specification_1 ON Specification;

DROP INDEX IDX_Specification_2 ON Specification;

# Drop table #

DROP TABLE Specification;

# ---------------------------------------------------------------------- #
# Drop table "SpecField"                                                 #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecField ALTER COLUMN position DROP DEFAULT;

ALTER TABLE SpecField DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_SpecField_1 ON SpecField;

# Drop table #

DROP TABLE SpecField;

# ---------------------------------------------------------------------- #
# Drop table "CatalogLangData"                                           #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE CatalogLangData DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_CatalogLang_1 ON CatalogLangData;

DROP INDEX IDX_CatalogLang_2 ON CatalogLangData;

# Drop table #

DROP TABLE CatalogLangData;

# ---------------------------------------------------------------------- #
# Drop table "SpecFieldLangData"                                         #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecFieldLangData DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_SpecFieldLang_1 ON SpecFieldLangData;

DROP INDEX IDX_SpecFieldLang_2 ON SpecFieldLangData;

# Drop table #

DROP TABLE SpecFieldLangData;

# ---------------------------------------------------------------------- #
# Drop table "SpecFieldValue"                                            #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecFieldValue DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_SpecFieldValue_1 ON SpecFieldValue;

# Drop table #

DROP TABLE SpecFieldValue;

# ---------------------------------------------------------------------- #
# Drop table "SpecFieldValueLangData"                                    #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE SpecFieldValueLangData DROP PRIMARY KEY;

# Drop indexes #

DROP INDEX IDX_SpecFieldValueLang_1 ON SpecFieldValueLangData;

DROP INDEX IDX_SpecFieldValueLang_2 ON SpecFieldValueLangData;

# Drop table #

DROP TABLE SpecFieldValueLangData;

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
# Drop table "FilterLangData"                                            #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE FilterLangData DROP PRIMARY KEY;

# Drop table #

DROP TABLE FilterLangData;

# ---------------------------------------------------------------------- #
# Drop table "FilterGroupLangData"                                       #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE FilterGroupLangData DROP PRIMARY KEY;

# Drop table #

DROP TABLE FilterGroupLangData;

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
# Drop table "ProductImageLangData"                                      #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductImageLangData DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductImageLangData;

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
