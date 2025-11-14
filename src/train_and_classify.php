<?php
require __DIR__ . '/../vendor/autoload.php';

use Phpml\SupportVectorMachine\Kernel;
use Phpml\Classification\SVC;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\ModelManager;

$modelFile = __DIR__ . '/../model/bld_text_classifier.model';
@mkdir(dirname($modelFile), 0777, true);

/**
 * Tränar en ny modell och sparar till fil
 */
function trainModel(string $modelFile): void
{
    echo "🔧 Tränar ny modell...\n";

    // === Läs träningsdata från CSV ===
    $csvFile = __DIR__ . '/../training/data.csv';
    if (!file_exists($csvFile)) {
        throw new \Exception("Träningsfilen $csvFile saknas!");
    }
    $texts = [];
    $labels = [];
    if (($handle = fopen($csvFile, 'r')) !== false) {
        $header = fgetcsv($handle); // skip header
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 2) {
                $texts[] = $row[0];
                $labels[] = $row[1];
            }
        }
        fclose($handle);
    } else {
        throw new \Exception("Kunde inte läsa $csvFile");
    }

    // === Vectorize texts ===
    $vectorizer = new TokenCountVectorizer(new WordTokenizer());
    $vectorizer->fit($texts);
    $vectorizer->transform($texts);

    // === Train SVC with probability estimation ===
    $classifier = new SVC(
        Kernel::RBF,
        $cost = 1000,
        $degree = 3,
        $gamma = null,
        $coef0 = 0.0,
        $tolerance = 0.001,
        $cacheSize = 100,
        $shrinking = true,
        $probabilityEstimates = true
    );
    $classifier->train($texts, $labels);

    // === Save classifier and vectorizer separately ===
    $modelManager = new ModelManager();
    $modelManager->saveToFile($classifier, $modelFile . '_classifier');

    file_put_contents($modelFile . '_vectorizer', serialize($vectorizer));

    echo "✅ Modell tränad och sparad till $modelFile\n";
}

/**
 * Laddar modell om den finns
 */
function loadModel(string $modelFile): array
{
    if (!file_exists($modelFile . '_classifier') || !file_exists($modelFile . '_vectorizer')) {
        throw new \Exception("Modellfiler saknas!");
    }

    $modelManager = new ModelManager();
    $classifier = $modelManager->restoreFromFile($modelFile . '_classifier');
    $vectorizer = unserialize(file_get_contents($modelFile . '_vectorizer'));

    return [$classifier, $vectorizer];
}

/**
 * Klassificerar text med sannolikheter och flera kategorier
 */
function classifyWithProbabilities(string $text, SVC $classifier, TokenCountVectorizer $vectorizer): array
{
    $samples = [$text]; // Assign to a variable to pass by reference
    $vectorizer->transform($samples);
    $probabilities = $classifier->predictProbability($samples)[0];
    arsort($probabilities); // Sort categories by probability in descending order
    return $probabilities;
}

// === CLI-hantering ===
$argv = $_SERVER['argv'] ?? [];

if (in_array('--train', $argv)) {
    trainModel($modelFile);
    exit;
}

list($classifier, $vectorizer) = loadModel($modelFile);

// === Om textargument ges (CLI) ===
if (isset($argv[1]) && $argv[1] !== '--train') {
    $input = $argv[1];
    $result = classifyWithProbabilities($input, $classifier, $vectorizer);

    // Prepare table header
    echo "+----------------------+-------------------+\n";
    echo "| Category             | Probability       |\n";
    echo "+----------------------+-------------------+\n";

    // Output each category and probability
    // Helper for multibyte string padding
    if (!function_exists('mb_str_pad')) {
        function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = 'UTF-8') {
            $diff = strlen($input) - mb_strlen($input, $encoding);
            return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
        }
    }

    foreach ($result as $category => $probability) {
        $cat = mb_str_pad($category, 20);
        $prob = str_pad(number_format($probability, 4), 17);
        echo "| $cat | $prob |\n";
    }
    echo "+----------------------+-------------------+\n";
    exit;
}

// === Annars: visa demoexempel ===
echo "🔍 Exempelklassificeringar:\n";

$tests = [
    'Ansök om bygglov för altan',
    'Kommunen bygger ny bro i centrum',
    'Information om sophämtning och återvinning',
    'Plan för hållbar energi och grön utveckling',
    'Digitalt formulär för ansökan om bidrag',
];

foreach ($tests as $t) {
    $result = classifyWithProbabilities($t, $classifier, $vectorizer);
    $prominent = array_key_first($result);
    echo "🟢 {$t}\n → Prominent: $prominent\n\n";
}