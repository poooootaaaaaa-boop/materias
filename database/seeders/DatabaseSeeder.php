<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Activity;
use App\Models\DifficultyLevel;
use App\Models\Subject;
use App\Models\Purchase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuarios del sistema
        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin Principal',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'role' => 'admin',
        ]);

        User::updateOrCreate(['email' => 'padre@example.com'], [
            'name' => 'Laura Cárdenas',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'role' => 'parent',
        ]);

        User::updateOrCreate(['email' => 'nino@example.com'], [
            'name' => 'Sofía Cárdenas',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'role' => 'child',
            'age' => 9,
        ]);

        User::updateOrCreate(['email' => 'teacher@example.com'], [
            'name' => 'Maestra Demo',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'role' => 'teacher',
        ]);

        // 2. Niveles de dificultad
        $levels = collect([
            ['name' => 'Pequeños', 'slug' => 'pequenos', 'min_age' => 5, 'max_age' => 7],
            ['name' => 'Grandes', 'slug' => 'grandes', 'min_age' => 8, 'max_age' => 11],
        ])->mapWithKeys(fn (array $data) => [$data['slug'] => DifficultyLevel::updateOrCreate(['slug' => $data['slug']], $data)]);

        // 3. Materias (Normales y Especiales de Isla del Saber)
        $subjectsData = [
            'math' => [
                'name' => 'Matemáticas',
                'slug' => 'math',
                'emoji' => '🔢',
                'accent' => '#F2A93B',
                'accent_dark' => '#8A5A0E',
                'bg' => '#FFE9A8',
                'type' => 'Normal',
            ],
            'spanish' => [
                'name' => 'Español',
                'slug' => 'spanish',
                'emoji' => '📖',
                'accent' => '#9B6FD1',
                'accent_dark' => '#57398C',
                'bg' => '#E4D4F7',
                'type' => 'Normal',
            ],
            'english' => [
                'name' => 'Inglés',
                'slug' => 'english',
                'emoji' => '🌎',
                'accent' => '#4FA9DB',
                'accent_dark' => '#185E80',
                'bg' => '#CFEBFA',
                'type' => 'Normal',
            ],
            'ruso' => [
                'name' => 'Ruso: abecedario y palabras',
                'slug' => 'ruso',
                'emoji' => '🇷🇺',
                'accent' => '#318E7D',
                'accent_dark' => '#1F5E51',
                'bg' => '#EAF7F4',
                'type' => 'Especial',
                'price' => 99,
                'description' => 'Aprende el alfabeto cirílico, su sonido, palabras básicas y asociaciones con objetos.',
            ],
            'musica' => [
                'name' => 'Música: aprende las notas',
                'slug' => 'musica',
                'emoji' => '🎼',
                'accent' => '#9B6FD1',
                'accent_dark' => '#57398C',
                'bg' => '#F2EBFF',
                'type' => 'Especial',
                'price' => 129,
                'description' => 'Practica las notas en un piano mientras sigues las indicaciones del director de orquesta.',
            ],
        ];

        $subjects = [];
        foreach ($subjectsData as $slug => $data) {
            $subjects[$slug] = Subject::updateOrCreate(['slug' => $slug], $data);
        }

        // Limpiar actividades para re-poblar limpiamente según IslaDelSaber (2).jsx
        Activity::truncate();

        // 4. Actividades de Matemáticas (10 actividades exactas de IslaDelSaber (2).jsx)
        $mathActivities = [
            [
                'type' => 'choice',
                'question' => "🍎 Cuenta las manzanas\n\n🍎 🍎 🍎 🍎",
                'options' => ['3', '4', '5'],
                'answer' => '4',
                'data' => ['correct' => 1],
            ],
            [
                'type' => 'fillnum',
                'question' => '➕ Completa la suma',
                'answer' => '4',
                'data' => ['expr' => '🔲 + 3 = 7', 'answer' => 4],
            ],
            [
                'type' => 'choice',
                'question' => '🔺 ¿Cuál de estas es un triángulo?',
                'options' => ['🔵', '🔺', '⭐'],
                'answer' => '🔺',
                'data' => ['correct' => 1],
            ],
            [
                'type' => 'fillnum',
                'question' => '➖ Completa la resta',
                'answer' => '2',
                'data' => ['expr' => '5 − 🔲 = 3', 'answer' => 2],
            ],
            [
                'type' => 'order',
                'kind' => 'Ordena los números',
                'question' => '🔢 Ordena estos números de menor a mayor',
                'answer' => '1,2,4,5',
                'data' => [
                    'clue' => 'Toca los números en el orden correcto',
                    'tokens' => ['1', '2', '4', '5'],
                ],
            ],
            [
                'type' => 'match',
                'kind' => 'Relaciona',
                'question' => '🔗 Relaciona cada número con su cantidad',
                'data' => [
                    'pairs' => [['2', '🍎🍎'], ['3', '🍎🍎🍎'], ['4', '🍎🍎🍎🍎']],
                ],
            ],
            [
                'type' => 'choice',
                'kind' => 'Significado',
                'question' => '➕ ¿Qué signo usamos para sumar?',
                'options' => ['+', '−', 'x'],
                'answer' => '+',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'fillnum',
                'question' => '➖ Completa la resta',
                'answer' => '4',
                'data' => ['expr' => '6 − 2 = 🔲', 'answer' => 4],
            ],
            [
                'type' => 'order',
                'kind' => 'Ordena los números',
                'question' => '🔢 Ordena estos números de menor a mayor',
                'answer' => '3,6,7,9',
                'data' => [
                    'clue' => 'Toca los números en el orden correcto',
                    'tokens' => ['3', '6', '7', '9'],
                ],
            ],
            [
                'type' => 'fillnum',
                'question' => '🏆 Súper reto: completa la operación',
                'answer' => '7',
                'data' => ['expr' => '4 + 3 = 🔲', 'answer' => 7],
            ],
        ];

        foreach ($mathActivities as $idx => $act) {
            Activity::create([
                'subject_id' => $subjects['math']->id,
                'difficulty_level_id' => $levels['grandes']->id,
                'type' => $act['type'],
                'kind' => $act['kind'] ?? null,
                'question' => $act['question'],
                'options' => $act['options'] ?? null,
                'answer' => $act['answer'] ?? null,
                'data' => $act['data'] ?? null,
                'order_num' => $idx + 1,
                'active' => true,
            ]);
        }

        // 5. Actividades de Español (10 actividades exactas de IslaDelSaber (2).jsx)
        $spanishActivities = [
            [
                'type' => 'choice',
                'question' => "🔤 Completa la palabra\n\n🐱 G _ T O",
                'options' => ['A', 'E', 'U'],
                'answer' => 'A',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'order',
                'kind' => 'Ordena las letras',
                'question' => '🧩 Ordena las letras',
                'answer' => 'CASA',
                'data' => ['tokens' => ['C', 'A', 'S', 'A'], 'word' => 'CASA'],
            ],
            [
                'type' => 'choice',
                'question' => '🔎 Encuentra la palabra SOL',
                'options' => ['GATO', 'SOL', 'CASA'],
                'answer' => 'SOL',
                'data' => ['correct' => 1],
            ],
            [
                'type' => 'choice',
                'kind' => 'Comprensión',
                'question' => "📖 Toby salió al parque y encontró una pelota.\n\n¿Dónde fue Toby?",
                'options' => ['Casa', 'Parque', 'Escuela'],
                'answer' => 'Parque',
                'data' => ['correct' => 1],
            ],
            [
                'type' => 'order',
                'kind' => 'Ordena las letras',
                'question' => '🏆 Súper reto: ordena las letras',
                'answer' => 'PERRO',
                'data' => ['tokens' => ['P', 'E', 'R', 'R', 'O'], 'word' => 'PERRO'],
            ],
            [
                'type' => 'choice',
                'kind' => 'Significado',
                'question' => '📖 ¿Qué significa "veloz"?',
                'options' => ['Rápido', 'Lento', 'Grande'],
                'answer' => 'Rápido',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'match',
                'kind' => 'Relaciona (sinónimos)',
                'question' => '🔗 Relaciona cada palabra con su sinónimo',
                'data' => [
                    'pairs' => [['FELIZ', 'CONTENTO'], ['TRISTE', 'APENADO'], ['GRANDE', 'ENORME']],
                ],
            ],
            [
                'type' => 'choice',
                'kind' => 'Escucha y responde',
                'question' => "🔊 Escucha con atención\n\n¿Qué animal escuchaste?",
                'speak' => 'gato',
                'speak_lang' => 'es-ES',
                'options' => ['Gato', 'Perro', 'Pez'],
                'answer' => 'Gato',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'order',
                'kind' => 'Crucigrama',
                'question' => '🧩 Resuelve el crucigrama',
                'answer' => 'SOL',
                'data' => [
                    'clue' => 'Astro que nos da luz durante el día',
                    'tokens' => ['S', 'O', 'L'],
                    'answer' => 'SOL',
                ],
            ],
            [
                'type' => 'match',
                'kind' => 'Relaciona (antónimos)',
                'question' => '🔗 Relaciona cada palabra con su antónimo',
                'data' => [
                    'pairs' => [['ALTO', 'BAJO'], ['RÁPIDO', 'LENTO'], ['DÍA', 'NOCHE']],
                ],
            ],
        ];

        foreach ($spanishActivities as $idx => $act) {
            Activity::create([
                'subject_id' => $subjects['spanish']->id,
                'difficulty_level_id' => $levels['grandes']->id,
                'type' => $act['type'],
                'kind' => $act['kind'] ?? null,
                'question' => $act['question'],
                'options' => $act['options'] ?? null,
                'answer' => $act['answer'] ?? null,
                'speak' => $act['speak'] ?? null,
                'speak_lang' => $act['speak_lang'] ?? null,
                'data' => $act['data'] ?? null,
                'order_num' => $idx + 1,
                'active' => true,
            ]);
        }

        // 6. Actividades de Inglés (10 actividades exactas de IslaDelSaber (2).jsx)
        $englishActivities = [
            [
                'type' => 'choice',
                'kind' => 'Escucha y responde',
                'question' => '🐶 ¿Cómo se dice "perro"?',
                'speak' => 'dog',
                'speak_lang' => 'en-US',
                'options' => ['DOG', 'CAT', 'COW'],
                'answer' => 'DOG',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'choice',
                'kind' => 'Escucha y responde',
                'question' => '🔊 Escucha y toca la imagen',
                'speak' => 'apple',
                'speak_lang' => 'en-US',
                'options' => ['🍎', '🍌', '🐶'],
                'answer' => '🍎',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'match',
                'kind' => 'Relaciona',
                'question' => '🔗 Une cada palabra en español con su significado en inglés',
                'data' => [
                    'pairs' => [['PERRO', 'DOG'], ['GATO', 'CAT'], ['CASA', 'HOUSE'], ['SOL', 'SUN']],
                ],
            ],
            [
                'type' => 'choice',
                'question' => "🎨 BLUE\n\n¿Cuál es azul?",
                'options' => ['🔵', '🟡', '🔴'],
                'answer' => '🔵',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'choice',
                'kind' => 'Significado',
                'question' => "👋 HELLO!\n\n¿Qué significa?",
                'options' => ['Hola', 'Buenas noches', 'Adiós'],
                'answer' => 'Hola',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'order',
                'kind' => 'Ordena en inglés',
                'question' => '🧩 Ordena las letras para formar la palabra en inglés (gato)',
                'answer' => 'CAT',
                'data' => ['tokens' => ['C', 'A', 'T'], 'word' => 'CAT'],
            ],
            [
                'type' => 'choice',
                'kind' => 'Significado',
                'question' => '📖 What does "big" mean?',
                'options' => ['Grande', 'Pequeño', 'Rápido'],
                'answer' => 'Grande',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'choice',
                'kind' => 'Escucha y responde',
                'question' => "🔊 Listen carefully\n\nWhat animal is this?",
                'speak' => 'cat',
                'speak_lang' => 'en-US',
                'options' => ['Cat', 'Dog', 'Bird'],
                'answer' => 'Cat',
                'data' => ['correct' => 0],
            ],
            [
                'type' => 'order',
                'kind' => 'Crucigrama',
                'question' => '🧩 Resuelve el crucigrama',
                'answer' => 'SUN',
                'data' => [
                    'clue' => 'Da luz durante el día en inglés',
                    'tokens' => ['S', 'U', 'N'],
                    'answer' => 'SUN',
                ],
            ],
            [
                'type' => 'choice',
                'kind' => 'Significado',
                'question' => '🌎 ¿Cómo se dice "gracias" en inglés?',
                'options' => ['Thanks', 'Sorry', 'Please'],
                'answer' => 'Thanks',
                'data' => ['correct' => 0],
            ],
        ];

        foreach ($englishActivities as $idx => $act) {
            Activity::create([
                'subject_id' => $subjects['english']->id,
                'difficulty_level_id' => $levels['grandes']->id,
                'type' => $act['type'],
                'kind' => $act['kind'] ?? null,
                'question' => $act['question'],
                'options' => $act['options'] ?? null,
                'answer' => $act['answer'] ?? null,
                'speak' => $act['speak'] ?? null,
                'speak_lang' => $act['speak_lang'] ?? null,
                'data' => $act['data'] ?? null,
                'order_num' => $idx + 1,
                'active' => true,
            ]);
        }

        // 7. Actividades de Ruso (18 actividades de RUSSIAN_ACTIVITIES)
        $russianActivities = [
            ['kind' => 'letter', 'title' => 'Reconoce la letra А', 'letter' => 'А', 'sound' => 'a', 'word' => 'арбуз', 'meaning' => 'sandía', 'emoji' => '🍉'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra Б', 'letter' => 'Б', 'sound' => 'b', 'word' => 'банан', 'meaning' => 'plátano', 'emoji' => '🍌'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra В', 'letter' => 'В', 'sound' => 'v', 'word' => 'вода', 'meaning' => 'agua', 'emoji' => '💧'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra Д', 'letter' => 'Д', 'sound' => 'd', 'word' => 'дом', 'meaning' => 'casa', 'emoji' => '🏠'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra Ж', 'letter' => 'Ж', 'sound' => 'zh', 'word' => 'жук', 'meaning' => 'escarabajo', 'emoji' => '🪲'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra К', 'letter' => 'К', 'sound' => 'k', 'word' => 'корабль', 'meaning' => 'barco', 'emoji' => '🚢'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra М', 'letter' => 'М', 'sound' => 'm', 'word' => 'море', 'meaning' => 'mar', 'emoji' => '🌊'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra П', 'letter' => 'П', 'sound' => 'p', 'word' => 'паровоз', 'meaning' => 'tren', 'emoji' => '🚂'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra С', 'letter' => 'С', 'sound' => 's', 'word' => 'самолёт', 'meaning' => 'avión', 'emoji' => '✈️'],
            ['kind' => 'letter', 'title' => 'Reconoce la letra Т', 'letter' => 'Т', 'sound' => 't', 'word' => 'телефон', 'meaning' => 'teléfono', 'emoji' => '📱'],
            ['kind' => 'meaning', 'title' => 'Acomoda el significado: avión', 'word' => 'самолёт', 'meaning' => 'avión', 'emoji' => '✈️'],
            ['kind' => 'meaning', 'title' => 'Acomoda el significado: barco', 'word' => 'корабль', 'meaning' => 'barco', 'emoji' => '🚢'],
            ['kind' => 'meaning', 'title' => 'Acomoda el significado: tren', 'word' => 'поезд', 'meaning' => 'tren', 'emoji' => '🚆'],
            ['kind' => 'order', 'title' => 'Ordena las letras de ДОМ', 'word' => 'ДОМ', 'pieces' => ['Д', 'О', 'М']],
            ['kind' => 'order', 'title' => 'Ordena las letras de МИР', 'word' => 'МИР', 'pieces' => ['М', 'И', 'Р']],
            ['kind' => 'audio', 'title' => 'Escucha y relaciona', 'word' => 'мама', 'meaning' => 'mamá', 'emoji' => '👩'],
            ['kind' => 'audio', 'title' => 'Escucha y relaciona', 'word' => 'папа', 'meaning' => 'papá', 'emoji' => '👨'],
            ['kind' => 'audio', 'title' => 'Escucha y relaciona', 'word' => 'кот', 'meaning' => 'gato', 'emoji' => '🐱'],
        ];

        foreach ($russianActivities as $idx => $act) {
            Activity::create([
                'subject_id' => $subjects['ruso']->id,
                'difficulty_level_id' => $levels['grandes']->id,
                'type' => $act['kind'] === 'order' ? 'order' : ($act['kind'] === 'audio' ? 'audio' : 'choice'),
                'kind' => $act['kind'],
                'question' => $act['title'],
                'answer' => $act['meaning'] ?? $act['word'] ?? null,
                'speak' => $act['sound'] ?? $act['word'] ?? null,
                'speak_lang' => 'ru-RU',
                'data' => $act,
                'order_num' => $idx + 1,
                'active' => true,
            ]);
        }

        // 8. Compras demo
        Purchase::updateOrCreate(['id' => 1], [
            'parent_name' => 'Laura Cárdenas',
            'child_name' => 'Sofía Cárdenas',
            'item' => '50 puntos',
            'amount' => 49,
            'status' => 'Pagado',
            'created_at' => now()->subDays(4),
        ]);
        Purchase::updateOrCreate(['id' => 2], [
            'parent_name' => 'Laura Cárdenas',
            'child_name' => 'Sofía Cárdenas',
            'item' => 'Materia Ruso',
            'amount' => 99,
            'status' => 'Pagado',
            'created_at' => now()->subDays(5),
        ]);
        Purchase::updateOrCreate(['id' => 3], [
            'parent_name' => 'Carlos Pérez',
            'child_name' => 'Mateo Pérez',
            'item' => 'Materia Música',
            'amount' => 129,
            'status' => 'Pagado',
            'created_at' => now()->subDays(6),
        ]);
    }
}
