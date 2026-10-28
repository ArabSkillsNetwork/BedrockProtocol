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

/**
 * @see ClientboundMatchmakingStatePacket
 */
final class MatchmakingState{

	private function __construct(){
		//NOOP
	}

	public const IDLE = 0;
	public const MATCHMAKING = 1;
	public const MATCH_FOUND = 2;
	public const CANCELED = 3;
	public const PLAYER_LEFT_PARTY = 4;
	public const PLAYER_LEFT_SERVER = 5;
	public const SERVER_SHUTDOWN = 6;
	public const TIMED_OUT = 7;
	public const REQUEUE_AS_PARTY = 8;
}
