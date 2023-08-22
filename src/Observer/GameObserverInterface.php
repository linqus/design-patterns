<?php

namespace App\Observer;

use App\FightResult;

interface GameObserverInterface
{
    public function onFigthFinished(FightResult $fightResult): void;

}