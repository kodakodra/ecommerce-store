<?php

declare(strict_types=1);

require dirname(__DIR__).'/src/bootstrap.php';
$currentPath=current_path();
$flash=flash_get();

if($currentPath==='/robots.txt'){header('Content-Type:text/plain; charset=UTF-8');echo "User-agent: *\nAllow: /\nDisallow: /.env\nDisallow: /database/\n\nSitemap: ".absolute_url('/sitemap.xml',$store)."\n";exit;}
if($currentPath==='/sitemap.xml'){header('Content-Type:application/xml; charset=UTF-8');$pages=['/','/products','/cart','/contact','/privacy','/terms'];echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';foreach($pages as $page)echo '<url><loc>'.e(absolute_url($page,$store)).'</loc></url>';echo '</urlset>';exit;}

$formData=[];$formErrors=[];$successOrder=null;$successError=null;
if($currentPath==='/cart' && is_post()){
    if(!csrf_valid($_POST['csrf_token']??null)){flash_set('error','Your session has expired. Please reload the page.');redirect('/cart');}
    $action=(string)($_POST['action']??'');$slug=(string)($_POST['slug']??'');
    if($action==='add')cart_add($store,$slug,(int)($_POST['quantity']??1));
    elseif($action==='update')cart_set($store,$slug,(int)($_POST['quantity']??0));
    elseif($action==='remove')cart_remove($slug);
    flash_set('success',$action==='add'?'Item added to your basket.':'Basket updated.');redirect('/cart');
}
if($currentPath==='/checkout' && is_post()){
    $formData=['name'=>trim((string)($_POST['name']??'')),'email'=>trim((string)($_POST['email']??''))];
    if(!csrf_valid($_POST['csrf_token']??null))$formErrors['form']='Your session has expired. Please reload the page and try again.';
    if($formData['name']===''||strlen($formData['name'])>100)$formErrors['name']='Please enter your name.';
    if(!filter_var($formData['email'],FILTER_VALIDATE_EMAIL)||strlen($formData['email'])>254)$formErrors['email']='Please enter a valid email address.';
    if($formErrors===[]){try{$order=create_order($store,$formData['name'],$formData['email']);$session=create_checkout_session($store,$order);attach_stripe_session($store,$order['id'],$session->id);header('Location: '.$session->url,true,303);exit;}catch(Throwable $e){error_log('Checkout failed: '.$e->getMessage());$formErrors['form']='We could not start checkout right now. Please try again.';}}
}
if($currentPath==='/contact' && is_post()){
    if(!csrf_valid($_POST['csrf_token']??null))$formErrors['form']='Your session has expired. Please reload the page and try again.';
    else {[$formData,$errors]=validate_contact($_POST);$formErrors=array_merge($formErrors,$errors);if($formErrors===[]){if(!can_submit_contact()){$formErrors['form']='Please wait a few seconds before sending another message.';}elseif(send_contact_message($formData,$store)){$_SESSION['last_contact_submit']=time();flash_set('success','Thanks. Your message has been sent.');redirect('/contact#form');}else $formErrors['form']='We could not send your message right now. Please email us directly instead.';}}
}

switch(true){
    case $currentPath==='/':$pageTitle=$store['name'].' | '.$store['tagline'];$pageDescription=$store['description'];$template='/templates/home.php';break;
    case $currentPath==='/products':$pageTitle='Shop | '.$store['name'];$pageDescription='Browse products from '.$store['name'].'.';$template='/templates/products.php';break;
    case str_starts_with($currentPath,'/products/'):$slug=trim(substr($currentPath,10),'/');$product=find_product($store,$slug);if(!$product){http_response_code(404);$pageTitle='Product not found | '.$store['name'];$pageDescription='The requested product could not be found.';$template='/templates/404.php';break;}$pageTitle=$product['name'].' | '.$store['name'];$pageDescription=$product['summary'];$template='/templates/product.php';break;
    case $currentPath==='/cart':$pageTitle='Basket | '.$store['name'];$pageDescription='Review your basket.';$template='/templates/cart.php';break;
    case $currentPath==='/checkout':$pageTitle='Checkout | '.$store['name'];$pageDescription='Enter your details before continuing to secure payment.';$template='/templates/checkout.php';break;
    case $currentPath==='/checkout/success':
        $pageTitle='Order received | '.$store['name'];
        $pageDescription='Your order has been received.';
        $sessionId=(string)($_GET['session_id']??'');
        if($sessionId===''){$successError='We could not verify the payment session. Please contact us if you were charged.';http_response_code(400);break;}
        try{
            $session=retrieve_checkout_session($sessionId);
            $successOrder=mark_order_paid($store,$session);
            if($successOrder===null){$successError='We could not verify the payment for this order. Please contact us if you were charged.';http_response_code(400);break;}
            if(empty($successOrder['confirmation_sent_at']) && send_order_confirmation($successOrder,$store)){
                mark_order_confirmation_sent($store,$successOrder['id']);
                $successOrder=get_order($store,$successOrder['id']);
            }
            cart_clear();
        }catch(Throwable $e){error_log('Checkout confirmation failed: '.$e->getMessage());$successError='We could not verify the payment right now. Please contact us if you were charged.';http_response_code(500);}
        $template='/templates/success.php';
        break;
    case $currentPath==='/checkout/cancel':$pageTitle='Checkout cancelled | '.$store['name'];$pageDescription='Your checkout was cancelled.';$template='/templates/cancel.php';break;
    case $currentPath==='/contact':$pageTitle='Contact | '.$store['name'];$pageDescription='Contact '.$store['name'].'.';$template='/templates/contact.php';break;
    case $currentPath==='/privacy':$pageTitle='Privacy notice | '.$store['name'];$pageDescription='Privacy information.';$template='/templates/privacy.php';break;
    case $currentPath==='/terms':$pageTitle='Terms | '.$store['name'];$pageDescription='Terms of sale and service.';$template='/templates/terms.php';break;
    default:http_response_code(404);$pageTitle='Page not found | '.$store['name'];$pageDescription='The requested page could not be found.';$template='/templates/404.php';break;
}

ob_start();require dirname(__DIR__).$template;$content=ob_get_clean();require dirname(__DIR__).'/templates/layout.php';
