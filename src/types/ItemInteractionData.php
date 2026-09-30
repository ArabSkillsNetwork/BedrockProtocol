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
use pmmp\encoding\VarInt;
use pocketmine\network\mcpe\protocol\serializer\CommonTypes;
use pocketmine\network\mcpe\protocol\types\inventory\InventoryTransactionChangedSlotsHack;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use function count;

final class ItemInteractionData{
	/**
	 * @param InventoryTransactionChangedSlotsHack[]|null $requestChangedSlots
	 */
	public function __construct(
		private int $requestId,
		private ?array $requestChangedSlots,
		private UseItemTransactionData $transactionData
	){}

	public function getRequestId() : int{
		return $this->requestId;
	}

	/**
	 * @return InventoryTransactionChangedSlotsHack[]|null
	 */
	public function getRequestChangedSlots() : ?array{
		return $this->requestChangedSlots;
	}

	public function getTransactionData() : UseItemTransactionData{
		return $this->transactionData;
	}

	public static function read(ByteBufferReader $in) : self{
		$requestId = CommonTypes::readLegacyItemStackRequestId($in);
		$requestChangedSlots = CommonTypes::readOptional($in, static function(ByteBufferReader $in) : array{
			$result = [];
			for($i = 0, $len = VarInt::readUnsignedInt($in); $i < $len; ++$i){
				$result[] = InventoryTransactionChangedSlotsHack::read($in);
			}
			return $result;
		});
		$transactionData = new UseItemTransactionData();
		$transactionData->decodeAuthInput($in);
		return new ItemInteractionData($requestId, $requestChangedSlots, $transactionData);
	}

	public function write(ByteBufferWriter $out) : void{
		CommonTypes::writeLegacyItemStackRequestId($out, $this->requestId);
		CommonTypes::writeOptional($out, $this->requestChangedSlots, static function(ByteBufferWriter $out, array $value) : void{
			VarInt::writeUnsignedInt($out, count($value));
			foreach($value as $changedSlot){
				$changedSlot->write($out);
			}
		});
		$this->transactionData->encodeAuthInput($out);
	}
}
