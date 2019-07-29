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

namespace pocketmine\entity\hostile;

use pocketmine\entity\behavior\FindAttackableTargetBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\MeleeAttackBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\RandomStrollBehavior;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Monster;
use pocketmine\inventory\AltayEntityEquipment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use function mt_rand;

class WitherSkeleton extends Monster{
	public const NETWORK_ID = self::WITHER_SKELETON;

	public $width = 0.7;
	public $height = 2.4;

	/** @var AltayEntityEquipment */
	protected $equipment;

	protected function initEntity() : void{
		$this->setMovementSpeed(0.3);
		$this->setFollowRange(35);
		$this->setAttackDamage(3);

		parent::initEntity();

		$this->equipment = new AltayEntityEquipment($this);

		$this->equipment->setItemInHand(ItemFactory::get(Item::STONE_SWORD));
	}

	public function getName() : string{
		return "Wither Skeleton";
	}

	public function getDrops() : array{
		$drops = [
			ItemFactory::get(Item::BONE, 0, mt_rand(0, 1))
		];

		if(mt_rand(0, 2) < 1){
			$drops[] = ItemFactory::get(Item::COAL, 0, 1);
		}
		if(mt_rand(1, 40) ===1){
			$drops[] = ItemFactory::get(Item::MOB_HEAD, 1, 1);
		}
		return $drops;
}

	public function onCollideWithEntity(Entity $entity) : void{
		parent::onCollideWithEntity($entity);

		if($entity instanceof Player){
			if($this->getTargetEntity() === $entity){
				$entity->addEffect(new EffectInstance(Effect::getEffect(Effect::WITHER), 200, 1));
			}
		}
	}




	public function getXpDropAmount() : int{
		//TODO: check for equipment
		return 5;
	}

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(0, new FloatBehavior($this));
		$this->behaviorPool->setBehavior(1, new MeleeAttackBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(2, new RandomStrollBehavior($this, 1.0));
		$this->behaviorPool->setBehavior(3, new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorPool->setBehavior(4, new RandomLookAroundBehavior($this));

		$this->targetBehaviorPool->setBehavior(1, new FindAttackableTargetBehavior($this, Player::class));
	}


	public function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		$this->equipment->sendContents([$player]);
	}
}