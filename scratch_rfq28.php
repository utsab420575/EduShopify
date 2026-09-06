<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$r = App\Models\Rfq::with(['items.attributeValues.attribute', 'items.listing'])->find(28);
echo "RFQ ID: {$r->id}, status: {$r->status}, title: {$r->title}\n";
echo "Items count: " . $r->items->count() . "\n";
foreach ($r->items as $idx => $i) {
    echo "Item #{$idx} (ID: {$i->id}): Name='{$i->item_name}', Qty={$i->quantity}, Specs=" . json_encode($i->specs) . ", Attrs count=" . $i->attributeValues->count() . "\n";
}

$quotes = App\Models\Quotation::with('items')->where('rfq_id', 28)->get();
echo "Quotations count: " . $quotes->count() . "\n";
foreach ($quotes as $q) {
    echo "Quote ID: {$q->id}, Supplier: {$q->supplier_account_id}, Status: {$q->status}, Items count: " . $q->items->count() . "\n";
    foreach ($q->items as $qi) {
        echo "  - Qi ID: {$qi->id}, RFQ item ID: {$qi->rfq_item_id}, Name: {$qi->item_name}\n";
    }
}

$rfq = App\Models\Rfq::find(28);
$buyerUser = App\Models\User::find($rfq->created_by_user_id);
$buyerAccount = $rfq->buyerAccount;

$service = app(\App\Services\RfqService::class);

echo "Current RFQ 28 version: {$rfq->current_version_no}\n";




