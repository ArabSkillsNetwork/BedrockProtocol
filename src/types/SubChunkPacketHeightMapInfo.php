<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use function array_fill;
use function count;

class SubChunkPacketHeightMapInfo{

	/**
	 * @param int[] $heights ZZZZXXXX key bit order
	 * @phpstan-param list<int> $heights
	 */
	public function __construct(private array $heights){
		if(count($heights) !== 256){
			throw new \InvalidArgumentException("Expected exactly 256 heightmap values");
		}
	}

	/** @return int[] */
	public function getHeights() : array{ return $this->heights; }

	public function getHeight(int $x, int $z) : int{
		return $this->heights[(($z & 0xf) << 4) | ($x & 0xf)];
	}

	public static function read(ByteBufferReader $in) : self{
		$heights = [];
		for($z = 0; $z < 16; ++$z){
			$rowLength = VarInt::readUnsignedInt($in);
			if($rowLength !== 16){
				throw new PacketDecodeException("Expected heightmap row of 16 values, got $rowLength");
			}
			for($x = 0; $x < 16; ++$x){
				$heights[] = Byte::readSigned($in);
			}
		}
		return new self($heights);
	}

	public function write(ByteBufferWriter $out) : void{
		for($z = 0; $z < 16; ++$z){
			VarInt::writeUnsignedInt($out, 16);
			for($x = 0; $x < 16; ++$x){
				Byte::writeSigned($out, $this->heights[($z << 4) | $x]);
			}
		}
	}

	public static function allTooLow() : self{
		return new self(array_fill(0, 256, -1));
	}

	public static function allTooHigh() : self{
		return new self(array_fill(0, 256, 16));
	}

	public function isAllTooLow() : bool{
		foreach($this->heights as $height){
			if($height >= 0){
				return false;
			}
		}
		return true;
	}

	public function isAllTooHigh() : bool{
		foreach($this->heights as $height){
			if($height <= 15){
				return false;
			}
		}
		return true;
	}
}
