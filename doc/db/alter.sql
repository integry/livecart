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
