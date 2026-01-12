<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $setting = DB::table('app_settings')->where('key', 'invoice_footer_text')->first();

        if ($setting && str_contains($setting->value, 'billing-table')) {
            // It contains the old table. Let's try to extract the "Thank you" part or just reset it to a safe default.
            // Since parsing HTML with regex is fragile, and we know the exact default structure we want to remove,
            // we will just replace it with the standard "Thank you" text IF it matches the known patterns.
            
            // Or simpler: just strip the table part?
            // Let's set it to the standard text which is what the user probably wants for a "clean" start, 
            // especially since they asked to "make default of kleening".
            
            $cleanText = '
        <p>Jangan lupa konfirmasi dengan melampirkan bukti transfer 😊</p>
        <p>Terima kasih telah memilih @kleening.id sebagai jasa cleaning kepercayaan Anda ✨🙏🏻</p>';

            DB::table('app_settings')
                ->where('key', 'invoice_footer_text')
                ->update(['value' => $cleanText]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy rollback for string replacement.
    }
};
