<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\passive;

use pocketmine\entity\Animal;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FollowParentBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MateBehavior;
use pocketmine\entity\behavior\PanicBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\TemptedBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class Llama extends Animal{

    public const NETWORK_ID = self::LLAMA;

    public $width = 0.9;
    public $height = 1.87;

    protected function addBehaviors() : void{
        $this->behaviorPool->setBehavior(0, new FloatBehavior($this));
        $this->behaviorPool->setBehavior(1, new PanicBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(3, new TemptedBehavior($this, [Item::WHEAT, Item::WHEAT_BLOCK], 0.75));
        $this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.25));
        $this->behaviorPool->setBehavior(5, new WanderBehavior($this, 1.2));
        //$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 2.0));
        $this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));
    }

    protected function initEntity(CompoundTag $nbt) : void{
        $this->setMaxHealth(30);
        $this->setMovementSpeed(0.2);
        $this->setFollowRange(100);
        $this->setChested(boolval($nbt->getByte("Chest", 0)));
        $this->setVariant(mt_rand(1, 4));
        $this->setColor(mt_rand(1, 4));

        parent::initEntity($nbt);
    }

    public function getName() : string{
        return "Llama";
    }

    public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : bool{
        if(!$this->isImmobile()){
            if($item->getId() == Item::CHEST){
                if(!$this->isChested()) {
                    $this->setChested(true);
                    if ($player->isSurvival()) {
                        $item->pop();
                    }
                }
                return true;
            }
            /*
            if($item->getId() === Item::CARPET){
                if(!$this->isColored()) {
                    $this->setColor(mt_rand(2, 4));
                    if ($player->isSurvival()) {
                        $item->pop();
                    }
                }
                return true;
            }elseif($this->isColored() and $this->riddenByEntity === null){
                $player->mountEntity($this);
                return true;
            }*/
        }

        return parent::onInteract($player, $item, $clickPos, $slot);
    }

    public function getXpDropAmount() : int{
        return rand(1, 3);
    }

    public function getDrops() : array{
        return [
            ItemFactory::get(Item::LEATHER, 0, rand(0, 2))
        ];
    }

    public function isChested() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_CHESTED);
    }

    public function setChested(bool $value = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_CHESTED, $value);
    }


    public function isColored(): bool{
        return $this->propertyManager->getInt(self::DATA_COLOR_2) !== null;
    }

    public function setColor(int $color): void{
        $this->propertyManager->setInt(self::DATA_MINECART_DISPLAY_BLOCK, (mt_rand(2,3) | (self::DATA_MINECART_DISPLAY_BLOCK << 16)));
    }

    public function getColor(): ?int{
        return $this->propertyManager->getInt(self::DATA_COLOR_2) ?? null;
    }

    public function setVariant(int $variant): void{
        $this->propertyManager->setInt(self::DATA_VARIANT, $variant);
    }

    public function saveNBT() : CompoundTag{
        $nbt = parent::saveNBT();

        $nbt->setByte("Chest", intval($this->isChested()));
        return $nbt;
    }

    public function getRiderSeatPosition(int $seatNumber = 0) : Vector3{
        return new Vector3(0, 1.2, -0.2);
    }

    public function getLivingSound() : ?string{
        return "mob.llama.idle";
    }

    public function getEatItems(): array{
        return [Item::WHEAT, Item::WHEAT_BLOCK];
    }
}