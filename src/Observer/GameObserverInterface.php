<?php

namespace App\GameObserverInterface;

use App\FightResult;

interface GameObserverInterface
{
    public function onFigthFinished(FightResult $fightResult): void;

}