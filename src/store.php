<?php

declare(strict_types=1);

function store_db(array $store, ?string $path = null): PDO
{
    static $connections = [];
    $path ??= (string) env('STORE_DB_PATH', dirname(__DIR__) . '/database/store.sqlite');
    $key = $path;
    if (isset($connections[$key])) return $connections[$key];
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $db = new PDO('sqlite:' . $path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA foreign_keys = ON');
    $db->exec('CREATE TABLE IF NOT EXISTS products (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT NOT NULL UNIQUE, name TEXT NOT NULL, summary TEXT NOT NULL, description TEXT NOT NULL, price_pence INTEGER NOT NULL, stock INTEGER NOT NULL DEFAULT 0, active INTEGER NOT NULL DEFAULT 1)');
    $db->exec('CREATE TABLE IF NOT EXISTS orders (id TEXT PRIMARY KEY, stripe_session_id TEXT UNIQUE, status TEXT NOT NULL, customer_name TEXT NOT NULL, customer_email TEXT NOT NULL, total_pence INTEGER NOT NULL, currency TEXT NOT NULL, stripe_payment_intent_id TEXT, shipping_name TEXT, shipping_address_json TEXT, created_at TEXT NOT NULL, paid_at TEXT)');
    $db->exec('CREATE TABLE IF NOT EXISTS order_items (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id TEXT NOT NULL REFERENCES orders(id) ON DELETE CASCADE, product_slug TEXT NOT NULL, product_name TEXT NOT NULL, unit_price_pence INTEGER NOT NULL, quantity INTEGER NOT NULL, line_total_pence INTEGER NOT NULL)');
    $db->exec('CREATE TABLE IF NOT EXISTS webhook_events (event_id TEXT PRIMARY KEY, event_type TEXT NOT NULL, received_at TEXT NOT NULL)');
    $stmt = $db->prepare('INSERT OR IGNORE INTO products (slug,name,summary,description,price_pence,stock,active) VALUES (:slug,:name,:summary,:description,:price,:stock,1)');
    foreach ($store['products'] as $product) $stmt->execute(['slug'=>$product['slug'],'name'=>$product['name'],'summary'=>$product['summary'],'description'=>$product['description'],'price'=>$product['price_pence'],'stock'=>$product['stock']]);
    return $connections[$key] = $db;
}

function products(array $store): array
{
    return store_db($store)->query('SELECT * FROM products WHERE active = 1 ORDER BY id')->fetchAll();
}

function find_product(array $store, string $slug): ?array
{
    $stmt = store_db($store)->prepare('SELECT * FROM products WHERE slug = :slug AND active = 1 LIMIT 1');
    $stmt->execute(['slug'=>$slug]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function cart(): array
{
    start_session();
    $cart = $_SESSION['cart'] ?? [];
    return is_array($cart) ? $cart : [];
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart()));
}

function cart_add(array $store, string $slug, int $quantity = 1): void
{
    $product = find_product($store, $slug);
    if (!$product || $product['stock'] < 1) return;
    start_session();
    $current = max(0, (int) ($_SESSION['cart'][$slug] ?? 0));
    $_SESSION['cart'][$slug] = min($product['stock'], $current + max(1, $quantity));
}

function cart_set(array $store, string $slug, int $quantity): void
{
    $product = find_product($store, $slug);
    start_session();
    if (!$product || $quantity <= 0) { unset($_SESSION['cart'][$slug]); return; }
    $_SESSION['cart'][$slug] = min($product['stock'], $quantity);
}

function cart_remove(string $slug): void
{
    start_session();
    unset($_SESSION['cart'][$slug]);
}

function cart_clear(): void
{
    start_session();
    $_SESSION['cart'] = [];
}

function cart_items(array $store): array
{
    $items = [];
    foreach (cart() as $slug => $quantity) {
        $product = find_product($store, (string) $slug);
        if (!$product) continue;
        $quantity = min((int)$quantity, (int)$product['stock']);
        if ($quantity < 1) continue;
        $items[] = ['product'=>$product, 'quantity'=>$quantity, 'line_total'=>$quantity * (int)$product['price_pence']];
    }
    return $items;
}

function cart_total(array $store): int
{
    return array_sum(array_column(cart_items($store), 'line_total'));
}

function create_order(array $store, string $name, string $email): array
{
    $items = cart_items($store);
    if ($items === []) throw new RuntimeException('Cart is empty.');
    $db = store_db($store);
    $id = bin2hex(random_bytes(12));
    $total = array_sum(array_column($items, 'line_total'));
    $db->beginTransaction();
    try {
        $stmt = $db->prepare('INSERT INTO orders (id,status,customer_name,customer_email,total_pence,currency,created_at) VALUES (:id,"pending",:name,:email,:total,:currency,:created)');
        $stmt->execute(['id'=>$id,'name'=>$name,'email'=>$email,'total'=>$total,'currency'=>$store['currency'],'created'=>date('c')]);
        $itemStmt = $db->prepare('INSERT INTO order_items (order_id,product_slug,product_name,unit_price_pence,quantity,line_total_pence) VALUES (:order_id,:slug,:name,:price,:quantity,:line)');
        foreach ($items as $item) $itemStmt->execute(['order_id'=>$id,'slug'=>$item['product']['slug'],'name'=>$item['product']['name'],'price'=>$item['product']['price_pence'],'quantity'=>$item['quantity'],'line'=>$item['line_total']]);
        $db->commit();
    } catch (Throwable $e) { $db->rollBack(); throw $e; }
    return ['id'=>$id,'total_pence'=>$total,'items'=>$items,'customer_name'=>$name,'customer_email'=>$email,'currency'=>$store['currency']];
}

function attach_stripe_session(array $store, string $orderId, string $sessionId): void
{
    $stmt = store_db($store)->prepare('UPDATE orders SET stripe_session_id = :session WHERE id = :id');
    $stmt->execute(['session'=>$sessionId,'id'=>$orderId]);
}

function mark_order_paid(array $store, string $eventId, string $eventType, object $session): ?array
{
    $db = store_db($store);
    $db->beginTransaction();
    try {
        $eventStmt = $db->prepare('INSERT INTO webhook_events (event_id,event_type,received_at) VALUES (:id,:type,:received)');
        $eventStmt->execute(['id'=>$eventId,'type'=>$eventType,'received'=>date('c')]);
        $orderId = (string) ($session->metadata->order_id ?? $session->client_reference_id ?? '');
        if ($orderId === '') { $db->commit(); return null; }
        $orderStmt = $db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $orderStmt->execute(['id'=>$orderId]);
        $order = $orderStmt->fetch();
        if (!$order) { $db->commit(); return null; }
        if ($order['status'] !== 'paid') {
            $shipping = $session->shipping_details ?? null;
            $shippingName = $shipping?->name ?? null;
            $shippingAddress = $shipping?->address ?? null;
            $stmt = $db->prepare('UPDATE orders SET status="paid", paid_at=:paid, stripe_payment_intent_id=:intent, shipping_name=:shipping_name, shipping_address_json=:shipping WHERE id=:id');
            $stmt->execute(['paid'=>date('c'),'intent'=>(string)($session->payment_intent ?? ''),'shipping_name'=>$shippingName,'shipping'=>is_object($shippingAddress) ? json_encode($shippingAddress, JSON_UNESCAPED_SLASHES) : null,'id'=>$orderId]);
        }
        $db->commit();
        return get_order($store, $orderId);
    } catch (PDOException $e) {
        $db->rollBack();
        if (str_contains($e->getMessage(), 'UNIQUE constraint failed: webhook_events.event_id')) return null;
        throw $e;
    }
}

function get_order(array $store, string $id): ?array
{
    $stmt = store_db($store)->prepare('SELECT * FROM orders WHERE id=:id LIMIT 1');
    $stmt->execute(['id'=>$id]);
    $order = $stmt->fetch();
    if (!$order) return null;
    $itemStmt = store_db($store)->prepare('SELECT * FROM order_items WHERE order_id=:id ORDER BY id');
    $itemStmt->execute(['id'=>$id]);
    $order['items'] = $itemStmt->fetchAll();
    return $order;
}
