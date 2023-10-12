<?php

namespace App\Manager;

use App\Entity\Match;
use App\Entity\Playoff;

class PlayoffManager
{
    public function getPlayoffMatches(array $teamMatches, $opponentMatches): array
    {
        $playoffMatchCount = count($teamMatches) / 2;
        $playOff[] = $this->playoffRound(
            array_slice($this->sortByTeamsScore($teamMatches), 0, $playoffMatchCount),
            array_slice($this->sortByTeamsScore($opponentMatches), 0, $playoffMatchCount)
        );
        for ($i = 1; $i < $playoffMatchCount; $i++) {
            $this->simulatePlayoffRound($playOff[$i - 1]);

            if ($playOff[$i - 1]->roundType == Playoff::FINAL_ROUND) {
                $playOff[$i] = $this->semiFinalWinnerRound($playOff[$i - 2]);
            } else {
                $playOff[$i] = $this->nextPlayoffRound($playOff[$i - 1]);
            }
        }

        return $playOff;
    }

    public function sortByTeamsScore(array $teams): array
    {
        $count = count($teams);
        do {
            $iteration = false;
            for ($i = 1; $i < $count; $i++) {
                if ($teams[$i]->score > $teams[$i - 1]->score) {
                    $temp = $teams[$i];
                    $teams[$i] = $teams[$i - 1];
                    $teams[$i - 1] = $temp;
                    $iteration = true;
                }
            }
        } while ($iteration);

        return $teams;
    }

    public function playoffRound(array $teams, ?array $opponents): Playoff
    {
        $matches = $opponents
            ? $this->getFirstRoundMatches($teams, $opponents)
            : $this->getNextMatches($teams);

        $playoff = new Playoff($matches);
        if (count($matches) == 1) {
            $playoff->setRoundType(Playoff::FINAL_ROUND);
        } else {
            $playoff->setRoundType(Playoff::ROUND);
        }

        return $playoff;
    }

    public function getFirstRoundMatches(array $teams, array $opponents): array
    {
        $matches = [];
        for ($i = 0; $i < count($teams); $i++) {
            $ii = count($opponents) - $i - 1;
            $team = clone $teams[$i];
            $opponent = clone $opponents[$ii];
            $matches[$i] = new Match($team, $opponent);
        }

        return $matches;
    }

    public function nextPlayoffRound(Playoff $playoff): Playoff
    {
        $teams = [];
        foreach ($playoff->matches as $match) {
            if ($match->result == Match::MATCH_RESULT_DRAW) {
                $match->generateExtendedTimeResult();
            }
            if ($match->result == Match::MATCH_RESULT_TEAM_WON) {
                $teams[] = $match->team;
            } else {
                $teams[] = $match->opponent;
            }
        }
        return $this->playoffRound($teams, null);
    }

    public function getNextMatches(array $teams): array
    {
        $ii = count($teams) / 2;
        $matches = [];
        for ($i = 0; $i < $ii; $i++) {
            $first = $i + $i;
            $second = $first + 1;

            $matches[$i] = new Match($teams[$first], $teams[$second]);
        }

        return $matches;
    }

    private function simulatePlayoffRound(Playoff $playoff): void
    {
        foreach ($playoff->matches as $match) {
            $match->generateResult();
        }
    }

    public function semiFinalWinnerRound(Playoff $playoff): Playoff
    {
        $teams = [];
        foreach ($playoff->matches as $match) {
            if (($match->result == Match::MATCH_RESULT_TEAM_WON)) {
                $teams[] = $match->opponent;
            } else {
                $teams[] = $match->team;
            }
        }
        $playoffSemiFinalWinnerRound = $this->playoffRound($teams, null);
        $playoffSemiFinalWinnerRound->roundType = Playoff::SEMI_FINAL_WINNER_ROUND;
        $this->simulatePlayoffRound($playoffSemiFinalWinnerRound);

        return $playoffSemiFinalWinnerRound;
    }

    public function playoffResult(array $playoffMatches): array
    {
        $len = count($playoffMatches) - 1;
        $finalMatch = $playoffMatches[$len - 1]->matches[0];
        $playoffResult[] = $finalMatch->result == Match::MATCH_RESULT_TEAM_WON
            ? $finalMatch->team->name
            : $finalMatch->opponent->name;
        $playoffResult[] = $finalMatch->result == Match::MATCH_RESULT_TEAM_WON
            ? $finalMatch->opponent->name
            : $finalMatch->team->name;


        $semiFinalMatch = $playoffMatches[$len]->matches[0];
        $playoffResult[] = $semiFinalMatch->result == Match::MATCH_RESULT_TEAM_WON
            ? $semiFinalMatch->team->name
            : $semiFinalMatch->opponent->name;
        $playoffResult[] = $semiFinalMatch->result == Match::MATCH_RESULT_TEAM_WON
            ? $semiFinalMatch->opponent->name
            : $semiFinalMatch->team->name;

        return $playoffResult;
    }
}
