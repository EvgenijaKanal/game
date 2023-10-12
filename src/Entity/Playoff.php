<?php

namespace App\Entity;

class Playoff
{
    const ROUND = 0;
    const SEMI_FINAL_WINNER_ROUND = 1;
    const FINAL_ROUND = 2;

    /** @var Match[] */
    public $matches;

    /** @var int */
    public $roundType;

    public function __construct(array $matches)
    {
        $this->matches = $matches;
    }

    public function setRoundType(int $roundType): void
    {
        $this->roundType = $roundType;
    }
}
