ALTER TABLE SpecFieldGroup ADD CONSTRAINT Category_SpecFieldGroup 
    FOREIGN KEY (categoryID) REFERENCES Category (ID);
