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
use pocketmine\network\mcpe\protocol\types\AudioContentPlaybackType;

/**
 * Makes the client play service-signed audio content, using sound options authored by the server.
 */
class ClientboundPlayAudioContentPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_PLAY_AUDIO_CONTENT_PACKET;

	private string $sharedMetadata;
	private string $playbackContent;
	/** @see AudioContentPlaybackType */
	private int $playbackType;
	private PlaySoundPacket $sound;

	/**
	 * @generate-create-func
	 */
	public static function create(string $sharedMetadata, string $playbackContent, int $playbackType, PlaySoundPacket $sound) : self{
		$result = new self;
		$result->sharedMetadata = $sharedMetadata;
		$result->playbackContent = $playbackContent;
		$result->playbackType = $playbackType;
		$result->sound = $sound;
		return $result;
	}

	public function getSharedMetadata() : string{ return $this->sharedMetadata; }

	public function getPlaybackContent() : string{ return $this->playbackContent; }

	/**
	 * @see AudioContentPlaybackType
	 */
	public function getPlaybackType() : int{ return $this->playbackType; }

	public function getSound() : PlaySoundPacket{ return $this->sound; }

	protected function decodePayload(ByteBufferReader $in) : void{
		$this->sharedMetadata = CommonTypes::getString($in);
		$this->playbackContent = CommonTypes::getString($in);
		$this->playbackType = Byte::readUnsigned($in);
		$this->sound = new PlaySoundPacket();
		$this->sound->decodePayload($in);
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		CommonTypes::putString($out, $this->sharedMetadata);
		CommonTypes::putString($out, $this->playbackContent);
		Byte::writeUnsigned($out, $this->playbackType);
		$this->sound->encodePayload($out);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundPlayAudioContent($this);
	}
}
