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

namespace pocketmine\entity\pathfinding;

use pocketmine\math\Vector3;

class PathEntity{
	/** @var PathPoint[] */
	protected $points;
	protected $currentPathIndex = 0;
	protected $pathLength = 0;

	/**
	 * PathEntity constructor.
	 *
	 * @param PathPoint[] $points
	 */
	public function __construct(array $points){
		$this->points = $points;
		$this->pathLength = count($points);
	}

	public function incrementPathIndex() : void{
		++$this->currentPathIndex;
	}

	public function isFinished() : bool{
		return $this->currentPathIndex >= $this->pathLength;
	}

	public function getFinalPathPoint() : ?PathPoint{
		return $this->pathLength > 0 ? $this->points[$this->pathLength - 1] : null;
	}

	public function getPathPointFromIndex(int $index) : ?PathPoint{
		return $this->points[$index] ?? null;
	}

	public function getCurrentPathLength() : int{
		return $this->pathLength;
	}

	public function setCurrentPathLength(int $length) : void{
		$this->pathLength = $length;
	}

	public function getCurrentPathIndex() : int{
		return $this->currentPathIndex;
	}

	public function setCurrentPathIndex(int $index) : void{
		$this->currentPathIndex = $index;
	}

	public function getVectorFromIndex(int $index) : ?Vector3{
		return ($point = $this->getPathPointFromIndex($index)) !== null ? $point->add(0.5, 0, 0.5) : null;
	}

	public function getPosition() : ?Vector3{
		return $this->getVectorFromIndex($this->currentPathIndex);
	}

	public function isSamePath(PathEntity $pathEntity) : bool{
		if(count($pathEntity->points) !== count($this->points)){
			return false;
		}else{
			foreach($this->points as $point){
				foreach($pathEntity->points as $point2){
					if(!$point->equals($point2)){
						return false;
					}
				}
			}

			return true;
		}
	}

	public function isDestinationSame(Vector3 $vec) : bool{
		$point = $this->getFinalPathPoint();
		return $point == null ? false : $point->x == $vec->x and $point->z == $vec->z;
	}
}