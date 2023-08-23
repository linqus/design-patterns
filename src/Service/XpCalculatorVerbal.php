<?php

namespace App\Service;

use App\Character\Character;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class XpCalculatorVerbal implements XpCalculatorInterface
{

    public function __construct(private readonly XpCalculatorInterface $innerCalculator)
    {
        
    }
    public function addXp(Character $winner, int $enemyLevel): void
    {
        $io = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());

        $old_level = $winner->getLevel();

        $this->innerCalculator->addXp($winner, $enemyLevel);

        if ($old_level < $winner->getLevel()) {
            $io->warning('Level UP!');
        }

    }

}