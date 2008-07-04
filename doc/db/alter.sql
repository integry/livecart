ALTER TABLE ProductRatingSummary ADD CONSTRAINT Product_ProductRatingSummary
    FOREIGN KEY (productID) REFERENCES Product (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE ProductRatingSummary ADD CONSTRAINT ProductRatingType_ProductRatingSummary
    FOREIGN KEY (ratingTypeID) REFERENCES ProductRatingType (ID) ON DELETE CASCADE ON UPDATE CASCADE;
