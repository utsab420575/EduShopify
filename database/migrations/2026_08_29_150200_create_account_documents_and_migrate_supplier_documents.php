<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces supplier_documents with a shared account_documents table so
     * Buyer document verification can reuse the same storage instead of a
     * parallel buyer_documents table. documentable_type/documentable_id is
     * the real, canonical relation (registered under the 'account' morph
     * alias in AppServiceProvider — same alias SocialLink already uses);
     * capability_type_id disambiguates a buyer-capability document from a
     * supplier-capability one on the same account (an account can hold
     * both). supplier_account_id is kept as a real, always-populated
     * column purely so the 17 existing call sites that already query
     * SupplierDocument by that column keep working unmodified — only
     * SupplierDocument's $table changes, nothing about how it's queried.
     */
    public function up(): void
    {
        Schema::create('account_documents', function (Blueprint $table) {
            $table->id();

            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->unsignedBigInteger('capability_type_id');

            // Compatibility column — see class docblock. Nullable because a
            // buyer-capability row will never populate it.
            $table->unsignedBigInteger('supplier_account_id')->nullable();

            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->string('custom_name')->nullable();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size_kb')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id', 'capability_type_id'], 'account_documents_documentable_capability_index');
            $table->index(['supplier_account_id', 'document_type_id'], 'account_documents_supplier_doctype_index');
            $table->index('status');
            $table->index('expires_at');

            $table->foreign('capability_type_id')->references('id')->on('capability_types')->restrictOnDelete();
            $table->foreign('document_type_id')->references('id')->on('document_types')->restrictOnDelete();
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('verified_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        $supplierCapabilityTypeId = DB::table('capability_types')->where('code', 'supplier')->value('id');

        if ($supplierCapabilityTypeId && Schema::hasTable('supplier_documents')) {
            $now = now();

            DB::table('supplier_documents')->orderBy('id')->chunk(200, function ($rows) use ($supplierCapabilityTypeId, $now) {
                $insert = $rows->map(fn ($row) => [
                    'documentable_type' => 'account',
                    'documentable_id' => $row->supplier_account_id,
                    'capability_type_id' => $supplierCapabilityTypeId,
                    'supplier_account_id' => $row->supplier_account_id,
                    'document_type_id' => $row->document_type_id,
                    'custom_name' => $row->custom_name,
                    'file_path' => $row->file_path,
                    'original_name' => $row->original_name,
                    'mime_type' => $row->mime_type,
                    'file_size_kb' => $row->file_size_kb,
                    'status' => $row->status,
                    'rejection_reason' => $row->rejection_reason,
                    'uploaded_by_user_id' => $row->uploaded_by_user_id,
                    'verified_by_user_id' => $row->verified_by_user_id,
                    'verified_at' => $row->verified_at,
                    'expires_at' => $row->expires_at,
                    'is_current' => $row->is_current,
                    'created_at' => $row->created_at ?? $now,
                    'updated_at' => $row->updated_at ?? $now,
                ])->toArray();

                DB::table('account_documents')->insert($insert);
            });
        }

        Schema::dropIfExists('supplier_documents');
    }

    public function down(): void
    {
        Schema::create('supplier_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_account_id');
            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->string('custom_name')->nullable();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size_kb')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['supplier_account_id', 'document_type_id']);
            $table->index('status');
            $table->index('expires_at');

            $table->foreign('supplier_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('document_type_id')->references('id')->on('document_types')->restrictOnDelete();
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('verified_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        $supplierCapabilityTypeId = DB::table('capability_types')->where('code', 'supplier')->value('id');

        if ($supplierCapabilityTypeId && Schema::hasTable('account_documents')) {
            DB::table('account_documents')
                ->where('capability_type_id', $supplierCapabilityTypeId)
                ->orderBy('id')
                ->chunk(200, function ($rows) {
                    $insert = $rows->map(fn ($row) => [
                        'supplier_account_id' => $row->supplier_account_id,
                        'document_type_id' => $row->document_type_id,
                        'custom_name' => $row->custom_name,
                        'file_path' => $row->file_path,
                        'original_name' => $row->original_name,
                        'mime_type' => $row->mime_type,
                        'file_size_kb' => $row->file_size_kb,
                        'status' => $row->status,
                        'rejection_reason' => $row->rejection_reason,
                        'uploaded_by_user_id' => $row->uploaded_by_user_id,
                        'verified_by_user_id' => $row->verified_by_user_id,
                        'verified_at' => $row->verified_at,
                        'expires_at' => $row->expires_at,
                        'is_current' => $row->is_current,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ])->toArray();

                    DB::table('supplier_documents')->insert($insert);
                });
        }

        Schema::dropIfExists('account_documents');
    }
};
