<?php

namespace App\Entity;

class Match
{
    public const MATCH_RESULT_NONE = 0;
    public const MATCH_RESULT_DRAW = 1;
    public const MATCH_RESULT_TEAM_WON = 2;
    public const MATCH_RESULT_OPPONENT_WON = 3;

    /** @var int  */
    public $result;

    /** @var bool */
    public $emptyMatch = false;

    /** @var Team */
    public $team;

    /** @var Team */
    public $opponent;

    /** @var int */
    private $teamScore;

    /** @var int */
    private $opponentScore;

    /** @var ?string */
    public $score;

    public function __construct(Team $team, Team $opponent)
    {
        $this->team = $team;
        $this->opponent = $opponent;

        if ($this->team === $this->opponent) {
            $this->emptyMatch = true;
        }
    }

    public function setDraw(): void
    {
        $this->result = self::MATCH_RESULT_DRAW;
        $this->team->draw();
        $this->opponent->draw();
    }

    public function getTeamScore(): ?int
    {
        return $this->teamScore;
    }

    public function getOpponentScore(): ?int
    {
        return $this->opponentScore;
    }

    public function generateExtendedTimeResult(): void
    {
        do {
            $this->result = self::MATCH_RESULT_NONE;
            $this->generateResult();
        } while ($this->getResult() == self::MATCH_RESULT_DRAW);
    }

    public function generateResult(): void
    {
        if (($this->emptyMatch) || ($this->result != self::MATCH_RESULT_NONE)) {
            return;
        }
        $this->teamScore = rand(0, 9);
        $this->opponentScore = rand(0, 9);

        if ($this->teamScore === $this->opponentScore) {
            $this->team->draw();
            $this->opponent->draw();
            $this->result = self::MATCH_RESULT_DRAW;
            return;
        }
        if ($this->teamScore > $this->opponentScore) {
            $this->team->won();
            $this->result = self::MATCH_RESULT_TEAM_WON;
        } else {
            $this->opponent->won();
            $this->result = self::MATCH_RESULT_OPPONENT_WON;
        }
    }

    public function getScoreStr(?Team $currentTeam): string
    {
        if ($currentTeam != null && (
                $currentTeam->getName() == $this->team->getName() &&
                $currentTeam->getDivision() == $this->team->getDivision()
            )) {
            return $this->teamScore . " - " . $this->opponentScore;
        } else {
            return $this->opponentScore . " - " . $this->teamScore;
        }
    }

    public function getResult(): int
    {
        return $this->result;
    }
}
