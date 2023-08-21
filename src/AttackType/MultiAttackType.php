<?php

namespace App\AttackType;

class MultiAttackType implements AttackType
{
    /**
     * @param AttackType[] $attackTypeArray 
     */
    public function __construct(private array $attackTypeArray)
    {
        
    }

    public function performAttack(int $baseDamage): int
    {
        $attack = $this->attackTypeArray[array_rand($this->attackTypeArray)];
        return $attack->performAttack($baseDamage);
    }
}