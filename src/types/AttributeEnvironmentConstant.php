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

/**
 * @see AttributeEnvironment
 */
final class AttributeEnvironmentConstant extends AttributeEnvironmentPayload{
	use GetTypeIdFromConstTrait;

	public const ID = AttributeEnvironmentPayloadType::CONSTANT;

	public function __construct(
		private AttributeValue $attribute
	){}

	public function getAttribute() : AttributeValue{ return $this->attribute; }

	public static function read(ByteBufferReader $in) : self{
		$attribute = AttributeValue::read($in);

		return new self($attribute);
	}

	public function write(ByteBufferWriter $out) : void{
		self::writeAttributeValue($out, $this->attribute);
	}
}
