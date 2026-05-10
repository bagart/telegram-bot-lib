<?php

declare(strict_types=1);

namespace BAGArt\TelegramBot\ApiCommunication\Transport;

use BAGArt\TelegramBot\Exceptions\TgTransportException;
use CurlHandle;
use CurlMultiHandle;

final class TgCurlMultiHandle
{
    /**
     * @var array<int, CurlHandle>
     */
    private array $handles = [];

    private CurlMultiHandle $handle;

    private int $active = 0;

    private const SELECT_TIMEOUT = 0.1;

    public function __construct()
    {
        $this->handle = curl_multi_init();
    }

    public function add(
        TgCurlRequest $curlRequest,
    ): CurlHandle {
        $ch = curl_init();

        if (!$ch instanceof CurlHandle) {
            throw new TgTransportException('Failed to initialize curl handle.');
        }

        $method = strtoupper($curlRequest->getMethod());
        $url = $curlRequest->getUrl();

        // --- METHOD ---
        if ($method === 'GET') {
            $query = http_build_query($curlRequest->getQueryParams());
            if ($query !== '') {
                $url .= (str_contains($url, '?') ? '&' : '?') . $query;
            }

            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

            $body = $curlRequest->getBody();
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        // --- CORE OPTIONS ---
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,

            // Таймауты
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 30,

            // Сжатие
            CURLOPT_ENCODING => '',

            // Ошибки HTTP как ошибки curl
            CURLOPT_FAILONERROR => false,

            // Keepalive (очень помогает под нагрузкой)
            CURLOPT_TCP_KEEPALIVE => 1,
        ]);

        // --- REQUEST-LEVEL OVERRIDES ---
        $requestOptions = $curlRequest->getCurlOptions();
        if ($requestOptions !== []) {
            curl_setopt_array($ch, $requestOptions);
        }

        // --- HEADERS ---
        $headers = $curlRequest->getHeaders();

        if ($curlRequest->getBody() !== null && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        if (!empty($headers)) {
            $formatted = [];
            foreach ($headers as $k => $v) {
                $formatted[] = $k . ': ' . $v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $formatted);
        }

        // --- ADD HANDLE ---
        $result = curl_multi_add_handle($this->handle, $ch);
        if ($result !== CURLM_OK) {
            curl_close($ch);
            throw new TgTransportException(
                'Failed to add curl handle: ' . curl_multi_strerror($result)
            );
        }

        $chObjId = spl_object_id($ch);
        $this->handles[$chObjId] = $ch;

        return $ch;
    }

    public function remove(CurlHandle $ch): void
    {
        $id = spl_object_id($ch);

        if (!isset($this->handles[$id])) {
            return;
        }

        curl_multi_remove_handle($this->handle, $ch);
        unset($this->handles[$id]);
    }

    public function execute(int &$active): int
    {
        do {
            $status = curl_multi_exec($this->handle, $active);
            $this->active = $active;

            if ($status === CURLM_CALL_MULTI_PERFORM) {
                continue;
            }

            if ($active > 0) {
                $ready = curl_multi_select($this->handle, self::SELECT_TIMEOUT);

                // Иногда select возвращает -1 → надо поспать
                if ($ready === -1) {
                    usleep(1000);
                }
            }
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active > 0);

        return $status;
    }
    /**
     * @return list<CurlHandle>
     */
    public function readCompletedHandles(): array
    {
        $completed = [];

        while (true) {
            $info = curl_multi_info_read($this->handle);

            if ($info === false) {
                break;
            }

            if (($info['msg'] ?? null) === CURLMSG_DONE) {
                assert($info['handle'] instanceof CurlHandle);
                $completed[] = $info['handle'];
            }
        }

        return $completed;
    }

    public function hasActive(): bool
    {
        return $this->active > 0;
    }

    public function __destruct()
    {
        foreach ($this->handles as $ch) {
            try {
                @curl_multi_remove_handle($this->handle, $ch);
            } catch (Throwable) {
            }

            try {
                @curl_close($ch);
            } catch (Throwable) {
            }
        }

        $this->handles = [];

        try {
            curl_multi_close($this->handle);
        } catch (Throwable) {
        }
    }
}
