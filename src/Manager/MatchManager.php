<?php

namespace App\Manager;

use App\Entity\IntermediateResult;
use App\Entity\Match;
use App\Entity\Team;
use App\Manager\PlayoffManager;

class MatchManager
{
    /** @var PlayoffManager $playoffManager */
    private $playoffManager;

    public function __construct(PlayoffManager $playoffManager)
    {
        $this->playoffManager = $playoffManager;
    }

    public function generateMatches(): array
    {
        [
            'divisions' => $divisions,
            'teams' => $teams,
        ] = \json_decode(
            \file_get_contents(__DIR__ . '/../DB/teams.json'),
            true
        );
        $divisionStructure = $this->getDivisionTeams($divisions, $teams);
        $divisionMatches = $this->getDivisionMatches($divisionStructure);
        $playoffMatches = $this->playoffManager->getPlayoffMatches($divisionStructure['A'], $divisionStructure['B']);

        return [
            'divisions' => $divisionStructure,
            'matches' => $divisionMatches,
            'playoff' => $playoffMatches,
            'result' => $this->playoffManager->playoffResult($playoffMatches),
        ];
    }

    private function getDivisionTeams(array $divisions, array $teams): array
    {
        $divisionsList = [];
        foreach ($divisions as $divisionID) {
            foreach ($teams as $team) {
                if (0 === strcasecmp($team['division'], $divisionID)) {
                    $divisionsList[$divisionID][] = new Team($team['name'], $team['division']);
                }
            }
            $divisionsList[$divisionID] = array_values($divisionsList[$divisionID]);
        }
        return $divisionsList;
    }

    private function getDivisionMatches(array $divisionStructure): array
    {
        $divisionMatches = [];
        foreach ($divisionStructure as $divisionID => $teams) {
            $divisionMatches[$divisionID] = $this->generateDivisionTable($teams);
        }

        return $divisionMatches;
    }

    public function generateDivisionTable(array $teams): array
    {
        $divisionMatches = [];
        $row = 0;
        $col = 0;
        for ($i = 0; $i < count($teams); $i++) {
            $match = new Match($teams[$row], $teams[$i]);
            $match->generateResult();
            $divisionMatches[$i] = $match;
        }

        for ($it = $row + 1; $it < count($teams); $it++) {
            $ii = 0;
            $row++;
            for ($indexRow = 0; $indexRow < $row; $indexRow++) {
                $col++;
                $ii = $col + ($row * (count($teams) - 1));
                $diff = $indexRow * (count($teams) - 1);
                $divisionMatches[$ii] = $divisionMatches[$row + $diff + $indexRow];
            }
            $col++;

            $ii = $col + ($row * (count($teams) - 1));
            $divisionMatches[$ii] = new Match($teams[$row], $teams[$row]);

            for ($i = $row + 1; $i < count($teams); $i++) {
                $col++;
                $ii = $col + ($row * (count($teams) - 1));
                $match = new Match($teams[$row], $teams[$i]);
                $match->generateResult();
                $divisionMatches[$ii] = $match;
            }
            $col = $row;
        }

        return $divisionMatches;
    }
}
