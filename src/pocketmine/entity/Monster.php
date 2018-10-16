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

namespace pocketmine\entity;

use pocketmine\level\Level;
use pocketmine\math\Vector3;

abstract class Monster extends Mob{

	protected function isValidLightLevel() : bool{
		if($this->level->getBlockSkyLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) < $this->random->nextBoundedInt(32)){
			$i = max(
				$this->level->getBlockLightAt($this->getFloorX(), $this->getFloorY() + 1, $this->getFloorZ()),
				$this->level->getBlockLightAt($this->getFloorX(), $this->getFloorY() - 1, $this->getFloorZ()),
				$this->level->getBlockLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ() + 1),
				$this->level->getBlockLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ() - 1),
				$this->level->getBlockLightAt($this->getFloorX() + 1, $this->getFloorY(), $this->getFloorZ()),
				$this->level->getBlockLightAt($this->getFloorX() - 1, $this->getFloorY(), $this->getFloorZ())
			);

			return $i <= $this->random->nextBoundedInt(8);
		}
		return false;
	}

	public function canSpawnHere() : bool{
		return $this->level->getDifficulty() !== Level::DIFFICULTY_PEACEFUL and $this->isValidLightLevel();
	}

	public function getBlockPathWeight(Vector3 $pos) : float{
		return 0.5 - $this->level->getFullLight($pos);
	}

}