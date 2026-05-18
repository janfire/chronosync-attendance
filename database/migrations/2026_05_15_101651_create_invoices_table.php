<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();

            $table->string('invoice_number')->unique(); // INV-2026-0001
            $table->decimal('amount_usd', 8, 2);
            $table->string('currency', 10)->default('USD');

            // Billing period
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date');

            // Status lifecycle: pending → paid | overdue | cancelled
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');

            // Payment details (filled by tenant or finance)
            $table->enum('payment_method', ['ecocash', 'zipit', 'bank_transfer', 'cash', 'other'])->nullable();
            $table->string('payment_reference')->nullable(); // EcoCash TXN ID / ZIPIT ref
            $table->string('payment_proof_path')->nullable(); // Uploaded screenshot path

            // Finance confirmation
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            // Reminders tracking
            $table->timestamp('reminder_7d_sent_at')->nullable();
            $table->timestamp('reminder_14d_sent_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
