<?php

namespace App\Service;

use App\Entity\LearningProgress;
use DateTime;
use DateInterval;

/**
 * SM-2 (SuperMemo 2) Algorithm Implementation
 * The most popular algorithm for Spaced Repetition
 */
class SpacedRepetitionService
{
    /**
     * Answer quality in SM-2 algorithm:
     * 5 = Perfect answer, remembered immediately
     * 4 = Correct answer with hesitation
     * 3 = Correct answer but recalled with difficulty
     * 2 = Incorrect answer but remembered the correct one
     * 1 = Incorrect answer but familiar
     * 0 = Didn't know at all
     */

    public function updateProgress(LearningProgress $progress, int $quality): void
    {
        // Limit quality value to 0-5 range
        $quality = max(0, min(5, $quality));

        // Get current values
        $interval = $progress->getInterval();
        $repetitions = $progress->getRepetitions();
        $easeFactor = $progress->getEaseFactor();

        // Calculate Ease Factor (EF)
        $newEaseFactor = $easeFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $newEaseFactor = max(1.3, $newEaseFactor); // Minimum 1.3
        $progress->setEaseFactor($newEaseFactor);

        // Reset interval if quality is below 3
        if ($quality < 3) {
            $newInterval = 1;
            $newRepetitions = 0;
        } else {
            // First repetition
            if ($repetitions === 0) {
                $newInterval = 1;
            }
            // Second repetition
            elseif ($repetitions === 1) {
                $newInterval = 3;
            }
            // Subsequent repetitions
            else {
                $newInterval = (int)($interval * $newEaseFactor);
            }
            $newRepetitions = $repetitions + 1;
        }

        // Update values
        $progress->setInterval($newInterval);
        $progress->setRepetitions($newRepetitions);
        $progress->setQuality($quality);
        $progress->setLastReviewDate(new DateTime());

        // Set next review date
        $nextReviewDate = new DateTime();
        $nextReviewDate->add(new DateInterval('P' . $newInterval . 'D'));
        $progress->setNextReviewDate($nextReviewDate);
    }

    /**
     * Check if card is ready for review
     * Returns true if card is due for review
     */
    public function isCardDueForReview(LearningProgress $progress): bool
    {
        $nextReviewDate = $progress->getNextReviewDate();
        if ($nextReviewDate === null) {
            return true;
        }

        return new DateTime() >= $nextReviewDate;
    }

    /**
     * Get the number of cards due for review
     */
    public function getDueCardsCount(array $progressList): int
    {
        $dueCount = 0;
        foreach ($progressList as $progress) {
            if ($this->isCardDueForReview($progress)) {
                $dueCount++;
            }
        }
        return $dueCount;
    }

    /**
     * Get learning statistics
     */
    public function getStatistics(array $progressList): array
    {
        $totalCards = count($progressList);
        $dueCards = 0;
        $totalRepetitions = 0;
        $averageEaseFactor = 0;
        $averageInterval = 0;

        foreach ($progressList as $progress) {
            if ($this->isCardDueForReview($progress)) {
                $dueCards++;
            }
            $totalRepetitions += $progress->getRepetitions();
            $averageEaseFactor += $progress->getEaseFactor();
            $averageInterval += $progress->getInterval();
        }

        if ($totalCards > 0) {
            $averageEaseFactor /= $totalCards;
            $averageInterval /= $totalCards;
        }

        return [
            'totalCards' => $totalCards,
            'dueCards' => $dueCards,
            'newCards' => $this->getNewCardsCount($progressList),
            'totalRepetitions' => $totalRepetitions,
            'averageEaseFactor' => round($averageEaseFactor, 2),
            'averageInterval' => round($averageInterval, 2),
            'completionRate' => $totalCards > 0 ? round(($totalRepetitions / ($totalCards * 10)) * 100, 2) : 0,
        ];
    }

    /**
     * Count new (never studied) cards
     */
    public function getNewCardsCount(array $progressList): int
    {
        $newCount = 0;
        foreach ($progressList as $progress) {
            if ($progress->getRepetitions() === 0) {
                $newCount++;
            }
        }
        return $newCount;
    }

    /**
     * Sort cards by difficulty
     */
    public function sortByDifficulty(array $progressList): array
    {
        usort($progressList, function (LearningProgress $a, LearningProgress $b) {
            // Low EF = more difficult card
            if ($a->getEaseFactor() !== $b->getEaseFactor()) {
                return $a->getEaseFactor() <=> $b->getEaseFactor();
            }
            // If same EF, cards not reviewed for longer come first
            return $a->getLastReviewDate() <=> $b->getLastReviewDate();
        });
        return $progressList;
    }
}
