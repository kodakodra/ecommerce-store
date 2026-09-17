<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

function validate_contact(array $input): array
{
    $data = ['name'=>trim((string)($input['name']??'')), 'email'=>trim((string)($input['email']??'')), 'message'=>trim((string)($input['message']??'')), 'website'=>trim((string)($input['website']??''))];
    $errors = [];
    if ($data['website'] !== '') return [$data, ['form'=>'Your message could not be submitted.']];
    if ($data['name']==='' || strlen($data['name'])<2 || strlen($data['name'])>100 || preg_match('/[\r\n]/',$data['name'])) $errors['name']='Please enter your name.';
    if (!filter_var($data['email'],FILTER_VALIDATE_EMAIL) || strlen($data['email'])>254) $errors['email']='Please enter a valid email address.';
    if ($data['message']==='' || strlen($data['message'])<20 || strlen($data['message'])>3000) $errors['message']='Please tell us a little more about your enquiry (20-3000 characters).';
    return [$data,$errors];
}

function mail_settings(): array
{
    return ['to'=>(string)env('CONTACT_EMAIL',''), 'from'=>(string)env('MAIL_FROM_ADDRESS',''), 'fromName'=>(string)env('MAIL_FROM_NAME','Store'), 'mailer'=>strtolower(trim((string)env('MAIL_MAILER','smtp'))), 'host'=>trim((string)env('MAIL_HOST','')), 'port'=>(int)env('MAIL_PORT','587'), 'username'=>(string)env('MAIL_USERNAME',''), 'password'=>(string)env('MAIL_PASSWORD',''), 'encryption'=>strtolower(trim((string)env('MAIL_ENCRYPTION','tls'))), 'auth'=>env('MAIL_AUTH','1')!=='0', 'timeout'=>max(5,(int)env('MAIL_TIMEOUT','15'))];
}

function send_smtp_mail(string $to, string $replyTo, string $replyName, string $subject, string $body, ?string &$error=null): bool
{
    $settings = mail_settings();
    if ($settings['mailer']!=='smtp' || !filter_var($to,FILTER_VALIDATE_EMAIL) || !filter_var($settings['from'],FILTER_VALIDATE_EMAIL) || $settings['host']==='') { $error='SMTP settings are incomplete.'; error_log('Email unavailable: '.$error); return false; }
    try {
        require_once dirname(__DIR__).'/vendor/autoload.php';
        $mail=new PHPMailer(true); $mail->isSMTP(); $mail->Host=$settings['host']; $mail->Port=$settings['port']; $mail->SMTPAuth=$settings['auth']; $mail->Timeout=$settings['timeout']; $mail->CharSet='UTF-8';
        if($settings['auth']){$mail->Username=$settings['username'];$mail->Password=$settings['password'];}
        switch($settings['encryption']){case 'tls':$mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;break;case 'ssl':$mail->SMTPSecure=PHPMailer::ENCRYPTION_SMTPS;break;case 'none':case '':$mail->SMTPSecure='';break;default:$error='Unsupported MAIL_ENCRYPTION value.';return false;}
        $mail->setFrom($settings['from'],$settings['fromName']); $mail->addAddress($to); if(filter_var($replyTo,FILTER_VALIDATE_EMAIL)) $mail->addReplyTo($replyTo,$replyName); $mail->Subject=$subject; $mail->isHTML(false); $mail->Body=$body; $mail->AltBody=$body; $mail->send(); return true;
    } catch(Throwable $exception){$error=$exception->getMessage(); error_log('Email failed: '.$exception->getMessage()); return false;}
}

function send_contact_message(array $data, array $store): bool
{
    $error=null; $body=implode(PHP_EOL,['New store enquiry','=================', '', 'Name: '.$data['name'],'Email: '.$data['email'],'','Message:',$data['message'],'','Sent from '.site_origin($store)]);
    return send_smtp_mail((string)env('CONTACT_EMAIL',$store['contact_email']),$data['email'],$data['name'],'Store enquiry from '.preg_replace('/[\r\n]+/',' ',$data['name']),$body,$error);
}

function send_order_confirmation(array $order, array $store): bool
{
    $error = null;
    $lines = [
        'Order confirmation',
        '==================',
        '',
        'Order: '.$order['id'],
        'Total: '.money((int)$order['total_pence'], $store),
        '',
    ];
    foreach ($order['items'] as $item) {
        $lines[] = $item['product_name'].' × '.$item['quantity'].' — '.money((int)$item['line_total_pence'], $store);
    }
    $lines[] = '';
    $lines[] = 'Thank you for your order.';
    return send_smtp_mail(
        $order['customer_email'],
        (string)env('CONTACT_EMAIL', $store['contact_email']),
        $store['name'],
        'Order confirmation '.$order['id'],
        implode(PHP_EOL, $lines),
        $error
    );
}
