<?php

namespace App\Entity;

class Team
{
    /** @var string */
    public $name;

    /** @var string */
    public $division;

    /** @var int */
    public $score;

    /** @var int */
    public $played;

    /**
     * @param string $name
     * @param string $division
     */
    public function __construct(string $name, string $division)
    {
        $this->name = $name;
        $this->division = $division;
        $this->score = 0;
        $this->played = 0;
    }

    public function won(): void
    {
        ++$this->played;
        $this->score += 2;
    }

    public function draw(): void
    {
        ++$this->played;
        $this->score += 1;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDivision(): string
    {
        return $this->division;
    }
}
