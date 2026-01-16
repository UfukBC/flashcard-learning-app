// Application State
let appState = {
    currentCardIndex: 0,
    dueCards: [],
    allCards: [],
    totalCards: 0,
};

// On Page Load
document.addEventListener('DOMContentLoaded', () => {
    setupNavigation();
    loadStatistics();
    setupFormListeners();
});

// Setup Navigation
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionId = link.getAttribute('data-section');
            switchSection(sectionId);
        });
    });
}

// Switch Section
function switchSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });

    // Show selected section
    const activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.classList.add('active');

        // If review section is selected, load cards
        if (sectionId === 'review') {
            loadDueCards();
        }
    }
}

// Load Statistics
async function loadStatistics() {
    try {
        const response = await fetch('/api/statistics');
        const stats = await response.json();

        // Update statistics
        document.getElementById('totalCards').textContent = stats.totalCards;
        document.getElementById('dueCards').textContent = stats.dueCards;
        document.getElementById('newCards').textContent = stats.newCards;
        document.getElementById('completionRate').textContent = stats.completionRate.toFixed(1) + '%';

        // List detailed statistics
        const statsList = document.getElementById('statsList');
        statsList.innerHTML = `
            <li>
                <strong>Total Repetitions:</strong>
                <span>${stats.totalRepetitions}</span>
            </li>
            <li>
                <strong>Average Ease Factor:</strong>
                <span>${stats.averageEaseFactor}</span>
            </li>
            <li>
                <strong>Average Interval:</strong>
                <span>${stats.averageInterval.toFixed(1)} day(s)</span>
            </li>
        `;

        appState.totalCards = stats.totalCards;
    } catch (error) {
        console.error('Failed to load statistics:', error);
    }
}

// Load Due Cards
async function loadDueCards() {
    try {
        const response = await fetch('/api/review/due');
        appState.dueCards = await response.json();

        if (appState.dueCards.length === 0) {
            document.getElementById('no-cards').style.display = 'block';
            document.getElementById('card-container').style.display = 'none';
        } else {
            document.getElementById('no-cards').style.display = 'none';
            document.getElementById('card-container').style.display = 'block';
            appState.currentCardIndex = 0;
            displayCard();
        }
    } catch (error) {
        console.error('Failed to load cards:', error);
    }
}

// Display Card
function displayCard() {
    if (appState.dueCards.length === 0) return;

    const card = appState.dueCards[appState.currentCardIndex];

    // Kartı Göster
    document.getElementById('finnishWord').textContent = card.finnishWord;
    document.getElementById('definition').textContent = card.definition;
    document.getElementById('turkishMeaning').textContent = card.turkishMeaning;
    document.getElementById('englishMeaning').textContent = card.englishMeaning;

    // Progress Güncelleştir
    const progress = (((appState.currentCardIndex + 1) / appState.dueCards.length) * 100);
    document.getElementById('progressFill').style.width = progress + '%';
    document.getElementById('progressText').textContent =
        `${appState.currentCardIndex + 1} / ${appState.dueCards.length}`;

    // Kart Bilgileri
    document.getElementById('interval').textContent = card.progress.interval;
    document.getElementById('repetitions').textContent = card.progress.repetitions;
    document.getElementById('easeFactor').textContent = card.progress.easeFactor.toFixed(2);
    document.getElementById('nextReview').textContent = card.progress.nextReviewDate || 'Bugün';

    // Arka yüzü gizle
    document.getElementById('cardBack').style.display = 'none';
    document.getElementById('qualityButtons').style.display = 'none';
    document.getElementById('revealBtn').textContent = 'Cevabı Göster';
    document.getElementById('userAnswer').value = '';
}

// Kartı Aç
function revealCard() {
    const cardBack = document.getElementById('cardBack');
    const qualityButtons = document.getElementById('qualityButtons');
    const revealBtn = document.getElementById('revealBtn');

    if (cardBack.style.display === 'none') {
        cardBack.style.display = 'block';
        qualityButtons.style.display = 'block';
        revealBtn.textContent = 'Hide Card';
    } else {
        cardBack.style.display = 'none';
        qualityButtons.style.display = 'none';
        revealBtn.textContent = 'Show Answer';
    }
}

// Submit Answer
async function submitAnswer(quality) {
    const card = appState.dueCards[appState.currentCardIndex];
    const userAnswer = document.getElementById('userAnswer').value;

    try {
        const response = await fetch('/api/review/answer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cardId: card.id,
                quality: quality,
                userAnswer: userAnswer,
            }),
        });

        const result = await response.json();

        if (result.success) {
            // Move to next card
            appState.currentCardIndex++;

            if (appState.currentCardIndex < appState.dueCards.length) {
                // Update card
                appState.dueCards[appState.currentCardIndex - 1] = {
                    ...appState.dueCards[appState.currentCardIndex - 1],
                    progress: {
                        interval: result.newInterval,
                        easeFactor: result.newEaseFactor,
                        nextReviewDate: result.nextReviewDate,
                    }
                };
                displayCard();
            } else {
                // All cards completed
                showCompletionMessage();
            }
        }
    } catch (error) {
        console.error('Failed to submit answer:', error);
        alert('An error occurred. Please try again.');
    }
}

// Completion Message
function showCompletionMessage() {
    document.getElementById('card-container').style.display = 'none';
    document.getElementById('no-cards').innerHTML = `
        <p>🎉 Harika! Tüm kartları gözden geçirdin!</p>
        <p style="font-size: 18px; color: #6b7280; margin-bottom: 30px;">
            Bugün için öğrenme seansı tamamlandı.
        </p>
        <button class="btn btn-primary" onclick="goToDashboard()">
            Dashboard'a Dön
        </button>
    `;
    document.getElementById('no-cards').style.display = 'block';

    // İstatistikleri güncelle
    loadStatistics();
}

// Form Dinleyicileri Kurulumu
function setupFormListeners() {
    const form = document.getElementById('addCardForm');
    form.addEventListener('submit', addCard);
}

// Kart Ekle
async function addCard(event) {
    event.preventDefault();

    const finnishWord = document.getElementById('finnishWordInput').value;
    const definition = document.getElementById('definitionInput').value;
    const turkishMeaning = document.getElementById('turkishMeaningInput').value;
    const englishMeaning = document.getElementById('englishMeaningInput').value;

    if (!finnishWord || !definition || !turkishMeaning || !englishMeaning) {
        alert('Lütfen tüm alanları doldurunuz!');
        return;
    }

    try {
        const response = await fetch('/api/cards/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                finnishWord,
                definition,
                turkishMeaning,
                englishMeaning,
            }),
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ Kart başarıyla eklendi!');
            document.getElementById('addCardForm').reset();

            // İstatistikleri güncelle
            loadStatistics();

            // Dashboard'a dön
            goToDashboard();
        } else {
            alert('❌ Kart eklenirken bir hata oluştu!');
        }
    } catch (error) {
        console.error('Kart eklenemedi:', error);
        alert('Bir hata oluştu. Lütfen tekrar deneyiniz.');
    }
}

// Sayfalar arası geçiş fonksiyonları
function goToDashboard() {
    loadStatistics();
    switchSection('dashboard');
}

function goToAddCard() {
    switchSection('add-card');
}

function startReview() {
    switchSection('review');
}

// Update statistics every hour
setInterval(() => {
    if (document.getElementById('dashboard').classList.contains('active')) {
        loadStatistics();
    }
}, 60000); // Every 1 minute
