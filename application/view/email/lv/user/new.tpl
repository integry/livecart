Laipni lūdzam [[ config('STORE_NAME') ]]!
Cien. [[user.fullName]],

Jūsu lietotāja konta pieejas informācija [[ config('STORE_NAME') ]] ir sekojoša:

E-pasts: <b>[[user.email]]</b>
Parole: <b>[[user.newPassword]]</b>

No Jūsu lietotāja konta, varat sekot līdzi pasūtījuma statusam, apskatīt iepriekšējos pasūtījumus, lejupielādēt failus un nomainīt savu kontaktinformāciju.

Pieslēgties savam lietotāja kontam varat no šejienes:
[[ fullurl("user/login") ]]

[[ partial("email/lv/signature.tpl") ]]