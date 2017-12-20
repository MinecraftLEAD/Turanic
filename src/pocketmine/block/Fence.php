<?php

/*
 *
 *    _______                    _
 *   |__   __|                  (_)
 *      | |_   _ _ __ __ _ _ __  _  ___
 *      | | | | | '__/ _` | '_ \| |/ __|
 *      | | |_| | | | (_| | | | | | (__
 *      |_|\__,_|_|  \__,_|_| |_|_|\___|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Turanic
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class Fence extends Transparent {

	const FENCE_OAK = 0;
	const FENCE_SPRUCE = 1;
	const FENCE_BIRCH = 2;
	const FENCE_JUNGLE = 3;
	const FENCE_ACACIA = 4;
	const FENCE_DARKOAK = 5;

	protected $id = self::FENCE;

	/**
	 * Fence constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return int
	 */
	public function getHardness(){
		return 2;
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	/**
	 * @return int
	 */
	public function getBurnChance() : int{
		return 5;
	}

	/**
	 * @return int
	 */
	public function getBurnAbility() : int{
		return 20;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		static $names = [
			0 => "Oak Fence",
			1 => "Spruce Fence",
			2 => "Birch Fence",
			3 => "Jungle Fence",
			4 => "Acacia Fence",
			5 => "Dark Oak Fence"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	/**
	 * @return AxisAlignedBB
	 */
	protected function recalculateBoundingBox(){

		$north = $this->canConnect($this->getSide(Vector3::SIDE_NORTH));
		$south = $this->canConnect($this->getSide(Vector3::SIDE_SOUTH));
		$west = $this->canConnect($this->getSide(Vector3::SIDE_WEST));
		$east = $this->canConnect($this->getSide(Vector3::SIDE_EAST));

		$n = $north ? 0 : 0.375;
		$s = $south ? 1 : 0.625;
		$w = $west ? 0 : 0.375;
		$e = $east ? 1 : 0.625;

		return new AxisAlignedBB(
			$this->x + $w,
			$this->y,
			$this->z + $n,
			$this->x + $e,
			$this->y + 1.5,
			$this->z + $s
		);
	}

	/**
	 * @param Block $block
	 *
	 * @return bool
	 */
	public function canConnect(Block $block){
		return ($block instanceof Fence or $block instanceof FenceGate) ? true : $block->isSolid() and !$block->isTransparent();
	}

    protected function recalculateCollisionBoxes() : array{
        $inset = 0.5 - 0.25 / 2;
        /** @var AxisAlignedBB[] $bbs */
        $bbs = [];
        $connectWest = $this->canConnect($this->getSide(Vector3::SIDE_WEST));
        $connectEast = $this->canConnect($this->getSide(Vector3::SIDE_EAST));
        if($connectWest or $connectEast){
            //X axis (west/east)
            $bbs[] = new AxisAlignedBB(
                $this->x + ($connectWest ? 0 : $inset),
                $this->y,
                $this->z + $inset,
                $this->x + 1 - ($connectEast ? 0 : $inset),
                $this->y + 1.5,
                $this->z + 1 - $inset
            );
        }
        $connectNorth = $this->canConnect($this->getSide(Vector3::SIDE_NORTH));
        $connectSouth = $this->canConnect($this->getSide(Vector3::SIDE_SOUTH));
        if($connectNorth or $connectSouth){
            //Z axis (north/south)
            $bbs[] = new AxisAlignedBB(
                $this->x + $inset,
                $this->y,
                $this->z + ($connectNorth ? 0 : $inset),
                $this->x + 1 - $inset,
                $this->y + 1.5,
                $this->z + 1 - ($connectSouth ? 0 : $inset)
            );
        }
        if(empty($bbs)){
            //centre post AABB (only needed if not connected on any axis - other BBs overlapping will do this if any connections are made)
            return [
                new AxisAlignedBB(
                    $this->x + $inset,
                    $this->y,
                    $this->z + $inset,
                    $this->x + 1 - $inset,
                    $this->y + 1.5,
                    $this->z + 1 - $inset
                )
            ];
        }
        return $bbs;
    }

    public function getFuelTime(): int{
        return 300;
    }

}
