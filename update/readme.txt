LIVECART UPDATE INSTRUCTIONS

I.   Please back up your data

It is strongly advised to MAKE FULL BACKUPS of both your files and database before applying the update. Although we're doing our best to make sure that each update can be applied flawlessly, it is not possible for us to guarantee that it will work so on each possible setup.

II.  Applying the update

When you have downloaded the update package, please upload its contents to your LiveCart installation directory.

There are two ways for installing the update - automated (requires SSH access) and manual (if SSH is not available).

a. Automated installation:

1) When the files have been uploaded, connect to your site using SSH and go into the /updates/version directory

2) Execute the following command:

chmod 0755 update.sh && ./update.sh

If the update is successful, you will see a confirmation message. If there was a problem, an error message will be displayed.

b. Manual installation

1) !IMPORTANT! First, please make sure that you have the right update package. Please compare the from.version file from the update package with the .version file in your LiveCart installation directory. Version numbers in these files must match.

2) Copy the /files directory from the update package over the existing files. Allow to overwrite files when prompted.

3) From your web browser open the following URL: http://yourstore.com/backend.update/update

If you encounter any problems when updating your LiveCart installation please contact the support team (http://support.turn-k.net).

For an updated version of these instructions, please visit http://doc.livecart.com/install/update