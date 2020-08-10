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
use pocketmine\entity\behavior\TemptBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function boolval;
use function intval;
use function rand;

class Rabbit extends Animal{

	public const NETWORK_ID = self::RABBIT;

	public $width = 0.9;
	public $height = 0.9;

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new PanicBehavior($this, 1.25));
		$this->behaviorPool->setBehavior(2, new MateBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new TemptBehavior($this, [Item::CARROT], 1.2));
		$this->behaviorPool->setBehavior(4, new FollowParentBehavior($this, 1.1));
		$this->behaviorPool->setBehavior(5, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(6, new LookAtPlayerBehavior($this, 6.0));
		$this->behaviorPool->setBehavior(7, new RandomLookAroundBehavior($this));
	}

	protected function initEntity() : void{
		$this->setMaxHealth(10);
		$this->setMovementSpeed(0.25);
		$this->setFollowRange(10);

		parent::initEntity();
	}

	public function getName() : string{
		return "Rabbit";
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if(parent::onInteract($player, $item, $clickPos)){
			return true;
		}
		return false;
	}

	public function getXpDropAmount() : int{
		return rand(1, ($this->isInLove() ? 7 : 3));
	}

	public function getDrops() : array{
		$drops = [];
		array_push($drops, Item::get(Item::RABBIT_HIDE, 0, mt_rand(0, 1)));
		if($this->isOnFire()){
			array_push($drops, Item::get(Item::COOKED_RABBIT, 0, mt_rand(0, 1)));
		}else{
			array_push($drops, Item::get(Item::RAW_RABBIT, 0, mt_rand(0, 1)));
		}
		if(mt_rand(0, 100) <= 10){ // 10 percent chance of dropping rabbits foot
			array_push($drops, Item::get(Item::RABBIT_FOOT, 0, 1));
		}
    return $drops;
  }

	public function entityBaseTick(int $diff = 1) : bool{
		if($this->motion->x != 0 or $this->motion->y != 0 or $this->motion->z != 0)
			$this->jump();

		return parent::entityBaseTick($diff);
	}
}
