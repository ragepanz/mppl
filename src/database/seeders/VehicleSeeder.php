<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;
class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            ['Avanza G', 'Toyota', 'MPV', 2021, 180000000, 5],
            ['Rx-8', 'Mazda', 'Sport Coupe', 2001, 285000000, 2],
            ['Ranger G', 'Toyota', 'Sport Coupe', 2019, 215737657, 4],
            ['Jazz G', 'Kia', 'MPV', 2024, 263258777, 1],
            ['Jazz RS', 'Suzuki', 'Pickup', 2016, 220936015, 7],
            ['Creta G', 'Suzuki', 'Hatchback', 2019, 344798412, 7],
            ['Xenia RS', 'Daihatsu', 'Pickup', 2020, 276928566, 2],
            ['Almaz X', 'Hyundai', 'SUV', 2023, 327946064, 5],
            ['Avanza G', 'Daihatsu', 'Sedan', 2019, 345753229, 9],
            ['Seltos G', 'Mazda', 'SUV', 2015, 172512111, 5],
            ['Civic RS', 'Honda', 'Sedan', 2022, 350000000, 3],
            ['Brio Satya', 'Honda', 'Hatchback', 2021, 160000000, 6],
            ['Terios X', 'Daihatsu', 'SUV', 2020, 250000000, 4],
            ['Fortuner VRZ', 'Toyota', 'SUV', 2023, 560000000, 3],
            ['Pajero Sport', 'Mitsubishi', 'SUV', 2022, 580000000, 2],
            ['Camry Hybrid', 'Toyota', 'Sedan', 2022, 720000000, 1],
            ['Alphard Q', 'Toyota', 'MPV', 2023, 1200000000, 1],
            ['Xpander Cross', 'Mitsubishi', 'MPV', 2021, 310000000, 5],
            ['Yaris GR', 'Toyota', 'Hatchback', 2023, 850000000, 1],
            ['Mazda 2', 'Mazda', 'Hatchback', 2020, 300000000, 4],
            ['Mazda CX-5', 'Mazda', 'SUV', 2022, 600000000, 3],
            ['HR-V SE', 'Honda', 'SUV', 2021, 400000000, 2],
            ['CR-V Prestige', 'Honda', 'SUV', 2023, 700000000, 1],
            ['Wuling Cortez', 'Wuling', 'MPV', 2021, 250000000, 7],
            ['Ertiga GL', 'Suzuki', 'MPV', 2020, 230000000, 5],
            ['Ignis GX', 'Suzuki', 'Hatchback', 2019, 180000000, 4],
            ['S-Cross', 'Suzuki', 'SUV', 2021, 310000000, 2],
            ['Kona Electric', 'Hyundai', 'SUV', 2023, 750000000, 1],
            ['Stargazer Prime', 'Hyundai', 'MPV', 2022, 310000000, 6],
            ['Ioniq 5', 'Hyundai', 'Hatchback', 2023, 850000000, 1],
            ['Veloster Turbo', 'Hyundai', 'Hatchback', 2020, 400000000, 2],
            ['Kicks e-Power', 'Nissan', 'SUV', 2022, 470000000, 3],
            ['Grand Livina', 'Nissan', 'MPV', 2018, 220000000, 6],
            ['Serena HWS', 'Nissan', 'MPV', 2021, 480000000, 2],
            ['Navara VL', 'Nissan', 'Pickup', 2020, 560000000, 2],
            ['Triton Exceed', 'Mitsubishi', 'Pickup', 2021, 460000000, 2],
            ['L300 Euro4', 'Mitsubishi', 'Pickup', 2023, 230000000, 7],
            ['Karimun Wagon R', 'Suzuki', 'Hatchback', 2019, 120000000, 8],
            ['Pick Up Carry', 'Suzuki', 'Pickup', 2021, 160000000, 9],
            ['Luxio X', 'Daihatsu', 'MPV', 2022, 230000000, 5],
            ['Rocky ADS', 'Daihatsu', 'SUV', 2021, 260000000, 4],
            ['Sirion Sport', 'Daihatsu', 'Hatchback', 2020, 200000000, 6],
            ['Celerio X', 'Suzuki', 'Hatchback', 2017, 150000000, 3],
            ['Altis G', 'Toyota', 'Sedan', 2021, 400000000, 2],
            ['Rush S TRD', 'Toyota', 'SUV', 2020, 280000000, 5],
            ['Hiace Premio', 'Toyota', 'Van', 2023, 550000000, 1],
            ['D-Max LS', 'Isuzu', 'Pickup', 2020, 480000000, 2],
            ['MU-X', 'Isuzu', 'SUV', 2021, 560000000, 1],
            ['Elf NLR', 'Isuzu', 'Minibus', 2022, 620000000, 2],
            ['Wuling Air EV', 'Wuling', 'Hatchback', 2023, 250000000, 5],
            ['Formo Max', 'Wuling', 'Pickup', 2023, 180000000, 6],
        ];
    
        foreach ($vehicles as [$nama, $merk, $tipe, $tahun, $harga, $stok]) {
            Vehicle::updateOrCreate([
                'nama' => $nama,
                'merk' => $merk,
                'tipe' => $tipe,
                'tahun' => $tahun,
                'harga' => $harga,
                'stok' => $stok,
                'foto' => null,
            ]);
        }
    }
};