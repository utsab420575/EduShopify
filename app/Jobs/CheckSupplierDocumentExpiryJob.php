<?php

namespace App\Jobs;

use App\Models\SupplierDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckSupplierDocumentExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find current verified documents that have passed their expiration date
        $expiredDocs = SupplierDocument::with(['supplierAccount.primaryOwner', 'documentType'])
            ->where('is_current', true)
            ->where('status', 'verified')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredDocs as $doc) {
            $owner    = $doc->supplierAccount?->primaryOwner;
            $typeName = $doc->documentType?->name ?? 'Document';

            Log::info("CheckSupplierDocumentExpiryJob: Document ID {$doc->id} ({$typeName}) for Account {$doc->supplier_account_id} expired on {$doc->expires_at->format('Y-m-d')}.");

            // Point 7: Do NOT automatically change status from verified -> rejected.
            // Status remains unchanged; validation fails dynamically during approval/actions.
            if ($owner) {
                try {
                    // Send notification to supplier owner if email channel is configured
                } catch (\Throwable $e) {
                    Log::warning("CheckSupplierDocumentExpiryJob notification failed: " . $e->getMessage());
                }
            }
        }
    }
}
