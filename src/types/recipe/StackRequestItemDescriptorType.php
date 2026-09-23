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

namespace pocketmine\network\mcpe\protocol\types\recipe;

/** Descriptor type IDs used by item stack requests. */
final class StackRequestItemDescriptorType{

	private function __construct(){
		//NOOP
	}

	public const EMPTY = 0;
	public const STRING_ID_META = 1;
	public const MOLANG = 2;
	public const TAG = 3;
}
