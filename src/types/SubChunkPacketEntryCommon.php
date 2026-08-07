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
use pocketmine\network\mcpe\protocol\PacketDecodeException;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;

final class SubChunkPacketEntryCommon{

	public function __construct(
		private SubChunkPositionOffset $offset,
		private int $requestResult,
		private string $terrainData,
		private ?SubChunkPacketHeightMapInfo $heightMap,
		private ?SubChunkPacketHeightMapInfo $renderHeightMap
	){}

	public function getOffset() : SubChunkPositionOffset{ return $this->offset; }

	public function getRequestResult() : int{ return $this->requestResult; }

	public function getTerrainData() : string{ return $this->terrainData; }

	public function getHeightMap() : ?SubChunkPacketHeightMapInfo{ return $this->heightMap; }

	public function getRenderHeightMap() : ?SubChunkPacketHeightMapInfo{ return $this->renderHeightMap; }

	public static function read(ByteBufferReader $in) : self{
		$offset = SubChunkPositionOffset::read($in);

		$requestResult = Byte::readUnsigned($in);

		$data = CommonTypes::readOptional($in, CommonTypes::getString(...)) ?? "";

		$heightMapDataType = Byte::readUnsigned($in);
		$rawHeightMapData = CommonTypes::readOptional($in, SubChunkPacketHeightMapInfo::read(...));
		$heightMapData = match ($heightMapDataType) {
			SubChunkPacketHeightMapType::NO_DATA => null,
			SubChunkPacketHeightMapType::DATA => $rawHeightMapData,
			SubChunkPacketHeightMapType::ALL_TOO_HIGH => SubChunkPacketHeightMapInfo::allTooHigh(),
			SubChunkPacketHeightMapType::ALL_TOO_LOW => SubChunkPacketHeightMapInfo::allTooLow(),
			default => throw new PacketDecodeException("Unknown heightmap data type $heightMapDataType")
		};

		$renderHeightMapDataType = Byte::readUnsigned($in);
		$rawRenderHeightMapData = CommonTypes::readOptional($in, SubChunkPacketHeightMapInfo::read(...));
		$renderHeightMapData = match ($renderHeightMapDataType) {
			SubChunkPacketHeightMapType::NO_DATA => null,
			SubChunkPacketHeightMapType::DATA => $rawRenderHeightMapData,
			SubChunkPacketHeightMapType::ALL_TOO_HIGH => SubChunkPacketHeightMapInfo::allTooHigh(),
			SubChunkPacketHeightMapType::ALL_TOO_LOW => SubChunkPacketHeightMapInfo::allTooLow(),
			SubChunkPacketHeightMapType::ALL_COPIED => $heightMapData,
			default => throw new PacketDecodeException("Unknown render heightmap data type $renderHeightMapDataType")
		};

		return new self(
			$offset,
			$requestResult,
			$data,
			$heightMapData,
			$renderHeightMapData
		);
	}

	public function write(ByteBufferWriter $out) : void{
		$this->offset->write($out);

		Byte::writeUnsigned($out, $this->requestResult);

		CommonTypes::writeOptional($out, $this->terrainData !== "" ? $this->terrainData : null, CommonTypes::putString(...));

		self::writeHeightMap($out, $this->heightMap, SubChunkPacketHeightMapType::NO_DATA);
		self::writeHeightMap($out, $this->renderHeightMap, SubChunkPacketHeightMapType::ALL_COPIED);
	}

	private static function writeHeightMap(ByteBufferWriter $out, ?SubChunkPacketHeightMapInfo $heightMap, int $absentType) : void{
		if($heightMap === null){
			Byte::writeUnsigned($out, $absentType);
		}elseif($heightMap->isAllTooLow()){
			Byte::writeUnsigned($out, SubChunkPacketHeightMapType::ALL_TOO_LOW);
			$heightMap = null;
		}elseif($heightMap->isAllTooHigh()){
			Byte::writeUnsigned($out, SubChunkPacketHeightMapType::ALL_TOO_HIGH);
			$heightMap = null;
		}else{
			Byte::writeUnsigned($out, SubChunkPacketHeightMapType::DATA);
		}

		CommonTypes::writeOptional($out, $heightMap, fn(ByteBufferWriter $out, SubChunkPacketHeightMapInfo $v) => $v->write($out));
	}
}
