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

class SwimWanderBehavior extends Behavior{

	/** @var float */
	protected $speedMultiplier = 1.0;
	protected $interval;
	protected $lookAhead;

	public function __construct(Mob $mob, float $speedMultiplier = 1.0, int $interval = 10, float $lookAhead = 2.0){
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->interval = $interval;
		$this->lookAhead = $lookAhead;

		$this->mutexBits = 1;
	}

	public function canStart() : bool{
		return $this->random->nextBoundedInt($this->interval) === 0;
	}

	public function canContinue() : bool{
		return $this->lookAhead > 0;
	}

	public function onStart() : void{
		$this->mob->pitch = 0;
		$this->mob->yaw = $this->random->nextBoundedInt(360);
		$this->mob->headYaw = $this->mob->yaw;
		$this->mob->yawOffset = $this->mob->yaw;
		$this->mob->setMoveForward($speed = $this->speedMultiplier * $this->mob->getMovementSpeed());
		$this->mob->setAIMoveSpeed($speed);
	}

	public function onTick() : void{
		$this->lookAhead -= 0.1;
	}

	public function onEnd() : void{
		$this->mob->setMoveForward(0.0);
	}
}