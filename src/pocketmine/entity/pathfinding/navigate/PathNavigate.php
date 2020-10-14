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

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\entity\pathfinding\PathEntity;
use pocketmine\entity\pathfinding\PathFinder;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

abstract class PathNavigate{

	/** @var Mob */
	protected $theEntity;
	/** @var Level */
	protected $level;
	/** @var PathEntity|null */
	protected $currentPath;
	/** @var float */
	protected $speed = 1.0;

	private $pathSearchRange = 16;
	private $totalTicks = 0;
	private $ticksAtLastPos = 0;
	private $lastPosCheck;
	private $heightRequirement = 1.0;
	/** @var PathFinder */
	private $pathFinder;

	public function __construct(Mob $entity){
		$this->theEntity = $entity;
		$this->level = $entity->getLevel();
		$this->pathFinder = $this->getPathFinder();
		$this->lastPosCheck = new Vector3();
	}

	protected abstract function getPathFinder() : PathFinder;

	public function setSpeed(float $speedIn) : void{
		$this->speed = $speedIn;
	}

	public function getPathSearchRange() : int{
		return $this->pathSearchRange;
	}

	public function getPathToPos(Vector3 $pos) : ?PathEntity{
		if(!$this->canNavigate()){
			return null;
		}else{
			return $this->pathFinder->findPathToPosition($this->level, $this->theEntity, $pos, $this->getPathSearchRange());
		}
	}

	public function tryMoveToPos(Vector3 $pos, float $speed) : bool{
		return $this->setPath($this->getPathToPos($pos), $speed);
	}

	public function setHeightRequirement(float $jumpHeight) : void{
		$this->heightRequirement = $jumpHeight;
	}

	public function getPathToEntity(Entity $entity){
		if(!$this->canNavigate()){
			return null;
		}else{
			return $this->pathFinder->findPathToEntity($this->level, $this->theEntity, $entity, $this->getPathSearchRange());
		}
	}

	public function tryMoveToEntity(Entity $entity, float $speed) : bool{
		$v = $this->setPath($path = $this->getPathToEntity($entity), $speed);

		return $v;
	}

	public function setPath(?PathEntity $pathEntity, float $speed) : bool{
		if($pathEntity === null){
			$this->currentPath = null;
			return false;
		}else{
			if($this->currentPath === null or !$pathEntity->isSamePath($this->currentPath)){
				$this->currentPath = $pathEntity;
			}

			$this->removeSunnyPath();

			if($this->currentPath->getCurrentPathLength() === 0){
				return false;
			}else{
				$this->speed = $speed;
				$this->ticksAtLastPos = $this->totalTicks;
				$this->lastPosCheck = $this->getEntityPosition();

				return true;
			}
		}
	}

	public function getPath() : ?PathEntity{
		return $this->currentPath;
	}

	public function tick() : void{
		++$this->totalTicks;

		if(!$this->noPath()){
			if($this->canNavigate()){
				$this->pathFollow();
			}elseif($this->currentPath != null and $this->currentPath->getCurrentPathIndex() < $this->currentPath->getCurrentPathLength()){
				$pos1 = $this->getEntityPosition();
				$pos2 = $this->currentPath->getVectorFromIndex($this->currentPath->getCurrentPathIndex());

				if($pos1->y > $pos2->y and !$this->theEntity->onGround and floor($pos1->x) == floor($pos2->x) and floor($pos1->z) == floor($pos2->z)){
					$this->currentPath->setCurrentPathIndex($this->currentPath->getCurrentPathIndex() + 1);
				}
			}

			if(!$this->noPath()){
				$pos = $this->currentPath->getPosition();
				if($pos !== null){
					$this->theEntity->getMoveHelper()->moveTo($pos->x + 0.5, $pos->y, $pos->z + 0.5, $this->speed);
				}
			}
		}
	}

	protected function pathFollow() : void{
		$vec3 = $this->getEntityPosition();
		$i = $this->currentPath->getCurrentPathLength();

		for($j = $this->currentPath->getCurrentPathIndex(); $j < $this->currentPath->getCurrentPathLength(); ++$j){
			if($this->currentPath->getPathPointFromIndex($j)->y != $vec3->y){
				$i = $j;
				break;
			}
		}

		$f = $this->theEntity->width * $this->theEntity->width * $this->heightRequirement;

		for($k = $this->currentPath->getCurrentPathIndex(); $k < $i; ++$k){
			$vec31 = $this->currentPath->getVectorFromIndex($k);

			if($vec3->distanceSquared($vec31) < $f){
				$this->currentPath->setCurrentPathIndex($k + 1);
			}
		}

		$j1 = ceil($this->theEntity->width + 0.25);
		$k1 = $this->theEntity->height + 1;
		$l = $j1;

		for($i1 = $i - 1; $i1 >= $this->currentPath->getCurrentPathIndex(); --$i1){
			if($this->isDirectPathBetweenPoints($vec3, $this->currentPath->getVectorFromIndex($i1), new Vector3($j1, $k1, $l))){
				$this->currentPath->setCurrentPathIndex($i1);
				$this->checkForStuck($vec3);
				return;
			}
		}

		$this->checkForStuck($vec3);
	}

	protected function checkForStuck(Vector3 $pos) : void{
		if($this->totalTicks - $this->ticksAtLastPos > 100){
			if($pos->distanceSquared($this->lastPosCheck) < 2.25){
				$this->clearPathEntity();
			}

			$this->ticksAtLastPos = $this->totalTicks;
			$this->lastPosCheck = $pos;
		}
	}

	public function noPath() : bool{
		return $this->currentPath === null or $this->currentPath->isFinished();
	}

	public function clearPathEntity() : void{
		$this->currentPath = null;
	}

	protected abstract function getEntityPosition() : Vector3;

	protected abstract function canNavigate() : bool;

	protected function isInLiquid() : bool{
		return $this->theEntity->isInsideOfWater() or $this->theEntity->isInsideOfLava();
	}

	protected function removeSunnyPath() : void{
		// NOOP
	}

	public abstract function isDirectPathBetweenPoints(Vector3 $from, Vector3 $to, Vector3 $size) : bool;
}