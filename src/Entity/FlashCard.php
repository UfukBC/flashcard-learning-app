<?php

namespace App\Entity;

use DateTime;

class FlashCard
{
    private ?int $id = null;
    private string $finnishWord;
    private string $definition;
    private string $turkishMeaning;
    private string $englishMeaning;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        string $finnishWord,
        string $definition,
        string $turkishMeaning,
        string $englishMeaning
    ) {
        $this->finnishWord = $finnishWord;
        $this->definition = $definition;
        $this->turkishMeaning = $turkishMeaning;
        $this->englishMeaning = $englishMeaning;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
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

    public function getFinnishWord(): string
    {
        return $this->finnishWord;
    }

    public function setFinnishWord(string $finnishWord): self
    {
        $this->finnishWord = $finnishWord;
        return $this;
    }

    public function getDefinition(): string
    {
        return $this->definition;
    }

    public function setDefinition(string $definition): self
    {
        $this->definition = $definition;
        return $this;
    }

    public function getTurkishMeaning(): string
    {
        return $this->turkishMeaning;
    }

    public function setTurkishMeaning(string $turkishMeaning): self
    {
        $this->turkishMeaning = $turkishMeaning;
        return $this;
    }

    public function getEnglishMeaning(): string
    {
        return $this->englishMeaning;
    }

    public function setEnglishMeaning(string $englishMeaning): self
    {
        $this->englishMeaning = $englishMeaning;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
