<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/train_and_classify.php';

/**
 * Test the model against a set of test strings and evaluate accuracy
 */
function testModel(string $modelFile, string $testFile): array
{
    // Load model
    echo "📊 Laddar modell...\n";
    list($classifier, $vectorizer) = loadModel($modelFile);
    
    // Load test data
    echo "📋 Läser testdata från $testFile...\n";
    $tests = [];
    if (($handle = fopen($testFile, 'r')) !== false) {
        $header = fgetcsv($handle); // skip header
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 2) {
                $tests[] = [
                    'text' => $row[0],
                    'expected' => $row[1]
                ];
            }
        }
        fclose($handle);
    }
    
    echo "📝 Testar " . count($tests) . " testfall...\n\n";
    
    // Test each case
    $correct = 0;
    $total = count($tests);
    $categoryStats = [];
    $failures = [];
    
    foreach ($tests as $test) {
        $result = classifyWithProbabilities($test['text'], $classifier, $vectorizer);
        $predicted = array_key_first($result);
        $confidence = $result[$predicted];
        
        $isCorrect = ($predicted === $test['expected']);
        
        // Track stats per category
        if (!isset($categoryStats[$test['expected']])) {
            $categoryStats[$test['expected']] = [
                'total' => 0,
                'correct' => 0,
                'accuracy' => 0
            ];
        }
        $categoryStats[$test['expected']]['total']++;
        
        if ($isCorrect) {
            $correct++;
            $categoryStats[$test['expected']]['correct']++;
        } else {
            // Track failures
            $failures[] = [
                'text' => $test['text'],
                'expected' => $test['expected'],
                'predicted' => $predicted,
                'confidence' => $confidence,
                'secondBest' => array_keys($result)[1] ?? 'N/A',
                'secondBestConf' => array_values($result)[1] ?? 0
            ];
        }
    }
    
    // Calculate accuracy per category
    foreach ($categoryStats as $category => &$stats) {
        $stats['accuracy'] = ($stats['total'] > 0) 
            ? ($stats['correct'] / $stats['total']) * 100 
            : 0;
    }
    
    $overallAccuracy = ($total > 0) ? ($correct / $total) * 100 : 0;
    
    return [
        'total' => $total,
        'correct' => $correct,
        'accuracy' => $overallAccuracy,
        'categoryStats' => $categoryStats,
        'failures' => $failures
    ];
}

// Main execution
$modelFile = __DIR__ . '/../model/bld_text_classifier.model';
$testFile = __DIR__ . '/../training/test_strings.csv';

if (!file_exists($testFile)) {
    echo "❌ Testfilen $testFile saknas!\n";
    exit(1);
}

$results = testModel($modelFile, $testFile);

// Print overall results
echo "═══════════════════════════════════════════════════\n";
echo "📊 SAMMANFATTNING AV TESTRESULTAT\n";
echo "═══════════════════════════════════════════════════\n";
echo sprintf("Totalt antal tester: %d\n", $results['total']);
echo sprintf("Korrekta: %d\n", $results['correct']);
echo sprintf("Felaktiga: %d\n", $results['total'] - $results['correct']);
echo sprintf("Noggrannhet: %.2f%%\n\n", $results['accuracy']);

// Print category statistics
echo "═══════════════════════════════════════════════════\n";
echo "📈 NOGGRANNHET PER KATEGORI\n";
echo "═══════════════════════════════════════════════════\n";

// Sort by accuracy
uasort($results['categoryStats'], function($a, $b) {
    return $b['accuracy'] <=> $a['accuracy'];
});

foreach ($results['categoryStats'] as $category => $stats) {
    $indicator = $stats['accuracy'] >= 80 ? '✅' : ($stats['accuracy'] >= 60 ? '⚠️' : '❌');
    echo sprintf(
        "%s %-30s: %5.1f%% (%d/%d)\n",
        $indicator,
        $category,
        $stats['accuracy'],
        $stats['correct'],
        $stats['total']
    );
}

// Print worst performing categories
echo "\n═══════════════════════════════════════════════════\n";
echo "⚠️  KATEGORIER MED LÅGS NOGGRANNHET (< 80%)\n";
echo "═══════════════════════════════════════════════════\n";

$needsImprovement = array_filter($results['categoryStats'], function($stats) {
    return $stats['accuracy'] < 80;
});

if (empty($needsImprovement)) {
    echo "✅ Alla kategorier har > 80% noggrannhet!\n";
} else {
    foreach ($needsImprovement as $category => $stats) {
        echo sprintf(
            "• %-30s: %5.1f%% - Behöver %d fler träningsexempel\n",
            $category,
            $stats['accuracy'],
            max(0, ceil($stats['total'] * 0.2)) // suggest 20% more examples
        );
    }
}

// Show sample failures
echo "\n═══════════════════════════════════════════════════\n";
echo "🔍 EXEMPEL PÅ FELKLASSIFICERINGAR (max 20)\n";
echo "═══════════════════════════════════════════════════\n";

$sampleFailures = array_slice($results['failures'], 0, 20);
foreach ($sampleFailures as $failure) {
    echo sprintf(
        "Text: \"%s\"\n  ❌ Förväntad: %s\n  🎯 Förutspådd: %s (%.2f)\n  💡 Andraval: %s (%.2f)\n\n",
        $failure['text'],
        $failure['expected'],
        $failure['predicted'],
        $failure['confidence'],
        $failure['secondBest'],
        $failure['secondBestConf']
    );
}

// Suggestions for improvement
echo "═══════════════════════════════════════════════════\n";
echo "💡 REKOMMENDATIONER\n";
echo "═══════════════════════════════════════════════════\n";

if ($results['accuracy'] < 70) {
    echo "❌ Modellen har låg noggrannhet (<70%). Behöver:\n";
    echo "   1. Lägg till minst 50-100 träningsexempel per kategori\n";
    echo "   2. Se till att varje kategori har balanserad representation\n";
    echo "   3. Granska felklassificeringar för att hitta mönster\n";
} elseif ($results['accuracy'] < 85) {
    echo "⚠️  Modellen är OK men kan förbättras (70-85%). Behöver:\n";
    echo "   1. Fokusera på kategorier med <80% noggrannhet\n";
    echo "   2. Lägg till fler variationer av felklassificerade texter\n";
    echo "   3. Balansera träningsdatan mellan kategorier\n";
} else {
    echo "✅ Modellen presterar bra (>85%)!\n";
    echo "   • Fortsätt övervaka och förbättra lågpresterande kategorier\n";
    echo "   • Lägg till edge cases vid behov\n";
}

echo "\n";
