<?php

// Keys match the existing supply_requests.supply_category database enum.
return [
    'units' => ['pieces', 'tablets', 'capsules', 'boxes', 'packs', 'bottles', 'vials', 'ampoules', 'doses', 'kits', 'sets', 'pairs', 'rolls', 'tubes'],
    'categories' => [
        'vitamins' => ['label' => 'Supplements / Micronutrients', 'items' => [
            'Iron + folic acid tablets' => ['tablets', 'boxes'], 'Iron tablets' => ['tablets', 'boxes'],
            'Folic acid tablets' => ['tablets', 'boxes'], 'Calcium tablets' => ['tablets', 'boxes'],
            'Multivitamin capsules' => ['capsules', 'boxes'],
        ]],
        'vaccines' => ['label' => 'Vaccines', 'items' => [
            'Tetanus-diphtheria vaccine' => ['vials', 'doses', 'boxes'],
            'Tetanus toxoid vaccine' => ['vials', 'doses', 'boxes'],
        ]],
        'birthing_kits' => ['label' => 'Birthing Kits', 'items' => [
            'Clean delivery kit' => ['kits', 'boxes'], 'Newborn care kit' => ['kits', 'sets'],
        ]],
        'medicines' => ['label' => 'Medicines / Family Planning', 'items' => [
            'Oral contraceptive pills' => ['packs', 'boxes'], 'Injectable contraceptive' => ['vials', 'ampoules', 'boxes'],
            'Condoms' => ['pieces', 'boxes'], 'Intrauterine device (IUD)' => ['pieces', 'kits'],
            'Paracetamol tablets' => ['tablets', 'boxes'],
        ]],
        'equipment' => ['label' => 'Medical Equipment', 'items' => [
            'Blood pressure monitor' => ['pieces', 'sets'], 'Stethoscope' => ['pieces'],
            'Digital thermometer' => ['pieces', 'boxes'], 'Weighing scale' => ['pieces'],
        ]],
        'ppe' => ['label' => 'Personal Protective Equipment', 'items' => [
            'Examination gloves' => ['pairs', 'boxes'], 'Surgical masks' => ['pieces', 'boxes'],
            'Protective gowns' => ['pieces', 'packs'], 'Face shields' => ['pieces', 'boxes'],
        ]],
        'other' => ['label' => 'Clinical Supplies / Other', 'items' => [
            'Sterile gauze' => ['pieces', 'packs', 'boxes'], 'Syringes' => ['pieces', 'boxes'],
            'Alcohol solution' => ['bottles'], 'Cotton' => ['packs', 'rolls'], 'Medical tape' => ['rolls', 'boxes'],
        ]],
    ],
];
