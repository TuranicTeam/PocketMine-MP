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

namespace pocketmine\entity\pathfinding\navigate;

use pocketmine\block\Block;
use pocketmine\entity\Mob;
use pocketmine\entity\pathfinding\PathFinder;
use pocketmine\entity\pathfinding\processor\WalkNodeProcessor;
use pocketmine\math\Vector3;

class PathNavigateGround extends PathNavigate{
	/** @var WalkNodeProcessor */
	protected $nodeProcessor;
	private $shouldAvoidSun = false;

	public function __construct(Mob $entity){
		$this->nodeProcessor = new WalkNodeProcessor();
		$this->nodeProcessor->setEnterDoors(true);

		parent::__construct($entity);
	}

	protected function getPathFinder() : PathFinder{
		return new PathFinder($this->nodeProcessor);
	}

	protected function canNavigate() : bool{
		return $this->theEntity->onGround or ($this->getCanSwim() and $this->isInLiquid());
	}

	protected function getEntityPosition() : Vector3{
		return new Vector3($this->theEntity->x, $this->getPathablePosY(), $this->theEntity->z);
	}

	protected function getPathablePosY() : int{
		if($this->theEntity->isInsideOfWater() && $this->getCanSwim()){
			$i = $this->theEntity->getBoundingBox()->minY - 1;
			$block = $this->level->getBlock(new Vector3(floor($this->theEntity->x), $i, floor($this->theEntity->z)));
			$j = 0;

			while($block->getId() === Block::WATER or $block->getId() === Block::STILL_WATER){
				++$i;
				$block = $this->level->getBlock(new Vector3(floor($this->theEntity->x), $i, floor($this->theEntity->z)));
				++$j;

				if($j > 16){
					return (int) $this->theEntity->getBoundingBox()->minY;
				}
			}

			return (int) $i;
		}else{
			return (int) $this->theEntity->getBoundingBox()->minY;
		}
	}

	protected function removeSunnyPath() : void{
		if($this->shouldAvoidSun){
			if($this->level->canSeeSky(new Vector3(floor($this->theEntity->x), $this->theEntity->getBoundingBox()->minY + 0.5, floor($this->theEntity->z)))){
				return;
			}

			for($i = 0; $i < $this->currentPath->getCurrentPathLength(); ++$i){
				$point = $this->currentPath->getPathPointFromIndex($i);

				if($this->level->canSeeSky(new Vector3($point->x, $point->y, $point->z))){
					$this->currentPath->setCurrentPathLength($i - 1);
					return;
				}
			}
		}
	}

	/**
	 * @param Vector3 $from
	 * @param Vector3 $to
	 * @param Vector3 $size
	 *
	 * @return bool
	 */
	public function isDirectPathBetweenPoints(Vector3 $from, Vector3 $to, Vector3 $size) : bool{
		$currentX = $from->getFloorX();
		$currentZ = $from->getFloorZ();
		$dX = $to->x - $from->x + 0.001;
		$dZ = $to->z - $from->z + 0.001;
		$distNormal = 1 / sqrt($dX ** 2 + $dZ ** 2);
		$dX *= $distNormal;
		$dZ *= $distNormal;

		if(!$this->isSafeToStandAt($from->floor(), $size->add(2, 0, 2), $from, $dX, $dZ)){
			return false;
		}else{
			$dX = max($dX, 0.0001);
			$dZ = max($dZ, 0.0001);
			$dXN = 1 / abs($dX);
			$dZN = 1 / abs($dZ);
			$xDiff = $currentX - $from->x;
			$zDiff = $currentZ - $from->z;

			if($dX >= 0){
				$xDiff++;
			}

			if($dZ >= 0){
				$zDiff++;
			}

			$xDiff /= $dX;
			$zDiff /= $dZ;

			$pX = $dX < 0 ? -1 : 1;
			$pZ = $dZ < 0 ? -1 : 1;

			$rX = $to->getFloorX() - $currentX;
			$rZ = $to->getFloorZ() - $currentZ;

			while($rX * $pX > 0 or $rZ * $pZ > 0){
				if($xDiff < $zDiff){
					$zDiff += $dXN;
					$currentX += $pX;
					$rX = $to->getFloorX() - $currentX;
				}else{
					$zDiff += $dZN;
					$currentZ += $pZ;
					$rZ = $to->getFloorZ() - $currentZ;
				}

				if(!$this->isSafeToStandAt(new Vector3($currentX, round($from->y), $currentZ), $size, $from, $dX, $dZ)){
					return false;
				}
			}

			return true;
		}
	}

	protected function isSafeToStandAt(Vector3 $current, Vector3 $size, Vector3 $base, float $distX, float $distZ) : bool{
		$a = $current->x - $size->x / 2;
		$c = $current->z - $size->z / 2;

		if(!$this->isPositionClear($current->asVector3()->setComponents($a, $current->y, $c), $size, $base, $distX, $distZ)){
			return false;
		}else{
			for($x = $a; $x < $a + $size->x; ++$x){
				for($z = $c; $z < $c + $size->z; ++$z){
					$x2 = $x + 0.5 - $base->x;
					$z2 = $z + 0.5 - $base->z;

					if($x2 * $distX + $z2 * $distZ >= 0){
						$block = $this->level->getBlock(new Vector3($x, $current->y - 1, $z));

						if($block->getId() === Block::AIR){
							return false;
						}

						if($block->getId() === Block::WATER and !$this->theEntity->isInsideOfWater()){
							return false;
						}

						if($block->getId() === Block::LAVA){
							return false;
						}
					}
				}
			}

			return true;
		}
	}

	/**
	 * @param Vector3 $pos
	 * @param Vector3 $size
	 * @param Vector3 $point
	 * @param float   $distX
	 * @param float   $distZ
	 *
	 * @return bool
	 */
	protected function isPositionClear(Vector3 $pos, Vector3 $size, Vector3 $point, float $distX, float $distZ) : bool{
		$tempVector = new Vector3();

		for($x1 = min($pos->x, $pos->x + $size->x - 1); $x1 <= max($pos->x, $pos->x + $size->x - 1); $x1++){
			for($y1 = min($pos->y, $pos->y + $size->y - 1); $y1 <= max($pos->y, $pos->y + $size->y - 1); $y1++){
				for($z1 = min($pos->z, $pos->z + $size->z - 1); $z1 <= max($pos->z, $pos->z + $size->z - 1); $z1++){
					$tempVector->setComponents($x1, $y1, $z1);

					$d0 = $tempVector->getX() + 0.5 - $point->x;
					$d1 = $tempVector->getZ() + 0.5 - $point->z;

					if($d0 * $distX + $d1 * $distZ >= 0){
						$block = $this->level->getBlock($tempVector);

						if(!$block->isPassable()){
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	public function setAvoidsWater(bool $value) : void{
		$this->nodeProcessor->setAvoidsWater($value);
	}

	public function getAvoidsWater() : bool{
		return $this->nodeProcessor->getAvoidsWater();
	}

	public function setBreakDoors(bool $value) : void{
		$this->nodeProcessor->setBreakDoors($value);
	}

	public function setEnterDoors(bool $value) : void{
		$this->nodeProcessor->setEnterDoors($value);
	}

	public function getEnterDoors() : bool{
		return $this->nodeProcessor->getEnterDoors();
	}

	public function setCanSwim(bool $value) : void{
		$this->nodeProcessor->setCanSwim($value);
	}

	public function getCanSwim() : bool{
		return $this->nodeProcessor->getCanSwim();
	}

	public function setAvoidSun(bool $value) : void{
		$this->shouldAvoidSun = $value;
	}
}