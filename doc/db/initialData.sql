#
# Category root node
#
INSERT INTO `Category` ( `ID` , `parentNodeID` , `name` , `description` , `keywords` , `activeProductCount` , `totalProductCount` , `isEnabled` , `handle` , `lft` , `rgt` )
VALUES ('1', NULL , 'a:1:{s:2:"en";s:8:"LiveCart";}', NULL , NULL , '0', '0', '1', NULL , '1', '2');

#
# Default system language
#
INSERT INTO `Language` ( `ID` , `isEnabled` , `isDefault` , `position` )
VALUES ('en', '1', '1', '0');