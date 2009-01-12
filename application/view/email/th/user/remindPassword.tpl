รหัสผ่านของคุณจากร้าน {'STORE_NAME'|config}!
เรียนคุณ {$user.fullName},

ด้านล่างนี้คือรายละเอียดสำหรับใช้ในการล็อกอินเข้าสู่ระบบที่ร้าน {$config.STORE_NAME}:

อีเมล์: {$user.email}
รหัสผ่าน: {$user.newPassword}

คุณสามารถเข้าสู่ระบบของทางร้านได้โดยการคลิกลิ้งค์ด้านล่าง:
{link controller=user action=login url=true}

{include file="email/en/signature.tpl"}