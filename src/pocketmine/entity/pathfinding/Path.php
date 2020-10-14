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

class Path{
	/** @var PathPoint[] */
	protected $pathPoints = [];
	protected $count = 0;

	public function addPoint(PathPoint $point) : PathPoint{
		if($point->index === -1){
			$this->pathPoints[$this->count] = $point;
			$point->index = $this->count;
			$this->sortBack($this->count++);
		}

		return $point;
	}

	public function dequeue() : ?PathPoint{
		$point = $this->pathPoints[0] ?? null;

		if($point !== null){
			$this->pathPoints[0] = $this->pathPoints[--$this->count];
			unset($this->pathPoints[$this->count]);

			if($this->count > 0){
				$this->sortForward(0);
			}

			$point->index = -1;
		}

		return $point;
	}

	public function changeDistance(PathPoint $point, float $distance) : void{
		$f = $point->distanceToTarget;
		$point->distanceToTarget = $distance;

		if($distance < $f){
			$this->sortBack($point->index);
		}else{
			$this->sortForward($point->index);
		}
	}

	protected function sortBack(int $index) : void{
		$point = $this->pathPoints[$index] ?? null;
		if($point !== null){
			$point1 = null;

			for($f = $point->distanceToTarget; $index > 0; $index = $i){
				$i = $index - 1 >> 1;
				$point1 = $this->pathPoints[$i] ?? null;

				if($point1 === null or $f >= $point1->distanceToTarget){
					break;
				}

				$this->pathPoints[$index] = $point1;
				$point1->index = $index;
			}

			if($point1 !== null){
				$this->pathPoints[$index] = $point;
				$point->index = $index;
			}
		}
	}

	protected function sortForward(int $index) : void{
		$point = $this->pathPoints[$index] ?? null;
		if($point !== null){
			$f = $point->distanceToTarget;

			while(true){
				$i = 1 + ($index << 1);
				$j = $i + 1;

				if($i >= $this->count){
					break;
				}

				$point1 = $this->pathPoints[$i];
				$f1 = $point1->distanceToTarget;

				if($j >= $this->count){
					$point2 = null;
					$f2 = PHP_INT_MAX;
				}else{
					$point2 = $this->pathPoints[$j];
					$f2 = $point2->distanceToTarget;
				}

				if($f1 < $f2){
					if($f1 >= $f){
						break;
					}

					$this->pathPoints[$index] = $point1;
					$point1->index = $index;
					$index = $i;
				}else{
					if($f2 >= $f){
						break;
					}

					$this->pathPoints[$index] = $point2;
					$point2->index = $index;
					$index = $j;
				}
			}

			$this->pathPoints[$index] = $point;
			$point->index = $index;
		}
	}

	public function clearPath() : void{
		$this->count = 0;
	}

	public function isEmpty() : bool{
		return $this->count === 0;
	}

	public function getCount() : int{
		return $this->count;
	}
}