<?php

namespace App\Tests\Entity;

use App\Entity\Team;
use App\Entity\Match;
use PHPUnit\Framework\TestCase;

class MatchTest extends TestCase
{
    public function testGenerateExtendedTimeResult()
    {
        $match = new Match(new Team('A', 'A'), new Team('b', 'A'));
        $match->setDraw();
        $match->generateExtendedTimeResult();

        $this->assertNotEquals(Match::MATCH_RESULT_DRAW, $match->result);
    }
}
