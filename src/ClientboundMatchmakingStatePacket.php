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

use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\MatchmakingState;
use pocketmine\network\mcpe\protocol\types\MatchmakingStateOptions;

/**
 * Informs the client of the state of the matchmaking process it is taking part in.
 */
class ClientboundMatchmakingStatePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_MATCHMAKING_STATE_PACKET;

	/** @see MatchmakingState */
	private int $state;
	private string $destinationName;
	private ?MatchmakingStateOptions $options;

	/**
	 * @generate-create-func
	 */
	public static function create(int $state, string $destinationName, ?MatchmakingStateOptions $options) : self{
		$result = new self;
		$result->state = $state;
		$result->destinationName = $destinationName;
		$result->options = $options;
		return $result;
	}

	/**
	 * @see MatchmakingState
	 */
	public function getState() : int{ return $this->state; }

	public function getDestinationName() : string{ return $this->destinationName; }

	public function getOptions() : ?MatchmakingStateOptions{ return $this->options; }

	protected function decodePayload(ByteBufferReader $in) : void{
		$this->state = Byte::readUnsigned($in);
		$this->destinationName = CommonTypes::getString($in);
		$this->options = CommonTypes::readOptional($in, MatchmakingStateOptions::read(...));
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		Byte::writeUnsigned($out, $this->state);
		CommonTypes::putString($out, $this->destinationName);
		CommonTypes::writeOptional($out, $this->options, fn(ByteBufferWriter $out, MatchmakingStateOptions $v) => $v->write($out));
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundMatchmakingState($this);
	}
}
