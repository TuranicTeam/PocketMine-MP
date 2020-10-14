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

use pocketmine\entity\behavior\AvoidMobTypeBehavior;
use pocketmine\entity\behavior\RandomSwimBehavior;
use pocketmine\entity\behavior\SwimWanderBehavior;
use pocketmine\entity\helper\EntityLookHelper;
use pocketmine\entity\WaterAnimal;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use function mt_rand;

class TropicalFish extends WaterAnimal{
	public const NETWORK_ID = self::TROPICAL_FISH;

	public const SHAPE_A = 0;
	public const SHAPE_B = 1;

	public const PATTERN_1 = 0;
	public const PATTERN_2 = 1;
	public const PATTERN_3 = 2;
	public const PATTERN_4 = 3;
	public const PATTERN_5 = 4;
	public const PATTERN_6 = 5;

	public const COLOR_WHITE = 0;
	public const COLOR_ORANGE = 1;
	public const COLOR_MAGENTA = 2;
	public const COLOR_LIGHTBLUE = 3;
	public const COLOR_YELLOW = 4;
	public const COLOR_LIGHTGREEN = 5;
	public const COLOR_PINK = 6;
	public const COLOR_GRAY = 7;
	public const COLOR_SILVER = 8;
	public const COLOR_CYAN = 9;
	public const COLOR_PURPLE = 10;
	public const COLOR_BLUE = 11;
	public const COLOR_BROWN = 12;
	public const COLOR_GREEN = 13;
	public const COLOR_RED = 14;

	public $width = 0.4;
	public $height = 0.4;

	public function initEntity() : void{
		$this->setMaxHealth(6);

		if($this->namedtag->hasTag("Variant", IntTag::class)){
			$this->propertyManager->setInt(self::DATA_VARIANT, $this->namedtag->getInt("Variant"));
		}else{
			$this->propertyManager->setInt(self::DATA_VARIANT, mt_rand(0, 1));
		}

		if($this->namedtag->hasTag("MarkVariant", IntTag::class)){
			$this->propertyManager->setInt(self::DATA_MARK_VARIANT, $this->namedtag->getInt("MarkVariant"));
		}else{
			$this->propertyManager->setInt(self::DATA_MARK_VARIANT, mt_rand(0, 5));
		}

		if($this->namedtag->hasTag("Color", ByteTag::class)){
			$this->propertyManager->setByte(self::DATA_COLOR, $this->namedtag->getByte("Color"));
		}else{
			$this->propertyManager->setByte(self::DATA_COLOR, mt_rand(0, 14));
		}

		if($this->namedtag->hasTag("Color2", ByteTag::class)){
			$this->propertyManager->setByte(self::DATA_COLOR_2, $this->namedtag->getByte("Color2"));
		}else{
			$this->propertyManager->setByte(self::DATA_COLOR_2, mt_rand(0, 14));
		}

		$this->setMovementSpeed(0.12);

		parent::initEntity();

		$this->navigator->setAvoidsWater(false);
	}

	protected function addBehaviors() : void{
		$this->behaviorPool->setBehavior(1, new AvoidMobTypeBehavior($this, Player::class, null, 6, 1.5, 2.0));
		$this->behaviorPool->setBehavior(3, new RandomSwimBehavior($this, 1.0, 16, 4, 0));
		$this->behaviorPool->setBehavior(4, new SwimWanderBehavior($this, 1.0));
	}

	public function getName() : string{
		return "Tropical Fish";
	}

	public function getDrops() : array{
		$drops = [
			ItemFactory::get(Item::CLOWNFISH, 0, 1),
		];
		if(mt_rand(1, 4) ===1){
			$drops[] = ItemFactory::get(Item::BONE, 1, mt_rand(1, 2));
		}
		return $drops;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if(!$this->isInsideOfWater()){
			$this->jumpHelper->setJumping(true);
		}

		return parent::entityBaseTick($tickDiff);
	}

	public function jump() : void{
		parent::jump();

		$this->yaw = $this->random->nextBoundedInt(360);
		$this->headYaw = $this->yaw;
		$this->yawOffset = $this->yaw;

		$this->setAIMoveSpeed($speed = $this->getMovementSpeed() * 2);
		$this->setMoveForward($speed);

		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_JUMP, -1, self::TROPICAL_FISH);
	}

	protected function applyGravity() : void{
		if(!$this->isUnderwater()){
			parent::applyGravity();
		}
	}
}