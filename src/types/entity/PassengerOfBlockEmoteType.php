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

namespace pocketmine\network\mcpe\protocol\types\entity;

/**
 * The pose an entity takes while it is riding a block.
 * @see PassengerOfBlockData
 */
final class PassengerOfBlockEmoteType{

	private function __construct(){
		//NOOP
	}

	public const STANDING = 0;
	public const RIDING = 1;
	public const LAYING = 2;
}
