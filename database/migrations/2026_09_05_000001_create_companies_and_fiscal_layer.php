<?php

use App\Models\UserSifConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('legal_name');
            $table->string('tax_id', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('country', 2)->default('ES');
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('vat_regime', 30)->default('general');
            $table->decimal('default_vat_rate', 5, 2)->default(21.00);
            $table->decimal('default_irpf_rate', 5, 2)->default(0);
            $table->decimal('default_recargo_rate', 5, 2)->default(0);
            $table->unsignedInteger('default_due_days')->default(30);
            $table->string('invoice_prefix', 20)->default('FAC');
            $table->string('quote_prefix', 20)->default('PRE');
            $table->string('rectificativa_prefix', 20)->default('R');
            $table->string('timezone', 40)->default('Europe/Madrid');
            $table->text('invoice_footer')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('legal_terms_version', 20)->nullable();
            $table->timestamp('legal_terms_accepted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('invoice_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('prefix', 20);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('next_sequence')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'kind', 'year']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method', 40)->nullable();
            $table->date('paid_on');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('billing_submission_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_record_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->boolean('permanent')->default(false);
            $table->string('idempotency_key', 80)->nullable();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['billing_record_id', 'created_at']);
        });

        Schema::create('legal_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_key', 50);
            $table->string('version', 20);
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
            $table->string('person_type', 20)->default('company')->after('company_id');
            $table->string('country', 2)->default('ES')->after('address');
            $table->string('city', 100)->nullable()->after('country');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->boolean('is_active')->default(true)->after('notes');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
            $table->string('invoice_kind', 10)->default('F1')->after('type');
            $table->date('operation_date')->nullable()->after('issue_date');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('irpf_amount', 12, 2)->default(0)->after('vat_amount');
            $table->decimal('recargo_amount', 12, 2)->default(0)->after('irpf_amount');
            $table->timestamp('number_assigned_at')->nullable()->after('number');
            $table->string('pdf_path')->nullable()->after('notes');
            $table->foreignId('created_by')->nullable()->after('accepted_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('line_items', function (Blueprint $table) {
            $table->decimal('discount_rate', 5, 2)->default(0)->after('unit_price');
            $table->decimal('irpf_rate', 5, 2)->default(0)->after('vat_rate');
            $table->decimal('recargo_rate', 5, 2)->default(0)->after('irpf_rate');
            $table->decimal('line_discount', 12, 2)->default(0)->after('line_subtotal');
            $table->decimal('line_irpf', 12, 2)->default(0)->after('line_vat');
            $table->decimal('line_recargo', 12, 2)->default(0)->after('line_irpf');
        });

        if (Schema::hasTable('billing_records')) {
            Schema::table('billing_records', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
                $table->string('invoice_type', 10)->nullable()->after('record_type');
                $table->string('schema_version', 10)->default('1.0')->after('invoice_type');
                $table->string('idempotency_key', 80)->nullable()->after('schema_version');
                $table->unsignedInteger('retry_count')->default(0)->after('aeat_status');
                $table->timestamp('generated_at')->nullable()->after('sent_at');
                $table->unique(['document_id', 'record_type']);
            });
        }

        if (Schema::hasTable('user_sif_config')) {
        try {
            Schema::table('user_sif_config', function (Blueprint $table) {
                $table->dropUnique(['user_id']);
            });
        } catch (\Throwable) {
            // SQLite / índice ya eliminado
        }
        Schema::table('user_sif_config', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });
        }

        if (Schema::hasTable('sif_events')) {
            Schema::table('sif_events', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE documents MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'draft'");
        }

        $this->backfill();

        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'number']);
            $table->unique(['company_id', 'number']);
        });

        if (Schema::hasTable('user_sif_config') && Schema::hasColumn('user_sif_config', 'company_id')) {
            Schema::table('user_sif_config', function (Blueprint $table) {
                $table->unique('company_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'number']);
            $table->unique(['user_id', 'number']);
        });

        if (Schema::hasTable('user_sif_config') && Schema::hasColumn('user_sif_config', 'company_id')) {
            Schema::table('user_sif_config', function (Blueprint $table) {
                $table->dropUnique(['company_id']);
                $table->dropConstrainedForeignId('company_id');
            });
        }

        if (Schema::hasTable('sif_events') && Schema::hasColumn('sif_events', 'company_id')) {
            Schema::table('sif_events', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }

        if (Schema::hasTable('billing_records') && Schema::hasColumn('billing_records', 'company_id')) {
            Schema::table('billing_records', function (Blueprint $table) {
                $table->dropUnique(['document_id', 'record_type']);
                $table->dropConstrainedForeignId('company_id');
                $table->dropColumn(['invoice_type', 'schema_version', 'idempotency_key', 'retry_count', 'generated_at']);
            });
        }

        Schema::table('line_items', function (Blueprint $table) {
            $table->dropColumn(['discount_rate', 'irpf_rate', 'recargo_rate', 'line_discount', 'line_irpf', 'line_recargo']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'invoice_kind', 'operation_date', 'discount_amount', 'irpf_amount',
                'recargo_amount', 'number_assigned_at', 'pdf_path',
            ]);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['person_type', 'country', 'city', 'postal_code', 'is_active']);
        });

        Schema::dropIfExists('legal_consents');
        Schema::dropIfExists('billing_submission_attempts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_series');
        Schema::dropIfExists('companies');
    }

    private function backfill(): void
    {
        $now = now();

        foreach (DB::table('users')->orderBy('id')->get() as $user) {
            $companyId = DB::table('companies')->insertGetId([
                'user_id' => $user->id,
                'legal_name' => $user->business_name ?: $user->name,
                'tax_id' => $user->tax_id,
                'address' => $user->address,
                'city' => $user->city,
                'postal_code' => $user->postal_code,
                'country' => $user->country ?: 'ES',
                'email' => $user->email,
                'phone' => $user->phone,
                'iban' => $user->iban,
                'logo_path' => $user->logo_path,
                'default_vat_rate' => $user->default_vat_rate ?? 21,
                'default_due_days' => $user->default_due_days ?? 30,
                'invoice_prefix' => $user->invoice_prefix ?: 'FAC',
                'quote_prefix' => $user->quote_prefix ?: 'PRE',
                'is_default' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $year = (int) $now->year;
            foreach ([
                ['kind' => 'invoice', 'prefix' => $user->invoice_prefix ?: 'FAC'],
                ['kind' => 'quote', 'prefix' => $user->quote_prefix ?: 'PRE'],
                ['kind' => 'rectificativa', 'prefix' => 'R'],
            ] as $series) {
                DB::table('invoice_series')->insert([
                    'company_id' => $companyId,
                    'kind' => $series['kind'],
                    'prefix' => $series['prefix'],
                    'year' => $year,
                    'next_sequence' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('clients')->where('user_id', $user->id)->update(['company_id' => $companyId]);
            DB::table('documents')->where('user_id', $user->id)->update(['company_id' => $companyId]);

            if (Schema::hasTable('billing_records')) {
                DB::table('billing_records')->where('user_id', $user->id)->update(['company_id' => $companyId]);
            }

            if (Schema::hasTable('sif_events')) {
                DB::table('sif_events')->where('user_id', $user->id)->update(['company_id' => $companyId]);
            }

            if (Schema::hasTable('user_sif_config')) {
                $updated = DB::table('user_sif_config')->where('user_id', $user->id)->update(['company_id' => $companyId]);
                if ($updated === 0) {
                    DB::table('user_sif_config')->insert([
                        'user_id' => $user->id,
                        'company_id' => $companyId,
                        'mode' => UserSifConfig::MODE_VERIFACTU,
                        'enabled' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
};
