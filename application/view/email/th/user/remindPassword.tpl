รหัสผ่านของคุณจากร้าน [[ config('STORE_NAME') ]]!
เรียนคุณ [[user.fullName]],

ด้านล่างนี้คือรายละเอียดสำหรับใช้ในการล็อกอินเข้าสู่ระบบที่ร้าน [[config.STORE_NAME]]:

อีเมล์: <b>[[user.email]]</b>
รหัสผ่าน: <b>[[user.newPassword]]</b>

คุณสามารถเข้าสู่ระบบของทางร้านได้โดยการคลิกลิ้งค์ด้านล่าง:
[[ fullurl("user/login") ]]

[[ partial("email/en/signature.tpl") ]]