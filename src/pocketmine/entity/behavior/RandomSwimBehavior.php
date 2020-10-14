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

namespace pocketmine\entity\behavior;

use pocketmine\block\Water;
use pocketmine\entity\Mob;
use pocketmine\entity\utils\RandomPositionGenerator;

class RandomSwimBehavior extends Behavior{

	/** @var float */
	protected $speedMultiplier = 1.0;
	/** @var int */
	protected $interval = 120;
	protected $xzDist;
	protected $yDist;

	protected $targetPos;

	public function __construct(Mob $mob, float $speedMultiplier = 1.0, int $xzDist = 10, int $yDist = 7, int $interval = 120){
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->interval = $interval;
		$this->xzDist = $xzDist;
		$this->yDist = $yDist;

		$this->mutexBits = 1;
	}

	public function canStart() : bool{
		if($this->interval <= 0 or $this->random->nextBoundedInt($this->interval) === 0){
			$pos = RandomPositionGenerator::findRandomTargetBlock($this->mob, $this->xzDist, $this->yDist);

			if($pos instanceof Water){
				$this->targetPos = $pos;

				return true;
			}
		}

		return false;
	}

	public function canContinue() : bool{
		return !$this->mob->getNavigator()->noPath();
	}

	public function onStart() : void{
		$this->mob->getNavigator()->tryMoveToPos($this->targetPos, $this->speedMultiplier);
	}

	public function onEnd() : void{
		$this->targetPos = null;
		$this->mob->getNavigator()->clearPathEntity();
	}
}