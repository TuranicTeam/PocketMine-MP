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
use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\entity\pathfinding\PathEntity;
use pocketmine\entity\pathfinding\PathFinder;
use pocketmine\entity\pathfinding\processor\WalkNodeProcessor;
use pocketmine\math\Vector3;

class PathNavigateClimber extends PathNavigateGround{
	/** @var Vector3|null */
	protected $targetPos;

	public function getPathToPos(Vector3 $pos) : ?PathEntity{
		$this->targetPos = $pos;
		return parent::getPathToPos($pos);
	}

	public function getPathToEntity(Entity $entity){
		$this->targetPos = $entity->asVector3();
		return parent::getPathToEntity($entity);
	}

	public function tryMoveToEntity(Entity $entity, float $speed) : bool{
		$path = $this->getPathToEntity($entity);

		if($path !== null){
			return $this->setPath($path, $speed);
		}else{
			$this->targetPos = $entity->asVector3();
			$this->setSpeed($speed);

			return true;
		}
	}

	public function tick() : void{
		if(!$this->noPath()){
			parent::tick();
		}elseif($this->targetPos !== null){
			$f = $this->theEntity->width ** 2;

			if($this->theEntity->distanceSquared($this->targetPos->add(0.5, 0.5, 0.5)) >= $f and
				($this->theEntity->y <= $this->targetPos->y or
					$this->theEntity->distanceSquared(new Vector3($this->targetPos->x + 0.5, floor($this->theEntity->y) + 0.5, $this->targetPos->z)) >= $f)){
				$this->theEntity->getMoveHelper()->moveTo($this->targetPos->x, $this->targetPos->y, $this->targetPos->z, $this->speed);
			}else{
				$this->targetPos = null;
			}
		}
	}
}