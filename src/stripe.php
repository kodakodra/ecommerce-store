<?php

declare(strict_types=1);

use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Stripe\Webhook;

function stripe_client(): StripeClient
{
    $key = trim((string) env('STRIPE_SECRET_KEY',''));
    if ($key === '' || !str_starts_with($key,'sk_')) throw new RuntimeException('Stripe secret key is not configured.');
    return new StripeClient($key);
}

function create_checkout_session(array $store, array $order): Session
{
    $lineItems=[];
    foreach($order['items'] as $item){
        $lineItems[]=['price_data'=>['currency'=>$order['currency'],'product_data'=>['name'=>$item['product']['name']], 'unit_amount'=>(int)$item['product']['price_pence']], 'quantity'=>(int)$item['quantity']];
    }
    $params=['mode'=>'payment','client_reference_id'=>$order['id'],'line_items'=>$lineItems,'success_url'=>absolute_url('/checkout/success?session_id={CHECKOUT_SESSION_ID}',$store),'cancel_url'=>absolute_url('/checkout/cancel',$store),'customer_email'=>$order['customer_email'],'metadata'=>['order_id'=>$order['id']]];
    if (!empty($store['checkout']['collect_billing_address'])) $params['billing_address_collection']='required';
    if (!empty($store['checkout']['collect_shipping_address'])) $params['shipping_address_collection']=['allowed_countries'=>$store['checkout']['allowed_countries']];
    return stripe_client()->checkout->sessions->create($params);
}

function construct_stripe_event(string $payload, string $signature): \Stripe\Event
{
    $secret=trim((string)env('STRIPE_WEBHOOK_SECRET',''));
    if($secret==='' || !str_starts_with($secret,'whsec_')) throw new RuntimeException('Stripe webhook secret is not configured.');
    return Webhook::constructEvent($payload,$signature,$secret);
}
