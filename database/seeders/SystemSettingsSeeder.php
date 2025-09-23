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
                'value' => json_encode([
                    'gastos_bancarios' => 0,
                    'gastos_documentarios' => 100,
                    'agente_aduana' => 327,
                    'emision_ecas' => 0,
                    'almacenaje' => 0,
                    'transporte_terrestre' => 0,
                    'devolucion_contenedor' => 0,
                    'demoraje' => 0
                ]),
                'type' => 'json',
                'description' => 'Costos adicionales por defecto antes de impuestos',
                'is_user_configurable' => true,
            ],
            [
                'key' => 'default_additional_costs_post_tax',
                'value' => json_encode([
                    'flete_terrestre' => ['amount' => 0, 'iva_applies' => false],
                    'gastos_bodega_aduana' => ['amount' => 0, 'iva_applies' => true],
                    'gastos_naviera' => ['amount' => 195, 'iva_applies' => true],
                    'agente_aduana_post' => ['amount' => 0, 'iva_applies' => true],
                    'devolucion_contenedores' => ['amount' => 0, 'iva_applies' => false]
                ]),
                'type' => 'json',
                'description' => 'Costos adicionales por defecto después de impuestos con IVA',
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
