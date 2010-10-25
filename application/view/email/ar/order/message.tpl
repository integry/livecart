رسالة جديدة فيما يتعلق بالطلب الخاص بك في {'STORE_NAME'|config}رسالة 
العزيز{$user.fullName},

وثمة رسالة جديدة تضاف بشأن طلبك. الجديدة

--------------------------------------------------
{$message.text}
--------------------------------------------------

يمكنك الرد على هذه الرسالة من الصفحة التالية :
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}