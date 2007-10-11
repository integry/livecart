# Category root node #
INSERT INTO `Category` ( `ID` , `parentNodeID` , `name` , `description` , `keywords` , `activeProductCount` , `totalProductCount` , `availableProductCount` , `isEnabled` , `lft` , `rgt` )
VALUES ('1', NULL , 'a:1:{s:2:"en";s:8:"LiveCart";}', NULL , NULL , '0', '0', '0', '1', '1', '2');

ALTER TABLE `Product` CHANGE `dateCreated` `dateCreated` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Product creation date';
 
ALTER TABLE `CustomerOrder` CHANGE `dateCreated` `dateCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Initial order creation date';

ALTER TABLE `OrderedItem` CHANGE `dateAdded` `dateAdded` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date when the product was added to shopping cart';

ALTER TABLE `User` CHANGE `dateCreated` `dateCreated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date the users account was created';

ALTER TABLE `Currency` CHANGE `lastUpdated` `lastUpdated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The date the rate was last updated';

ALTER TABLE `Transaction` CHANGE `time` `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time of the transaction';

ALTER TABLE `Shipment` CHANGE `dateShipped` `dateShipped` TIMESTAMP NULL COMMENT 'Date the product was shipped to customer';

ALTER TABLE `OrderNote` CHANGE `time` `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `OrderLog` CHANGE `time` `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `NewsPost` CHANGE `time` `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;