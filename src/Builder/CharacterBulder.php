<?php

namespace App\Builder;

use App\ArmorType\ArmorType;
use App\ArmorType\IceBlockType;
use App\ArmorType\LeatherArmorType;
use App\ArmorType\ShieldType;
use App\AttackType\AttackType;
use App\AttackType\BowType;
use App\AttackType\FireBoltType;
use App\AttackType\MultiAttackType;
use App\AttackType\TwoHandedSwordType;
use App\Character\Character;
use Psr\Log\LoggerInterface;

class CharacterBuilder
{

    private int $maxHealth;
    private int $baseDamage;
    private array $attackTypes;
    private string $armorType;

    public function __construct(private LoggerInterface $logger)
    {
        
    }

    public function setMaxHealth(int $maxHealth): self {
        $this->maxHealth = $maxHealth;
        return $this;
    }

    public function setBaseDamage(int $baseDamage): self {
        $this->baseDamage = $baseDamage;
        return $this;

    }

    public function setAttackType(string ...$attackTypes): self {
        $this->attackTypes = $attackTypes;
        return $this;
    }

    public function setArmorType(string $armorType): self {
        $this->armorType = $armorType;
        return $this;
    }

    public function buildCharacter(): Character {
        $this->logger->info('character created', [
            'maxHealth' => $this->maxHealth,
            'baseDamage' => $this->baseDamage,
        ]);

        $character = new Character(
            $this->maxHealth,
            $this->baseDamage,
            $this->createArmorType(),
            $this->createAttackType()
        );

        return $character;

    }

    private function createAttackType(): AttackType
    {
        $multiAttack = [];

        foreach ($this->attackTypes as $attack) {
            $multiAttack[] = match ($attack) {
                'bow' => new BowType(),
                'fire_bolt' => new FireBoltType(),
                'sword' => new TwoHandedSwordType(),
                default => throw new \RuntimeException('Invalid attack type given')
            };    
        };

        if (count($multiAttack) === 1) {
            return $multiAttack[0];
        } else {
            return new MultiAttackType($multiAttack);
        }
        
    }

    private function createArmorType(): ArmorType
    {
        return match ($this->armorType) {
            'ice_block' => new IceBlockType(),
            'shield' => new ShieldType(),
            'leather_armor' => new LeatherArmorType(),
            default => throw new \RuntimeException('Invalid armor type given')
        };
    }

}