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

use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\PacketDecodeException;

/**
 * A single change to a sound which is already playing, identified by the handle the server gave out in
 * PlaySoundPacket.
 */
final class SoundDataUpdate{

	public const STOP = 0;
	public const SET_VOLUME = 1;
	public const SET_PITCH = 2;
	public const FADE = 3;
	public const SEEK_TO = 4;
	public const PAUSE = 5;
	public const RESUME = 6;

	public function __construct(
		private int $type,
		private float $volume = 0.0,
		private float $pitch = 0.0,
		private float $duration = 0.0,
		private float $targetVolume = 0.0,
		private float $seconds = 0.0
	){}

	public function getType() : int{ return $this->type; }

	public function getVolume() : float{ return $this->volume; }

	public function getPitch() : float{ return $this->pitch; }

	public function getDuration() : float{ return $this->duration; }

	public function getTargetVolume() : float{ return $this->targetVolume; }

	public function getSeconds() : float{ return $this->seconds; }

	/**
	 * @throws PacketDecodeException
	 * @throws \pmmp\encoding\DataDecodeException
	 */
	public static function read(ByteBufferReader $in) : self{
		$type = VarInt::readUnsignedInt($in);

		$volume = 0.0;
		$pitch = 0.0;
		$duration = 0.0;
		$targetVolume = 0.0;
		$seconds = 0.0;
		switch($type){
			case self::STOP:
			case self::PAUSE:
			case self::RESUME:
				break;
			case self::SET_VOLUME:
				$volume = LE::readFloat($in);
				break;
			case self::SET_PITCH:
				$pitch = LE::readFloat($in);
				break;
			case self::FADE:
				$duration = LE::readFloat($in);
				$targetVolume = LE::readFloat($in);
				break;
			case self::SEEK_TO:
				$seconds = LE::readFloat($in);
				break;
			default:
				throw new PacketDecodeException("Unknown sound data update type $type");
		}

		return new self($type, $volume, $pitch, $duration, $targetVolume, $seconds);
	}

	public function write(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, $this->type);

		switch($this->type){
			case self::SET_VOLUME:
				LE::writeFloat($out, $this->volume);
				break;
			case self::SET_PITCH:
				LE::writeFloat($out, $this->pitch);
				break;
			case self::FADE:
				LE::writeFloat($out, $this->duration);
				LE::writeFloat($out, $this->targetVolume);
				break;
			case self::SEEK_TO:
				LE::writeFloat($out, $this->seconds);
				break;
		}
	}
}
