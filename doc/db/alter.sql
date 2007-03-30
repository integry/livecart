# ---------------------------------------------------------------------- #
# Script generated with: DeZign for Databases v4.1.3                     #
# Target DBMS:           MySQL 4                                         #
# Project file:          LiveCart.dez                                    #
# Project name:          LiveCart                                        #
# Author:                Integry Systems                                 #
# Script type:           Alter database script                           #
# Created on:            2007-03-30 19:23                                #
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

ALTER TABLE CustomerOrder DROP FOREIGN KEY User_CustomerOrder;

ALTER TABLE OrderedItem DROP FOREIGN KEY Product_OrderedItem;

ALTER TABLE OrderedItem DROP FOREIGN KEY CustomerOrder_OrderedItem;

ALTER TABLE OrderedItem DROP FOREIGN KEY Shipment_OrderedItem;

ALTER TABLE User DROP FOREIGN KEY UserBillingAddress_User;

ALTER TABLE User DROP FOREIGN KEY UserShippingAddress_User;

ALTER TABLE AccessControlList DROP FOREIGN KEY User_AccessControlList;

ALTER TABLE AccessControlList DROP FOREIGN KEY RoleGroup_AccessControlList;

ALTER TABLE AccessControlList DROP FOREIGN KEY Role_AccessControlList;

ALTER TABLE UserGroup DROP FOREIGN KEY User_UserGroup;

ALTER TABLE UserGroup DROP FOREIGN KEY RoleGroup_UserGroup;

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

ALTER TABLE ProductReview DROP FOREIGN KEY Product_ProductReview;

ALTER TABLE ProductReview DROP FOREIGN KEY User_ProductReview;

ALTER TABLE UserBillingAddress DROP FOREIGN KEY User_UserBillingAddress;

ALTER TABLE UserBillingAddress DROP FOREIGN KEY UserAddress_UserBillingAddress;

ALTER TABLE Transaction DROP FOREIGN KEY CustomerOrder_Transaction;

ALTER TABLE Shipment DROP FOREIGN KEY CustomerOrder_Shipment;

ALTER TABLE UserShippingAddress DROP FOREIGN KEY User_UserShippingAddress;

ALTER TABLE UserShippingAddress DROP FOREIGN KEY UserAddress_UserShippingAddress;

ALTER TABLE OrderNote DROP FOREIGN KEY CustomerOrder_OrderNote;

ALTER TABLE OrderNote DROP FOREIGN KEY User_OrderNote;

ALTER TABLE DeliveryZoneCountry DROP FOREIGN KEY DeliveryZone_DeliveryZoneCountry;

ALTER TABLE DeliveryZoneState DROP FOREIGN KEY DeliveryZone_DeliveryZoneState;

ALTER TABLE DeliveryZoneState DROP FOREIGN KEY State_DeliveryZoneState;

ALTER TABLE DeliveryZoneCityMask DROP FOREIGN KEY DeliveryZone_DeliveryZoneCityMask;

ALTER TABLE DeliveryZoneZipMask DROP FOREIGN KEY DeliveryZone_DeliveryZoneZipMask;

ALTER TABLE DeliveryZoneAddressMask DROP FOREIGN KEY DeliveryZone_DeliveryZoneAddressMask;

ALTER TABLE TaxRate DROP FOREIGN KEY TaxType_TaxRate;

ALTER TABLE TaxRate DROP FOREIGN KEY DeliveryZone_TaxRate;

# ---------------------------------------------------------------------- #
# Modify table "OrderedItem"                                             #
# ---------------------------------------------------------------------- #

ALTER TABLE OrderedItem DROP PRIMARY KEY;

ALTER TABLE OrderedItem MODIFY ID INTEGER NOT NULL AUTO_INCREMENT;

ALTER TABLE OrderedItem ADD CONSTRAINT PK_OrderedItem 
    PRIMARY KEY (ID);

# ---------------------------------------------------------------------- #
# Add foreign key constraints                                            #
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
    FOREIGN KEY (userID) REFERENCES User (ID);

ALTER TABLE OrderedItem ADD CONSTRAINT Product_OrderedItem 
    FOREIGN KEY (productID) REFERENCES Product (ID);

ALTER TABLE OrderedItem ADD CONSTRAINT CustomerOrder_OrderedItem 
    FOREIGN KEY (customerOrderID) REFERENCES CustomerOrder (ID);

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
