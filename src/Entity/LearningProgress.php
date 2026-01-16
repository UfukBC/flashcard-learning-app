<?php

namespace App\Entity;

use DateTime;

class LearningProgress
{
    private ?int $id = null;
    private int $cardId;
    private int $interval = 1; // Günler cinsinden
    private int $repetitions = 0;
    private float $easeFactor = 2.5; // Başlangıç EF değeri
    private ?DateTime $nextReviewDate = null;
    private int $quality = 0; // 0-5 arası değer (cevap kalitesi)
    private DateTime $lastReviewDate;
    private DateTime $createdAt;

    public function __construct(int $cardId)
    {
        $this->cardId = $cardId;
        $this->lastReviewDate = new DateTime();
        $this->createdAt = new DateTime();
        $this->nextReviewDate = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getCardId(): int
    {
        return $this->cardId;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): self
    {
        $this->interval = max(1, $interval);
        return $this;
    }

    public function getRepetitions(): int
    {
        return $this->repetitions;
    }

    public function setRepetitions(int $repetitions): self
    {
        $this->repetitions = max(0, $repetitions);
        return $this;
    }

    public function getEaseFactor(): float
    {
        return $this->easeFactor;
    }

    public function setEaseFactor(float $easeFactor): self
    {
        $this->easeFactor = max(1.3, $easeFactor); // Minimum 1.3
        return $this;
    }

    public function getNextReviewDate(): ?DateTime
    {
        return $this->nextReviewDate;
    }

    public function setNextReviewDate(DateTime $nextReviewDate): self
    {
        $this->nextReviewDate = $nextReviewDate;
        return $this;
    }

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = max(0, min(5, $quality));
        return $this;
    }

    public function getLastReviewDate(): DateTime
    {
        return $this->lastReviewDate;
    }

    public function setLastReviewDate(DateTime $lastReviewDate): self
    {
        $this->lastReviewDate = $lastReviewDate;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
