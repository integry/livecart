# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v5.1.1                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Alter database script                           #
# Created on:            2008-04-06 03:17                                #
# Model version:         Version 2008-04-06 2                            #
# From model version:    Version 2008-04-06 1                            #
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
# Modify table "Currency"                                                #
# ---------------------------------------------------------------------- #

ALTER TABLE Currency ALTER COLUMN decimalSeparator DROP DEFAULT;

ALTER TABLE Currency MODIFY decimalSeparator VARCHAR(3) DEFAULT '.';

ALTER TABLE Currency MODIFY thousandSeparator VARCHAR(3) DEFAULT ' ';

# ---------------------------------------------------------------------- #
# Add foreign key constraints                                            #
# ---------------------------------------------------------------------- #

ALTER TABLE Product ADD CONSTRAINT Category_Product 
    FOREIGN KEY (categoryID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE Product ADD CONSTRAINT Manufacturer_Product 
    FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (ID) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE;

ALTER TABLE ProductReview ADD CONSTRAINT User_ProductReview 
    FOREIGN KEY (userID) REFERENCES User (ID) ON DELETE CASCADE;

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
    FOREIGN KEY (reviewID) REFERENCES ProductReview (ID);

ALTER TABLE CategoryPresentation ADD CONSTRAINT Category_CategoryPresentation 
    FOREIGN KEY (ID) REFERENCES Category (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPriceRule ADD CONSTRAINT Product_ProductPriceRule 
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPriceRule ADD CONSTRAINT UserGroup_ProductPriceRule 
    FOREIGN KEY (userGroupID) REFERENCES UserGroup (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductPresentation ADD CONSTRAINT Product_ProductPresentation 
    FOREIGN KEY (ID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;
