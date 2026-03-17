<?php

/**
 * Rous Cheat — MySQL Seeder
 * Run: php database/seed.php
 */

require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/DB.php';

use App\Helpers\{Env, DB};

Env::load(__DIR__ . '/../.env');
DB::init();

echo "🌱 Seeding MySQL database...\n\n";

// ── Categories ────────────────────────────────────────────────────────────────
$categories = [
    ['Soups & Stews',   'សម្ល',       '🍲', 1],
    ['Noodles',         'មីគុយ',       '🍜', 2],
    ['Salads & Sides',  'នំ',          '🥗', 3],
    ['Street Food',     'អាហារតូច',   '🍢', 4],
    ['Desserts',        'នំផ្អែម',     '🍡', 5],
    ['Fermented Foods', 'ផ្រហិត',      '🫙', 6],
    ['BBQ & Grilled',   'អាំង',        '🔥', 7],
    ['Drinks',          'ភេសជ្ជៈ',    '🫖', 8],
    ['Ceremonial',      'ពិធី',        '🎋', 9],
    ['Vegetarian',      'បួស',         '🌿', 10],
];

foreach ($categories as [$en, $kh, $emoji, $sort]) {
    DB::execute(
        'INSERT IGNORE INTO `categories` (name_en, name_kh, emoji, sort_order)
         VALUES (:name_en, :name_kh, :emoji, :sort_order)',
        [':name_en' => $en, ':name_kh' => $kh, ':emoji' => $emoji, ':sort_order' => $sort]
    );
}
echo "  ✓ Categories (10)\n";

// ── Regions — all 24 provinces ────────────────────────────────────────────────
$regions = [
    ['Phnom Penh',      'ភ្នំពេញ'],
    ['Siem Reap',       'សៀមរាប'],
    ['Battambang',      'បាត់ដំបង'],
    ['Kampong Cham',    'កំពង់ចាម'],
    ['Kandal',          'កណ្ដាល'],
    ['Kampot',          'កំពត'],
    ['Kep',             'កែប'],
    ['Sihanoukville',   'ព្រះសីហនុ'],
    ['Kratie',          'ក្រចេះ'],
    ['Mondulkiri',      'មណ្ឌលគីរី'],
    ['Preah Vihear',    'ព្រះវិហារ'],
    ['Prey Veng',       'ព្រៃវែង'],
    ['Pursat',          'ពោធិ៍សាត់'],
    ['Ratanakiri',      'រតនគិរី'],
    ['Stung Treng',     'ស្ទឹងត្រែង'],
    ['Svay Rieng',      'ស្វាយរៀង'],
    ['Takeo',           'តាកែវ'],
    ['Oddar Meanchey',  'ឧត្ដរមានជ័យ'],
    ['Koh Kong',        'កោះកុង'],
    ['Pailin',          'ប៉ៃលិន'],
    ['Kampong Speu',    'កំពង់ស្ពឺ'],
    ['Kampong Thom',    'កំពង់ធំ'],
    ['Kampong Chhnang', 'កំពង់ឆ្នាំង'],
    ['Tboung Khmum',    'ត្បូងឃ្មុំ'],
];

foreach ($regions as [$en, $kh]) {
    DB::execute(
        'INSERT IGNORE INTO `regions` (name_en, name_kh) VALUES (:name_en, :name_kh)',
        [':name_en' => $en, ':name_kh' => $kh]
    );
}
echo "  ✓ Regions (24 provinces)\n";

// ── Ingredients ───────────────────────────────────────────────────────────────
$ingredients = [
    ['Lemongrass',         'ស្លឹកគ្រៃ',       'citrus'],
    ['Galangal',           'រំដេង',            'spice'],
    ['Kaffir Lime Leaves', 'ស្លឹកក្រូចសើច',   'citrus'],
    ['Turmeric',           'រមៀត',             'spice'],
    ['Fish Sauce',         'ទឹកត្រី',          'condiment'],
    ['Prahok',             'ប្រហុក',           'fermented'],
    ['Palm Sugar',         'ស្ករត្នោត',        'sweetener'],
    ['Coconut Milk',       'ទឹកដោះគោ',        'dairy'],
    ['Holy Basil',         'ជ្រៃ',             'herb'],
    ['Long Bean',          'ស្ពៃ',             'vegetable'],
    ['Banana Blossom',     'ផ្ការចេក',         'vegetable'],
    ['Rice Vermicelli',    'គុយទាវ',           'noodle'],
    ['Kroeung Paste',      'គ្រឿង',            'paste'],
    ['Makrut Lime',        'ក្រូចសើច',         'citrus'],
];

foreach ($ingredients as [$en, $kh, $cat]) {
    DB::execute(
        'INSERT IGNORE INTO `ingredients` (name_en, name_kh, category)
         VALUES (:name_en, :name_kh, :category)',
        [':name_en' => $en, ':name_kh' => $kh, ':category' => $cat]
    );
}
echo "  ✓ Ingredients (14)\n";

// ── Demo user ─────────────────────────────────────────────────────────────────
$hash = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 10]);
DB::execute(
    'INSERT IGNORE INTO `users` (name, email, password, bio)
     VALUES (:name, :email, :password, :bio)',
    [
        ':name'     => 'Rous Cheat Admin',
        ':email'    => 'admin@rouscheat.kh',
        ':password' => $hash,
        ':bio'      => 'Keeper of Khmer culinary heritage',
    ]
);
$user   = DB::queryOne('SELECT id FROM users WHERE email = :email', [':email' => 'admin@rouscheat.kh']);
$userId = (int)$user['id'];
echo "  ✓ Demo user → admin@rouscheat.kh / password123\n";

// ── Sample recipes ────────────────────────────────────────────────────────────
$recipes = [
    [
        'title_en'          => 'Fish Amok',
        'title_kh'          => 'អាម៉ុកត្រី',
        'description_en'    => "Cambodia's national dish — a delicate steamed fish curry mousse in coconut milk and kroeung paste, traditionally served in banana leaf cups.",
        'difficulty'        => 'medium',
        'cook_time_minutes' => 45,
        'prep_time_minutes' => 20,
        'servings'          => 4,
        'category_id'       => 1,
        'region_id'         => 2,
        'tags'              => ['national dish', 'fish', 'coconut', 'steamed'],
        'ingredients'       => [
            ['name' => 'Fresh fish (catfish or snapper)', 'amount' => '500g'],
            ['name' => 'Coconut milk',                   'amount' => '400ml'],
            ['name' => 'Kroeung paste',                  'amount' => '3 tbsp'],
            ['name' => 'Eggs',                           'amount' => '2'],
            ['name' => 'Fish sauce',                     'amount' => '2 tbsp'],
            ['name' => 'Palm sugar',                     'amount' => '1 tbsp'],
            ['name' => 'Kaffir lime leaves',             'amount' => '4 leaves'],
            ['name' => 'Banana leaves (for cups)',       'amount' => '6 pieces'],
        ],
        'instructions_en' => [
            'Prepare the banana leaf cups by cutting into circles and pinning edges with toothpicks.',
            'In a bowl, mix kroeung paste with coconut milk until smooth.',
            'Add beaten eggs, fish sauce, and palm sugar. Mix well.',
            'Fold in the sliced fish gently.',
            'Pour mixture into banana leaf cups and top with coconut cream.',
            'Steam over high heat for 20–25 minutes until set.',
            'Garnish with fresh kaffir lime leaves and red chili slices.',
        ],
    ],
    [
        'title_en'          => 'Kuy Teav',
        'title_kh'          => 'គុយទាវ',
        'description_en'    => "A beloved Cambodian breakfast noodle soup with clear pork bone broth, rice noodles, and an array of fresh garnishes.",
        'difficulty'        => 'easy',
        'cook_time_minutes' => 30,
        'prep_time_minutes' => 15,
        'servings'          => 2,
        'category_id'       => 2,
        'region_id'         => 1,
        'tags'              => ['breakfast', 'noodles', 'pork', 'soup'],
        'ingredients'       => [
            ['name' => 'Rice vermicelli',        'amount' => '200g'],
            ['name' => 'Pork bones (for broth)', 'amount' => '500g'],
            ['name' => 'Bean sprouts',           'amount' => '100g'],
            ['name' => 'Spring onions',          'amount' => '3 stalks'],
            ['name' => 'Garlic',                 'amount' => '4 cloves'],
            ['name' => 'Fish sauce',             'amount' => '3 tbsp'],
        ],
        'instructions_en' => [
            'Simmer pork bones in water for 2 hours with garlic and salt for clear broth.',
            'Soak rice noodles in cold water for 20 minutes, then blanch in boiling water.',
            'Strain the broth and season with fish sauce.',
            'Place noodles in bowl, ladle hot broth over.',
            'Top with bean sprouts, spring onions, and your choice of garnishes.',
        ],
    ],
    [
        'title_en'          => 'Samlor Korko',
        'title_kh'          => 'សម្លកកូរ',
        'description_en'    => 'A rustic Cambodian mixed vegetable soup with pork and roasted kroeung — considered one of the oldest Khmer dishes.',
        'difficulty'        => 'hard',
        'cook_time_minutes' => 60,
        'prep_time_minutes' => 30,
        'servings'          => 6,
        'category_id'       => 1,
        'region_id'         => 3,
        'tags'              => ['traditional', 'vegetable', 'pork', 'heritage'],
        'ingredients'       => [
            ['name' => 'Pork ribs',               'amount' => '400g'],
            ['name' => 'Unripe green papaya',     'amount' => '200g'],
            ['name' => 'Banana blossom',          'amount' => '1 flower'],
            ['name' => 'Long beans',              'amount' => '100g'],
            ['name' => 'Kroeung paste',           'amount' => '4 tbsp'],
            ['name' => 'Prahok (fermented fish)', 'amount' => '2 tbsp'],
            ['name' => 'Lemongrass',              'amount' => '2 stalks'],
        ],
        'instructions_en' => [
            'Roast the kroeung paste in a dry pan until fragrant.',
            'Boil pork ribs until tender, about 30 minutes.',
            'Add roasted kroeung and prahok to the broth.',
            'Add green papaya and banana blossom; simmer 15 minutes.',
            'Add long beans and remaining vegetables. Cook 10 more minutes.',
            'Adjust seasoning with fish sauce. Serve with rice.',
        ],
    ],
    [
        'title_en'          => 'Lok Lak',
        'title_kh'          => 'លីកឡាក់',
        'description_en'    => 'Stir-fried marinated beef cubes served over lettuce with a tangy Kampot pepper dipping sauce and fried egg.',
        'difficulty'        => 'easy',
        'cook_time_minutes' => 20,
        'prep_time_minutes' => 30,
        'servings'          => 2,
        'category_id'       => 7,
        'region_id'         => 6,
        'tags'              => ['beef', 'stir-fry', 'Kampot pepper', 'popular'],
        'ingredients'       => [
            ['name' => 'Beef tenderloin', 'amount' => '300g'],
            ['name' => 'Oyster sauce',    'amount' => '2 tbsp'],
            ['name' => 'Kampot pepper',   'amount' => '1 tsp'],
            ['name' => 'Lime juice',      'amount' => '2 tbsp'],
            ['name' => 'Romaine lettuce', 'amount' => '1 head'],
            ['name' => 'Eggs',            'amount' => '2'],
            ['name' => 'Tomatoes',        'amount' => '2'],
        ],
        'instructions_en' => [
            'Cut beef into 2cm cubes. Marinate with oyster sauce, soy sauce, and sugar for 30 minutes.',
            'Make the dipping sauce: mix lime juice, salt, and cracked Kampot pepper.',
            'Arrange lettuce, sliced tomatoes, and cucumber on a plate.',
            'Fry eggs sunny-side up.',
            'In a hot wok, quickly stir-fry beef cubes on high heat for 3–4 minutes.',
            'Place beef over the salad, top with egg, serve with dipping sauce.',
        ],
    ],
    [
        'title_en'          => 'Nom Banh Chok',
        'title_kh'          => 'នំបញ្ចុក',
        'description_en'    => "Cambodia's beloved morning noodle dish — fresh rice noodles topped with green fish curry sauce and seasonal vegetables.",
        'difficulty'        => 'medium',
        'cook_time_minutes' => 40,
        'prep_time_minutes' => 20,
        'servings'          => 4,
        'category_id'       => 2,
        'region_id'         => 1,
        'tags'              => ['breakfast', 'noodles', 'fish', 'green curry'],
        'ingredients'       => [
            ['name' => 'Fresh rice noodles',        'amount' => '400g'],
            ['name' => 'Fish fillets',              'amount' => '300g'],
            ['name' => 'Lemongrass',                'amount' => '3 stalks'],
            ['name' => 'Turmeric',                  'amount' => '1 tsp'],
            ['name' => 'Bean sprouts',              'amount' => '150g'],
            ['name' => 'Banana blossom (shredded)', 'amount' => '100g'],
        ],
        'instructions_en' => [
            'Blend lemongrass, turmeric, galangal into a green paste.',
            'Simmer fish in water until cooked. Remove, shred, and keep broth.',
            'Fry the green paste in oil until aromatic.',
            'Add fish broth and shredded fish. Simmer 15 minutes.',
            'Serve over fresh rice noodles topped with banana blossom, bean sprouts, and fresh herbs.',
        ],
    ],
];

foreach ($recipes as $r) {
    DB::execute(
        'INSERT IGNORE INTO `recipes`
            (user_id, category_id, region_id,
             title_en, title_kh, description_en,
             difficulty, cook_time_minutes, prep_time_minutes, servings,
             ingredients, instructions_en, tags)
         VALUES
            (:user_id, :category_id, :region_id,
             :title_en, :title_kh, :description_en,
             :difficulty, :cook_time_minutes, :prep_time_minutes, :servings,
             :ingredients, :instructions_en, :tags)',
        [
            ':user_id'            => $userId,
            ':category_id'        => $r['category_id'],
            ':region_id'          => $r['region_id'] ?? null,
            ':title_en'           => $r['title_en'],
            ':title_kh'           => $r['title_kh'],
            ':description_en'     => $r['description_en'] ?? null,
            ':difficulty'         => $r['difficulty'],
            ':cook_time_minutes'  => $r['cook_time_minutes'],
            ':prep_time_minutes'  => $r['prep_time_minutes'] ?? 0,
            ':servings'           => $r['servings'],
            ':ingredients'        => json_encode($r['ingredients'],    JSON_UNESCAPED_UNICODE),
            ':instructions_en'    => json_encode($r['instructions_en'], JSON_UNESCAPED_UNICODE),
            ':tags'               => json_encode($r['tags'] ?? [],      JSON_UNESCAPED_UNICODE),
        ]
    );
    echo "  ✓ Recipe: {$r['title_en']} ({$r['title_kh']})\n";
}

echo "\n✅ Seeding complete!\n";
echo "   Login: admin@rouscheat.kh / password123\n";
