[[ config('STORE_NAME') ]] ยืนยันอีเมล์เพื่อบอกรับจดหมายข่าว
เรียนท่านเจ้าของอีเมล์,

เพื่อเป็นการยืนยันที่อยู่อีเมล์ของคุณและเริ่มรับจดหมายข่าวจากทางร้าน กรุณาคลิกลิ้งค์ด้านล่าง: 
{link controller=newsletter action=confirm query="email=`$email`&code=`$subscriber.confirmationCode`" url=true}

[[ partial("email/en/signature.tpl") ]]

-----------------------------------------------
หากคุณไม่ต้องการรับจดหมายข่าวจากทางร้านต่อไปกรุณาคลิกลิ้งค์ด้านล่างเพื่อบอกยกเลิก:
{link controller=newsletter action=unsubscribe query="email=`$email`" url=true}