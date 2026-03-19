<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Exceptions;

class TgUnregisteredEntityNameException extends TelegramBotException
{
    public function __construct(
        public string $tgEntityName,
        public ?string $tgEntityScope = null,
    ) {
        if (is_object($tgEntityName)) {
            $this->tgEntityName = $tgEntityName->name;
        }
        if (is_object($tgEntityScope)) {
            $this->tgEntityScope = $tgEntityScope->name;
        } elseif ($tgEntityScope === null) {
            $this->tgEntityScope = '*';
        }

        parent::__construct("Unregistered $tgEntityScope / $tgEntityName");
    }
}
