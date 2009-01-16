
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

ALTER TABLE OrderedItem DROP FOREIGN KEY OrderedItem_OrderedItem;

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

ALTER TABLE ProductRelationship DROP FOREIGN KEY Category_ProductRelationship;

ALTER TABLE ProductPrice DROP FOREIGN KEY Product_ProductPrice;

ALTER TABLE ProductPrice DROP FOREIGN KEY Currency_ProductPrice;

ALTER TABLE ProductPrice DROP FOREIGN KEY RecurringProductPeriod_ProductPrice;

ALTER TABLE Manufacturer DROP FOREIGN KEY ManufacturerImage_Manufacturer;

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

ALTER TABLE ProductRelationshipGroup DROP FOREIGN KEY Category_ProductRelationshipGroup;

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

ALTER TABLE Shipment DROP FOREIGN KEY UserAddress_Shipment;

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

ALTER TABLE ProductRating DROP FOREIGN KEY Product_ProductRating;

ALTER TABLE ProductRating DROP FOREIGN KEY User_ProductRating;

ALTER TABLE CategoryPresentation DROP FOREIGN KEY Category_CategoryPresentation;

ALTER TABLE ProductPresentation DROP FOREIGN KEY Product_ProductPresentation;

ALTER TABLE NewsletterSubscriber DROP FOREIGN KEY User_NewsletterSubscriber;

ALTER TABLE NewsletterSentMessage DROP FOREIGN KEY NewsletterMessage_NewsletterSentMessage;

ALTER TABLE NewsletterSentMessage DROP FOREIGN KEY NewsletterSubscriber_NewsletterSentMessage;

ALTER TABLE NewsletterSentMessage DROP FOREIGN KEY User_NewsletterSentMessage;

ALTER TABLE RecurringProductPeriod DROP FOREIGN KEY Product_RecurringProductPeriod;

ALTER TABLE EavDateValue DROP FOREIGN KEY EavField_EavDateValue;

ALTER TABLE EavDateValue DROP FOREIGN KEY EavObject_EavDateValue;

ALTER TABLE EavStringValue DROP FOREIGN KEY EavField_EavStringValue;

ALTER TABLE EavStringValue DROP FOREIGN KEY EavObject_EavStringValue;

ALTER TABLE EavNumericValue DROP FOREIGN KEY EavObject_EavNumericValue;

ALTER TABLE EavNumericValue DROP FOREIGN KEY EavField_EavNumericValue;

ALTER TABLE EavItem DROP FOREIGN KEY EavValue_EavItem;

ALTER TABLE EavItem DROP FOREIGN KEY EavObject_EavItem;

ALTER TABLE EavValue DROP FOREIGN KEY EavField_EavValue;

ALTER TABLE EavField DROP FOREIGN KEY EavFieldGroup_EavField;

ALTER TABLE EavObject DROP FOREIGN KEY Category_EavObject;

ALTER TABLE EavObject DROP FOREIGN KEY User_EavObject;

ALTER TABLE EavObject DROP FOREIGN KEY UserGroup_EavObject;

ALTER TABLE EavObject DROP FOREIGN KEY Manufacturer_EavObject;

ALTER TABLE EavObject DROP FOREIGN KEY CustomerOrder_EavObject;

ALTER TABLE EavObject DROP FOREIGN KEY UserAddress_EavObject;

ALTER TABLE EavObject DROP FOREIGN KEY Transaction_EavObject;

ALTER TABLE ManufacturerImage DROP FOREIGN KEY Manufacturer_ManufacturerImage;

ALTER TABLE ProductRatingSummary DROP FOREIGN KEY Product_ProductRatingSummary;

ALTER TABLE ProductRatingSummary DROP FOREIGN KEY ProductRatingType_ProductRatingSummary;

ALTER TABLE ProductList DROP FOREIGN KEY Category_ProductList;

ALTER TABLE ProductListItem DROP FOREIGN KEY ProductList_ProductListItem;

ALTER TABLE ProductListItem DROP FOREIGN KEY Product_ProductListItem;

ALTER TABLE DiscountCondition DROP FOREIGN KEY DiscountCondition_DiscountCondition;

ALTER TABLE DiscountAction DROP FOREIGN KEY DiscountCondition_DiscountAction;

ALTER TABLE DiscountAction DROP FOREIGN KEY DiscountCondition_DiscountAction_ActionCondition;

ALTER TABLE OrderDiscount DROP FOREIGN KEY CustomerOrder_OrderDiscount;

ALTER TABLE OrderCoupon DROP FOREIGN KEY CustomerOrder_OrderCoupon;

ALTER TABLE DiscountConditionRecord DROP FOREIGN KEY DiscountCondition_DiscountConditionRecord;

ALTER TABLE DiscountConditionRecord DROP FOREIGN KEY DeliveryZone_DiscountConditionRecord;

ALTER TABLE DiscountConditionRecord DROP FOREIGN KEY Product_DiscountConditionRecord;

ALTER TABLE DiscountConditionRecord DROP FOREIGN KEY Manufacturer_DiscountConditionRecord;

ALTER TABLE DiscountConditionRecord DROP FOREIGN KEY Category_DiscountConditionRecord;

ALTER TABLE DiscountConditionRecord DROP FOREIGN KEY UserGroup_DiscountConditionRecord;

ALTER TABLE DiscountConditionRecord DROP FOREIGN KEY User_DiscountConditionRecord;

ALTER TABLE ProductBundle DROP FOREIGN KEY Product_ProductBundle;

ALTER TABLE ProductBundle DROP FOREIGN KEY Product_ProductBundle_Related;

ALTER TABLE ProductCategory DROP FOREIGN KEY Category_ProductCategory;

ALTER TABLE ProductCategory DROP FOREIGN KEY Product_ProductCategory;

ALTER TABLE ProductVariationType DROP FOREIGN KEY ProductVariationTemplate_ProductVariationType;

ALTER TABLE ProductVariationType DROP FOREIGN KEY Product_ProductVariationType;

ALTER TABLE ProductVariation DROP FOREIGN KEY ProductVariationType_ProductVariation;

ALTER TABLE ProductVariationValue DROP FOREIGN KEY Product_ProductVariationValue;

ALTER TABLE ProductVariationValue DROP FOREIGN KEY ProductVariation_ProductVariationValue;

# ---------------------------------------------------------------------- #
# Modify table "Product"                                                 #
# ---------------------------------------------------------------------- #

ALTER TABLE Product MODIFY ID INTEGER UNSIGNED NOT NULL COMMENT 'Number of times product has been viewed by customers';

ALTER TABLE Product ADD COLUMN pageTitle MEDIUMTEXT;

ALTER TABLE Product MODIFY pageTitle MEDIUMTEXT AFTER longDescription;

ALTER TABLE Product MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Number of times product has been viewed by customers';

# ---------------------------------------------------------------------- #
# Modify table "Category"                                                #
# ---------------------------------------------------------------------- #

ALTER TABLE Category MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE Category ADD COLUMN eavObjectID INTEGER UNSIGNED;

ALTER TABLE Category ADD COLUMN pageTitle MEDIUMTEXT;

ALTER TABLE Category MODIFY eavObjectID INTEGER UNSIGNED AFTER defaultImageID;

ALTER TABLE Category MODIFY pageTitle MEDIUMTEXT AFTER keywords;

ALTER TABLE Category MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "CustomerOrder"                                           #
# ---------------------------------------------------------------------- #

ALTER TABLE CustomerOrder MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE CustomerOrder ADD COLUMN eavObjectID INTEGER UNSIGNED;

ALTER TABLE CustomerOrder ADD COLUMN checkoutStep TINYINT UNSIGNED NOT NULL COMMENT '0 - cart 1 - registered 2 - selected address 3 - selected shipping method 4 - attempted payment';

ALTER TABLE CustomerOrder MODIFY eavObjectID INTEGER UNSIGNED AFTER shippingAddressID;

ALTER TABLE CustomerOrder MODIFY checkoutStep TINYINT UNSIGNED NOT NULL COMMENT '0 - cart 1 - registered 2 - selected address 3 - selected shipping method 4 - attempted payment' AFTER currencyID;

ALTER TABLE CustomerOrder MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "User"                                                    #
# ---------------------------------------------------------------------- #

ALTER TABLE User MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE User ADD COLUMN eavObjectID INTEGER UNSIGNED;

ALTER TABLE User ADD COLUMN locale CHAR(4);

ALTER TABLE User MODIFY eavObjectID INTEGER UNSIGNED AFTER userGroupID;

ALTER TABLE User MODIFY locale CHAR(4) AFTER eavObjectID;

ALTER TABLE User MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "UserGroup"                                               #
# ---------------------------------------------------------------------- #

ALTER TABLE UserGroup MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE UserGroup ADD COLUMN eavObjectID INTEGER UNSIGNED;

ALTER TABLE UserGroup MODIFY eavObjectID INTEGER UNSIGNED AFTER ID;

ALTER TABLE UserGroup MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "Manufacturer"                                            #
# ---------------------------------------------------------------------- #

ALTER TABLE Manufacturer MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE Manufacturer ADD COLUMN eavObjectID INTEGER UNSIGNED;

ALTER TABLE Manufacturer MODIFY eavObjectID INTEGER UNSIGNED AFTER defaultImageID;

ALTER TABLE Manufacturer MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "UserAddress"                                             #
# ---------------------------------------------------------------------- #

ALTER TABLE UserAddress MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE UserAddress ADD COLUMN eavObjectID INTEGER UNSIGNED;

ALTER TABLE UserAddress MODIFY eavObjectID INTEGER UNSIGNED AFTER stateID;

ALTER TABLE UserAddress MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "Transaction"                                             #
# ---------------------------------------------------------------------- #

ALTER TABLE Transaction MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE Transaction ADD COLUMN eavObjectID INTEGER UNSIGNED;

ALTER TABLE Transaction MODIFY eavObjectID INTEGER UNSIGNED AFTER userID;

ALTER TABLE Transaction MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "SearchLog"                                               #
# ---------------------------------------------------------------------- #

ALTER TABLE SearchLog MODIFY ID INTEGER UNSIGNED NOT NULL;

DROP INDEX TUC_SearchLog_1 ON SearchLog;

ALTER TABLE SearchLog MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Modify table "NewsletterMessage"                                       #
# ---------------------------------------------------------------------- #

ALTER TABLE NewsletterMessage MODIFY ID INTEGER UNSIGNED NOT NULL;

ALTER TABLE NewsletterMessage ADD COLUMN html TEXT;

ALTER TABLE NewsletterMessage MODIFY ID INTEGER UNSIGNED NOT NULL AUTO_INCREMENT;

# ---------------------------------------------------------------------- #
# Data migration                                                         #
# ---------------------------------------------------------------------- #

UPDATE Category LEFT JOIN EavObject ON Category.ID=EavObject.categoryID SET Category.eavObjectID=EavObject.ID;
UPDATE CustomerOrder LEFT JOIN EavObject ON CustomerOrder.ID=EavObject.customerOrderID SET CustomerOrder.eavObjectID=EavObject.ID;
UPDATE Manufacturer LEFT JOIN EavObject ON Manufacturer.ID=EavObject.manufacturerID SET Manufacturer.eavObjectID=EavObject.ID;
UPDATE User LEFT JOIN EavObject ON User.ID=EavObject.userID SET User.eavObjectID=EavObject.ID;
UPDATE UserGroup LEFT JOIN EavObject ON UserGroup.ID=EavObject.userGroupID SET UserGroup.eavObjectID=EavObject.ID;
UPDATE UserAddress LEFT JOIN EavObject ON UserAddress.ID=EavObject.userAddressID SET UserAddress.eavObjectID=EavObject.ID;
UPDATE Transaction LEFT JOIN EavObject ON Transaction.ID=EavObject.transactionID SET Transaction.eavObjectID=EavObject.ID;

UPDATE CustomerOrder SET checkoutStep = 0 WHERE userID IS NULL;
UPDATE CustomerOrder SET checkoutStep = 1 WHERE userID IS NOT NULL;
UPDATE CustomerOrder SET checkoutStep = 2 WHERE billingAddressID IS NOT NULL;
UPDATE CustomerOrder SET checkoutStep = 3 WHERE shipping LIKE '%"selectedRateId";%' AND shipping NOT LIKE '%"selectedRateId";N;%';

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
