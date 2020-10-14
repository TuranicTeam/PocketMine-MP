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
use pocketmine\block\BlockFactory;
use pocketmine\block\Door;
use pocketmine\block\Fence;
use pocketmine\block\FenceGate;
use pocketmine\block\Rail;
use pocketmine\block\StoneBricks;
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\entity\pathfinding\PathPoint;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class SwimNodeProcessor extends NodeProcessor{

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
		return $this->openPoint(floor($entity->getBoundingBox()->minX), floor($entity->getBoundingBox()->minX + 0.5), floor($entity->getBoundingBox()->minZ));
	}

	public function getPathPointToCoords(Entity $entity, float $x, float $y, float $z) : PathPoint{
		return $this->openPoint(floor($x - ($entity->width / 2)), floor($y + 0.5), floor($z - ($entity->width / 2)));
	}

	public function findPathOptions(array &$pathOptions, Entity $entity, PathPoint $currentPoint, PathPoint $targetPoint, float $maxDistance) : int{
		$i = 0;

		foreach([Vector3::SIDE_DOWN, Vector3::SIDE_UP, Vector3::SIDE_EAST, Vector3::SIDE_SOUTH, Vector3::SIDE_WEST, Vector3::SIDE_NORTH] as $side){
			$vec = $currentPoint->getSide($side);
			$point = $this->getSafePoint($entity, $vec->x, $vec->y, $vec->z);

			if($point !== null and !$point->visited and $point->distanceSquared($targetPoint) < $maxDistance){
				$pathOptions[$i++] = $point;
			}
		}

		return $i;
	}

	protected function getSafePoint(Entity $entity, float $x, float $y, float $z) : ?PathPoint{
		return $this->isFullyWater($x, $y, $z) ? $this->openPoint($x, $y, $z) : null;
	}

	protected function isFullyWater(float $x, float $y, float $z) : bool{
		$tempVector = new Vector3();

		for($x1 = $x; $x1 < $x + $this->entityBlockBox->maxX; $x1++){
			for($y1 = $y; $y1 < $y + $this->entityBlockBox->maxY; $y1++){
				for($z1 = $z; $z1 < $z + $this->entityBlockBox->maxZ; $z1++){
					$block = $this->level->getBlock($tempVector->setComponents($x1, $y1, $z1));

					if(!($block instanceof Water)){
						return false;
					}
				}
			}
		}

		return true;
	}
}