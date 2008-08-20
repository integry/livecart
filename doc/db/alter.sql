ALTER TABLE DiscountAction ADD CONSTRAINT DiscountCondition_DiscountAction
    FOREIGN KEY (conditionID) REFERENCES DiscountCondition (ID) ON DELETE CASCADE ON UPDATE CASCADE;
