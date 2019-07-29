<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\entity\passive;

use pocketmine\entity\Animal;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FollowParentBehavior;
use pocketmine\entity\behavior\HurtByTargetBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MateBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;

class PolarBear extends Animal
{
    public const NETWORK_ID = self::POLAR_BEAR;

    public $width = 1.3;
    public $height = 1.4;

    protected function addBehaviors(): void
    {
        $this->behaviorPool->setBehavior(0, new FloatBehavior($this));
        $this->behaviorPool->setBehavior(1, new MateBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(2, new MeleeAttackBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(3, new FollowParentBehavior($this, 1.1));
        $this->behaviorPool->setBehavior(4, new RandomStrollBehavior($this, 1.0));
        $this->behaviorPool->setBehavior(5, new LookAtPlayerBehavior($this, 16.0));
        $this->behaviorPool->setBehavior(6, new RandomLookAroundBehavior($this));

        $this->targetBehaviorPool->setBehavior(0, new HurtByTargetBehavior($this));
    }
        public
        function initEntity(): void
        {
            $this->setMaxHealth(30);
            $this->setMovementSpeed(0.30);
            $this->setAttackDamage(4);
            $this->setFollowRange(16);
            parent::initEntity();
        }
        //TODO: atack foxes
        public
        function getName(): string
        {
            return "Polar Bear";
        }

        public function setTargetEntity(?Entity $target) : void{
        parent::setTargetEntity($target);
        if($target == null){
            $this->setAngry(false);
        }
        }
        public function isAngry() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_ANGRY);
        }

        public function setAngry(bool $angry = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_ANGRY, $angry);
         }

        private function generateRandomDirection(): Vector3
        {
            return new Vector3(mt_rand(-1000, 1000) / 1000, mt_rand(-500, 500) / 1000, mt_rand(-1000, 1000) / 1000);
        }

        public function getXpDropAmount() : int{
        return rand(1, 3);
    }

        public
        function getDrops(): array
        {
	        $drops = [];
	        if(mt_rand(1, 4) >1){
		        $drops[] = ItemFactory::get(Item::RAW_FISH, 1, mt_rand(0, 2));
		        return $drops;
	        }
	        else{
	        	$drops[] = ItemFactory::get(Item::RAW_SALMON, 1, mt_rand(0, 2));
	        	return $drops;}
        }
    }