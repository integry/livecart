UPDATE Category LEFT JOIN EavObject ON Category.ID=EavObject.categoryID SET Category.eavObjectID=EavObject.ID;
UPDATE CustomerOrder LEFT JOIN EavObject ON CustomerOrder.ID=EavObject.customerOrderID SET CustomerOrder.eavObjectID=EavObject.ID;
UPDATE Manufacturer LEFT JOIN EavObject ON Manufacturer.ID=EavObject.manufacturerID SET Manufacturer.eavObjectID=EavObject.ID;
UPDATE User LEFT JOIN EavObject ON User.ID=EavObject.userID SET User.eavObjectID=EavObject.ID;
UPDATE UserGroup LEFT JOIN EavObject ON UserGroup.ID=EavObject.userGroupID SET UserGroup.eavObjectID=EavObject.ID;
UPDATE UserAddress LEFT JOIN EavObject ON UserAddress.ID=EavObject.userAddressID SET UserAddress.eavObjectID=EavObject.ID;
UPDATE Transaction LEFT JOIN EavObject ON Transaction.ID=EavObject.transactionID SET Transaction.eavObjectID=EavObject.ID;
