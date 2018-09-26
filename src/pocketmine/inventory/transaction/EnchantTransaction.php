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

namespace pocketmine\inventory\transaction;

use pocketmine\inventory\transaction\action\EnchantAction;

class EnchantTransaction extends InventoryTransaction{

	public function validate() : void{
		$this->rebuildAction();
		parent::validate();
	}

	public function rebuildAction() : void{
		if(reset($this->actions)->getSourceItem()->isNull()){
			$this->actions = array_reverse($this->actions);
		}

		foreach($this->actions as $key => $action){
			$this->actions[$key] = new EnchantAction($action->getInventory(), $action->getSlot(), $action->getSourceItem(), $action->getTargetItem());
		}
	}
}