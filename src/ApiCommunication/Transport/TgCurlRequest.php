<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Transport;

class TgCurlRequest
{
    /**
     * @param  array<string, mixed>  $parameters
     * @param  array<string, string>  $headers
     */
    public function __construct(
        private readonly string $url,
        private readonly string $method = 'POST',
        private readonly array $parameters = [],
        private readonly array $headers = []
    ) {
    }

    /**
     * @var array<int, mixed>
     */
    private array $curlOptions = [];

    /**
     * @return $this
     */
    public function setCurlOption(int $option, mixed $value): static
    {
        $this->curlOptions[$option] = $value;

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function getCurlOptions(): array
    {
        return $this->curlOptions;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        if (strtoupper($this->method) === 'GET') {
            return null;
        }

        $data = $this->parameters;

        // For Telegram API, we typically send JSON for POST requests
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function getQueryParams(): array
    {
        return $this->parameters;
    }
}
