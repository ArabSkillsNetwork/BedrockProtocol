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

namespace pocketmine\network\mcpe\protocol;

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\entity\PassengerOfBlockData;

/**
 * Makes an entity start or stop riding a block.
 */
class SetPassengerOfBlockPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_PASSENGER_OF_BLOCK_PACKET;

	private int $passengerActorUniqueId;
	private ?PassengerOfBlockData $data;

	/**
	 * @generate-create-func
	 */
	public static function create(int $passengerActorUniqueId, ?PassengerOfBlockData $data) : self{
		$result = new self;
		$result->passengerActorUniqueId = $passengerActorUniqueId;
		$result->data = $data;
		return $result;
	}

	public function getPassengerActorUniqueId() : int{ return $this->passengerActorUniqueId; }

	/**
	 * Returns the block the entity starts riding, or null if the entity stops riding a block.
	 */
	public function getData() : ?PassengerOfBlockData{ return $this->data; }

	protected function decodePayload(ByteBufferReader $in) : void{
		$this->passengerActorUniqueId = CommonTypes::getActorUniqueId($in);
		$this->data = CommonTypes::readOptional($in, PassengerOfBlockData::read(...));
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		CommonTypes::putActorUniqueId($out, $this->passengerActorUniqueId);
		CommonTypes::writeOptional($out, $this->data, fn(ByteBufferWriter $out, PassengerOfBlockData $v) => $v->write($out));
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleSetPassengerOfBlock($this);
	}
}
