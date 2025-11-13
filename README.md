# ML Text Classifier - Dataset Expansion Project

This project contains a Swedish municipal text classifier that categorizes citizen queries into 30 different municipal service categories.

## Quick Start

### Train the Model
```bash
php src/train_and_classify.php --train
```

### Classify Text
```bash
php bin/classify "Your Swedish text here"
```

### Test Model Accuracy
```bash
php src/test_model.php
```

## Project Overview

This ML model classifies Swedish text queries about municipal services into 30 categories including:
- boende (housing)
- bygglov (building permits)
- trafik (traffic)
- miljö (environment)
- utbildning (education)
- vård & omsorg (healthcare)
- And 24 more categories

## Recent Improvements

**Dataset Expansion Initiative** (See `DATASET_EXPANSION_REPORT.md` for full details)

- ✅ Expanded training data from 210 to 1,427 examples
- ✅ Improved accuracy from 18.22% to 67.56% (+271%)
- ✅ Created comprehensive test suite with 450 test cases
- ✅ Built automated testing framework

## Model Performance

**Overall Accuracy**: 67.56% (450 test cases)

**Top Performing Categories** (>80% accuracy):
- e-tjänster: 100%
- politik & styrning: 90%
- utbildning: 86.7%
- kartor: 85%
- boende: 80%

## Example Usage

```bash
# Building permit question
$ php bin/classify "Hur ansöker jag om bygglov för garage?"
Prominent Category: bygglov

# Recycling question  
$ php bin/classify "Var kan jag återvinna elektronik?"
Prominent Category: miljö

# Education question
$ php bin/classify "Jag vill läsa komvux kurser"
Prominent Category: utbildning

# E-services question
$ php bin/classify "Hur loggar jag in på mina sidor?"
Prominent Category: e-tjänster
```

## Files

### Training Data
- `training/data.csv` - Main training dataset (1,427 examples)
- `training/test_strings.csv` - Test cases for accuracy evaluation (450 examples)
- `training/data_old_backup.csv` - Original dataset backup (210 examples)

### Model Files
- `model/bld_text_classifier.model_classifier` - Trained SVC classifier
- `model/bld_text_classifier.model_vectorizer` - Token count vectorizer

### Source Code
- `src/train_and_classify.php` - Training and classification logic
- `src/test_model.php` - Automated testing and accuracy measurement
- `bin/classify` - Command-line classification tool

### Documentation
- `DATASET_EXPANSION_REPORT.md` - Detailed report on improvement initiative
- `training/data_expansion_plan.md` - Original expansion planning document

## Technology Stack

- **Language**: PHP 8.3+
- **ML Library**: php-ai/php-ml v0.10
- **Algorithm**: Support Vector Classification (SVC) with RBF kernel
- **Features**: Token Count Vectorization with Word Tokenizer

## Further Improvements

To reach 80%+ accuracy, consider:
1. Adding 50+ more examples per weak category
2. Improving disambiguation between confused categories
3. Tuning SVC hyperparameters (cost, gamma)
4. Experimenting with TF-IDF vectorization
5. Considering multi-label classification for overlapping categories

See `DATASET_EXPANSION_REPORT.md` for detailed recommendations.

## Testing Methodology

The automated test suite (`src/test_model.php`) evaluates the model by:
1. Loading 450 diverse test cases covering all 30 categories
2. Classifying each test case
3. Comparing predictions against expected categories
4. Calculating overall and per-category accuracy
5. Identifying common misclassification patterns
6. Providing actionable improvement recommendations

## License

This is an internal municipal text classification project.
