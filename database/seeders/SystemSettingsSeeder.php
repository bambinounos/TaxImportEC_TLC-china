<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'default_iva_rate',
                'value' => '15.00',
                'type' => 'decimal',
                'description' => 'Tasa de IVA por defecto (%)',
                'is_user_configurable' => true,
            ],
            [
                'key' => 'default_insurance_rate',
                'value' => '1.00',
                'type' => 'decimal',
                'description' => 'Tasa de seguro por defecto (%)',
                'is_user_configurable' => true,
            ],
            [
                'key' => 'default_profit_margin',
                'value' => '60.00',
                'type' => 'decimal',
                'description' => 'Margen de ganancia por defecto (%)',
                'is_user_configurable' => true,
            ],
            [
                'key' => 'tlc_china_start_date',
                'value' => '2024-01-01',
                'type' => 'string',
                'description' => 'Fecha de inicio del TLC con China',
                'is_user_configurable' => false,
            ],
            [
                'key' => 'default_additional_costs_pre_tax',
                'value' => '{"gastos_bancarios": 0, "gastos_documentarios": 0}',
                'type' => 'json',
                'description' => 'Costos adicionales por defecto antes de impuestos',
                'is_user_configurable' => true,
            ],
            [
                'key' => 'default_additional_costs_post_tax',
                'value' => '{"flete_terrestre": 0, "gastos_bodega_aduana": 0, "gastos_naviera": 0, "agente_aduana": 0, "devolucion_contenedores": 0}',
                'type' => 'json',
                'description' => 'Costos adicionales por defecto después de impuestos',
                'is_user_configurable' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
