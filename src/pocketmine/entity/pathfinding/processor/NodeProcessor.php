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

use pocketmine\entity\Entity;
use pocketmine\entity\pathfinding\PathPoint;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;

abstract class NodeProcessor{
	/** @var Level */
	protected $level;
	protected $pointMap = [];
	/** @var AxisAlignedBB */
	protected $entityBlockBox;

	public function initProcessor(Level $level, Entity $entity){
		$this->level = $level;
		$this->pointMap = [];

		$this->entityBlockBox = new AxisAlignedBB(0, 0, 0, floor($entity->width + 1), floor($entity->height + 1), floor($entity->width + 1));
	}

	public function postProcess() : void{
		// NOOP
	}

	protected function openPoint(float $x, float $y, float $z) : PathPoint{
		$i = PathPoint::makeHash($x, $y, $z);

		if (empty($this->pointMap[$i])){
			$point = new PathPoint($x, $y, $z);
			$this->pointMap[$i] = $point;
		}else{
			$point = $this->pointMap[$i];
		}

		return $point;
	}

	public abstract function getPathPointTo(Entity $entity) : PathPoint;

	public abstract function getPathPointToCoords(Entity $entity, float $x, float $y, float $z) : PathPoint;

	public abstract function findPathOptions(array &$pathOptions, Entity $entity, PathPoint $currentPoint, PathPoint $targetPoint, float $maxDistance) : int;
}