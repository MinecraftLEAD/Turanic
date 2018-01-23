<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\tile\Chest;

class DoubleChestInventory extends ChestInventory implements InventoryHolder{
	/** @var ChestInventory */
	private $left;
	/** @var ChestInventory */
	private $right;

	public function __construct(Chest $left, Chest $right){
		$this->left = $left->getRealInventory();
		$this->right = $right->getRealInventory();
		$items = array_merge($this->left->getContents(true), $this->right->getContents(true));
		BaseInventory::__construct($items);
	}

	public function getName() : string{
		return "Double Chest";
	}

	public function getDefaultSize() : int{
		return $this->left->getDefaultSize() + $this->right->getDefaultSize();
	}

	public function getInventory(){
		return $this;
	}

	/**
	 * @return Chest
	 */
	public function getHolder(){
		return $this->left->getHolder();
	}

	public function getItem(int $index) : Item{
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->right->getSize());
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool{
		return $index < $this->left->getSize() ? $this->left->setItem($index, $item, $send) : $this->right->setItem($index - $this->right->getSize(), $item, $send);
	}

	public function clear(int $index, bool $send = true) : bool{
		return $index < $this->left->getSize() ? $this->left->clear($index, $send) : $this->right->clear($index - $this->right->getSize(), $send);
	}

	/**
	 * @param Item[] $items
	 * @param bool   $send
	 */
	public function setContents(array $items, bool $send = true) {
		$size = $this->getSize();
		if(count($items) > $size){
			$items = array_slice($items, 0, $size, true);
		}

		$leftSize = $this->left->getSize();

		for($i = 0; $i < $size; ++$i){
			if(!isset($items[$i])){
				if(($i < $leftSize and isset($this->left->slots[$i])) or isset($this->right->slots[$i - $leftSize])){
					$this->clear($i, false);
				}
			}elseif(!$this->setItem($i, $items[$i], false)){
				$this->clear($i, false);
			}
		}

		if($send){
			$this->sendContents($this->getViewers());
		}
	}

	public function onOpen(Player $who) {
		parent::onOpen($who);

		if(count($this->getViewers()) === 1 and ($level = $this->right->getHolder()->getLevel()) instanceof Level){
			$this->broadcastBlockEventPacket($this->right->getHolder(), true);
		}
	}

	public function onClose(Player $who) {
		if(count($this->getViewers()) === 1 and ($level = $this->right->getHolder()->getLevel()) instanceof Level){
			$this->broadcastBlockEventPacket($this->right->getHolder(), false);
		}
		parent::onClose($who);
	}

	/**
	 * @return ChestInventory
	 */
	public function getLeftSide() : ChestInventory{
		return $this->left;
	}

	/**
	 * @return ChestInventory
	 */
	public function getRightSide() : ChestInventory{
		return $this->right;
	}

	public function invalidate(){
		$this->left = null;
		$this->right = null;
	}
}