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

namespace pocketmine\network\mcpe\protocol\types\entity;

use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

/**
 * Describes a block which an entity is riding.
 * @see SetPassengerOfBlockPacket
 */
final class PassengerOfBlockData{

	/**
	 * @param Vector3 $offset        Offset of the entity relative to the block position. Each component must be between -5 and 5.
	 * @param float   $rotation      Rotation of the entity while riding the block, in degrees between 0 and 360.
	 * @param float   $rotationLimit Rotation limit of the entity while riding the block, in degrees between 0 and 360.
	 * @param int     $emoteType     @see PassengerOfBlockEmoteType
	 */
	public function __construct(
		private BlockPosition $blockPosition,
		private Vector3 $offset,
		private float $rotation,
		private float $rotationLimit,
		private int $emoteType
	){}

	public function getBlockPosition() : BlockPosition{ return $this->blockPosition; }

	public function getOffset() : Vector3{ return $this->offset; }

	public function getRotation() : float{ return $this->rotation; }

	public function getRotationLimit() : float{ return $this->rotationLimit; }

	/**
	 * @see PassengerOfBlockEmoteType
	 */
	public function getEmoteType() : int{ return $this->emoteType; }

	public static function read(ByteBufferReader $in) : self{
		$blockPosition = CommonTypes::getBlockPosition($in);
		$offset = CommonTypes::getVector3($in);
		$rotation = LE::readFloat($in);
		$rotationLimit = LE::readFloat($in);
		$emoteType = Byte::readUnsigned($in);

		return new self($blockPosition, $offset, $rotation, $rotationLimit, $emoteType);
	}

	public function write(ByteBufferWriter $out) : void{
		CommonTypes::putBlockPosition($out, $this->blockPosition);
		CommonTypes::putVector3($out, $this->offset);
		LE::writeFloat($out, $this->rotation);
		LE::writeFloat($out, $this->rotationLimit);
		Byte::writeUnsigned($out, $this->emoteType);
	}
}
