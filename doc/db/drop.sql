# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v5.0.1                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Database drop script                            #
# Created on:            2008-03-16 22:42                                #
# Model version:         Version 2008-03-16 1                            #
# ---------------------------------------------------------------------- #


# ---------------------------------------------------------------------- #
# Drop foreign key constraints                                           #
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

# ---------------------------------------------------------------------- #
# Drop table "Product"                                                   #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Product ALTER COLUMN isEnabled DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN isFeatured DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN type DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN voteSum DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN voteCount DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN hits DROP DEFAULT;

ALTER TABLE Product ALTER COLUMN position DROP DEFAULT;

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
# Drop table "CustomerOrder"                                             #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE CustomerOrder DROP PRIMARY KEY;

# Drop table #

DROP TABLE CustomerOrder;

# ---------------------------------------------------------------------- #
# Drop table "OrderedItem"                                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE OrderedItem DROP PRIMARY KEY;

# Drop table #

DROP TABLE OrderedItem;

# ---------------------------------------------------------------------- #
# Drop table "User"                                                      #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE User DROP PRIMARY KEY;

# Drop table #

DROP TABLE User;

# ---------------------------------------------------------------------- #
# Drop table "AccessControlAssociation"                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE AccessControlAssociation DROP PRIMARY KEY;

# Drop table #

DROP TABLE AccessControlAssociation;

# ---------------------------------------------------------------------- #
# Drop table "UserGroup"                                                 #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE UserGroup DROP PRIMARY KEY;

# Drop table #

DROP TABLE UserGroup;

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
# Drop table "Role"                                                      #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Role DROP PRIMARY KEY;

# Drop table #

DROP TABLE Role;

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
# Drop table "State"                                                     #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE State DROP PRIMARY KEY;

# Drop table #

DROP TABLE State;

# ---------------------------------------------------------------------- #
# Drop table "PostalCode"                                                #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE PostalCode DROP PRIMARY KEY;

# Drop table #

DROP TABLE PostalCode;

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

# ---------------------------------------------------------------------- #
# Drop table "ProductReview"                                             #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductReview DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductReview;

# ---------------------------------------------------------------------- #
# Drop table "UserAddress"                                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE UserAddress DROP PRIMARY KEY;

# Drop table #

DROP TABLE UserAddress;

# ---------------------------------------------------------------------- #
# Drop table "BillingAddress"                                            #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE BillingAddress DROP PRIMARY KEY;

# Drop table #

DROP TABLE BillingAddress;

# ---------------------------------------------------------------------- #
# Drop table "Transaction"                                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Transaction DROP PRIMARY KEY;

# Drop table #

DROP TABLE Transaction;

# ---------------------------------------------------------------------- #
# Drop table "Shipment"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Shipment DROP PRIMARY KEY;

# Drop table #

DROP TABLE Shipment;

# ---------------------------------------------------------------------- #
# Drop table "ShippingAddress"                                           #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ShippingAddress DROP PRIMARY KEY;

# Drop table #

DROP TABLE ShippingAddress;

# ---------------------------------------------------------------------- #
# Drop table "OrderNote"                                                 #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE OrderNote DROP PRIMARY KEY;

# Drop table #

DROP TABLE OrderNote;

# ---------------------------------------------------------------------- #
# Drop table "DeliveryZone"                                              #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE DeliveryZone ALTER COLUMN position DROP DEFAULT;

ALTER TABLE DeliveryZone DROP PRIMARY KEY;

# Drop table #

DROP TABLE DeliveryZone;

# ---------------------------------------------------------------------- #
# Drop table "DeliveryZoneCountry"                                       #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE DeliveryZoneCountry DROP PRIMARY KEY;

# Drop table #

DROP TABLE DeliveryZoneCountry;

# ---------------------------------------------------------------------- #
# Drop table "DeliveryZoneState"                                         #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE DeliveryZoneState DROP PRIMARY KEY;

# Drop table #

DROP TABLE DeliveryZoneState;

# ---------------------------------------------------------------------- #
# Drop table "DeliveryZoneCityMask"                                      #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE DeliveryZoneCityMask DROP PRIMARY KEY;

# Drop table #

DROP TABLE DeliveryZoneCityMask;

# ---------------------------------------------------------------------- #
# Drop table "DeliveryZoneZipMask"                                       #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE DeliveryZoneZipMask DROP PRIMARY KEY;

# Drop table #

DROP TABLE DeliveryZoneZipMask;

# ---------------------------------------------------------------------- #
# Drop table "DeliveryZoneAddressMask"                                   #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE DeliveryZoneAddressMask DROP PRIMARY KEY;

# Drop table #

DROP TABLE DeliveryZoneAddressMask;

# ---------------------------------------------------------------------- #
# Drop table "Tax"                                                       #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE Tax ALTER COLUMN position DROP DEFAULT;

ALTER TABLE Tax DROP PRIMARY KEY;

# Drop table #

DROP TABLE Tax;

# ---------------------------------------------------------------------- #
# Drop table "TaxRate"                                                   #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE TaxRate DROP PRIMARY KEY;

DROP INDEX TUC_TaxRate_DeliveryZone_Tax ON TaxRate;

# Drop table #

DROP TABLE TaxRate;

# ---------------------------------------------------------------------- #
# Drop table "ShippingRate"                                              #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ShippingRate DROP PRIMARY KEY;

# Drop table #

DROP TABLE ShippingRate;

# ---------------------------------------------------------------------- #
# Drop table "ProductFileGroup"                                          #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductFileGroup ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductFileGroup DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductFileGroup;

# ---------------------------------------------------------------------- #
# Drop table "ShippingService"                                           #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ShippingService ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ShippingService DROP PRIMARY KEY;

# Drop table #

DROP TABLE ShippingService;

# ---------------------------------------------------------------------- #
# Drop table "StaticPage"                                                #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE StaticPage ALTER COLUMN position DROP DEFAULT;

ALTER TABLE StaticPage DROP PRIMARY KEY;

# Drop table #

DROP TABLE StaticPage;

# ---------------------------------------------------------------------- #
# Drop table "ShipmentTax"                                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ShipmentTax DROP PRIMARY KEY;

# Drop table #

DROP TABLE ShipmentTax;

# ---------------------------------------------------------------------- #
# Drop table "OrderLog"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE OrderLog DROP PRIMARY KEY;

# Drop table #

DROP TABLE OrderLog;

# ---------------------------------------------------------------------- #
# Drop table "NewsPost"                                                  #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE NewsPost ALTER COLUMN position DROP DEFAULT;

ALTER TABLE NewsPost DROP PRIMARY KEY;

# Drop table #

DROP TABLE NewsPost;

# ---------------------------------------------------------------------- #
# Drop table "DeliveryZoneRealTimeService"                               #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE DeliveryZoneRealTimeService DROP PRIMARY KEY;

# Drop table #

DROP TABLE DeliveryZoneRealTimeService;

# ---------------------------------------------------------------------- #
# Drop table "ExpressCheckout"                                           #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ExpressCheckout DROP PRIMARY KEY;

# Drop table #

DROP TABLE ExpressCheckout;

# ---------------------------------------------------------------------- #
# Drop table "ProductOption"                                             #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductOption ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductOption DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductOption;

# ---------------------------------------------------------------------- #
# Drop table "ProductOptionChoice"                                       #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductOptionChoice ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductOptionChoice DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductOptionChoice;

# ---------------------------------------------------------------------- #
# Drop table "OrderedItemOption"                                         #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE OrderedItemOption DROP PRIMARY KEY;

# Drop table #

DROP TABLE OrderedItemOption;

# ---------------------------------------------------------------------- #
# Drop table "ProductRatingType"                                         #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductRatingType ALTER COLUMN position DROP DEFAULT;

ALTER TABLE ProductRatingType DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductRatingType;

# ---------------------------------------------------------------------- #
# Drop table "ProductRating"                                             #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductRating DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductRating;

# ---------------------------------------------------------------------- #
# Drop table "CategoryPresentation"                                      #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE CategoryPresentation DROP PRIMARY KEY;

# Drop table #

DROP TABLE CategoryPresentation;

# ---------------------------------------------------------------------- #
# Drop table "ProductPriceRule"                                          #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductPriceRule DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductPriceRule;

# ---------------------------------------------------------------------- #
# Drop table "ProductPresentation"                                       #
# ---------------------------------------------------------------------- #

# Drop constraints #

ALTER TABLE ProductPresentation DROP PRIMARY KEY;

# Drop table #

DROP TABLE ProductPresentation;
