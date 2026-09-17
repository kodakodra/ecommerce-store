<?php

declare(strict_types=1);

function handle_stripe_webhook(array $store): never
{
    $payload=file_get_contents('php://input') ?: '';
    $signature=$_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    try { $event=construct_stripe_event($payload,$signature); } catch(Throwable $e){ http_response_code(400); echo 'Invalid webhook.'; exit; }
    try {
        if (in_array($event->type,['checkout.session.completed','checkout.session.async_payment_succeeded'],true)) {
            $order=mark_order_paid($store,$event->id,$event->type,$event->data->object);
            if($order && $order['status']==='paid' && ($order['customer_email']??'')!=='') {
                $error=null;
                $lines=['Order confirmation','==================','','Order: '.$order['id'],'Total: '.money((int)$order['total_pence'],$store),''];
                foreach($order['items'] as $item) $lines[]=$item['product_name'].' × '.$item['quantity'].' — '.money((int)$item['line_total_pence'],$store);
                $lines[]=''; $lines[]='Thank you for your order.';
                send_smtp_mail($order['customer_email'], $store['contact_email'], $store['name'], 'Order confirmation '.$order['id'], implode(PHP_EOL,$lines), $error);
            }
        }
        http_response_code(200); echo 'ok';
    } catch(Throwable $e){ error_log('Stripe webhook failed: '.$e->getMessage()); http_response_code(500); echo 'Webhook processing failed.'; }
    exit;
}
