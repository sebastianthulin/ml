# Data Expansion Plan

## Current Categories
The following categories are present in the `data.csv` file:

- boende
- bygglov
- kartor
- stadsplanering
- trafik
- infrastruktur
- miljö
- hållbarhet
- utbildning
- jobb & näringsliv
- företag
- fritid & evenemang
- kultur
- idrott & motion
- vård & omsorg
- stöd & hjälp
- barn & ungdom
- äldreomsorg
- funktionsnedsättning
- kris & beredskap
- statistik & fakta
- e-tjänster
- kontakt & service
- tillgänglighet
- ekonomi & skatter
- exploatering & mark
- digitalisering
- samhällsbyggnad
- besökare & turism
- politik & styrning

## Expansion Plan

### Total Rows Needed
The target is 5000 rows. With 29 categories, this means approximately 172 rows per category.

### Current Row Count
The current file has 283 rows. This means we need to generate an additional 4717 rows.

### Rows to Add Per Category
Each category will be expanded to 172 rows. If a category already has more than 172 rows, it will not be expanded further.

### Sentence Generation
Sentences will be generated to ensure variety and relevance to each category. Examples include:

- For `boende`: "Tips för att hitta studentbostad i kommunen."
- For `bygglov`: "Bygglov för att installera solpaneler."
- For `kartor`: "Interaktiva kartor för naturreservat."

### Implementation
1. Analyze the current row count per category.
2. Generate additional sentences for underrepresented categories.
3. Append the new sentences to `data.csv`.

### Notes
- Ensure no duplicate sentences.
- Maintain consistent formatting.
- Validate the final file for correctness.