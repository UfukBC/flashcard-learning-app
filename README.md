# Finnish Flash Card Application - Spaced Repetition

This project is a web application that uses the spaced repetition technique based on the SM-2 (SuperMemo 2) algorithm to learn Finnish words.

## 📋 Features

- ✅ Create and manage Flash Cards
- ✅ Spaced Repetition (SM-2 Algorithm)
- ✅ Support for Turkish, English and Finnish words
- ✅ Spelling practice
- ✅ Detailed learning statistics
- ✅ Responsive design
- ✅ Modern and user-friendly interface

## 🏗️ Project Structure

```
src/
├── Entity/
│   ├── FlashCard.php        # Flash Card model
│   └── LearningProgress.php # Learning progress model
├── Service/
│   └── SpacedRepetitionService.php  # SM-2 algorithm
└── Controller/
    └── FlashCardController.php      # API endpoints

public/
├── index.html              # Main page
├── css/
│   └── style.css          # Design file
└── js/
    └── app.js             # Frontend logic

data/                       # Data storage (JSON)
├── flash_cards.json
└── progress.json
```

## 🚀 Installation

### Requirements
- PHP 8.2+
- Composer
- Symfony 7.4

### Steps

1. Clone repository:
```bash
git clone <repo-url>
cd anki_clone
```

2. Install Composer packages:
```bash
composer install
```

3. Start application:
```bash
symfony serve
```

4. Open in browser:
```
http://localhost:8000
```

## 🎓 SM-2 Algorithm (Spaced Repetition)

### Answer Quality Scale

| Score | Description |
|-------|-------------|
| 0    | Didn't know at all |
| 1    | Incorrect answer but familiar |
| 2    | Incorrect answer but remembered the correct one |
| 3    | Correct answer but recalled with difficulty |
| 4    | Correct answer with hesitation |
| 5    | Perfect answer, remembered immediately |

### Algorithm Logic

```php
// Update Ease Factor (Difficulty)
EF' = EF + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02))

// Calculate Interval
I(1) = 1 day
I(2) = 3 days
I(n) = I(n-1) * EF

// If q < 3 (failed):
  - Interval = 1 day
  - Repetitions = 0
```

Where:
- **q**: Answer quality (0-5)
- **EF**: Ease Factor (initially 2.5, minimum 1.3)
- **I(n)**: Repetition interval (in days)

## 📡 API Endpoints

### Get Cards
```http
GET /api/cards
```
Returns all cards.

**Response:**
```json
[
  {
    "id": 1,
    "finnishWord": "kissa",
    "definition": "Four-legged domestic animal"
  }
]
```

### Create Card
```http
POST /api/cards/create
Content-Type: application/json

{
  "finnishWord": "kissa",
  "definition": "Four-legged domestic animal",
  "turkishMeaning": "Kedi",
  "englishMeaning": "Cat"
}
```

### Get Specific Card
```http
GET /api/cards/{id}
```

**Response:**
```json
{
  "id": 1,
  "finnishWord": "kissa",
  "definition": "Four-legged domestic animal",
  "turkishMeaning": "Kedi",
  "englishMeaning": "Cat"
}
```

### Get Cards Due for Review
```http
GET /api/review/due
```

Returns cards that need to be reviewed today.

**Response:**
```json
[
  {
    "id": 1,
    "finnishWord": "kissa",
    "definition": "Four-legged domestic animal",
    "turkishMeaning": "Kedi",
    "englishMeaning": "Cat",
    "progress": {
      "interval": 1,
      "repetitions": 0,
      "easeFactor": 2.5,
      "nextReviewDate": "2025-12-25"
    }
  }
]
```

### Submit Answer
```http
POST /api/review/answer
Content-Type: application/json

{
  "cardId": 1,
  "quality": 4,
  "userAnswer": "kissa"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Answer saved",
  "nextReviewDate": "2025-12-28",
  "newInterval": 3,
  "newEaseFactor": 2.62
}
```

### Get Statistics
```http
GET /api/statistics
```

**Response:**
```json
{
  "totalCards": 10,
  "dueCards": 3,
  "newCards": 2,
  "totalRepetitions": 45,
  "averageEaseFactor": 2.45,
  "averageInterval": 5.2,
  "completionRate": 45.0
}
```

## 🎨 User Interface

### Dashboard
- Total card count
- Cards to review
- New cards
- Completion rate
- Detailed statistics

### Review
- Flash card display
- Definition, Turkish and English meanings
- Spelling practice
- Answer quality selection (6 levels)
- Card information (interval, repetitions, etc.)

### Add Card
- Finnish word spelling
- Definition
- Turkish meaning
- English meaning

## 📊 Data Structure

### FlashCard Entity

```php
class FlashCard {
    private int $id;
    private string $finnishWord;      // Finnish word
    private string $definition;        // Definition
    private string $turkishMeaning;    // Turkish meaning
    private string $englishMeaning;    // English meaning
    private DateTime $createdAt;       // Creation date
    private DateTime $updatedAt;       // Update date
}
```

### LearningProgress Entity

```php
class LearningProgress {
    private int $id;
    private int $cardId;              // Related card
    private int $interval = 1;         // Next review interval
    private int $repetitions = 0;      // Repetition count
    private float $easeFactor = 2.5;   // Difficulty factor
    private DateTime $nextReviewDate;  // Next review date
    private int $quality = 0;          // Last answer quality
    private DateTime $lastReviewDate;  // Last review date
}
```

## 🎯 Learning Strategy

1. **Initial Learning**: New cards are repeated every day
2. **Consolidation**: Intervals increase after successful answers
3. **Long-term Memory**: SM-2 algorithm optimizes learning speed
4. **Difficult Cards**: Cards with low ease factor are repeated more frequently

## 💾 Data Storage

By default, data is stored in JSON files (`data/flash_cards.json` and `data/progress.json`).

### Database Integration

To use a database:

1. Install Doctrine ORM:
```bash
composer require symfony/orm-pack
```

2. Adapt entities for database operations
3. Create migration files
4. Update controller for database operations

## 🔧 Advanced Features

### Sort Cards by Difficulty
```php
$progressList = $spacedRepetitionService->sortByDifficulty($progressList);
```

### Count New Cards
```php
$newCount = $spacedRepetitionService->getNewCardsCount($progressList);
```

### Check if Card is Ready for Review
```php
if ($spacedRepetitionService->isCardDueForReview($progress)) {
    // Card is ready for review
}
```

## 📈 Performance Tips

1. **Daily Goal**: Learn 10-20 new cards per day
2. **Consistency**: Review daily
3. **Quality**: Honestly evaluate answer quality
4. **Patience**: Trust the algorithm, intervals increase automatically

## 🐛 Troubleshooting

### Cards not showing
1. Check that `data` folder is writable
2. Verify that JSON files are valid
3. Check browser console for errors

### API errors
1. Check Symfony log files (`var/log/dev.log`)
2. List routes with `bin/console debug:router`
3. Verify that POST request is in correct format

## 📝 License

MIT License

## 👨‍💻 Development

Contributions are welcome!

1. Fork it
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📚 Resources

- [About SM-2 Algorithm](https://en.wikipedia.org/wiki/Spaced_repetition#SM-2)
- [Anki - Popular Flash Card Application](https://apps.ankiweb.net/)
- [Spaced Repetition Research](https://www.gwern.net/Spaced-repetition)

---

**Good luck! 🎓**
