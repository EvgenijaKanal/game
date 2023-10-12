<?php

namespace App\Tests\Manager;

use App\Entity\Match;
use App\Entity\Team;
use App\Manager\MatchManager;
use App\Manager\PlayoffManager;
use PHPUnit\Framework\TestCase;

class MatchManagerTest extends TestCase
{
    public function testGenerateDivisionTable(): void
    {
        $teams = [
            new Team('A', 'A'),
            new Team('B', 'A'),
            new Team('C', 'A'),
        ];
        $playoffManager = $this->createMock(PlayoffManager::class);
        $matchManager = new MatchManager($playoffManager);
        $matches = $matchManager->generateDivisionTable($teams);

        $this->assertCount(9, $matches);
        $this->assertInstanceOf(Match::class, $matches[0]);
        $this->assertEquals($matches[1], $matches[3]);
        $this->assertEquals($matches[2], $matches[6]);
        $this->assertEquals($matches[5], $matches[7]);
    }
}
