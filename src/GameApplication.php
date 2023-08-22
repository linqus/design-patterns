<?php

namespace App;

use App\ArmorType\IceBlockType;
use App\ArmorType\LeatherArmorType;
use App\ArmorType\ShieldType;
use App\AttackType\BowType;
use App\AttackType\FireBoltType;
use App\AttackType\MultiAttackType;
use App\AttackType\TwoHandedSwordType;
use App\Builder\CharacterBuilder;
use App\Builder\CharacterBuilderFactory;
use App\Character\Character;
use App\Event\FightStartingEvent;
use App\Observer\GameObserverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameApplication
{
    /**
     * @var GameObserverInterface[]
     */
    private array $gameObservers = [];


    public function __construct(
        private CharacterBuilderFactory $characterBuilderFactory,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
        
    }

    public function play(Character $player, Character $ai): FightResult
    {

        $this->eventDispatcher->dispatch(new FightStartingEvent());

        $player->rest();

        $fightResult = new FightResult();
        while (true) {
            $fightResult->addRound();

            $damage = $player->attack();
            if ($damage === 0) {
                $fightResult->addExhaustedTurn();
            }

            $damageDealt = $ai->receiveAttack($damage);
            $fightResult->addDamageDealt($damageDealt);

            if ($this->didPlayerDie($ai)) {
                return $this->finishFightResult($fightResult, $player, $ai);
            }

            $damageReceived = $player->receiveAttack($ai->attack());
            $fightResult->addDamageReceived($damageReceived);

            if ($this->didPlayerDie($player)) {
                return $this->finishFightResult($fightResult, $ai, $player);
            }
        }
    }

    public function createCharacter(string $character): Character
    {
        return match (strtolower($character)) {
            'fighter' => $this->createCharacterBuilder()
                ->setMaxHealth(90)
                ->setBaseDamage(12)
                ->setArmorType('shield')
                ->setAttackType('sword')
                ->buildCharacter(),
            'archer' => $this->createCharacterBuilder()
                ->setMaxHealth(80)
                ->setBaseDamage(10)
                ->setAttackType('bow')
                ->setArmorType('leather_armor')
                ->buildCharacter(),
            'mage' => $this->createCharacterBuilder()
                ->setMaxHealth(70)
                ->setBaseDamage(8)
                ->setAttackType('fire_bolt')
                ->setArmorType('ice_block')
                ->buildCharacter(),
            'mage_archer' => $this->createCharacterBuilder()
                ->setMaxHealth(75)
                ->setBaseDamage(9)
                ->setAttackType('fire_bolt', 'bow') // TODO re-add bow!
                ->setArmorType('shield')
                ->buildCharacter(),
            default => throw new \RuntimeException('Undefined Character'),
        };
    }

    public function getCharactersList(): array
    {
        return [
            'fighter',
            'mage',
            'archer',
            'mage_archer'
        ];
    }

    public function subscribe(GameObserverInterface $observer): void
    {
        if (!in_array($observer, $this->gameObservers)) {
            $this->gameObservers[] = $observer;
        }
    }

    public function unsubscribe(GameObserverInterface $observer): void
    {
        $key = array_search($observer, $this->gameObservers);

        if ($key !== false) {
            unset($this->gameObservers[$key]);
        }
    }

    private function finishFightResult(FightResult $fightResult, Character $winner, Character $loser): FightResult
    {
        $fightResult->setWinner($winner);
        $fightResult->setLoser($loser);

        $this->notify($fightResult);

        return $fightResult;
    }

    private function didPlayerDie(Character $player): bool
    {
        return $player->getCurrentHealth() <= 0;
    }

    private function createCharacterBuilder(): CharacterBuilder
    {
        return $this->characterBuilderFactory->createBuilder();
    }

    private function notify(FightResult $result)
    {
        foreach ($this->gameObservers as $observer) {
            $observer->onFigthFinished($result);
        }
    }
}
