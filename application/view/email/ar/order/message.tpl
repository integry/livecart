رسالة جديدة فيما يتعلق بالطلب الخاص بك في [[ config('STORE_NAME') ]]رسالة
العزيز{$user.fullName},

وثمة رسالة جديدة تضاف بشأن طلبك. الجديدة

--------------------------------------------------
{$message.text}
--------------------------------------------------

يمكنك الرد على هذه الرسالة من الصفحة التالية :
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}