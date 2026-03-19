<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\ApiCommunication\ClientServices;

use BAGArt\ASKClient\Contracts\Network\RetryPolicyContract;

/**
 * Retry policy for failed Telegram API calls.
 */
interface TgRetryPolicyContract extends RetryPolicyContract
{
}
