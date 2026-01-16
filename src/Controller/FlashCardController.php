<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\FlashCard;
use App\Entity\LearningProgress;
use App\Service\SpacedRepetitionService;

class FlashCardController extends AbstractController
{
    private array $flashCards = [];
    private array $progressData = [];

    public function __construct(private SpacedRepetitionService $spacedRepetitionService)
    {
    }

    #[Route('/', name: 'app_index', methods: ['GET'])]
    public function index(): Response
    {
        $path = $this->getProjectRoot() . '/public/index.html';
        if (file_exists($path)) {
            return new Response(file_get_contents($path), Response::HTTP_OK, ['Content-Type' => 'text/html']);
        }
        return new Response('Index not found', Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/cards', name: 'api_get_cards', methods: ['GET'])]
    public function getCards(): JsonResponse
    {
        $cards = $this->loadFlashCards();
        $cardsData = [];

        foreach ($cards as $card) {
            $cardsData[] = [
                'id' => $card->getId(),
                'finnishWord' => $card->getFinnishWord(),
                'definition' => $card->getDefinition(),
            ];
        }

        return $this->json($cardsData);
    }

    #[Route('/api/cards/create', name: 'api_create_card', methods: ['POST'])]
    public function createCard(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $card = new FlashCard(
            $data['finnishWord'] ?? '',
            $data['definition'] ?? '',
            $data['turkishMeaning'] ?? '',
            $data['englishMeaning'] ?? ''
        );

        $cards = $this->loadFlashCards();
        $cardId = count($cards) + 1;
        $card->setId($cardId);
        $cards[] = $card;
        $this->saveFlashCards($cards);

        // Create progress for new card
        $progress = new LearningProgress($cardId);
        $progressList = $this->loadProgress();
        $progressList[] = $progress;
        $this->saveProgress($progressList);

        return $this->json([
            'success' => true,
            'message' => 'Card created successfully',
            'cardId' => $card->getId()
        ]);
    }

    #[Route('/api/cards/{id}', name: 'api_get_card', methods: ['GET'])]
    public function getCard(int $id): JsonResponse
    {
        $cards = $this->loadFlashCards();
        $card = null;

        foreach ($cards as $c) {
            if ($c->getId() === $id) {
                $card = $c;
                break;
            }
        }

        if (!$card) {
            return $this->json(['error' => 'Card not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $card->getId(),
            'finnishWord' => $card->getFinnishWord(),
            'definition' => $card->getDefinition(),
            'turkishMeaning' => $card->getTurkishMeaning(),
            'englishMeaning' => $card->getEnglishMeaning(),
        ]);
    }

    #[Route('/api/review/due', name: 'api_due_cards', methods: ['GET'])]
    public function getDueCards(): JsonResponse
    {
        $cards = $this->loadFlashCards();
        $progressList = $this->loadProgress();
        $dueCards = [];

        foreach ($progressList as $progress) {
            if ($this->spacedRepetitionService->isCardDueForReview($progress)) {
                // Find card
                $card = null;
                foreach ($cards as $c) {
                    if ($c->getId() === $progress->getCardId()) {
                        $card = $c;
                        break;
                    }
                }

                if ($card) {
                    $dueCards[] = [
                        'id' => $card->getId(),
                        'finnishWord' => $card->getFinnishWord(),
                        'definition' => $card->getDefinition(),
                        'turkishMeaning' => $card->getTurkishMeaning(),
                        'englishMeaning' => $card->getEnglishMeaning(),
                        'progress' => [
                            'interval' => $progress->getInterval(),
                            'repetitions' => $progress->getRepetitions(),
                            'easeFactor' => $progress->getEaseFactor(),
                            'nextReviewDate' => $progress->getNextReviewDate()?->format('Y-m-d'),
                        ]
                    ];
                }
            }
        }

        return $this->json($dueCards);
    }

    #[Route('/api/review/answer', name: 'api_answer', methods: ['POST'])]
    public function submitAnswer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cardId = $data['cardId'] ?? null;
        $quality = $data['quality'] ?? 0;
        $userAnswer = $data['userAnswer'] ?? '';

        if (!$cardId) {
            return $this->json(['error' => 'Card ID required'], Response::HTTP_BAD_REQUEST);
        }

        $progressList = $this->loadProgress();
        $progress = null;

        foreach ($progressList as $p) {
            if ($p->getCardId() === $cardId) {
                $progress = $p;
                break;
            }
        }

        if (!$progress) {
            return $this->json(['error' => 'Progress not found'], Response::HTTP_NOT_FOUND);
        }

        // Apply SM-2 algorithm
        $this->spacedRepetitionService->updateProgress($progress, $quality);
        $this->saveProgress($progressList);

        return $this->json([
            'success' => true,
            'message' => 'Answer saved',
            'nextReviewDate' => $progress->getNextReviewDate()?->format('Y-m-d'),
            'newInterval' => $progress->getInterval(),
            'newEaseFactor' => round($progress->getEaseFactor(), 2),
        ]);
    }

    #[Route('/api/statistics', name: 'api_statistics', methods: ['GET'])]
    public function getStatistics(): JsonResponse
    {
        $progressList = $this->loadProgress();
        $stats = $this->spacedRepetitionService->getStatistics($progressList);

        return $this->json($stats);
    }

    // Helper methods
    private function loadFlashCards(): array
    {
        $filePath = $this->getProjectRoot() . '/data/flash_cards.json';
        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $data = json_decode($json, true);

            $cards = [];
            foreach ($data as $item) {
                $card = new FlashCard(
                    $item['finnishWord'] ?? '',
                    $item['definition'] ?? '',
                    $item['turkishMeaning'] ?? '',
                    $item['englishMeaning'] ?? ''
                );
                $card->setId($item['id'] ?? null);
                $cards[] = $card;
            }
            return $cards;
        }
        return [];
    }

    private function saveFlashCards(array $cards): void
    {
        $filePath = $this->getProjectRoot() . '/data/flash_cards.json';
        @mkdir(dirname($filePath), 0777, true);

        $cardsData = [];
        foreach ($cards as $card) {
            $cardsData[] = [
                'id' => $card->getId(),
                'finnishWord' => $card->getFinnishWord(),
                'definition' => $card->getDefinition(),
                'turkishMeaning' => $card->getTurkishMeaning(),
                'englishMeaning' => $card->getEnglishMeaning(),
                'createdAt' => $card->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $card->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        file_put_contents($filePath, json_encode($cardsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function loadProgress(): array
    {
        $filePath = $this->getProjectRoot() . '/data/progress.json';
        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            $data = json_decode($json, true);

            $progressList = [];
            foreach ($data as $item) {
                $progress = new LearningProgress($item['cardId'] ?? 0);
                $progress->setInterval($item['interval'] ?? 1);
                $progress->setRepetitions($item['repetitions'] ?? 0);
                $progress->setEaseFactor($item['easeFactor'] ?? 2.5);
                $progress->setQuality($item['quality'] ?? 0);

                if (isset($item['nextReviewDate'])) {
                    $progress->setNextReviewDate(new \DateTime($item['nextReviewDate']));
                }
                if (isset($item['lastReviewDate'])) {
                    $progress->setLastReviewDate(new \DateTime($item['lastReviewDate']));
                }

                $progressList[] = $progress;
            }
            return $progressList;
        }
        return [];
    }

    private function saveProgress(array $progress): void
    {
        $filePath = $this->getProjectRoot() . '/data/progress.json';
        @mkdir(dirname($filePath), 0777, true);

        $progressData = [];
        foreach ($progress as $item) {
            $progressData[] = [
                'cardId' => $item->getCardId(),
                'interval' => $item->getInterval(),
                'repetitions' => $item->getRepetitions(),
                'easeFactor' => $item->getEaseFactor(),
                'quality' => $item->getQuality(),
                'nextReviewDate' => $item->getNextReviewDate()?->format('Y-m-d'),
                'lastReviewDate' => $item->getLastReviewDate()->format('Y-m-d H:i:s'),
                'createdAt' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        file_put_contents($filePath, json_encode($progressData, JSON_PRETTY_PRINT));
    }

    private function getProjectRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
