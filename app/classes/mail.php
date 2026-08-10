<?php

if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

function sendMail($arr)
{
    global $conn, $settings, $mail;
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = "";
        $mail->SMTPAuth = true;
        $mail->Username = "";
        $mail->Password = "";

            $mail->SMTPSecure = "HTTP/1.1";
        
        $mail->Port = $settings["smtp_port"];
        $mail->SetLanguage("tr", "phpmailer/language");
        $mail->CharSet = "utf-8";
        $mail->Encoding = "base64";
        $mail->setFrom($settings["smtp_user"], $settings["site_title"]);
        if (is_array($arr["mail"])) :
            foreach ($arr["mail"] as $goMail) {
                $mail->ClearAddresses();
                $mail->addAddress($goMail);
                $mail->isHTML(true);
                $mail->Subject = $arr["subject"];
                $mail->Body = $arr["body"];
                $mail->send();
            }
        else :
            $mail->addAddress($arr["mail"]);
            $mail->isHTML(true);
            $mail->Subject = $arr["subject"];
            $mail->Body = $arr["body"];
            $mail->send();
        endif;
        return 1;
    } catch (Exception $e) {
        return 0;
    }
}
