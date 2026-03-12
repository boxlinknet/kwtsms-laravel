<?php

namespace KwtSMS\Laravel\Database\Seeders;

use Illuminate\Database\Seeder;
use KwtSMS\Laravel\Models\KwtSmsTemplate;

class KwtSmsDefaultTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // OTP
            [
                'name' => 'otp_en',
                'event_type' => 'otp',
                'locale' => 'en',
                'body' => 'Your OTP for {app_name} is: {otp_code}. Valid for {expiry_minutes} minutes. Do not share this code.',
            ],
            [
                'name' => 'otp_ar',
                'event_type' => 'otp',
                'locale' => 'ar',
                'body' => 'رمز التحقق الخاص بك في {app_name} هو: {otp_code}. صالح لمدة {expiry_minutes} دقائق. لا تشاركه مع احد.',
            ],
            // Password Reset
            [
                'name' => 'password_reset_en',
                'event_type' => 'password_reset',
                'locale' => 'en',
                'body' => 'Your {app_name} password reset code is: {otp_code}. Valid for {expiry_minutes} minutes.',
            ],
            [
                'name' => 'password_reset_ar',
                'event_type' => 'password_reset',
                'locale' => 'ar',
                'body' => 'رمز اعادة تعيين كلمة مرورك في {app_name} هو: {otp_code}. صالح لمدة {expiry_minutes} دقائق.',
            ],
            // COD OTP (Cash on Delivery confirmation)
            [
                'name' => 'cod_otp_en',
                'event_type' => 'cod_otp',
                'locale' => 'en',
                'body' => 'Your {app_name} delivery confirmation code is: {otp_code}. Please provide this to the delivery agent.',
            ],
            [
                'name' => 'cod_otp_ar',
                'event_type' => 'cod_otp',
                'locale' => 'ar',
                'body' => 'رمز تاكيد التسليم الخاص بك في {app_name} هو: {otp_code}. يرجى تقديمه لمندوب التوصيل.',
            ],
            // Order Placed
            [
                'name' => 'order_placed_en',
                'event_type' => 'order_placed',
                'locale' => 'en',
                'body' => 'Hello {customer_name}, your order #{order_id} has been placed successfully. Total: {order_total}. Thank you for shopping at {app_name}.',
            ],
            [
                'name' => 'order_placed_ar',
                'event_type' => 'order_placed',
                'locale' => 'ar',
                'body' => 'مرحبا {customer_name}، تم استلام طلبك رقم #{order_id} بنجاح. المجموع: {order_total}. شكرا لتسوقك في {app_name}.',
            ],
            // Order Status Changed
            [
                'name' => 'order_status_en',
                'event_type' => 'order_status_changed',
                'locale' => 'en',
                'body' => 'Your order #{order_id} status has been updated to: {order_status}. Visit {app_name} for details.',
            ],
            [
                'name' => 'order_status_ar',
                'event_type' => 'order_status_changed',
                'locale' => 'ar',
                'body' => 'تم تحديث حالة طلبك رقم #{order_id} الى: {order_status}. تفضل بزيارة {app_name} للمزيد من التفاصيل.',
            ],
            // Order Shipped
            [
                'name' => 'order_shipped_en',
                'event_type' => 'order_shipped',
                'locale' => 'en',
                'body' => 'Your order #{order_id} from {app_name} has been shipped and is on its way. Expected delivery: {date}.',
            ],
            [
                'name' => 'order_shipped_ar',
                'event_type' => 'order_shipped',
                'locale' => 'ar',
                'body' => 'تم شحن طلبك رقم #{order_id} من {app_name} وهو في الطريق اليك. موعد التسليم المتوقع: {date}.',
            ],
            // Order Cancelled
            [
                'name' => 'order_cancelled_en',
                'event_type' => 'order_cancelled',
                'locale' => 'en',
                'body' => 'Your order #{order_id} from {app_name} has been cancelled. Contact us if you have any questions.',
            ],
            [
                'name' => 'order_cancelled_ar',
                'event_type' => 'order_cancelled',
                'locale' => 'ar',
                'body' => 'تم الغاء طلبك رقم #{order_id} من {app_name}. تواصل معنا اذا كان لديك اي استفسار.',
            ],
            // Low Balance Alert (admin)
            [
                'name' => 'low_balance_alert_en',
                'event_type' => 'low_balance_alert',
                'locale' => 'en',
                'body' => '{app_name} kwtSMS Alert: Your balance is low ({current_balance} credits remaining). Please recharge to avoid service interruption.',
            ],
            [
                'name' => 'low_balance_alert_ar',
                'event_type' => 'low_balance_alert',
                'locale' => 'ar',
                'body' => 'تنبيه {app_name}: رصيد الرسائل منخفض ({current_balance} رسالة متبقية). يرجى اعادة الشحن لتجنب انقطاع الخدمة.',
            ],
        ];

        foreach ($templates as $template) {
            KwtSmsTemplate::updateOrCreate(
                ['name' => $template['name']],
                array_merge($template, ['is_active' => true])
            );
        }

        $count = count($templates);
        echo "Seeded {$count} default kwtSMS templates.\n";
    }
}
