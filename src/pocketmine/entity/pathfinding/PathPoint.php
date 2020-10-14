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

class PathPoint extends Vector3{
	/** @var int */
	protected $hash;

	public $index = -1;
	public $totalPathDistance = 0;
	public $distanceToNext = 0;
	public $distanceToTarget = 0;
	/** @var PathPoint|null */
	public $previous;
	public $visited = false;

	public function __construct($x = 0, $y = 0, $z = 0){
		parent::__construct($x, $y, $z);

		$this->hash = PathPoint::makeHash($x, $y, $z);
	}

	public static function makeHash($x, $y, $z){
		return $y & 255 | ($x & 32767) << 8 | ($z & 32767) << 24 | ($x < 0 ? -2147483648 : 0) | ($z < 0 ? 32768 : 0);
	}

	public function equals(Vector3 $point) : bool{
		if (!($point instanceof PathPoint)){
			return false;
		}else{
			return $this->hash == $point->hash;
		}
	}

	public function hashCode() : int{
		return $this->hash;
	}

	public function isAssigned() : bool{
		return $this->index >= 0;
	}
}