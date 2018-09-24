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

namespace pocketmine\inventory\transaction\action;

use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class EnchantAction extends InventoryAction{

	protected $inventory;
	private $inventorySlot;


	public function __construct(Inventory $inventory, int $inventorySlot, Item $sourceItem, Item $targetItem){
		parent::__construct($sourceItem, $targetItem);
		$this->inventory = $inventory;
		$this->inventorySlot = $inventorySlot;
	}

	public function getInventory() : Inventory{
		return $this->inventory;
	}

	public function getSlot() : int{
		return $this->inventorySlot;
	}

	public function isValid(Player $source) : bool{
		$contents = $this->inventory->getContents();
		if($this->sourceItem->isNull()){
			if(isset($contents[0], $contents[1]) && $contents[$this->inventorySlot]->equals($this->targetItem, true, false)){
				if(!$contents[0]->isNull() && ($source->isCreative(true) || $this->isMaterial($contents[1])) && $this->targetItem->hasEnchantments()){
					return true;
				}
			}elseif($this->inventorySlot === 0 && $this->isMaterial($this->targetItem) && $this->inventory->contains($this->targetItem)){
				$level = $this->targetItem->count;
				if($source->getXpLevel() < $level){
					return false;
				}

				$this->inventorySlot = 1;
				$temp = clone $contents[1];
				$temp->count -= $level;
				$this->targetItem = $temp;
				return true;
			}
			return $source->getInventory()->contains($this->targetItem) || $source->getCursorInventory()->contains($this->targetItem) || $this->inventory->contains($this->targetItem);
		}else{
			return $this->inventory->contains($this->sourceItem) || (isset($contents[$this->inventorySlot]) && $contents[$this->inventorySlot]->equals($this->sourceItem, true, false));
		}
	}

	public function isMaterial(Item $item) : bool{
		return $item->getId() === Item::DYE && $item->getDamage() === 4;
	}

	public function onAddToTransaction(InventoryTransaction $transaction) : void{
		$transaction->addInventory($this->inventory);
	}

	public function execute(Player $source) : bool{
		return $this->inventory->setItem($this->inventorySlot, $this->targetItem, false);
	}

	public function onExecuteSuccess(Player $source) : void{
		$viewers = $this->inventory->getViewers();
		unset($viewers[spl_object_hash($source)]);
		$this->inventory->sendSlot($this->inventorySlot, $viewers);
	}

	public function onExecuteFail(Player $source) : void{
		$this->inventory->sendSlot($this->inventorySlot, $source);
	}
}
