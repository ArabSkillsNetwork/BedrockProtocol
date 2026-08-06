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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

/**
 * Item as sent in the results of DeprecatedCraftingResultsStackRequestAction. Unlike everywhere else, the item is
 * described by name rather than by network ID.
 */
final class DeprecatedCraftingResultsEntry{

	public const DESCRIPTOR_EMPTY = 0;
	public const DESCRIPTOR_NAME = 1;
	public const DESCRIPTOR_MOLANG = 2;
	public const DESCRIPTOR_ITEM_TAG = 3;

	public function __construct(
		private int $descriptorType,
		private string $name,
		private int $meta,
		private int $count,
		private int $blockRuntimeId,
		private string $rawExtraData
	){}

	public function getDescriptorType() : int{ return $this->descriptorType; }

	public function getName() : string{ return $this->name; }

	public function getMeta() : int{ return $this->meta; }

	public function getCount() : int{ return $this->count; }

	public function getBlockRuntimeId() : int{ return $this->blockRuntimeId; }

	public function getRawExtraData() : string{ return $this->rawExtraData; }

	/**
	 * @throws PacketDecodeException
	 * @throws \pmmp\encoding\DataDecodeException
	 */
	public static function read(ByteBufferReader $in) : self{
		$descriptorType = VarInt::readUnsignedInt($in);
		Byte::readUnsigned($in); //legacy descriptor type, redundant with the one above

		$name = "";
		$meta = 0;
		switch($descriptorType){
			case self::DESCRIPTOR_EMPTY:
				break;
			case self::DESCRIPTOR_NAME:
				$name = CommonTypes::getString($in);
				$meta = VarInt::readSignedInt($in);
				break;
			case self::DESCRIPTOR_MOLANG:
				$name = CommonTypes::getString($in);
				$meta = Byte::readUnsigned($in); //molang version
				break;
			case self::DESCRIPTOR_ITEM_TAG:
				$name = CommonTypes::getString($in);
				break;
			default:
				throw new PacketDecodeException("Unknown stack request item descriptor type $descriptorType");
		}

		$count = LE::readSignedShort($in);
		$blockRuntimeId = VarInt::readUnsignedInt($in);
		$rawExtraData = CommonTypes::getString($in);

		return new self($descriptorType, $name, $meta, $count, $blockRuntimeId, $rawExtraData);
	}

	public function write(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, $this->descriptorType);
		Byte::writeUnsigned($out, $this->descriptorType);

		switch($this->descriptorType){
			case self::DESCRIPTOR_NAME:
				CommonTypes::putString($out, $this->name);
				VarInt::writeSignedInt($out, $this->meta);
				break;
			case self::DESCRIPTOR_MOLANG:
				CommonTypes::putString($out, $this->name);
				Byte::writeUnsigned($out, $this->meta);
				break;
			case self::DESCRIPTOR_ITEM_TAG:
				CommonTypes::putString($out, $this->name);
				break;
		}

		LE::writeSignedShort($out, $this->count);
		VarInt::writeUnsignedInt($out, $this->blockRuntimeId);
		CommonTypes::putString($out, $this->rawExtraData);
	}
}
