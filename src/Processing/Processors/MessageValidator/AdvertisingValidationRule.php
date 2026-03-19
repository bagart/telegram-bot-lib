<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\Processing\Processors\MessageValidator;

use BAGArt\TelegramBot\TgApi\Types\DTO\MessageTypeDTO;
use BAGArt\TelegramBot\TgApi\Types\Enum\MessageEntityPropTypeEnum;

class AdvertisingValidationRule implements MessageValidationRule
{
    private const GROUP_SHORTENER = 'shortener';
    private const GROUP_PROMOTIONAL = 'promotional';
    private const GROUP_PRIVATE_MSG = 'private_message';

    // --- URL shortener patterns ---

    private const REGEXP_T_ME = 'https?:\/\/t\.me\/';
    private const REGEXP_BITLY = 'https?:\/\/bit\.ly\/';
    private const REGEXP_TINYURL = 'https?:\/\/tinyurl\.com\/';

    // --- Promotional text patterns ---

    private const REGEXP_KUPI = 'куп[ии]\w*';
    private const REGEXP_PRODAI = 'прода[юй]\w*';
    private const REGEXP_SKIDKA = 'скидк\w*';
    private const REGEXP_AKCII = 'акци\w+';
    private const REGEXP_BESPLATNO = 'бесплатн\w+';
    private const REGEXP_ZAKAZ = 'заказ\w*';
    private const REGEXP_DOSTAVKA = 'доставк\w*';
    private const REGEXP_CENA = 'цена\b';
    private const REGEXP_STOIMOST = 'стоимост\w+';
    private const REGEXP_SALE = 'sale\b';
    private const REGEXP_BUY = 'buy\b';
    private const REGEXP_DISCOUNT = 'discount\b';
    private const REGEXP_FREE = 'free\b';
    private const REGEXP_ORDER = 'order\b';
    private const REGEXP_PRICE = 'price\b';
    private const REGEXP_PODRABOTKA = 'подработк\w*';
    private const REGEXP_PODRABOT = 'подр[аа]бот\w+';
    private const REGEXP_KURER = 'курьер\b';
    private const REGEXP_TREBUYUT_RABOCH = 'требуют?\w*\s*рабоч';
    private const REGEXP_TREBUYUT_SOTRUDNIK = 'требуют?\w*\s*сотрудник';
    private const REGEXP_VAKANSII = 'ваканси\w+';
    private const REGEXP_HALTURKA = 'халтурк\w*';
    private const REGEXP_ZARABOTOK_NA = 'заработ\w+\s+на\b';
    private const REGEXP_NUZHNY_RABOCH = 'нужн\w+\s+рабоч';
    private const REGEXP_NABOR_PERSONAL = 'набор\s+персонал';
    private const REGEXP_RABOTA_NA_DOMU = 'работ\w+\s+на\s+дому';
    private const REGEXP_RABOTA_UDALENNO = 'работ\w+\s+удал[её]нн';
    private const REGEXP_UDALENNAYA_RABOTA = 'удал[её]нн\w+\s+работ';
    private const REGEXP_RABOTA_V_INTERNETE = 'работ\w+\s+в\s+интернет';

    // --- Private message invitation patterns ---

    private const REGEXP_V_LICHKU = 'в\s+л[сич]+к?[уеи]?';
    private const REGEXP_V_PM = '\b[рp]\s*[mм]\b';
    private const REGEXP_V_DIRECT = '\b[дd][иi][рr][еe][кc][тt]\b';
    private const REGEXP_PISHITE = '(?:на)?пиш[иы](?:те)?';
    private const REGEXP_OBRASHCHAYTES = 'обраща[йя](?:тесь|ся)';

    private ?string $cachedShortenerRegexp = null;
    private ?string $cachedPromotionalRegexp = null;
    private ?string $cachedPrivateMessageRegexp = null;

    private array $urlShortenerPatterns;
    private array $promotionalPatterns;
    private array $privateMessagePatterns;

    public function __construct(
        private readonly int $rulePriority = 20,
        private readonly MessageVerdictActionEnum $action = MessageVerdictActionEnum::Restrict,
        private readonly ?int $restrictDuration = null,
    ) {
        $this->urlShortenerPatterns = [
            self::REGEXP_T_ME,
            self::REGEXP_BITLY,
            self::REGEXP_TINYURL,
        ];
        $this->promotionalPatterns = [
            self::REGEXP_KUPI,
            self::REGEXP_PRODAI,
            self::REGEXP_SKIDKA,
            self::REGEXP_AKCII,
            self::REGEXP_BESPLATNO,
            self::REGEXP_ZAKAZ,
            self::REGEXP_DOSTAVKA,
            self::REGEXP_CENA,
            self::REGEXP_STOIMOST,
            self::REGEXP_SALE,
            self::REGEXP_BUY,
            self::REGEXP_DISCOUNT,
            self::REGEXP_FREE,
            self::REGEXP_ORDER,
            self::REGEXP_PRICE,
            self::REGEXP_PODRABOTKA,
            self::REGEXP_PODRABOT,
            self::REGEXP_KURER,
            self::REGEXP_TREBUYUT_RABOCH,
            self::REGEXP_TREBUYUT_SOTRUDNIK,
            self::REGEXP_VAKANSII,
            self::REGEXP_HALTURKA,
            self::REGEXP_ZARABOTOK_NA,
            self::REGEXP_NUZHNY_RABOCH,
            self::REGEXP_NABOR_PERSONAL,
            self::REGEXP_RABOTA_NA_DOMU,
            self::REGEXP_RABOTA_UDALENNO,
            self::REGEXP_UDALENNAYA_RABOTA,
            self::REGEXP_RABOTA_V_INTERNETE,
        ];
        $this->privateMessagePatterns = [
            self::REGEXP_V_LICHKU,
            self::REGEXP_V_PM,
            self::REGEXP_V_DIRECT,
            self::REGEXP_PISHITE,
            self::REGEXP_OBRASHCHAYTES,
        ];
    }

    public function priority(): int
    {
        return $this->rulePriority;
    }

    public function addRegexp(string $group, string $pattern): static
    {
        match ($group) {
            self::GROUP_SHORTENER => $this->urlShortenerPatterns[] = $pattern,
            self::GROUP_PROMOTIONAL => $this->promotionalPatterns[] = $pattern,
            self::GROUP_PRIVATE_MSG => $this->privateMessagePatterns[] = $pattern,
            default => throw new \InvalidArgumentException(
                "Unknown regexp group: {$group}. Use '".self::GROUP_SHORTENER."', '".self::GROUP_PROMOTIONAL."' or '".self::GROUP_PRIVATE_MSG."'."
            ),
        };

        $this->cachedShortenerRegexp = null;
        $this->cachedPromotionalRegexp = null;
        $this->cachedPrivateMessageRegexp = null;

        return $this;
    }

    public function validate(MessageTypeDTO $dto): ?MessageValidationVerdict
    {
        $text = $this->extractCheckableText($dto);
        if ($text === null) {
            return null;
        }

        if ($this->hasShortenerUrl($text)) {
            return MessageValidationVerdict::reject(
                action: $this->action,
                reason: 'suspicious link',
                matchedRule: 'url_shortener',
                priority: $this->rulePriority,
                restrictDuration: $this->restrictDuration,
            );
        }

        if ($this->hasUrlEntities($dto) && $this->hasPromotionalText($text)) {
            return MessageValidationVerdict::reject(
                action: $this->action,
                reason: 'link with promotional text',
                matchedRule: 'url_with_promotion',
                priority: $this->rulePriority,
                restrictDuration: $this->restrictDuration,
            );
        }

        if ($this->hasPrivateMessageInvitation($text) && $this->hasPromotionalText($text)) {
            return MessageValidationVerdict::reject(
                action: $this->action,
                reason: 'private message invitation with promotional text',
                matchedRule: 'pm_with_promotion',
                priority: $this->rulePriority,
                restrictDuration: $this->restrictDuration,
            );
        }

        return null;
    }

    private function extractCheckableText(MessageTypeDTO $dto): ?string
    {
        $parts = [];

        if ($dto->text !== null) {
            $parts[] = $dto->text;
        }

        if ($dto->caption !== null) {
            $parts[] = $dto->caption;
        }

        if ($dto->entities !== null) {
            foreach ($dto->entities as $entity) {
                if (in_array(
                    $entity->type,
                    [MessageEntityPropTypeEnum::URL, MessageEntityPropTypeEnum::TEXT_LINK],
                    true
                )
                    && $entity->url !== null) {
                    $parts[] = $entity->url;
                }
            }
        }

        if ($dto->captionEntities !== null) {
            foreach ($dto->captionEntities as $entity) {
                if (in_array(
                    $entity->type,
                    [MessageEntityPropTypeEnum::URL, MessageEntityPropTypeEnum::TEXT_LINK],
                    true
                )
                    && $entity->url !== null) {
                    $parts[] = $entity->url;
                }
            }
        }

        $combined = implode("\n", $parts);

        return $combined !== '' ? $combined : null;
    }

    private function hasShortenerUrl(string $text): bool
    {
        return @preg_match($this->getShortenerRegexp(), $text) === 1;
    }

    private function getShortenerRegexp(): string
    {
        if ($this->cachedShortenerRegexp === null) {
            $this->cachedShortenerRegexp = self::buildSuperRegexp($this->urlShortenerPatterns);
        }

        return $this->cachedShortenerRegexp;
    }

    private static function buildSuperRegexp(array $patterns): string
    {
        $alternatives = array_map(fn (string $p) => '(?:'.$p.')', $patterns);

        return '/'.implode('|', $alternatives).'/ui';
    }

    private function hasUrlEntities(MessageTypeDTO $dto): bool
    {
        $entities = array_merge(
            $dto->entities ?? [],
            $dto->captionEntities ?? [],
        );

        foreach ($entities as $entity) {
            if (in_array($entity->type, [
                MessageEntityPropTypeEnum::URL,
                MessageEntityPropTypeEnum::TEXT_LINK,
            ], true)) {
                return true;
            }
        }

        return false;
    }

    private function hasPrivateMessageInvitation(string $text): bool
    {
        return @preg_match($this->getPrivateMessageRegexp(), $text) === 1;
    }

    private function getPrivateMessageRegexp(): string
    {
        if ($this->cachedPrivateMessageRegexp === null) {
            $this->cachedPrivateMessageRegexp = self::buildSuperRegexp($this->privateMessagePatterns);
        }

        return $this->cachedPrivateMessageRegexp;
    }

    private function hasPromotionalText(string $text): bool
    {
        return @preg_match($this->getPromotionalRegexp(), $text) === 1;
    }

    private function getPromotionalRegexp(): string
    {
        if ($this->cachedPromotionalRegexp === null) {
            $this->cachedPromotionalRegexp = self::buildSuperRegexp($this->promotionalPatterns);
        }

        return $this->cachedPromotionalRegexp;
    }
}
