#
# Category root node
#
INSERT INTO `Category` ( `ID` , `parentNodeID` , `name` , `description` , `keywords` , `activeProductCount` , `totalProductCount` , `isEnabled` , `handle` , `position` , `lft` , `rgt` )
VALUES ('1', NULL , 'LiveCart', NULL , NULL , '0', '0', '1', NULL , '0', '1', '2');

#
# Default system language
#
INSERT INTO `Language` ( `ID` , `isEnabled` , `isDefault` , `position` )
VALUES ('en', '1', '1', '0');