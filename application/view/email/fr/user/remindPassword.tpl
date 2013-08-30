Votre mot de passe sur [[ config('STORE_NAME') ]]!
Cher [[user.fullName]],

Voici les informations d'accès a votre compte sur [[config.STORE_NAME]]:

E-mail: <b>[[user.email]]</b>
Mot de passe: <b>[[user.newPassword]]</b>

Vous pouvez utiliser cette adresse pour vous connecter a votre compte:
[[ fullurl("user/login") ]]

[[ partial("email/fr/signature.tpl") ]]