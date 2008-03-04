Votre mot de passe sur {'STORE_NAME'|config}!
Cher {$user.fullName},

Voici les informations d'accès a votre compte sur {$config.STORE_NAME}:

E-mail: {$user.email}
Mot de passe: {$user.newPassword}

Vous pouvez utiliser cette adresse pour vous connecter a votre compte:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}