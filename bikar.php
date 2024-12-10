<?php
chdir(__DIR__); // تنظیم مسیر جاری به مسیر اسکریپت

function bot($method_name, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tapi.bale.ai/bot.../" . $method_name);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true); // بازگرداندن پاسخ
}

// خواندن تمام فایل‌ها در پوشه "chats"
$files = glob("chats/*.json");
if (empty($files)) {
    bot("sendMessage", ["chat_id" => -1002351043224, "text" => "هیچ فایلی برای پردازش موجود نیست."]);
    exit;
}

foreach ($files as $file) {
    // خواندن فایل JSON
    $json = file_get_contents($file);
    if ($json === false) {
        bot("sendMessage", ["chat_id" => $json["chat_id"], "text" => "خطا در خواندن فایل " . basename($file)]);
        continue;
    }

    // تبدیل JSON به آرایه PHP
    $data = json_decode($json, true);
    if ($data === null) {
        bot("sendMessage", ["chat_id" => $data["chat_id"], "text" => "خطا در تجزیه JSON در فایل " . basename($file)]);
        continue;
    }

    // بررسی وجود بخش users
    if (isset($data['users']) && is_array($data['users'])) {
        // مرتب‌سازی نزولی کاربران بر اساس message_count
        uasort($data['users'], function ($a, $b) {
            return $b['message_count'] <=> $a['message_count'];
        });

        // استخراج 3 کاربر برتر
        $top_users = array_slice($data['users'], 0, 3); // اولین 3 کاربر
        $arr = [];
        foreach ($top_users as $user) {
            $arr[] = [$user["name"], $user["message_count"]];
        }

        // ساخت پیام خروجی
        $message = "لیست 3 بی‌کار برتر امروز:\n\n";
        $medals = ["🥇", "🥈", "🥉"];
        foreach ($arr as $index => $user) {
            $message .= "{$medals[$index]} {$user[0]} با تعداد {$user[1]} پیام\n";
        }

        // ارسال پیام به چت تلگرام
        bot("sendMessage", ["chat_id" => $data["chat_id"], "text" => $message]);

        // ریست کردن فایل JSON
        $reset_data = [
            "users" => [], // کاربران خالی می‌شوند
            "chat_id" => $data["chat_id"],
            "chat_name" => $data["chat_name"]
        ];
        file_put_contents($file, json_encode($reset_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
?>
