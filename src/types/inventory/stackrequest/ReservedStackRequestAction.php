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
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

/**
 * Reserved by Mojang for a future crafting action. Its purpose is currently unknown.
 */
final class ReservedStackRequestAction extends ItemStackRequestAction{
	use GetTypeIdFromConstTrait;

	public const ID = ItemStackRequestActionType::CRAFTING_RESERVED;

	public function __construct(
		private string $reservedId,
		private int $repetitions
	){}

	public function getReservedId() : string{ return $this->reservedId; }

	public function getRepetitions() : int{ return $this->repetitions; }

	public static function read(ByteBufferReader $in) : self{
		$reservedId = CommonTypes::getString($in);
		$repetitions = Byte::readUnsigned($in);
		return new self($reservedId, $repetitions);
	}

	public function write(ByteBufferWriter $out) : void{
		CommonTypes::putString($out, $this->reservedId);
		Byte::writeUnsigned($out, $this->repetitions);
	}
}
