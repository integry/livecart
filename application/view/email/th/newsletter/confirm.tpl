[[ config('STORE_NAME') ]] ยืนยันอีเมล์เพื่อบอกรับจดหมายข่าว
เรียนท่านเจ้าของอีเมล์,

เพื่อเป็นการยืนยันที่อยู่อีเมล์ของคุณและเริ่มรับจดหมายข่าวจากทางร้าน กรุณาคลิกลิ้งค์ด้านล่าง: 
[[ fullurl("newsletter/confirm", email=`email`&code=`subscriber.confirmationCode`) ]]

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
หากคุณไม่ต้องการรับจดหมายข่าวจากทางร้านต่อไปกรุณาคลิกลิ้งค์ด้านล่างเพื่อบอกยกเลิก:
[[ fullurl("newsletter/unsubscribe", email=`email`) ]]