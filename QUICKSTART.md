# 📚 Finnish Flash Card Application - Setup Guide

## 🎯 Quick Start

### 1. Start the Application

```bash
# Navigate to project directory
cd C:\SWare2\SWare\Backup\anki_clone

# Start Symfony server
symfony serve
```

### 2. Open in Browser

```
http://localhost:8000
```

## 📋 System Requirements

- **PHP**: 8.2 or newer
- **Composer**: Must be installed
- **Symfony CLI**: Optional (for symfony serve)

## 🚀 Try Basic Features

### 1. View Statistics on Dashboard
- Total 5 sample cards loaded
- 2 cards ready for review today
- Statistics update in real-time

### 2. Add New Card
1. Click "Add Card" tab
2. Example:
   - **Finnish Word**: "mies"
   - **Definition**: "Male person, adult male"
   - **Turkish Meaning**: "Erkek"
   - **English Meaning**: "Man"
3. Click "Add Card" button

### 3. Review Section
1. Click "Review" tab
2. View flash card
3. Click "Show Answer" button
4. See definition, meanings and spelling field
5. Select answer quality (0-5):
   - 🔴 **0**: Didn't know at all
   - 🟠 **1**: Incorrect but familiar
   - 🟡 **2**: Hard to recall
   - 🟢 **3**: Recalled
   - 💚 **4**: Easy recall
   - 🟦 **5**: Perfect!
6. Algorithm automatically calculates next review date

## 🎓 How SM-2 Algorithm Works?

### Learning Process

```
Initial Learning (Rep 0)
       ↓
Quality 3+ ? → Yes → Repeat after 1 day (Rep 1)
       ↓ No
  Repeat after 1 day (Rep 0)

2nd Repetition (Rep 1)
       ↓
Quality 3+ ? → Yes → Repeat after 3 days (Rep 2)
       ↓ No
  Repeat after 1 day (Rep 0)

3rd Repetition (Rep 2+)
       ↓
Quality 3+ ? → Yes → Repeat after Interval × EF days
       ↓ No
  Repeat after 1 day (Rep 0)
```

### Ease Factor (Difficulty Factor)

- **Initial**: 2.5 (medium difficulty)
- **Update**: Automatically adjusted based on answer quality
- **Difficult cards**: EF decreases (more frequent repetition)
- **Easy cards**: EF increases (less frequent repetition)
- **Minimum**: 1.3 (even very difficult cards increase quickly)

## 📊 Data Structure

### Flash Cards (`data/flash_cards.json`)

```json
{
  "id": 1,
  "finnishWord": "kissa",
  "definition": "Four-legged domestic animal",
  "turkishMeaning": "Kedi",
  "englishMeaning": "Cat",
  "createdAt": "2025-12-24 10:00:00",
  "updatedAt": "2025-12-24 10:00:00"
}
```

### Progress Data (`data/progress.json`)

```json
{
  "cardId": 1,
  "interval": 1,
  "repetitions": 2,
  "easeFactor": 2.35,
  "quality": 3,
  "nextReviewDate": "2025-12-25",
  "lastReviewDate": "2025-12-24 11:00:00",
  "createdAt": "2025-12-24 10:00:00"
}
```

## 🔌 API Endpoints

### GET /api/statistics
Fetch statistics

**Response example:**
```json
{
  "totalCards": 5,
  "dueCards": 2,
  "newCards": 3,
  "totalRepetitions": 3,
  "averageEaseFactor": 2.43,
  "averageInterval": 1.4,
  "completionRate": 0.0
}
```

### GET /api/review/due
Fetch cards to review today

### POST /api/cards/create
Create new card

```json
{
  "finnishWord": "nainen",
  "definition": "Female person, adult woman",
  "turkishMeaning": "Kadın",
  "englishMeaning": "Woman"
}
```

### POST /api/review/answer
Submit answer and update progress

```json
{
  "cardId": 1,
  "quality": 4,
  "userAnswer": "kissa"
}
```

## 💾 Data Management

### Data Location

```
C:\SWare2\SWare\Backup\anki_clone\
└── data\
    ├── flash_cards.json     (Cards)
    └── progress.json        (Learning progress)
```

### Reset Data

1. **Reset Flash Cards**:
   - Delete `data/flash_cards.json` file
   - Application will start with empty card list

2. **Reset Progress**:
   - Delete `data/progress.json` file
   - All cards will be marked as new

3. **Reset All Data**:
   - Delete `data` folder completely
   - Application will start fresh

## 🎯 Learning Tips

### Effective Learning Plan

1. **Daily Goal**: Learn 10-20 new cards
2. **Regular Review**: At least 10 minutes every day
3. **Consistency**: Study at the same time
4. **Quality**: Honestly evaluate answer quality

### When Selecting Answer Quality

- **0-1**: If you know the word very poorly
- **2**: If you have difficulty remembering
- **3**: If you remembered correctly but struggled
- **4-5**: If you know the word confidently

## 🐛 Troubleshooting

### Problem: API 404 error

**Solution:**
1. Check that Symfony server is running
2. Check routes: `symfony console debug:router`
3. Open browser console (F12) and observe errors

### Problem: JSON files not updating

**Solution:**
1. Check that `data` folder is writable
2. Check that PHP has file write permissions
3. Check `var_log` folder

### Problem: Styles not showing on page

**Solution:**
1. Clear browser cache (Ctrl+Shift+Del)
2. Check that `public/css/style.css` file exists
3. Check browser console for CSS errors

## 📈 Advanced Features

### Database Integration

For more reliable data management:

```bash
composer require symfony/orm-pack
symfony console make:migration
symfony console doctrine:migrations:migrate
```

### Export Statistics

To create reports in CSV or PDF format:
- Add report service after database integration

### Multi-User Support

To add user system:
- Install Symfony Security Bundle
- Add user authentication
- Link progress to users

## 📚 Additional Resources

- **Spaced Repetition**: https://en.wikipedia.org/wiki/Spaced_repetition
- **SM-2 Algorithm**: https://www.supermemo.com/en/archives1990-2015/english/ol/2a
- **Anki (Reference)**: https://apps.ankiweb.net/
- **Finnish Language**: https://fi.wikipedia.org

## 🎉 Pre-loaded Data for Your First Try

Application comes with pre-loaded data:

| Finnish | Turkish | English | Status |
|---------|---------|---------|--------|
| kissa | Kedi | Cat | 2 repetitions |
| koira | Köpek | Dog | New |
| talo | Ev | House | 1 repetition |
| auto | Araba | Car | New |
| vesi | Su | Water | New |

---

**Happy Learning! 🎓**

Read README.md file for questions.
