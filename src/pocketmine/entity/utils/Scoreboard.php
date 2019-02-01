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

namespace pocketmine\entity\utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use InvalidArgumentException;
class Scoreboard {
	/** @var string */
	public $objectiveName;
	/** @var string */
	public $title;
	/** @var string[] */
	public $lines = [];
	/** @var Player[] */
	public $viewers;

	/**
	 * Scoreboard constructor.
	 *
	 * @param string $objectiveName Scoreboard objective name.
	 * @param string $title Scoreboard title.
	 * @param array  $lines 1-15
	 */
	public function __construct(string $objectiveName = "altay", $title = "Altay", array $lines = []){
		$this->objectiveName = $objectiveName;
		$this->title = $title;
		$linesCount = count($lines);
		if($linesCount > 15){
			throw new InvalidArgumentException("Scoreboard lines can be a max of 15(1 to 14)");
		}
		$this->lines = $lines;
	}

	public function setTitle(string $title, bool $update = false){
		$this->title = $title;
		if($update) $this->updateForAll();
	}

	public function getTitle(): string{
		return $this->title;
	}

	public function setObjectiveName(string $objectiveName){
		$this->objectiveName = $objectiveName;
	}

	public function getObjectiveName(): string{
		return $this->objectiveName;
	}

	public function setLines(array $lines = [], bool $update = false){
		$linesCount = count($lines);
		if($linesCount > 15){
			throw new InvalidArgumentException("Scoreboard lines can be a max of 15(1 to 14)");
		}
		$this->lines = $lines;
		if($update) $this->updateForAll();
	}

	public function getLines(): array{
		return $this->lines;
	}

	public function showTo(Player $player){
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = $this->objectiveName;
		$pk->displayName = $this->title;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 1;
		$player->sendDataPacket($pk);

		foreach(array_values($this->lines) as $line => $str){
			$line++;
			$entry = new ScorePacketEntry();
			$entry->objectiveName = $this->objectiveName;
			$entry->type = $entry::TYPE_FAKE_PLAYER;
			$entry->customName = $str;
			$entry->score = $line;
			$entry->scoreboardId = $line;
			$pk2 = new SetScorePacket();
			$pk2->type = $pk2::TYPE_CHANGE;
			$pk2->entries[] = $entry;
			$player->sendDataPacket($pk2);
		}
		$this->viewers[] = $player;
	}

	public function hideFrom(Player $player){
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $this->objectiveName;
		$player->sendDataPacket($pk);
		unset($this->viewers[array_search($player, $this->viewers)]);
	}

	public function updateFor(Player $player){
		$this->hideFrom($player);
		$this->showTo($player);
	}

	public function updateForAll(){
		foreach($this->viewers as $viewer) $this->updateFor($viewer);
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array{
		return $this->viewers;
	}
}