<?php

declare(strict_types=1);

$root=dirname(__DIR__);
$files=['composer.json','.env.example','.gitignore','LICENSE','config/store.php','docs/CUSTOMISATION.md','docs/DEPLOYMENT.md','docs/EMAIL.md','docs/ROADMAP.md','docs/STRIPE.md','.github/workflows/ci.yml','public/.htaccess','public/index.php','public/router.php','public/favicon.svg','public/css/app.css','src/bootstrap.php','src/contact.php','src/helpers.php','src/store.php','src/stripe.php','templates/cancel.php','templates/cart.php','templates/checkout.php','templates/contact.php','templates/home.php','templates/layout.php','templates/privacy.php','templates/product.php','templates/products.php','templates/success.php','templates/terms.php','templates/404.php','tests/smoke.php'];
foreach($files as $file){$path=$root.'/'.$file;if(!is_file($path))throw new RuntimeException('Missing required file: '.$file);}
if(is_file($root.'/src/webhook.php'))throw new RuntimeException('Stripe webhook file should not exist.');
$envExample=file_get_contents($root.'/.env.example');if(str_contains($envExample,'STRIPE_WEBHOOK_SECRET'))throw new RuntimeException('Webhook secret should not be configured.');
$rii=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));$phpFiles=[];foreach($rii as $file){$name=$file->getPathname();if($file->getExtension()==='php'&&!str_contains($name.DIRECTORY_SEPARATOR,'vendor'.DIRECTORY_SEPARATOR))$phpFiles[]=$name;}
foreach($phpFiles as $file){$output=[];$code=0;exec('php -l '.escapeshellarg($file).' 2>&1',$output,$code);if($code!==0)throw new RuntimeException("PHP lint failed for {$file}: ".implode(' ',$output));}
require $root.'/vendor/autoload.php';require $root.'/src/helpers.php';$store=require $root.'/config/store.php';require $root.'/src/store.php';
if(!class_exists('PHPMailer\\PHPMailer\\PHPMailer'))throw new RuntimeException('PHPMailer is unavailable.');
if(!class_exists('Stripe\\StripeClient'))throw new RuntimeException('Stripe SDK is unavailable.');
foreach($store['products'] as $product){$image=(string)($product['image']??'');if($image===''||!is_file($root.'/public'.url($image)))throw new RuntimeException('Product image asset missing: '.$product['slug']);}
$tmp=tempnam(sys_get_temp_dir(),'ecommerce-test-');if($tmp===false)throw new RuntimeException('Could not create test database.');unlink($tmp);$db=store_db($store,$tmp);$count=(int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn();if($count!==count($store['products']))throw new RuntimeException('Product seeding test failed.');if(money(2500,$store)!=='£25.00')throw new RuntimeException('Money formatting test failed.');$columns=array_column($db->query('PRAGMA table_info(orders)')->fetchAll(),'name');if(!in_array('confirmation_sent_at',$columns,true))throw new RuntimeException('Order confirmation column test failed.');unlink($tmp);
echo 'Ecommerce smoke tests passed ('.count($phpFiles).' PHP files checked).'.PHP_EOL;
