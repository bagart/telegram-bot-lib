<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Contracts\Modules;

/**
 * Single point through which a module's audit events are typed —
 * no duck-typing across boundaries. The audit package refines these
 * classes at consumption; modules only declare and dispatch them.
 */
interface ModuleAuditEventContract
{
    /**
     * Owning module id, e.g. 'antispam'.
     */
    public function moduleId(): string;

    /**
     * Namespaced operation, e.g. 'antispam.rule.created'.
     */
    public function operation(): string;

    public function botId(): ?string;

    /**
     * Subject kind, e.g. 'antispam_rule', 'chat'.
     */
    public function subjectType(): ?string;

    public function subjectId(): ?string;

    /**
     * Acting user/admin id; null ⇒ AuditActor system actor.
     */
    public function actorId(): ?string;

    /**
     * AuditActor type; null ⇒ AuditActor::TYPE_SYSTEM.
     */
    public function actorType(): ?string;

    /**
     * State before the operation, if applicable.
     *
     * @return array<string, mixed>|null
     */
    public function oldState(): ?array;

    /**
     * State after the operation, if applicable.
     *
     * @return array<string, mixed>|null
     */
    public function newState(): ?array;

    /**
     * AuditEntry metadata — structured non-secret facts only.
     *
     * @return array<string, mixed> credentials/token values FORBIDDEN (AuditEntry metadata docblock).
     */
    public function context(): array;
}
