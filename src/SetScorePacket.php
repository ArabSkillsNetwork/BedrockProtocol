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
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use function count;

class SetScorePacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_SCORE_PACKET;

	public const TYPE_CHANGE = 0;
	public const TYPE_REMOVE = 1;

	private const ACTION_IDS = [
		ScorePacketEntry::TYPE_REMOVE => "remove",
		ScorePacketEntry::TYPE_PLAYER => "changeplayer",
		ScorePacketEntry::TYPE_ENTITY => "changeentity",
		ScorePacketEntry::TYPE_FAKE_PLAYER => "changefakeplayer",
	];

	public int $type;
	/** @var ScorePacketEntry[] */
	public array $entries = [];

	/**
	 * @generate-create-func
	 * @param ScorePacketEntry[] $entries
	 */
	public static function create(int $type, array $entries) : self{
		$result = new self;
		$result->type = $type;
		$result->entries = $entries;
		return $result;
	}

	protected function decodePayload(ByteBufferReader $in) : void{
		$onlyRemovals = true;
		for($i = 0, $count = VarInt::readUnsignedInt($in); $i < $count; ++$i){
			$entry = new ScorePacketEntry();
			$entry->type = VarInt::readUnsignedInt($in);
			CommonTypes::getString($in); //action ID, redundant with the type above
			$entry->scoreboardId = VarInt::readSignedLong($in);
			switch($entry->type){
				case ScorePacketEntry::TYPE_REMOVE:
					//1.26.44: the objective name is wrapped in a second optional
					$entry->objectiveName = CommonTypes::readOptional($in, CommonTypes::getString(...));
					break;
				case ScorePacketEntry::TYPE_PLAYER:
				case ScorePacketEntry::TYPE_ENTITY:
					$onlyRemovals = false;
					$entry->objectiveName = CommonTypes::getString($in);
					$entry->score = LE::readSignedInt($in);
					$entry->actorUniqueId = CommonTypes::getActorUniqueId($in);
					break;
				case ScorePacketEntry::TYPE_FAKE_PLAYER:
					$onlyRemovals = false;
					$entry->objectiveName = CommonTypes::getString($in);
					$entry->score = LE::readSignedInt($in);
					$entry->customName = CommonTypes::getString($in);
					break;
				default:
					throw new PacketDecodeException("Unknown entry type $entry->type");
			}
			$this->entries[] = $entry;
		}
		$this->type = $onlyRemovals ? self::TYPE_REMOVE : self::TYPE_CHANGE;
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		VarInt::writeUnsignedInt($out, count($this->entries));
		foreach($this->entries as $entry){
			$entryType = $this->type === self::TYPE_REMOVE ? ScorePacketEntry::TYPE_REMOVE : $entry->type;
			VarInt::writeUnsignedInt($out, $entryType);
			CommonTypes::putString($out, self::ACTION_IDS[$entryType] ?? throw new \InvalidArgumentException("Unknown entry type $entryType"));
			VarInt::writeSignedLong($out, $entry->scoreboardId);
			switch($entryType){
				case ScorePacketEntry::TYPE_REMOVE:
					CommonTypes::writeOptional($out, $entry->objectiveName, CommonTypes::putString(...));
					break;
				case ScorePacketEntry::TYPE_PLAYER:
				case ScorePacketEntry::TYPE_ENTITY:
					CommonTypes::putString($out, $entry->objectiveName ?? "");
					LE::writeSignedInt($out, $entry->score);
					CommonTypes::putActorUniqueId($out, $entry->actorUniqueId);
					break;
				case ScorePacketEntry::TYPE_FAKE_PLAYER:
					CommonTypes::putString($out, $entry->objectiveName ?? "");
					LE::writeSignedInt($out, $entry->score);
					CommonTypes::putString($out, $entry->customName ?? "");
					break;
			}
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleSetScore($this);
	}
}
