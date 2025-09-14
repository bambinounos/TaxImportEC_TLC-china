<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TlcSchedule;

class TlcScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $tlcSchedules = [
        ];

        foreach ($tlcSchedules as $scheduleData) {
            TlcSchedule::updateOrCreate([
                'hs_code' => $scheduleData['hs_code'],
                'country_code' => $scheduleData['country_code']
            ], $scheduleData);
        }
    }

    private function getEnglishDescription($hsCode)
    {
        $descriptions = [
            '0101210000' => 'Pure-bred breeding horses',
            '0201100000' => 'Carcasses and half-carcasses of bovine animals, fresh or chilled',
            '8471300000' => 'Portable automatic data processing machines',
            '8517120000' => 'Telephones for cellular networks or for other wireless networks',
        ];
        
        return $descriptions[$hsCode] ?? 'Product ' . $hsCode;
    }

    private function getSpanishDescription($hsCode)
    {
        $descriptions = [
            '0101210000' => 'Caballos reproductores de raza pura',
            '0201100000' => 'Canales y medias canales de bovino, frescas o refrigeradas',
            '8471300000' => 'Máquinas automáticas para tratamiento de datos, portátiles',
            '8517120000' => 'Teléfonos para redes celulares o para otras redes inalámbricas',
        ];
        
        return $descriptions[$hsCode] ?? 'Producto ' . $hsCode;
    }

    private function hasIce($hsCode)
    {
        $iceCategories = ['2402', '2208', '2203', '2202', '8703', '3303', '9504', '9303', '8539', '8802'];
        $prefix = substr($hsCode, 0, 4);
        return in_array($prefix, $iceCategories);
    }
}
