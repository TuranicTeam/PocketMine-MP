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

namespace pocketmine\entity\pathfinding\processor;

use pocketmine\block\Block;
use pocketmine\block\Door;
use pocketmine\block\Fence;
use pocketmine\block\FenceGate;
use pocketmine\block\Rail;
use pocketmine\block\StoneBricks;
use pocketmine\entity\Entity;
use pocketmine\entity\pathfinding\PathPoint;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class WalkNodeProcessor extends NodeProcessor{

	protected $canEnterDoors = false;
	protected $canBreakDoors = false;
	protected $avoidsWater = false;
	protected $canSwim = false;
	protected $shouldAvoidWater = false;

	public function initProcessor(Level $level, Entity $entity){
		parent::initProcessor($level, $entity);

		$this->shouldAvoidWater = $this->avoidsWater;
	}

	public function postProcess() : void{
		$this->avoidsWater = $this->shouldAvoidWater;
	}

	public function getPathPointTo(Entity $entity) : PathPoint{
		if($this->canSwim and $entity->isInsideOfWater()){
			$i = $entity->getBoundingBox()->minY;
			$mutablePos = new Vector3(floor($entity->x), $i, floor($entity->z));

			for($block = $this->level->getBlock($mutablePos); $block->getId() == Block::STILL_WATER or $block->getId() == Block::WATER; $block = $this->level->getBlock($mutablePos)){
				++$i;
				$mutablePos->setComponents(floor($entity->x), $i, floor($entity->z));
			}

			$this->avoidsWater = false;
		}else{
			$i = floor($entity->getBoundingBox()->minY + 0.5);
		}

		return $this->openPoint(floor($entity->getBoundingBox()->minX), $i, floor($entity->getBoundingBox()->minZ));
	}

	public function getPathPointToCoords(Entity $entity, float $x, float $y, float $z) : PathPoint{
		return $this->openPoint(floor($x - ($entity->width / 2.0)), floor($y), floor($z - ($entity->width / 2.0)));
	}

	public function findPathOptions(array &$pathOptions, Entity $entity, PathPoint $currentPoint, PathPoint $targetPoint, float $maxDistance) : int{
		$i = 0;
		$j = 0;

		if($this->getState($entity, $currentPoint->add(0, 1, 0)) === 1){
			$j = 1;
		}

		$point = $this->getSafePoint($entity, $currentPoint->x, $currentPoint->y + 1, $currentPoint->z + 1, $j);
		$point1 = $this->getSafePoint($entity, $currentPoint->x - 1, $currentPoint->y + 1, $currentPoint->z, $j);
		$point2 = $this->getSafePoint($entity, $currentPoint->x + 1, $currentPoint->y + 1, $currentPoint->z, $j);
		$point3 = $this->getSafePoint($entity, $currentPoint->x, $currentPoint->y + 1, $currentPoint->z - 1, $j);

		if($point !== null and !$point->visited and $point->distance($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $point;
		}

		if($point1 !== null and !$point1->visited and $point1->distance($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $point1;
		}

		if($point2 !== null and !$point2->visited and $point2->distance($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $point2;
		}

		if($point3 !== null and !$point3->visited and $point3->distance($targetPoint) < $maxDistance){
			$pathOptions[$i++] = $point3;
		}

		return $i;
	}

	protected function getSafePoint(Entity $entity, float $x, float $y, float $z, int $yOffset) : ?PathPoint{
		$point = null;
		$i = $this->getState($entity, $pos = new Vector3($x, $y, $z));

		if($i === 2){
			return $this->openPoint($x, $y, $z);
		}else{
			if($i === 1){
				$point = $this->openPoint($x, $y, $z);
			}

			if($point === null and $yOffset > 0 and $i !== -3 and $i !== -4 and $this->getState($entity, $pos->add(0 , $yOffset, 0)) === 1){
				$point = $this->openPoint($x, $y + $yOffset, $z);
				$y += $yOffset;
			}

			if($point !== null){
				$j = 0;

				for($k = 0; $y > 0; $point = $this->openPoint($x, $y, $z)){
					$k = $this->getState($entity, new Vector3($x, $y - 1, $z));

					if($this->avoidsWater and $k === -1){
						return null;
					}

					if($k !== 1){
						break;
					}

					if($j++ >= 3){ // TODO: add a function for this
						return null;
					}

					--$y;

					if($y <= 0){
						return null;
					}
				}

				if($k === -2){
					return null;
				}
			}

			return $point;
		}
	}

	protected function getState(Entity $entity, Vector3 $pos) : int{
		return self::getPositionState($entity, $this->entityBlockBox->offsetCopy($pos->x, $pos->y, $pos->z), $this->avoidsWater, $this->canBreakDoors, $this->canEnterDoors);
	}

	public static function getPositionState(Entity $entity, AxisAlignedBB $bb, bool $avoidWater, bool $breakDoors, bool $enterDoors) : int{
		$flag = false;
		$mutablePos = new Vector3();

		for($x = $bb->minX; $x < $bb->maxX; ++$x){
			for($y = $bb->minY; $y < $bb->maxY; ++$y){
				for($z = $bb->minZ; $z < $bb->maxZ; ++$z){
					$block = $entity->level->getBlock($mutablePos->setComponents($x, $y, $z));

					if($block->getId() !== Block::AIR){
						if($block->getId() !== Block::TRAPDOOR and $block->getId() !== Block::IRON_TRAPDOOR){
							if($block->getId() !== Block::STILL_WATER and $block->getId() !== Block::WATER){
								if(!$enterDoors and $block instanceof Door){
									return 0;
								}
							}else{
								if($avoidWater){
									return -1;
								}

								$flag = true;
							}
						}else{
							$flag = true;
						}

						if($block instanceof Rail){
							if(!($entity->level->getBlock($entity) instanceof Rail) and !($entity->level->getBlock($entity->getSide(Vector3::SIDE_DOWN)) instanceof Rail)){
								return -3;
							}
						}elseif(!$block->isTransparent() and (!$breakDoors or !($block instanceof Door))){
							if($block instanceof Fence or $block instanceof FenceGate or $block instanceof StoneBricks){
								return -3;
							}

							if($block->getId() !== Block::TRAPDOOR and $block->getId() !== Block::IRON_TRAPDOOR){
								return -4;
							}

							if($block->getId() === Block::LAVA){
								return 0;
							}

							if(!$entity->isInsideOfLava()){
								return -2;
							}
						}
					}
				}
			}
		}

		return $flag ? 2 : 1;
	}

	public function setEnterDoors(bool $value) : void{
		$this->canEnterDoors = $value;
	}

	public function setBreakDoors(bool $value) : void{
		$this->canBreakDoors = $value;
	}

	public function setAvoidsWater(bool $value) : void{
		$this->avoidsWater = $value;
	}

	public function setCanSwim(bool $value) : void{
		$this->canSwim = $value;
	}

	public function getEnterDoors() : bool{
		return $this->canEnterDoors;
	}

	public function getCanSwim() : bool{
		return $this->canSwim;
	}

	public function getAvoidsWater() : bool{
		return $this->avoidsWater;
	}
}