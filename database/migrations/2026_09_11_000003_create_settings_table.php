<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $defaults = [
        // Global clinical decision-support thresholds (CHO, city-wide)
        ['key' => 'threshold.bp_systolic_high', 'value' => '140', 'group' => 'thresholds', 'label' => 'High systolic BP (mmHg)'],
        ['key' => 'threshold.bp_diastolic_high', 'value' => '90', 'group' => 'thresholds', 'label' => 'High diastolic BP (mmHg)'],
        ['key' => 'threshold.hemoglobin_low', 'value' => '10', 'group' => 'thresholds', 'label' => 'Low hemoglobin (g/dL)'],
        ['key' => 'threshold.gestational_age_max', 'value' => '42', 'group' => 'thresholds', 'label' => 'Max gestational age (weeks)'],
        // CHO office profile
        ['key' => 'cho.office_name', 'value' => 'City Health Office - San Carlos City', 'group' => 'cho_office', 'label' => 'CHO office name'],
        ['key' => 'cho.contact_number', 'value' => '', 'group' => 'cho_office', 'label' => 'CHO contact number'],
        ['key' => 'cho.director_name', 'value' => '', 'group' => 'cho_office', 'label' => 'City health director name (report signature)'],
        // Audit log retention
        ['key' => 'audit.retention_days', 'value' => '365', 'group' => 'audit', 'label' => 'Activity log retention (days)'],
        // SMS gateway (DB overrides config/services.php when filled)
        ['key' => 'sms.provider', 'value' => 'movider', 'group' => 'sms', 'label' => 'SMS provider'],
        ['key' => 'sms.mock', 'value' => '1', 'group' => 'sms', 'label' => 'Mock mode (1 = log only, 0 = live send)'],
        ['key' => 'sms.textbee_api_key', 'value' => '', 'group' => 'sms', 'label' => 'TextBee API key'],
        ['key' => 'sms.textbee_device_id', 'value' => '', 'group' => 'sms', 'label' => 'TextBee device ID'],
        // Team reporting deadline (day of month BHWs must submit field reports)
        ['key' => 'reports.deadline_day', 'value' => '25', 'group' => 'reports', 'label' => 'Monthly BHW submission deadline (day of month)'],
        // Monthly / quarterly report templates (RHU-customizable)
        ['key' => 'reports.template_monthly', 'value' => "Monthly Maternal & Child Health Report\nStation: {station}\nMonth: {month} {year}\nPrepared by: {preparer}\n\n", 'group' => 'reports', 'label' => 'Monthly report template'],
        ['key' => 'reports.template_quarterly', 'value' => "Quarterly Maternal & Child Health Report\nStation: {station}\nQuarter: {quarter} {year}\nPrepared by: {preparer}\n\n", 'group' => 'reports', 'label' => 'Quarterly report template'],
        // RHU escalation alert recipients (JSON array of user ids)
        ['key' => 'rhu.escalation_recipients', 'value' => '[]', 'group' => 'rhu', 'label' => 'RHU escalation alert recipients'],
        // RHU facility profile
        ['key' => 'rhu.station_name', 'value' => 'RHU I', 'group' => 'rhu', 'label' => 'RHU station name'],
        ['key' => 'rhu.operating_hours', 'value' => 'Mon–Fri, 8:00 AM – 5:00 PM', 'group' => 'rhu', 'label' => 'Operating hours'],
        ['key' => 'rhu.contact_number', 'value' => '', 'group' => 'rhu', 'label' => 'Station contact number'],
    ];

    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('label')->nullable();
            $table->unsignedBigInteger('updated_by_id')->nullable();
            $table->timestamps();
        });

        foreach ($this->defaults as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                ['value' => $row['value'], 'group' => $row['group'], 'label' => $row['label'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
