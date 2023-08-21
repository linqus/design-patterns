<?php

namespace App\ArmorType;

class LeatherType implements ArmorType
{
    public function getArmorReduction(int $damage): int
    {
        return floor($damage * 0.25);
    }
}