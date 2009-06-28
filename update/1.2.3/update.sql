INSERT INTO CategoryPresentation (productID, theme) SELECT ID, theme FROM `ProductPresentation`;

UPDATE DiscountAction SET actionClass = 'RuleActionPercentageDiscount' WHERE type=0;
UPDATE DiscountAction SET actionClass = 'RuleActionAmountDiscount' WHERE type=1;
UPDATE DiscountAction SET actionClass = 'RuleActionDisableCheckout' WHERE type=2;
UPDATE DiscountAction SET actionClass = 'RuleActionPercentageSurcharge' WHERE type=3;
UPDATE DiscountAction SET actionClass = 'RuleActionAmountSurcharge' WHERE type=4;
UPDATE DiscountAction SET actionClass = 'RuleActionSumVariations' WHERE type=5;