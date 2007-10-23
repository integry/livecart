INSERT INTO Role (name) VALUES ('userGroup.permissions'), ('template.save');
INSERT INTO AccessControlAssociation SET roleID=(SELECT ID FROM Role WHERE name='userGroup.permissions'), userGroupID=1;
INSERT INTO AccessControlAssociation SET roleID=(SELECT ID FROM Role WHERE name='template.save'), userGroupID=1;