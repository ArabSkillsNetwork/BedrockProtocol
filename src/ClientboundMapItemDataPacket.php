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
use pmmp\encoding\LE;
use pmmp\encoding\VarInt;
use pocketmine\color\Color;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\MapDecoration;
use pocketmine\network\mcpe\protocol\types\MapImage;
use pocketmine\network\mcpe\protocol\types\MapTrackedObject;
use pocketmine\utils\Binary;
use function count;

class ClientboundMapItemDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CLIENTBOUND_MAP_ITEM_DATA_PACKET;

	public int $mapId;
	public int $dimensionId = DimensionIds::OVERWORLD;
	public bool $isLocked = false;
	public BlockPosition $origin;

	/** @var int[] */
	public array $parentMapIds = [];
	public ?int $scale = null;

	/** @var MapTrackedObject[] */
	public array $trackedEntities = [];
	/** @var MapDecoration[] */
	public array $decorations = [];

	public int $xOffset = 0;
	public int $yOffset = 0;
	public ?MapImage $colors = null;

	protected function decodePayload(ByteBufferReader $in) : void{
		$this->mapId = CommonTypes::getActorUniqueId($in);
		$this->dimensionId = Byte::readUnsigned($in);
		$this->isLocked = CommonTypes::getBool($in);
		$this->origin = CommonTypes::getBlockPosition($in);

		$this->parentMapIds = CommonTypes::readOptional($in, function(ByteBufferReader $in) : array{
			$parentMapIds = [];
			for($i = 0, $count = VarInt::readUnsignedInt($in); $i < $count; ++$i){
				$parentMapIds[] = CommonTypes::getActorUniqueId($in);
			}
			return $parentMapIds;
		}) ?? [];

		$this->scale = CommonTypes::readOptional($in, Byte::readUnsigned(...));

		$this->trackedEntities = CommonTypes::readOptional($in, function(ByteBufferReader $in) : array{
			$trackedEntities = [];
			for($i = 0, $count = VarInt::readUnsignedInt($in); $i < $count; ++$i){
				$object = new MapTrackedObject();
				$object->type = LE::readUnsignedInt($in);
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					$object->blockPosition = CommonTypes::getBlockPosition($in);
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					$object->actorUniqueId = CommonTypes::getActorUniqueId($in);
				}else{
					throw new PacketDecodeException("Unknown map object type $object->type");
				}
				$trackedEntities[] = $object;
			}
			return $trackedEntities;
		}) ?? [];

		$this->decorations = CommonTypes::readOptional($in, function(ByteBufferReader $in) : array{
			$decorations = [];
			for($i = 0, $count = VarInt::readUnsignedInt($in); $i < $count; ++$i){
				$icon = Byte::readUnsigned($in);
				$rotation = Byte::readUnsigned($in);
				$xOffset = Byte::readUnsigned($in);
				$yOffset = Byte::readUnsigned($in);
				$label = CommonTypes::getString($in);
				$color = Color::fromRGBA(Binary::flipIntEndianness(VarInt::readUnsignedInt($in)));
				$decorations[] = new MapDecoration($icon, $rotation, $xOffset, $yOffset, $label, $color);
			}
			return $decorations;
		}) ?? [];

		$width = CommonTypes::readOptional($in, VarInt::readSignedInt(...));
		$height = CommonTypes::readOptional($in, VarInt::readSignedInt(...));
		$this->xOffset = CommonTypes::readOptional($in, VarInt::readSignedInt(...)) ?? 0;
		$this->yOffset = CommonTypes::readOptional($in, VarInt::readSignedInt(...)) ?? 0;

		if(CommonTypes::getBool($in)){
			if($width === null || $height === null){
				throw new PacketDecodeException("Map pixels sent without a width and height");
			}
			$count = VarInt::readUnsignedInt($in);
			if($count !== $width * $height){
				throw new PacketDecodeException("Expected colour count of " . ($height * $width) . " (height $height * width $width), got $count");
			}
			$this->colors = MapImage::decode($in, $height, $width);
		}
	}

	protected function encodePayload(ByteBufferWriter $out) : void{
		CommonTypes::putActorUniqueId($out, $this->mapId);

		Byte::writeUnsigned($out, $this->dimensionId);
		CommonTypes::putBool($out, $this->isLocked);
		CommonTypes::putBlockPosition($out, $this->origin);

		CommonTypes::writeOptional($out, count($this->parentMapIds) !== 0 ? $this->parentMapIds : null, function(ByteBufferWriter $out, array $parentMapIds) : void{
			VarInt::writeUnsignedInt($out, count($parentMapIds));
			foreach($parentMapIds as $parentMapId){
				CommonTypes::putActorUniqueId($out, $parentMapId);
			}
		});

		CommonTypes::writeOptional($out, $this->scale, Byte::writeUnsigned(...));

		CommonTypes::writeOptional($out, count($this->trackedEntities) !== 0 ? $this->trackedEntities : null, function(ByteBufferWriter $out, array $trackedEntities) : void{
			VarInt::writeUnsignedInt($out, count($trackedEntities));
			foreach($trackedEntities as $object){
				LE::writeUnsignedInt($out, $object->type);
				if($object->type === MapTrackedObject::TYPE_BLOCK){
					CommonTypes::putBlockPosition($out, $object->blockPosition);
				}elseif($object->type === MapTrackedObject::TYPE_ENTITY){
					CommonTypes::putActorUniqueId($out, $object->actorUniqueId);
				}else{
					throw new \InvalidArgumentException("Unknown map object type $object->type");
				}
			}
		});

		CommonTypes::writeOptional($out, count($this->decorations) !== 0 ? $this->decorations : null, function(ByteBufferWriter $out, array $decorations) : void{
			VarInt::writeUnsignedInt($out, count($decorations));
			foreach($decorations as $decoration){
				Byte::writeUnsigned($out, $decoration->getIcon());
				Byte::writeUnsigned($out, $decoration->getRotation());
				Byte::writeUnsigned($out, $decoration->getXOffset());
				Byte::writeUnsigned($out, $decoration->getYOffset());
				CommonTypes::putString($out, $decoration->getLabel());
				VarInt::writeUnsignedInt($out, Binary::flipIntEndianness($decoration->getColor()->toRGBA()));
			}
		});

		$colors = $this->colors;
		CommonTypes::writeOptional($out, $colors?->getWidth(), VarInt::writeSignedInt(...));
		CommonTypes::writeOptional($out, $colors?->getHeight(), VarInt::writeSignedInt(...));
		CommonTypes::writeOptional($out, $colors !== null ? $this->xOffset : null, VarInt::writeSignedInt(...));
		CommonTypes::writeOptional($out, $colors !== null ? $this->yOffset : null, VarInt::writeSignedInt(...));

		CommonTypes::writeOptional($out, $colors, function(ByteBufferWriter $out, MapImage $colors) : void{
			VarInt::writeUnsignedInt($out, $colors->getWidth() * $colors->getHeight());
			$colors->encode($out);
		});
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleClientboundMapItemData($this);
	}
}
