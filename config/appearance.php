<?php

return [
    // ReproCare has one protected brand palette. Clinical status colors are
    // defined separately and are intentionally not appearance preferences.
    'defaults' => ['primary' => 'purple', 'secondary' => 'charcoal', 'accent' => 'purple', 'mode' => 'light'],
    'colors' => [
        'purple' => ['label' => 'Purple', 'hex' => '#9B64B9', 'soft' => '#F4EFF8'],
        'charcoal' => ['label' => 'Charcoal', 'hex' => '#1F2937', 'soft' => '#EDF0F4'],
    ],
    'options' => [
        'primary' => ['purple'],
        'secondary' => ['charcoal'],
        'accent' => ['purple'],
    ],
];
