<?php
$text = null;

$update = json_decode(file_get_contents("php://input") , true);
if (isset($update['callback_query'])) $chatID = $update['callback_query']['message']['chat']["id"];
if (!isset($update['callback_query'])) $chatID = $update['message']['chat']["id"];;
if(isset($update["message"]["text"])) $text = $update["message"]["text"];
if(isset($update["message"]["photo"])) $photo = $update["message"]["photo"];
function bot($method_name,$data){
    global $config_data;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tapi.bale.ai/bot.../".$method_name);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($ch);

}
function create_bot_panel($chatID){
    global $update;
    if(!file_exists("chats/".$chatID.".json")){
        $data = [
            "users" => [],
            "chat_id" => $chatID,
            "chat_name" => $update["message"]["chat"]["title"]
        ];
        file_put_contents("chats/$chatID.json" , json_encode($data));
    }
    return true;
}
if ($text == "/config") {
    if($update["message"]["chat"]["type"] == 'supergroup'){
        bot("sendMessage" , ["chat_id" => $chatID , "text" => "bot Was Conf in your group"]);
    }
    create_bot_panel($chatID);
}else{

    if(file_exists("chats/$chatID.json")){  
        $users = json_decode(file_get_contents("chats/$chatID.json") , true);
        if(isset($users["users"][$update["message"]["from"]["id"]])){
            $users["users"][$update["message"]["from"]["id"]]["message_count"]++;
         }else{
            $users["users"][$update["message"]["from"]["id"]] = [
                'message_count' => 1,
                "name" => $update["message"]["from"]["first_name"]
            ];
            //bot("sendMessage" , ["chat_id" => $chatID , "text" => "کاربر {$update["message"]["from"]["first_name"]} اولین پیام خود را در امروز ارسال کرد"]);
        }
        file_put_contents("chats/$chatID.json" , json_encode($users));
    }
}