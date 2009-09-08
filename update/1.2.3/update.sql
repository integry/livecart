INSERT INTO CategoryPresentation (productID, theme) SELECT ID, theme FROM `ProductPresentation`;

UPDATE DiscountAction SET actionClass = 'RuleActionPercentageDiscount' WHERE type=0;
UPDATE DiscountAction SET actionClass = 'RuleActionAmountDiscount' WHERE type=1;
UPDATE DiscountAction SET actionClass = 'RuleActionDisableCheckout' WHERE type=2;
UPDATE DiscountAction SET actionClass = 'RuleActionPercentageSurcharge' WHERE type=3;
UPDATE DiscountAction SET actionClass = 'RuleActionAmountSurcharge' WHERE type=4;
UPDATE DiscountAction SET actionClass = 'RuleActionSumVariations' WHERE type=5;

UPDATE CustomerOrder SET invoiceNumber = ID WHERE isFinalized=1;

ALTER TABLE CustomerOrder ADD CONSTRAINT TUC_CustomerOrder_1 UNIQUE (invoiceNumber);

UPDATE Product
	LEFT JOIN (
		SELECT productID, GROUP_CONCAT(CONCAT(lft,'-',rgt) SEPARATOR ',') AS intervals
			FROM ProductCategory
			LEFT JOIN Category ON categoryID=ID GROUP BY productID) AS intv
		ON productID=ID
	LEFT JOIN Category ON Product.categoryID=Category.ID
	SET categoryIntervalCache=CONCAT(Category.lft,'-',Category.rgt,',',COALESCE(intervals,''));