<?php

namespace Database\Seeders;

use App\Models\TlcSchedule;
use App\Models\TariffCode;
use Illuminate\Database\Seeder;

class TlcScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $tlcSchedules = [
            [
                'hs_code' => '0101.21.00',
                'country_code' => 'CHN',
                'base_rate' => 5.0,
                'elimination_years' => 5,
                'start_date' => '2024-01-01',
                'reduction_type' => 'linear',
                'yearly_rates' => [
                    '2024' => 5.0,
                    '2025' => 4.0,
                    '2026' => 3.0,
                    '2027' => 2.0,
                    '2028' => 1.0,
                    '2029' => 0.0,
                ],
                'notes' => 'Caballos reproductores de raza pura - Eliminación gradual en 5 años',
                'is_active' => true,
            ],
            [
                'hs_code' => '0102.21.00',
                'country_code' => 'CHN',
                'base_rate' => 10.0,
                'elimination_years' => 10,
                'start_date' => '2024-01-01',
                'reduction_type' => 'linear',
                'yearly_rates' => [
                    '2024' => 10.0,
                    '2025' => 9.0,
                    '2026' => 8.0,
                    '2027' => 7.0,
                    '2028' => 6.0,
                    '2029' => 5.0,
                    '2030' => 4.0,
                    '2031' => 3.0,
                    '2032' => 2.0,
                    '2033' => 1.0,
                    '2034' => 0.0,
                ],
                'notes' => 'Bovinos reproductores de raza pura - Eliminación gradual en 10 años',
                'is_active' => true,
            ],
            [
                'hs_code' => '0201.10.00',
                'country_code' => 'CHN',
                'base_rate' => 15.0,
                'elimination_years' => 15,
                'start_date' => '2024-01-01',
                'reduction_type' => 'linear',
                'yearly_rates' => [
                    '2024' => 15.0,
                    '2025' => 14.0,
                    '2026' => 13.0,
                    '2027' => 12.0,
                    '2028' => 11.0,
                    '2029' => 10.0,
                    '2030' => 9.0,
                    '2031' => 8.0,
                    '2032' => 7.0,
                    '2033' => 6.0,
                    '2034' => 5.0,
                    '2035' => 4.0,
                    '2036' => 3.0,
                    '2037' => 2.0,
                    '2038' => 1.0,
                    '2039' => 0.0,
                ],
                'notes' => 'Carne bovina fresca - Eliminación gradual en 15 años (producto sensible)',
                'is_active' => true,
            ],
            [
                'hs_code' => '0401.10.00',
                'country_code' => 'CHN',
                'base_rate' => 20.0,
                'elimination_years' => 20,
                'start_date' => '2024-01-01',
                'reduction_type' => 'linear',
                'yearly_rates' => [
                    '2024' => 20.0,
                    '2025' => 19.0,
                    '2026' => 18.0,
                    '2027' => 17.0,
                    '2028' => 16.0,
                    '2029' => 15.0,
                    '2030' => 14.0,
                    '2031' => 13.0,
                    '2032' => 12.0,
                    '2033' => 11.0,
                    '2034' => 10.0,
                    '2035' => 9.0,
                    '2036' => 8.0,
                    '2037' => 7.0,
                    '2038' => 6.0,
                    '2039' => 5.0,
                    '2040' => 4.0,
                    '2041' => 3.0,
                    '2042' => 2.0,
                    '2043' => 1.0,
                    '2044' => 0.0,
                ],
                'notes' => 'Leche y nata con contenido de grasa <= 1% - Eliminación gradual en 20 años (producto muy sensible)',
                'is_active' => true,
            ],
            [
                'hs_code' => '8471.30.00',
                'country_code' => 'CHN',
                'base_rate' => 0.0,
                'elimination_years' => 0,
                'start_date' => '2024-01-01',
                'reduction_type' => 'immediate',
                'yearly_rates' => [
                    '2024' => 0.0,
                ],
                'notes' => 'Máquinas automáticas para tratamiento de datos portátiles - Eliminación inmediata',
                'is_active' => true,
            ],
            [
                'hs_code' => '8471.41.00',
                'country_code' => 'CHN',
                'base_rate' => 0.0,
                'elimination_years' => 0,
                'start_date' => '2024-01-01',
                'reduction_type' => 'immediate',
                'yearly_rates' => [
                    '2024' => 0.0,
                ],
                'notes' => 'Computadoras - Eliminación inmediata',
                'is_active' => true,
            ],
            [
                'hs_code' => '8517.12.00',
                'country_code' => 'CHN',
                'base_rate' => 0.0,
                'elimination_years' => 0,
                'start_date' => '2024-01-01',
                'reduction_type' => 'immediate',
                'yearly_rates' => [
                    '2024' => 0.0,
                ],
                'notes' => 'Teléfonos móviles - Eliminación inmediata',
                'is_active' => true,
            ],
            [
                'hs_code' => '8703.23.00',
                'country_code' => 'CHN',
                'base_rate' => 35.0,
                'elimination_years' => 15,
                'start_date' => '2024-01-01',
                'reduction_type' => 'staged',
                'yearly_rates' => [
                    '2024' => 35.0,
                    '2025' => 35.0,
                    '2026' => 30.0,
                    '2027' => 30.0,
                    '2028' => 25.0,
                    '2029' => 25.0,
                    '2030' => 20.0,
                    '2031' => 20.0,
                    '2032' => 15.0,
                    '2033' => 15.0,
                    '2034' => 10.0,
                    '2035' => 10.0,
                    '2036' => 5.0,
                    '2037' => 5.0,
                    '2038' => 2.5,
                    '2039' => 0.0,
                ],
                'notes' => 'Automóviles de turismo cilindrada 1000-1500 cc - Reducción por etapas en 15 años',
                'is_active' => true,
            ],
        ];

        foreach ($tlcSchedules as $schedule) {
            TariffCode::updateOrCreate(
                ['hs_code' => $schedule['hs_code']],
                [
                    'description_en' => $this->getDescriptionEn($schedule['hs_code']),
                    'description_es' => $this->getDescriptionEs($schedule['hs_code']),
                    'base_tariff_rate' => $schedule['base_rate'],
                    'iva_rate' => 15.0,
                    'has_ice' => $this->hasIce($schedule['hs_code']),
                    'is_active' => true,
                ]
            );

            TlcSchedule::updateOrCreate(
                [
                    'hs_code' => $schedule['hs_code'],
                    'country_code' => $schedule['country_code']
                ],
                $schedule
            );
        }
    }

    private function getDescriptionEn(string $hsCode): string
    {
        $descriptions = [
            '0101.21.00' => 'Horses, pure-bred breeding animals',
            '0102.21.00' => 'Cattle, pure-bred breeding animals',
            '0201.10.00' => 'Meat of bovine animals, fresh or chilled, carcasses and half-carcasses',
            '0401.10.00' => 'Milk and cream, not concentrated, fat content <= 1%',
            '8471.30.00' => 'Portable automatic data processing machines',
            '8471.41.00' => 'Computers comprising CPU, input and output units',
            '8517.12.00' => 'Mobile telephones',
            '8703.23.00' => 'Motor cars, cylinder capacity > 1000 cc but <= 1500 cc',
        ];

        return $descriptions[$hsCode] ?? 'Product description not available';
    }

    private function getDescriptionEs(string $hsCode): string
    {
        $descriptions = [
            '0101.21.00' => 'Caballos reproductores de raza pura',
            '0102.21.00' => 'Bovinos reproductores de raza pura',
            '0201.10.00' => 'Carne de bovino, fresca o refrigerada, en canales o medias canales',
            '0401.10.00' => 'Leche y nata, sin concentrar, con contenido de grasa <= 1%',
            '8471.30.00' => 'Máquinas automáticas para tratamiento de datos portátiles',
            '8471.41.00' => 'Computadoras que incluyan CPU, unidades de entrada y salida',
            '8517.12.00' => 'Teléfonos móviles',
            '8703.23.00' => 'Automóviles de turismo, cilindrada > 1000 cc pero <= 1500 cc',
        ];

        return $descriptions[$hsCode] ?? 'Descripción del producto no disponible';
    }

    private function hasIce(string $hsCode): bool
    {
        $iceProducts = ['8703.23.00'];
        return in_array($hsCode, $iceProducts);
    }
}
